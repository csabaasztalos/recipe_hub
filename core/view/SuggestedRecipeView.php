<?php

final class SuggestedRecipeView {
    public static function DisplayRandomRecipe(array $randomRecipe): string
    {
        global $cfg;
        if (! empty($randomRecipe)) {
            try {
                $recipeImages = RecipeService::GetRecipeImages($randomRecipe['id']);
                $mainImage = $recipeImages ? $recipeImages['mainImage'] : $cfg['noImage'];
                $slugifiedTitle = UrlConverter::Slugify($randomRecipe['title']);

                $randomRecipeHtml =
                    "<div class=\"\" id=\"recipeImage\">
                    <a href=\"{$cfg['viewRecipePage']}&id={$randomRecipe['id']}&title={$slugifiedTitle}\">
                        <img src=\"{$mainImage}\">
                    </a>
                </div>
                <div class=\"\" id=\"descripton\">
                    <div id=\"randomRecipeTitle\"><h3>{$randomRecipe['title']}</h3></div>
                    <div id=\"recipeDesc\"><p>{$randomRecipe['description']}</p></div>
                </div>";
            } catch (\Throwable $th) {
                Logger::Log("Could not dispaly random recipe.", logLvl::Error);
                throw new RandomRecipeException("Could not dispaly random recipe.");
            }
        } else {
            throw new RandomRecipeException("No recipe to display.");
        }
        return $randomRecipeHtml;
    }
}