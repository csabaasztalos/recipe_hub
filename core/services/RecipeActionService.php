<?php

final class RecipeActionService
{
    public static function GetUserRecipeActions(int $userId, int $recipeId): array
    {
        try {
            $recipesModel = new RecipesModel();

            $likedRecipes = [];
            $markedRecipes = [];

            $likedResult = $recipesModel->GetRecipesWithRealtionByUser($userId, "favourite");
            if ($likedResult && $likedResult->num_rows > 0) {
                while ($row = $likedResult->fetch_assoc()) {
                    $likedRecipes[] = $row['id'];
                }
            }

            $markedResult = $recipesModel->GetRecipesWithRealtionByUser($userId, "bookmark");
            if ($markedResult && $markedResult->num_rows > 0) {
                while ($row = $markedResult->fetch_assoc()) {
                    $markedRecipes[] = $row['id'];
                }
            }

            return [
                'favourite' => in_array($recipeId, $likedRecipes),
                'marked' => in_array($recipeId, $markedRecipes)
            ];
        } catch (Exception $ex) {
            throw new RecipeActionServiceException("Hiba történt a felhasználó reakcióinak lekérésében.");
        }
    }

    public static function ToggleUserReaction(int $userId, int $recipeId, string $actionType): bool
    {
        try {
            if (! in_array($actionType, ['bookmark', 'favourite'])) {
                Logger::log("Invalid action type: {$actionType}. ", logLvl::Warning);
                Logger::log("Invalid action type: {$actionType}. ", logLvl::Warning);
                return false;
            }

            $recipesModel = new RecipesModel();
            $result = $recipesModel->ToggleRelation($userId, $recipeId, $actionType);

            if (! $result) {
                Logger::log("Toggling {$actionType} failed for recipe {$recipeId}. ", logLvl::Error);
                throw new RecipeActionServiceException("Toggling {$actionType} failed. ");
            }

            return $result;
        } catch (Exception $ex) {
            Logger::log("Could not toggle user reaction.", logLvl::Error);
            throw new RecipeActionServiceException("Could not toggle user reaction.", 0, $ex);
        }
    }
}