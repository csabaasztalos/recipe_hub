<?php
class TagsModel
{
    private DBHandler $db;
    private ActivityLogModel $activityLog;

    public function __construct()
    {
        $this->db = Container::Get('db');
        $this->activityLog = Container::Get('activityLog');
    }

    public function GetTag($id): mysqli_result
    {
        return $this->db->RunQuery("SELECT `name` FROM `tags` WHERE id = ?",
            [new DBParam(DBTypes::INT, $id)]);
    }

    public function GetAllTags(): mysqli_result
    {
        return $this->db->RunQuery("SELECT * FROM tags ORDER BY tag_category");
    }

    public function GetAllTagsByStatus(string $status): mysqli_result
    {
        return $this->db->RunQuery("SELECT * FROM `tags` WHERE `status` = ? ORDER BY tag_category",
            [new DBParam(DBTypes::STRING, $status)]);
    }


    public function GetTagCategories(): mysqli_result
    {
        return $this->db->RunQuery("SELECT DISTINCT `tag_category` FROM tags");
    }

    public function GetRecipeTags($recipeId): mysqli_result
    {
        return $this->db->RunQuery("SELECT t.* FROM tags t JOIN recipe_tags rt ON t.id = rt.tag_id WHERE rt.recipe_id = ? ORDER BY t.name",
            [new DBParam(DBTypes::INT, $recipeId)]
        );
    }

    public function CreateTag(string $name, int $submitted_by, string $status = 'pending', string $description = ''): bool
    {

        $result = $this->db->RunQuery("INSERT INTO `tags` (`name`, `submitted_by`, `status`) VALUES (?, ?, ?); ",
            [
                new DBParam(DBTypes::STRING, $name),
                new DBParam(DBTypes::INT, $submitted_by),
                new DBParam(DBTypes::STRING, $status)
            ]);

        if ($result) {
            $id = $this->db->GetLastInsertId();
            $this->activityLog->ActivityLog("tags", $id, "created", $submitted_by, $description);
            return $id;
        }
        return false;
    }

    public function AddRecipeTags(int $recipeId, array|string $tagIds): bool
    {
        if (! is_array($tagIds)) {
            $tagIds = explode(";", $tagIds);
        }

        foreach ($tagIds as $id) {
            $result = $this->db->RunQuery("INSERT INTO recipe_tags (recipe_id, tag_id) VALUES (?, ?)",
                [
                    new DBParam(DBTypes::INT, $recipeId),
                    new DBParam(DBTypes::INT, $id)
                ]);

            if (! $result) {
                return false;
            }
        }
        return true;
    }

    public function UpdateTag(
        int $id, ?string $name = null, ?string $status = null, ?string $approved_at = null,
        ?string $tag_category = null, ?int $submitted_by = null, string $description = ''
    ): bool {
        $setParts = [];
        $params = [];

        if ($name !== null) {
            $setParts[] = "name = ?";
            $params[] = new DBParam(DBTypes::STRING, $name);
        }
        if ($status !== null) {
            $setParts[] = "status = ?";
            $params[] = new DBParam(DBTypes::STRING, $status);
            if ($status === 'approved') {
                $setParts[] = "approved_at = NOW()";
            }
        }
        if ($tag_category !== null) {
            $setParts[] = "tag_category = ?";
            $params[] = new DBParam(DBTypes::STRING, $tag_category);
        }

        if (empty($setParts)) {
            // Nothing to update
            return false;
        }

        $setSql = implode(', ', $setParts);
        $params[] = new DBParam(DBTypes::INT, $id);

        $result = $this->db->RunQuery("UPDATE `tags` SET $setSql WHERE id = ?", $params);
        if ($result) {
            $this->activityLog->ActivityLog("tags", $id, "updated", $submitted_by, $description);
            return $id;
        }
        return false;
    }

    public function UpdateTagStatus($id, $status, ?int $submittedBy, string $description = "")
    {
        global $cfg;
        $setSql = "status = ?";
        $params = [new DBParam(DBTypes::STRING, $status)];

        if ($status === 'approved') {
            $setSql .= ", approved_at = NOW()";
        }
        if (! in_array($status, $cfg['statuses'])) {
            throw new StatusException("Ther's no status called: {$status}");
        }

        $params[] = new DBParam(DBTypes::INT, $id);

        $result = $this->db->RunQuery("UPDATE `tags` SET $setSql WHERE id = ?", $params);
        if ($result) {
            if ($submittedBy === null) {
                $submittedBy = $cfg["adminUserID"];
            }
            $this->activityLog->ActivityLog("tags", $id, "status updated", $submittedBy, $description);
            return true;
        }
        return false;
    }

    public function DeleteTags(int $id, ?int $submitted_by, string $description = ''): bool
    {
        global $cfg;
        $result = $this->db->RunQuery("DELETE FROM `tags` WHERE id = ?",
            [new DBParam(DBTypes::INT, $id)]);
        if ($result) {
            if ($submitted_by === null) {
                $submitted_by = $cfg['adminUserID'];
            }
            $this->activityLog->ActivityLog("tags", $id, "deleted", $submitted_by, $description);
            return $id;
        }
        return false;
    }

    public function DeleteRecipeTags(int $recipeId, $tagIds): bool
    {
        if (is_string($tagIds) && strpos($tagIds, ';') !== false) {
            $tagIds = explode(';', $tagIds);
        }

        if (! is_array($tagIds)) {
            $tagIds = [$tagIds];
        }

        $allSuccessful = true;

        foreach ($tagIds as $tagId) {
            $tag = (int) $tagId;

            $result = $this->db->RunQuery(
                "DELETE FROM `recipe_tags` WHERE recipe_id = ? AND tag_id = ?",
                [new DBParam(DBTypes::INT, $recipeId),
                    new DBParam(DBTypes::INT, $tag)]
            );
        }
        if (! $result) {
            $allSuccessful = false;
        }
        return $allSuccessful;
    }

}
