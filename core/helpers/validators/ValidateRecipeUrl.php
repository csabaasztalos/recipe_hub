<?php

use Uri\WhatWg\InvalidUrlException;

final class ValidateRecipeUrl
{
    public static function Validate(int $id, string $title): void
    {
        try {
            RecipeService::CheckRecipeId($id);
            $recipeData = RecipeService::FetchRecipeData($id);
            $slugifiedTitle = UrlConverter::Slugify($recipeData['title']);
            
            if ($title !== $slugifiedTitle) {
                throw new UrlException("Invalid URL.");
            }
        } catch (Exception $ex) {
            Logger::log("ValidateRecipeUrl error for ID {$id}: ".$ex->getMessage(), logLvl::Warning);
            throw new ValidateException("Recipe URL was is invalid.");
        }
    }
}