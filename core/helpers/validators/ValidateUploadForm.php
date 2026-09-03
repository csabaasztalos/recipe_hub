<?php

final class ValidateUploadForm
{
    public static function Validate($post)
    {
        //title
        if (! isset($post['recipeTitle']) || trim($post['recipeTitle']) === "") {
            throw new RecipeUploadException("Add meg a recept címét!");
        }
        $normalizedTitle = str_replace("\r\n", "\n", $post['recipeTitle']);

        if (mb_strlen($normalizedTitle) < 3 || mb_strlen($normalizedTitle) > 64) {
            throw new RecipeUploadException("A cím minimum hossza 3, maximum 64 karakter!");
        }

        //serving size
        if (! isset($post['servings'])) {
            throw new RecipeUploadException("Az adagszámot kötelező megadni!");
        }
        if ($post['servings'] < 1 || $post['servings'] > 50) {
            throw new RecipeUploadException("Megfelelő adagszámot adj meg! (1-50 karakter)");
        }

        //ingredients
        if (! isset($post['ingredients'][0]['id']) ||
            ! isset($post['ingredients'][0]['quantity']) ||
            ! isset($post['ingredients'][0]['unit'])) {
            throw new RecipeUploadException("Legalább egy hozzávalót és annak mennyiségét meg kell adni!");
        }

        $ingredients = $post['ingredients'];

        foreach ($ingredients as $key => $value) {
            IngredientService::CheckIngredientId($value['id']);
            $qty = $value['quantity'] ?? "";
            $unit = $value['unit'] ?? "";

            if (! is_numeric($qty)) {
                throw new RecipeUploadException("Hibás hozzávaló ". ($key + 1) . ". A mennyiség csak szám lehet!");
            }
            if ((int) $qty < 1) {
                throw new RecipeUploadException("Hibás hozzávaló." . ($key + 1) ." Megfelelő hozzávló mennyiséget adj meg! (legalább 1)");
            }
            if (trim($unit) === "") {
                throw new RecipeUploadException("Hibás hozzávaló." . ($key + 1) . " Megfelelő hozzávló mértékegységet adj meg! (1-100 karakter)");
            }
        }

        //description
        if (! isset($post['smallDescription']) || trim($post['smallDescription']) === "") {
            throw new RecipeUploadException("Add meg a recept rövid leírását! (10-300 karakter)");
        }

        $normalized = str_replace("\r\n", "\n", $post['smallDescription']);
        $visibleDesc = preg_replace('/\s+/u', '', $normalized);
        
        if (mb_strlen($visibleDesc) < 10 || mb_strlen($normalized) > 300) {
            throw new RecipeUploadException("A rövid leírás leglább 10 karakter hosszú és max. 300.");
        }

        //instructions
        if (! isset($post['instructions'][0]) || trim($post['instructions'][0]) === "") {
            throw new RecipeUploadException("Legalább egy lépést meg kell adni!");
        }

        $instructions = $post['instructions'];

        if (count($instructions) > 30) {
            throw new RecipeUploadException("Maximum 30 lépést adhatsz meg!");
        }

        foreach ($instructions as $key => $ins) {
            $normalizedText = str_replace("\r\n", "\n", $ins);
            $visibleIns = preg_replace('/\s+/u', '', $normalizedText);

            if (mb_strlen($visibleIns) < 10) {
                throw new RecipeUploadException("Hibás lépés." . ($key + 1) . " Legalább 10 karakter hosszú legyen!");
            }
        }

        //categories
        $categories = $post['selectedCategoryIds'];
        $categoriesExploded = explode(";", $categories);
        if (empty(array_filter($categoriesExploded))) {
            throw new RecipeUploadException("Legalább egy kategóriát kötelező választani!");
        }

        //tags
        $recivedTags = $post['tags'];
        if (count($recivedTags) !== 6 || in_array('', $recivedTags, true)) {
            throw new RecipeUploadException("Minden címke kategóriát ki kell választani!");
        }

        $requiredKeys = array_flip(['DIFFICULTY', 'TIME', 'BUDGET', 'OCCASION', 'METHOD', 'STYLE']);
        $missing = array_diff_key($requiredKeys, $recivedTags);

        if (! empty($missing)) {
            throw new RecipeUploadException("Minden címke kategóriát ki kell választani!");
        }
    }
}