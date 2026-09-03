<?php

final class TagsService
{
    public static function FetchRecipeTags(int $recipeId): array
    {
        $model = new TagsModel();
        $result = $model->GetRecipeTags($recipeId);
        $tags = [];

        if (! $result) {
            throw new DBException("Failed to fetch recipe tags.");
        }
        if ($result && $result->num_rows > 0) {
            while ($tag = $result->fetch_assoc()) {
                $tags[] = $tag;
            }
        }

        return $tags;
    }

    public static function FetchTags(): array
    {
        $model = new TagsModel();
        $result = $model->GetAllTags();
        $tags = [];

        if (! $result) {
            throw new DBException("Failed to fetch tags.");
        }
        if ($result && $result->num_rows > 0) {
            while ($tag = $result->fetch_assoc()) {
                $tags[] = $tag;
            }
        }

        return $tags;
    }

    public static function FetchTagCategories(): array
    {
        $model = new TagsModel();
        $result = $model->GetTagCategories();
        $tags = [];

        if (! $result) {
            throw new DBException("Failed to fetch tag categories.");
        }
        if ($result && $result->num_rows > 0) {
            while ($cat = $result->fetch_assoc()) {
                $tags[] = $cat;
            }
        }

        return $tags;
    }

    public static function AddRecipeTags(int $recipeId, array $chosenTagIds): bool
    {
        global $cfg;
        try {
            $model = new TagsModel();
            $result = $model->GetAllTags();
            $alltags = [];

            if (! $result) {
                throw new DBException("Failed to fetch tags.");
            }
            if ($result && $result->num_rows > 0) {
                while ($tag = $result->fetch_assoc()) {
                    $alltags[] = $tag;
                }
            }

            if (count($chosenTagIds) !== 6) {
                throw new TagException("Not enough tags were submitted.");

            }

            $requiredCategories = $cfg['tagCategories'];
            $tagMap = array_column($alltags, 'tag_category', 'id');
            $chosenCategories = [];

            foreach ($chosenTagIds as $id) {
                if (! isset($tagMap[$id])) {
                    throw new TagException("Submitted tag ID {$id} was not found.");
                }
                $chosenCategories[] = $tagMap[$id];
            }

            $missingCat = array_diff($requiredCategories, $chosenCategories);
            if (! empty($missingCat)) {
                $list = implode(", ", $missingCat);
                throw new TagException("No tag from {$list} category/categories was submitted.");
            }

            $result = $model->AddRecipeTags($recipeId, $chosenTagIds);
            if (! $result) {
                throw new DBException("Error during db query.");
            }

            return true;
        } catch (Exception $ex) {
            throw new TagException("Failed to add tags to recipe. ");
        }
    }

    public static function GetTagsByStatus(string $status): array
    {
        try {
            global $cfg;
            $tags = [];

            if (! in_array($status, $cfg['statuses'])) {
                Logger::Log("Status invalid.", logLvl::Warning);
                throw new StatusException("A megadott státusz nem valid.");
            }

            $data = self::FetchTags();
            if (! $data) {
                Logger::Log("Could not retrive categories data.", logLvl::Error);
                throw new DBException("Nem sikerült lekérdezni a cimkéket.");
            }

            foreach ($data as $tag) {
                if ($tag['status'] === $status) {
                    $tags[] = $tag;
                }
            }

            return $tags;
        } catch (Exception $ex) {
            Logger::Log("Failed to modify category status by id. ".$ex->getMessage(), logLvl::Error);
            throw new TagException("Nem sikerült lekérdezni a cimkék státuszát.");
        }
    }
}