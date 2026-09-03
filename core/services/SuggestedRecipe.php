<?php

final class SuggestedRecipe
{
    public static function SelectRecipe(): array
    {
        try {
            $recipes = RecipeService::GetAllRecipes();
            return $recipes[array_rand($recipes, 1)];

        } catch (\Throwable $th) {
            Logger::Log("Could not select random recipe.", logLvl::Error);
            throw new RandomRecipeException("Could not select random recipe.");
        }
    }
}

