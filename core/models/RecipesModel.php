<?php
class RecipesModel
{
    private DBHandler $db;
    private ActivityLogModel $activityLog;

    public function __construct()
    {
        $this->db = Container::Get('db');
        $this->activityLog = Container::Get('activityLog');
    }

    public function GetRecipe($id): mysqli_result
    {
        return $this->db->RunQuery("SELECT * FROM `recipes` WHERE id = ?",
            [new DBParam(DBTypes::INT, $id)]);
    }

    public function GetAllRecipes(string $sortOrder = "DESC"): mysqli_result
    {
        $sortOrder = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';
        return $this->db->RunQuery("SELECT * FROM `recipes` ORDER BY id {$sortOrder}");
    }

    public function GetAllRecipesWithUsernames(): mysqli_result
    {
        return $this->db->RunQuery(
            "SELECT recipes.*, users.username
         FROM recipes
         JOIN users ON recipes.user_id = users.id
         ORDER BY recipes.id DESC"
        );
    }

    public function GetRecipeWithUsername(int $id): mysqli_result
    {
        return $this->db->RunQuery(
            "SELECT recipes.*, users.username
         FROM recipes
         JOIN users ON recipes.user_id = users.id
         WHERE recipes.id = ".(int) $id
        );
    }

    public function AdminRecipeFilter(?string $keyword, ?int $category, ?string $status, ?string $dateFrom, ?string $dateTo): mysqli_result
    {
        $where = [];
        $params = [];

        // Keyword filter (search in title and username)
        if ($keyword !== null && $keyword !== '') {
            $where[] = "(recipes.title LIKE ? OR users.username LIKE ?)";
            $params[] = new DBParam(DBTypes::STRING, '%'.$keyword.'%');
            $params[] = new DBParam(DBTypes::STRING, '%'.$keyword.'%');
        }

        // Category filter
        if ($category !== null && $category !== '') {
            $where[] = "categories.id = ?";
            $params[] = new DBParam(DBTypes::INT, $category);
        }

        // Status filter
        if ($status !== null && $status !== '') {
            $where[] = "recipes.status = ?";
            $params[] = new DBParam(DBTypes::STRING, $status);
        }

        // SubmittedAt filter (date or date range)
        if ($dateFrom !== null && $dateFrom !== '' && $dateTo !== null && $dateTo !== '') {
            $where[] = "DATE(recipes.created_at) BETWEEN ? AND ?";
            $params[] = new DBParam(DBTypes::STRING, $dateFrom);
            $params[] = new DBParam(DBTypes::STRING, $dateTo);
        } elseif ($dateFrom !== null && $dateFrom !== '') {
            $where[] = "DATE(recipes.created_at) >= ?";
            $params[] = new DBParam(DBTypes::STRING, $dateFrom);
        } elseif ($dateTo !== null && $dateTo !== '') {
            $where[] = "DATE(recipes.created_at) <= ?";
            $params[] = new DBParam(DBTypes::STRING, $dateTo);
        }

        $whereSql = '';
        if (! empty($where)) {
            $whereSql = 'WHERE '.implode(' AND ', $where);
        }

        $sql = "SELECT DISTINCT recipes.*, users.username
            FROM recipes
            JOIN users ON recipes.user_id = users.id
            LEFT JOIN recipes_categories ON recipes.id = recipes_categories.recipe_id
            LEFT JOIN categories ON recipes_categories.category_id = categories.id
            $whereSql
            ORDER BY recipes.id DESC";

        return $this->db->RunQuery($sql, $params);
    }

    public function MainRecipeFilter(
        string $keyword = null, int $category = null, $tags = null,
        string $relation = null, int $limit = null, string $order = null,
        int $userID = null, int $recipeID = null, string $sortBy = 'id'): bool|mysqli_result
    {
        $where = [];
        $params = [];
        $joins = "JOIN users ON recipes.user_id = users.id";
        $select = "recipes.*, users.username";
        $orderDirection = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';
        $limitSql = $limit ? "LIMIT ".(int) $limit : "";

        // Keyword filter
        if ($keyword !== null && $keyword !== '') {
            $where[] = "(recipes.title LIKE ? OR recipes.description LIKE ?)";
            $params[] = new DBParam(DBTypes::STRING, "%$keyword%");
            $params[] = new DBParam(DBTypes::STRING, "%$keyword%");
        }

        // Category filter
        if ($category !== null && $category !== '') {
            $joins .= " LEFT JOIN recipes_categories ON recipes.id = recipes_categories.recipe_id
                    LEFT JOIN categories ON recipes_categories.category_id = categories.id";
            $where[] = "categories.id = ?";
            $params[] = new DBParam(DBTypes::INT, $category);
        }

        // Tag filter (by tag IDs)
        if ($tags !== null && is_array($tags) && ! empty($tags[0])) {
            $i = 0;
            foreach ($tags as $tagId) {
                $alias = "rt$i";
                $joins .= " INNER JOIN recipe_tags $alias ON recipes.id = $alias.recipe_id AND $alias.tag_id = ?";
                $params[] = new DBParam(DBTypes::INT, $tagId);
                $i++;
            }
        }

        // Relation filter (bookmark/favourite)
        if ($relation !== null && $relation !== '') {
            $joins .= " LEFT JOIN user_recipe_relations ON recipes.id = user_recipe_relations.recipe_id";
            $where[] = "user_recipe_relations.relation_type = ?";
            $params[] = new DBParam(DBTypes::STRING, $relation);

            if ($userID !== null) {
                $where[] = "user_recipe_relations.user_id = ?";
                $params[] = new DBParam(DBTypes::INT, $userID);
            }
        } elseif ($userID !== null) {
            $where[] = "recipes.user_id = ?";
            $params[] = new DBParam(DBTypes::INT, $userID);
        }

        // Recipe ID filter
        if ($recipeID !== null) {
            $where[] = "recipes.id = ?";
            $params[] = new DBParam(DBTypes::INT, $recipeID);
        }

        // Only approved recipes by default
        $where[] = "recipes.status = 'approved'";

        // Sorting
        switch ($sortBy) {
            case 'likes':
                $joins .= " LEFT JOIN (
                SELECT recipe_id, COUNT(*) as likes_count 
                FROM user_recipe_relations 
                WHERE relation_type = 'favourite'
                GROUP BY recipe_id
            ) likes ON recipes.id = likes.recipe_id";
                $select .= ", IFNULL(likes.likes_count, 0) as likes_count";
                $orderBy = "ORDER BY likes_count $orderDirection, recipes.id DESC";
                break;
            case 'bookmarks':
                $joins .= " LEFT JOIN (
                SELECT recipe_id, COUNT(*) as bookmarks_count 
                FROM user_recipe_relations 
                WHERE relation_type = 'bookmark'
                GROUP BY recipe_id
            ) bookmarks ON recipes.id = bookmarks.recipe_id";
                $select .= ", IFNULL(bookmarks.bookmarks_count, 0) as bookmarks_count";
                $orderBy = "ORDER BY bookmarks_count $orderDirection, recipes.id DESC";
                break;
            case 'date':
                $orderBy = "ORDER BY recipes.created_at $orderDirection";
                break;
            case 'id':
            default:
                $orderBy = "ORDER BY recipes.id $orderDirection";
                break;
        }

        $whereSql = 'WHERE '.implode(' AND ', $where);

        // DISTINCT to avoid duplicates from joins
        $sql = "SELECT DISTINCT $select FROM recipes $joins $whereSql $orderBy $limitSql";

        return $this->db->RunQuery($sql, $params);
    }


    public function GetLatestRecipes(?int $limit): mysqli_result
    {
        $sql = "SELECT * FROM `recipes` WHERE `status` = 'approved' ORDER BY id DESC";
        $params = [];

        if ($limit !== null) {
            $sql .= " LIMIT ?";
            $params[] = new DBParam(DBTypes::INT, $limit);
        }

        return $this->db->RunQuery($sql, $params);
    }

    public function GetRecipesByStatus(string $status): mysqli_result
    {
        return $this->db->RunQuery(
            "SELECT recipes.*, users.username FROM `recipes` JOIN `users` ON recipes.user_id = users.id WHERE recipes.status = ? ORDER BY recipes.title",
            [new DBParam(DBTypes::STRING, $status)]
        );
    }

    public function GetRecipeCountsByCategory(?int $limit): mysqli_result
    {
        $sql = "SELECT categories.name AS category_name, COUNT(recipes.id) AS recipe_count
         FROM categories
         LEFT JOIN recipes_categories ON categories.id = recipes_categories.category_id
         LEFT JOIN recipes ON recipes_categories.recipe_id = recipes.id
         GROUP BY categories.id
         ORDER BY recipe_count DESC";

        if ($limit !== null) {
            $sql .= " LIMIT ?";
            $params[] = new DBParam(DBTypes::INT, $limit);
        }

        return $this->db->RunQuery($sql, $params);
    }

    public function GetUploadsCountPerUser(): mysqli_result
    {
        return $this->db->RunQuery(
            "SELECT u.id AS user_id, u.username,
            COUNT(r.id) AS recipe_count
            FROM users u
            LEFT JOIN recipes r ON r.user_id = u.id
            GROUP BY u.id, u.username
            ORDER BY recipe_count DESC
            LIMIT 5;"
        );
    }

    public function GetTopRecipesByRelation(string $relationType, int $limit = 5): mysqli_result
    {
        return $this->db->RunQuery(
            "SELECT r.id AS recipe_id, r.title, u.username,
            COUNT(urr.recipe_id) AS relation_count
            FROM recipes r
            JOIN users u ON r.user_id = u.id
            JOIN user_recipe_relations urr ON urr.recipe_id = r.id
            WHERE urr.relation_type = ?
            AND r.status = 'approved'
            GROUP BY r.id, r.title, u.username
            ORDER BY relation_count DESC
            LIMIT ?",
            [
                new DBParam(DBTypes::STRING, $relationType),
                new DBParam(DBTypes::INT, $limit)
            ]
        );
    }

    public function GetRecipeStatuses(): mysqli_result
    {
        return $this->db->RunQuery("SELECT DISTINCT `status` FROM `recipes`");
    }

    public function GetRecipeStatus(int $id): mysqli_result
    {
        return $this->db->RunQuery("SELECT `status` FROM `recipes` WHERE `id` = ?", [new DBParam(DBTypes::INT, $id)]);
    }

    public function GetRecipesImages(int $id): mysqli_result
    {
        return $this->db->RunQuery("SELECT * FROM `recipe_images` WHERE `recipe_id` = ?", [new DBParam(DBTypes::INT, $id)]);
    }

    public function GetRecipesByUser(int $userId): mysqli_result
    {
        return $this->db->RunQuery("SELECT * FROM `recipes` WHERE `user_id` = ?", [new DBParam(DBTypes::INT, $userId)]);
    }

    public function GetRecipesWithRealtionByUser(int $userId, string $relationType): mysqli_result|bool
    {
        return $this->db->RunQuery("SELECT
                recipes.id,
                recipes.title,
                recipes.instructions,
                recipes.description,
                recipes.user_id AS recipe_owner_id,
                recipes.created_at AS recipe_created_at,
                recipes.status,
                user_recipe_relations.user_id AS relation_user_id,
                user_recipe_relations.relation_type,
                user_recipe_relations.created_at AS relation_created_at
                FROM user_recipe_relations
                INNER JOIN recipes ON recipes.id = user_recipe_relations.recipe_id
                WHERE user_recipe_relations.user_id = ?
                AND user_recipe_relations.relation_type = ?",
            [
                new DBParam(DBTypes::INT, $userId),
                new DBParam(DBTypes::STRING, $relationType)
            ]);
    }

    public function GetMarkedRecipesByUser(int $userId): mysqli_result
    {
        return $this->db->RunQuery("SELECT `recipe_id` FROM `user_recipe_relations` WHERE `user_id` = ? AND `relation_type` = 'bookmark'", [new DBParam(DBTypes::INT, $userId)]);
    }

    public function GetRelationCountByRelation(string $relationType, int $recipeId): int
    {
        $result = $this->db->RunQuery(
            "SELECT COUNT(*) AS cnt FROM `user_recipe_relations` WHERE `recipe_id` = ? AND `relation_type` = ?",
            [
                new DBParam(DBTypes::INT, $recipeId),
                new DBParam(DBTypes::STRING, $relationType)
            ]
        );
        if ($result && $row = $result->fetch_assoc()) {
            return (int) $row['cnt'];
        }
        return 0;
    }

    public function GetRecipesByRelationCount(string $relationType, int $limit, string $sortOrder): mysqli_result
    {
        $limitSql = $limit > 0 ? "LIMIT ".(int) $limit : "";

        $sortOrder = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';

        return $this->db->RunQuery(
            "SELECT
        recipes.id,
        recipes.title,
        recipes.instructions,
        recipes.description,
        recipes.user_id,
        recipes.created_at,
        recipes.status,
        COUNT(user_recipe_relations.recipe_id) AS relation_count
        FROM recipes
        LEFT JOIN user_recipe_relations ON recipes.id = user_recipe_relations.recipe_id 
            AND user_recipe_relations.relation_type = ?
        WHERE recipes.status = 'approved'
        GROUP BY recipes.id
        ORDER BY relation_count $sortOrder
        $limitSql",
            [new DBParam(DBTypes::STRING, $relationType)]
        );
    }

    public function CreateRecipe(string $title, string $instructions, string $description, int $user_id, int $submitted_by, string $activityDescription = '', int $servings = 1): int|false
    {
        $result = $this->db->RunQuery("INSERT INTO `recipes` (`title`, `instructions`, `description`, `serving_size`, `user_id`) VALUES (?, ?, ?, ?, ?); ",
            [
                new DBParam(DBTypes::STRING, $title),
                new DBParam(DBTypes::STRING, $instructions),
                new DBParam(DBTypes::STRING, $description),
                new DBParam(DBTypes::INT, $servings),
                new DBParam(DBTypes::INT, $user_id)
            ]);

        if ($result) {
            $id = $this->db->GetLastInsertId();
            $this->activityLog->ActivityLog("recipes", $id, "created", $submitted_by, $activityDescription);
            return $id;
        }
        return false;
    }

    public function ToggleRelation($userId, $recipeId, $relationType): bool
    {
        $relationExists = $this->db->RunQuery(
            "SELECT * FROM `user_recipe_relations` WHERE `user_id` = ? AND `recipe_id` = ? AND `relation_type` = ?",
            [
                new DBParam(DBTypes::INT, $userId),
                new DBParam(DBTypes::INT, $recipeId),
                new DBParam(DBTypes::STRING, $relationType)

            ]
        );

        if ($relationExists && $relationExists->num_rows > 0) {
            $result = $this->db->RunQuery(
                "DELETE FROM `user_recipe_relations` WHERE `user_id` = ? AND `recipe_id` = ? AND `relation_type` = ?",
                [
                    new DBParam(DBTypes::INT, $userId),
                    new DBParam(DBTypes::INT, $recipeId),
                    new DBParam(DBTypes::STRING, $relationType)
                ]
            );
            $action = "";
            $desc = "";
            if ($relationType === "bookmark") {
                $action = "unbookmarked";
                $desc = "Recipe unbookmarked by user";
            } elseif ($relationType === "favourite") {
                $action = "unliked";
                $desc = "Recipe remmoved from favourites by user";
            }
        } else {
            $result = $this->db->RunQuery(
                "INSERT INTO `user_recipe_relations` (`user_id`, `recipe_id`, `relation_type`) VALUES (?, ?, ?)",
                [
                    new DBParam(DBTypes::INT, $userId),
                    new DBParam(DBTypes::INT, $recipeId),
                    new DBParam(DBTypes::STRING, $relationType)
                ]
            );
            $action = "";
            $desc = "";
            if ($relationType === "bookmark") {
                $action = "bookmarked";
                $desc = "Recipe bookmarked by user";
            } elseif ($relationType === "favourite") {
                $action = "liked";
                $desc = "Recipe added to favourites by user";
            }
        }
        if ($result) {
            $this->activityLog->ActivityLog(
                "user_recipe_relations",
                $recipeId,
                $action,
                $userId,
                $desc
            );
            return true;
        }
        return false;
    }

    public function AddRecipeImages(int $recipe_id, string $mainImagePath, ?string $extraImagePath): int|false
    {
        if ($mainImagePath !== null && $extraImagePath !== null) {
            $result = $this->db->RunQuery("INSERT INTO `recipe_images` (`recipe_id`, `main_image`, `extra_images`) VALUES (?, ?, ?); ",
                [
                    new DBParam(DBTypes::INT, $recipe_id),
                    new DBParam(DBTypes::STRING, $mainImagePath),
                    new DBParam(DBTypes::STRING, $extraImagePath)
                ]);
        } elseif ($mainImagePath !== null) {
            $result = $this->db->RunQuery("INSERT INTO `recipe_images` (`recipe_id`, `main_image`) VALUES (?, ?); ",
                [
                    new DBParam(DBTypes::INT, $recipe_id),
                    new DBParam(DBTypes::STRING, $mainImagePath)
                ]);
        }

        if ($result) {
            return $this->db->GetLastInsertId();
        }
        return false;
    }

    public function UpdateRecipeImages(int $recipe_id, ?string $mainImagePath, ?string $extraImagePath): int|false
    {
        if ($mainImagePath !== null && $extraImagePath !== null) {
            $result = $this->db->RunQuery("UPDATE `recipe_images` SET `main_image` = ?, `extra_images` = ? WHERE `recipe_id` = ?;",
                [
                    new DBParam(DBTypes::STRING, $mainImagePath),
                    new DBParam(DBTypes::STRING, $extraImagePath),
                    new DBParam(DBTypes::INT, $recipe_id)
                ]);
        } elseif ($mainImagePath !== null) {
            $result = $this->db->RunQuery("UPDATE `recipe_images` SET `main_image` = ? WHERE `recipe_id` = ?;",
                [
                    new DBParam(DBTypes::STRING, $mainImagePath),
                    new DBParam(DBTypes::INT, $recipe_id)
                ]);
        } elseif ($extraImagePath !== null) {
            $result = $this->db->RunQuery("UPDATE `recipe_images` SET `extra_images` = ? WHERE `recipe_id` = ?;",
                [
                    new DBParam(DBTypes::STRING, $extraImagePath),
                    new DBParam(DBTypes::INT, $recipe_id)
                ]);
        }

        if ($result) {
            return $this->db->GetLastInsertId();
        }
        return false;
    }

    public function UpdateRecipeStatus($id, $status, ?int $submittedBy, string $description = "")
    {

        global $cfg;
        $setSql = "status = ?";
        $params = [new DBParam(DBTypes::STRING, $status)];

        if ($status === 'approved') {
            $setSql .= ", approved_at = NOW()";
        }

        $params[] = new DBParam(DBTypes::INT, $id);

        $result = $this->db->RunQuery("UPDATE `recipes` SET $setSql WHERE id = ?", $params);
        if ($result) {
            if ($submittedBy === null) {
                $submittedBy = $cfg["adminUserID"];
            }
            $this->activityLog->ActivityLog("recipes", $id, "status updated", $submittedBy, $description);
            return true;
        }
        return false;
    }

    private function deleteRecipeImagesFromDisk(int $id)
    {
        $dir = __DIR__."/../content/recipe_images/$id";
        if (is_dir($dir)) {
            $files = glob("$dir/*");
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            rmdir($dir);
        }
    }

    public function DeleteRecipe(int $id, ?int $submitted_by = null, string $description = ''): bool
    {
        global $cfg;
        if ($submitted_by === null) {
            $submitted_by = $cfg["adminUserID"];
        }

        $result = $this->db->RunQuery("DELETE FROM `recipes` WHERE id = ?",
            [new DBParam(DBTypes::INT, $id)]);

        if ($result) {
            self::deleteRecipeImagesFromDisk($id);
            $this->activityLog->ActivityLog("recipes", $id, "deleted", $submitted_by, $description);
            return true;
        }
        return false;
    }

    public function DeleteRecipeImages(int $id): bool
    {
        $result = $this->db->RunQuery("DELETE FROM `recipe_images` WHERE recipe_id = ?",
            [new DBParam(DBTypes::INT, $id)]);

        if ($result) {
            self::deleteRecipeImagesFromDisk($id);
            return true;
        }
        return false;
    }
}
