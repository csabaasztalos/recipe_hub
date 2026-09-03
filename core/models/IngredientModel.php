<?php
class IngredientModel
{
    private DBHandler $db;
    private ActivityLogModel $activityLog;

    public function __construct()
    {
        $this->db = Container::Get('db');
        $this->activityLog = Container::Get('activityLog');
    }


    public function GetIngredient($id): mysqli_result
    {
        return $this->db->RunQuery("SELECT `name` FROM `ingredients` WHERE id = ?",
            [new DBParam(DBTypes::INT, $id)]);
    }

    public function GetAllIngredients(): mysqli_result
    {
        return $this->db->RunQuery("SELECT * FROM `ingredients` ORDER BY name");
    }
    public function GetIngredientStatus($id): mysqli_result
    {
        return $this->db->RunQuery("SELECT `status` FROM `ingredients` WHERE id = ?", [new DBParam(DBTypes::INT, $id)]);
    }

    public function GetIngredientStatuses(): bool|mysqli_result
    {
        return $this->db->RunQuery("SELECT DISTINCT `status` FROM `ingredients`");
    }

    public function GetRecipeIngredients($recipeId): mysqli_result
    {
        return $this->db->RunQuery(
            "SELECT 
            ri.recipe_id AS recipe_id,
            ri.ingredient_id AS id,
            ri.quantity AS quantity,
            ri.unit AS unit,
            i.name AS name,
            i.status AS ingredient_status
        FROM `recipe_ingredients` ri
        JOIN `ingredients` i ON ri.ingredient_id = i.id
        WHERE ri.recipe_id = ?",
            [new DBParam(DBTypes::INT, $recipeId)]
        );
    }

    public function GetIngredientByName($name): mysqli_result
    {
        return $this->db->RunQuery("SELECT * FROM `ingredients` WHERE name = ?",
            [new DBParam(DBTypes::STRING, $name)]);
    }

    public function GetIngredientName(int $id): mysqli_result
    {
        return $this->db->RunQuery("SELECT `name` FROM `ingredients` WHERE id = ?",
            [new DBParam(DBTypes::INT, $id)]);
    }

    public function GetIngredientBySource(string $source, ?string $status): mysqli_result
    {
        if ($status) {
            $result = $this->db->RunQuery("SELECT * FROM `ingredients` WHERE source = ? AND status = ? ORDER BY name", [new DBParam(DBTypes::STRING, $source), new DBParam(DBTypes::STRING, $status)]);
        } else {
            $result = $this->db->RunQuery("SELECT * FROM `ingredients` WHERE source = ? ORDER BY name", [new DBParam(DBTypes::STRING, $source)]);
        }
        return $result;
    }

    public function GetFilteredIngredients(string $keyword, string $status, string $dateFrom, string $dateTo): mysqli_result
    {
        $where = [];
        $params = [];

        // Keyword filter (search in title and username)
        if ($keyword !== null && $keyword !== '') {
            $where[] = "(ingredients.name LIKE ? OR users.username LIKE ?)";
            $params[] = new DBParam(DBTypes::STRING, '%'.$keyword.'%');
            $params[] = new DBParam(DBTypes::STRING, '%'.$keyword.'%');
        }

        // Status filter
        if ($status !== null && $status !== '') {
            $where[] = "ingredients.status = ?";
            $params[] = new DBParam(DBTypes::STRING, $status);
        }

        // SubmittedAt filter (date or date range)
        if ($dateFrom !== null && $dateFrom !== '' && $dateTo !== null && $dateTo !== '') {
            $where[] = "DATE(ingredients.submitted_at) BETWEEN ? AND ?";
            $params[] = new DBParam(DBTypes::STRING, $dateFrom);
            $params[] = new DBParam(DBTypes::STRING, $dateTo);
        } elseif ($dateFrom !== null && $dateFrom !== '') {
            $where[] = "DATE(ingredients.submitted_at) >= ?";
            $params[] = new DBParam(DBTypes::STRING, $dateFrom);
        } elseif ($dateTo !== null && $dateTo !== '') {
            $where[] = "DATE(ingredients.submitted_at) <= ?";
            $params[] = new DBParam(DBTypes::STRING, $dateTo);
        }

        $whereSql = '';
        if (! empty($where)) {
            $whereSql = 'WHERE '.implode(' AND ', $where);
        }

        $sql = "SELECT ingredients.*, users.username
            FROM ingredients
            LEFT JOIN users ON ingredients.submitted_by = users.id
            $whereSql
            ORDER BY ingredients.id DESC";

        return $this->db->RunQuery($sql, $params);
    }

    public function UpdateIngredientStatus(int $id, string $status, ?int $submitted_by): bool
    {
        global $cfg;

        if (in_array($status, $cfg['statuses'])) {
            $sql = "`status` = ?";
            if ($status === "approved") {
                $sql .= ", `approved_at` = NOW()";
            }

            $result = $this->db->RunQuery("UPDATE `ingredients` SET $sql WHERE id=?", [
                new DBParam(DBTypes::STRING, $status),
                new DBParam(DBTypes::INT, $id)]);
        } else {
            $result = false;
        }

        if ($result) {
            if ($submitted_by === null) {
                $submitted_by = $cfg['adminUserID'];
            }
            $this->activityLog->ActivityLog("ingredients", $id,
                "status changed", $submitted_by, "Ingredient ".$status);
            return true;
        }
        return false;
    }

    public function UpdateIngredient(int $id, string $name, ?int $submitted_by, ?string $description): bool
    {
        if ($submitted_by == null) {
            $submitted_by = 1;
        }

        if ($description == null) {
            $description = "ingredient updated";
        }

        $result = $this->db->RunQuery("UPDATE `ingredients` SET name = ? WHERE id = ?",
            [
                new DBParam(DBTypes::STRING, $name),
                new DBParam(DBTypes::INT, $id)
            ]);

        if ($result) {
            $this->activityLog->ActivityLog("ingredients", $id,
                'updated', $submitted_by, $description);
            return true;
        }
        return false;
    }

    public function BatchImport(array $data): bool|mysqli_result
    {
        global $cfg;
        if (! empty($data)) {
            $placeholders = [];
            $params = [];

            $submitted_by = $cfg['adminUserID'];
            $status = "staging";
            $source = "api";

            foreach ($data as $ing) {
                $placeholders[] = "(?, ?, ?, ?)";
                $params[] = new DBParam(DBTypes::STRING, $ing);
                $params[] = new DBParam(DBTypes::INT, $submitted_by);
                $params[] = new DBParam(DBTypes::STRING, $status);
                $params[] = new DBParam(DBTypes::STRING, $source);
            }

            $sql = "INSERT IGNORE INTO `ingredients` (`name`, `submitted_by`, `status`, `source`) VALUES ".implode(',', $placeholders).";";

            return $this->db->RunQuery($sql, $params);
        }

        return false;
    }

    public function AddRecipeIngredient(int $recipeId, int $ingredientId, int $quantity, string $unit): bool
    {

        $result = $this->db->RunQuery("INSERT INTO `recipe_ingredients` (recipe_id, ingredient_id, quantity, unit) VALUES(?, ?, ?, ?)",
            [
                new DBParam(DBTypes::INT, $recipeId),
                new DBParam(DBTypes::INT, $ingredientId),
                new DBParam(DBTypes::INT, $quantity),
                new DBParam(DBTypes::STRING, $unit)
            ]);

        if ($result === true) {
            return true;
        }
        return false;
    }

    public function CreateIngredient(string $name, int $submitted_by, string $status, string $source = "manual", string $description = ''): bool
    {
        $result = $this->db->RunQuery("INSERT INTO `ingredients` (`name`, `submitted_by`, `status`, `source`) VALUES (?, ?, ?, ?); ",
            [
                new DBParam(DBTypes::STRING, $name),
                new DBParam(DBTypes::INT, $submitted_by),
                new DBParam(DBTypes::STRING, $status),
                new DBParam(DBTypes::STRING, $source)
            ]);


        if ($result) {
            $id = $this->db->GetLastInsertId();
            $this->activityLog->ActivityLog("ingredients", $id, 'created', $submitted_by, $description);
            return true;
        }
        return false;
    }

    public function DeleteIngredient(string|array $ids, ?int $submitted_by, string $description = ''): array
    {
        global $cfg;
        if ($submitted_by === null) {
            $submitted_by = $cfg["adminUserID"];
        }

        if (is_string($ids) && strpos($ids, ';') !== false) {
            $ids = explode(';', $ids);
        }

        if (! is_array($ids)) {
            $ids = [$ids];
        }

        $failed = [];
        $deleted = [];

        foreach ($ids as $id) {
            $id = (int) $id;

            if ($id <= 0) {
                continue;
            }

            $result = $this->db->RunQuery(
                "DELETE FROM `ingredients` WHERE id = ?",
                [new DBParam(DBTypes::INT, $id)]
            );

            if ($result) {
                $deleted[] = $id;
            } else {
                $failed[] = $id;
            }
        }

        if (! empty($deleted)) {
            $recordId = count($deleted) === 1 ? $deleted[0] : "bulk:".count($deleted);
            $this->activityLog->ActivityLog("categories", $recordId, "deleted", $submitted_by, $description);
            Logger::Log("Deleted ".count($deleted)." records from ingredient table.", logLvl::Info);
            Logger::Log("Deleted records:\n".implode(", ", $deleted), logLvl::Info);
        }

        return $failed;
    }

    public function DeleteRecipeIngredients(int $recipeId, $ingredientsIds): bool
    {
        if (is_string($ingredientsIds) && strpos($ingredientsIds, ';') !== false) {
            $ingredientsIds = explode(';', $ingredientsIds);
        }

        if (! is_array($ingredientsIds)) {
            $ingredientsIds = [$ingredientsIds];
        }

        $allSuccessful = true;

        foreach ($ingredientsIds as $ingredientsId) {
            $ingredient = (int) $ingredientsId;

            $result = $this->db->RunQuery(
                "DELETE FROM `recipe_ingredients` WHERE recipe_id = ? AND ingredient_id = ?",
                [new DBParam(DBTypes::INT, $recipeId),
                    new DBParam(DBTypes::INT, $ingredient)]
            );
        }
        if (! $result) {
            $allSuccessful = false;
        }
        return $allSuccessful;
    }
}
