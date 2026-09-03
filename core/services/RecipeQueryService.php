<?php
final class RecipeQueryService
{
    public static function FetchFilteredRecipeData(array $filters): array
    {
        $model = new RecipesModel();
        $recipeData = [];

        $result = $model->MainRecipeFilter(
            $filters['keyword'] === "" ? null : $filters['keyword'],
            $filters['category'] === "" ? null : $filters['category'],
            $filters['tags'] === "" ? null : $filters['tags'],
            $filters['relation'] === "" ? null : $filters['relation'],
            $filters['limit'] === "" ? null : $filters['limit'],
            $filters['order'] === "" ? null : $filters['order'],
            $filters['userID'] === "" ? null : $filters['userID'],
            $filters['recipeID'] === "" ? null : $filters['recipeID'],
            $filters['sortBy'] === "" ? 'id' : $filters['sortBy'],
        );

        if ($result && $result->num_rows > 0) {
            while ($data = $result->fetch_assoc()) {
                $recipeData[] = $data;
            }
        }

        return $recipeData;
    }

    public static function FetchLastestRecipes(?int $limit): array
    {
        $model = new RecipesModel();
        $result = $model->GetLatestRecipes($limit);
        $recipeData = [];

        if (! $result || $result->num_rows === 0) {
            throw new RecipeNotFoundException("Failed to fetch recipe data.");
        }
        if ($result && $result->num_rows > 0) {
            while ($data = $result->fetch_assoc()) {
                $recipeData[] = $data;
            }
        }

        return $recipeData;
    }

    public static function FetchRecipesByRelation(string $type, ?int $limit, string $order): array
    {
        $model = new RecipesModel();
        $order = "DESC" ? ($order !== "DESC" || $order !== "ASC") : $order;
        $recipeData = [];

        if ($type === 'favourite' || $type === 'bookmark') {
            $result = $model->GetRecipesByRelationCount($type, $limit, $order);
        } else {
            throw new RecipeNotFoundException("Wrong relation type.");
        }

        if (! $result || $result->num_rows === 0) {
            throw new RecipeNotFoundException("Failed to fetch recipe data.");
        }
        if ($result && $result->num_rows > 0) {
            while ($data = $result->fetch_assoc()) {
                $recipeData[] = $data;
            }
        }

        return $recipeData;
    }

    public static function GetRecipesByType(?int $limit, string $cardType, string $order, ?array $filters): array
    {
        try {
            if ($cardType === "latest" && ! $filters) {
                return self::FetchLastestRecipes($limit);
            } elseif ($cardType === "popular" && ! $filters) {
                return self::FetchRecipesByRelation("favourite", $limit, $order);
            } else {
                return self::FetchFilteredRecipeData($filters);
            }

        } catch (Exception $ex) {
            Logger::Log("Could not retrieve recipes by type {$cardType}: ".$ex->getMessage(), logLvl::Error);
            throw new RecipeQueryServiceException("Could not retrieve recipes by type. ", 0, $ex);
        }
    }

    public static function UploadRecipeImages(int $id, string $mainPath, ?string $extraPath): void
    {
        try {
            global $cfg;
            $model = new RecipesModel();

            $model->AddRecipeImages($id, $mainPath, $extraPath);

        } catch (Exception $ex) {
            Logger::Log("Could not upload images to db: ".$ex->getMessage(), logLvl::Error);
            throw new RecipeQueryServiceException("Could not upload images to db. ");
        }
    }

    public static function UpdateRecipeImages(int $id, ?string $mainPath, ?string $extraPath): void
    {
        try {
            $model = new RecipesModel();

            $model->UpdateRecipeImages($id, $mainPath, $extraPath);

        } catch (Exception $ex) {
            Logger::Log("Could not upload images to db: ".$ex->getMessage(), logLvl::Error);
            throw new RecipeQueryServiceException("Could not upload images to db. ");
        }
    }

    public static function UpdateRecipeStatus(int $id, string $status): void
    {
        try {
            global $cfg;
            $model = new RecipesModel();

            if (! in_array($status, $cfg['statuses'])) {
                Logger::Log("Recipe({$id}) status invalid.", logLvl::Warning);
                throw new RecipeNotFoundException("A recept státusza nem valid.");
            }

            $model->UpdateRecipeStatus($id, $status, 1, "status updated");

        } catch (Exception $ex) {
            Logger::Log("Could not update status for recipe({$id}): ".$ex->getMessage(), logLvl::Error);
            throw new RecipeQueryServiceException("Recipe status update failed. ");
        }
    }
}