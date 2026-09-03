<?php
class CategoriesModel
{
    private DBHandler $db;
    private ActivityLogModel $activityLog;

    public function __construct()
    {
        $this->db = Container::Get('db');
        $this->activityLog = Container::Get('activityLog');
    }

    public function GetCategory($id): mysqli_result
    {
        return $this->db->RunQuery("SELECT * FROM `categories` WHERE id = ?",
            [new DBParam(DBTypes::INT, $id)]);
    }

    public function GetCategoryByName($name): mysqli_result
    {
        return $this->db->RunQuery("SELECT * FROM `categories` WHERE name = ?",
            [new DBParam(DBTypes::STRING, $name)]);
    }

    public function GetAllCategories(): mysqli_result
    {
        return $this->db->RunQuery("SELECT * FROM `categories` ORDER BY name");
    }

    public function GetCategoriesByUser($id): mysqli_result
    {
        return $this->db->RunQuery("SELECT * FROM `categories` WHERE submitted_by = ?", [new DBParam(DBTypes::INT, $id)]);
    }

    public function GetCategoriesByStatus($status): mysqli_result
    {
        return $this->db->RunQuery("SELECT * FROM `categories` WHERE status = ? ORDER BY name", [new DBParam(DBTypes::STRING, $status)]);
    }

    public function GetCategoriesBySource(string $source, ?string $status): mysqli_result
    {
        if ($status) {
            $result = $this->db->RunQuery("SELECT * FROM `categories` WHERE source = ? AND status = ? ORDER BY name", [new DBParam(DBTypes::STRING, $source), new DBParam(DBTypes::STRING, $status)]);
        } else {
            $result = $this->db->RunQuery("SELECT * FROM `categories` WHERE source = ? ORDER BY name", [new DBParam(DBTypes::STRING, $source)]);
        }
        return $result;
    }

    public function GetRecipeCategories($recipeId): mysqli_result
    {
        return $this->db->RunQuery("SELECT c.* FROM categories c JOIN recipes_categories rc ON c.id = rc.category_id WHERE rc.recipe_id = ? ORDER BY c.name",
            [new DBParam(DBTypes::INT, $recipeId)]
        );
    }

    public function GetCategoryStatus($id): mysqli_result
    {
        return $this->db->RunQuery("SELECT `status` FROM `categories` WHERE id = ?", [new DBParam(DBTypes::INT, $id)]);
    }

    public function GetCategoryStatuses(): mysqli_result
    {
        return $this->db->RunQuery("SELECT DISTINCT `status` FROM `categories`");
    }

    public function GetFilteredCategories(string $keyword, string $status, string $dateFrom, string $dateTo): mysqli_result
    {
        $where = [];
        $params = [];

        // Keyword filter (search in title and username)
        if ($keyword !== null && $keyword !== '') {
            $where[] = "(categories.name LIKE ? OR users.username LIKE ?)";
            $params[] = new DBParam(DBTypes::STRING, '%'.$keyword.'%');
            $params[] = new DBParam(DBTypes::STRING, '%'.$keyword.'%');
        }

        // Status filter
        if ($status !== null && $status !== '') {
            $where[] = "categories.status = ?";
            $params[] = new DBParam(DBTypes::STRING, $status);
        }

        // SubmittedAt filter (date or date range)
        if ($dateFrom !== null && $dateFrom !== '' && $dateTo !== null && $dateTo !== '') {
            $where[] = "DATE(categories.submitted_at) BETWEEN ? AND ?";
            $params[] = new DBParam(DBTypes::STRING, $dateFrom);
            $params[] = new DBParam(DBTypes::STRING, $dateTo);
        } elseif ($dateFrom !== null && $dateFrom !== '') {
            $where[] = "DATE(categories.submitted_at) >= ?";
            $params[] = new DBParam(DBTypes::STRING, $dateFrom);
        } elseif ($dateTo !== null && $dateTo !== '') {
            $where[] = "DATE(categories.submitted_at) <= ?";
            $params[] = new DBParam(DBTypes::STRING, $dateTo);
        }

        $whereSql = '';
        if (! empty($where)) {
            $whereSql = 'WHERE '.implode(' AND ', $where);
        }

        $sql = "SELECT categories.*, users.username
            FROM categories
            LEFT JOIN users ON categories.submitted_by = users.id
            $whereSql
            ORDER BY categories.id DESC";

        return $this->db->RunQuery($sql, $params);
    }

    public function AddRecipeCategory(int $recipeId, string|array $categoryIds): bool
    {
        // If it's a string, convert to array
        if (! is_array($categoryIds)) {
            $categoryIds = explode(";", $categoryIds);
        }

        foreach ($categoryIds as $categoryId) {
            if (! empty($categoryId)) {
                try {
                    $this->db->RunQuery(
                        "INSERT INTO recipes_categories (recipe_id, category_id) VALUES (?, ?)",
                        [new DBParam(DBTypes::INT, $recipeId), new DBParam(DBTypes::INT, $categoryId)]
                    );
                } catch (Exception) {
                    return false;
                }
            }
        }
        return true;
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

            foreach ($data as $cat) {
                $placeholders[] = "(?, ?, ?, ?)";
                $params[] = new DBParam(DBTypes::STRING, $cat);
                $params[] = new DBParam(DBTypes::INT, $submitted_by);
                $params[] = new DBParam(DBTypes::STRING, $status);
                $params[] = new DBParam(DBTypes::STRING, $source);
            }

            $sql = "INSERT IGNORE INTO `categories` (`name`, `submitted_by`, `status`, `source`) VALUES ".implode(',', $placeholders).";";
            
            return $this->db->RunQuery($sql, $params);
        }
        
        return false;
    }

    public function CreateCategory(string $name, int $submittedBy, string $status, string $source, string $description = ''): bool
    {
        $result = $this->db->RunQuery("INSERT INTO `categories` (`name`, `submitted_by`, `status`, `source`) VALUES (?, ?, ?, ?); ",
            [
                new DBParam(DBTypes::STRING, $name),
                new DBParam(DBTypes::INT, $submittedBy),
                new DBParam(DBTypes::STRING, $status),
                new DBParam(DBTypes::STRING, $source),
            ]);

        if ($result) {
            $id = $this->db->GetLastInsertId();
            $this->activityLog->ActivityLog("categories", $id, "created", $submittedBy, $description);
            return true;
        }
        return false;
    }

    public function UpdateCategoryStatus($id, $status): bool
    {
        global $cfg;

        if (in_array($status, $cfg['statuses'])) {
            $result = $this->db->RunQuery("UPDATE `categories` SET `status` = ? WHERE id=?", [
                new DBParam(DBTypes::STRING, $status),
                new DBParam(DBTypes::INT, $id)]);
        } else {
            $result = false;
        }

        if ($result) {
            $action = $status == 'pending' ? 'UPDATED' : $status;
            $this->activityLog->ActivityLog("recipes", $id, $action, '1', "Category ".$status);
            return true;
        }
        return false;
    }

    public function UpdateCategory(int $id, string $name, ?int $submitted_by, ?string $description): bool
    {
        if ($submitted_by == null) {
            $submitted_by = 1;
        }

        if ($description == null) {
            $description = "category updated";
        }

        $result = $this->db->RunQuery("UPDATE `categories` SET name = ? WHERE id = ?", [new DBParam(DBTypes::STRING, $name), new DBParam(DBTypes::INT, $id)]);
        if ($result) {
            $this->activityLog->ActivityLog("categories", $id, "updated", $submitted_by, $description);
            return true;
        }
        return false;
    }

    public function DeleteCategory(string|array $ids, ?int $submitted_by, string $description = ''): array
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
                "DELETE FROM `categories` WHERE id = ?",
                [new DBParam(DBTypes::INT, $id)]
            );

            if ($result) {
                $deleted[] = $id;
            } else {
                $failed[] = $id;
            }
        }

        if (!empty($deleted)) {
            $recordId = count($deleted) === 1 ? $deleted[0] : "bulk:".count($deleted);
            $this->activityLog->ActivityLog("categories", $recordId, "deleted", $submitted_by, $description);
            Logger::Log("Deleted ". count($deleted). " records from category table.", logLvl::Info);
            Logger::Log("Deleted records:\n". implode(", ", $deleted), logLvl::Info);
        }

        return $failed;
    }

    public function DeleteRecipeCategory(int $recipe_id, string|array $category_ids): bool
    {
        if (is_string($category_ids) && strpos($category_ids, ';') !== false) {
            $category_ids = explode(';', $category_ids);
        }

        if (! is_array($category_ids)) {
            $category_ids = [$category_ids];
        }

        $allSuccessful = true;

        foreach ($category_ids as $catId) {
            $catIds = (int) $catId;

            $result = $this->db->RunQuery("DELETE FROM `recipes_categories` WHERE recipe_id = ? AND category_id = ?",
                [new DBParam(DBTypes::INT, $recipe_id),
                    new DBParam(DBTypes::INT, $catIds)]);
        }
        if (! $result) {
            $allSuccessful = false;
        }
        return $allSuccessful;
    }
}
