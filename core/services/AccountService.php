<?php

final class AccountService
{
    public static function GetRecipesData(int $userId, string $type): array
    {
        try {
            $recipesModel = new RecipesModel();
            $recipesResult = null;

            if ($type === "bookmark") {
                $recipesResult = $recipesModel->GetRecipesWithRealtionByUser($userId, "bookmark");
            } elseif ($type === "favourite") {
                $recipesResult = $recipesModel->GetRecipesWithRealtionByUser($userId, "favourite");
            } elseif ($type === "user") {
                $recipesResult = $recipesModel->GetRecipesByUser($userId);
            }

            $recipes = [];
            if ($recipesResult && $recipesResult->num_rows > 0) {
                while ($row = $recipesResult->fetch_assoc()) {
                    $recipes[] = $row;
                }
            }

            return self::ExpandRecipes($recipes);

        } catch (Exception $ex) {
            throw new AccountServiceException ("Failed to get recipes data.");
        }
    }

    private static function ExpandRecipes(array $recipes): array
    {
        $expandedRecipes = RecipeService::ExpandRecipeWithImage($recipes);
        $recipesModel = new RecipesModel();

        foreach ($expandedRecipes as &$recipe) {
            $recipe['likesCount'] = $recipesModel->GetRelationCountByRelation("favourite", $recipe['id']);
            $recipe['bookmarksCount'] = $recipesModel->GetRelationCountByRelation("bookmark", $recipe['id']);
        }
        unset($recipe); // Clean up reference

        return $expandedRecipes;
    }
}