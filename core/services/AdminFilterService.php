<?php

final class AdminFilterService
{
    public static function GetFilterData(array $get, bool $isUser, bool $isRecipe, ?array $categories): array
    {
        global $cfg;

        if ($isUser && $isRecipe) {
            throw new AdminFilterException("Error Processing Request");
        }

        $keyword = "";
        $dateFrom = "";
        $dateTo = "";

        if (isset($get['status']) && $get['status'] != "" && ! $isUser) {
            $status = $get['status'];
            $statusHtml = AdminView::BuildStatusOptions($status);
        } elseif (!$isUser) {
            $status = "";
            $statusHtml = AdminView::BuildStatusOptions("");
        } else {
            $status = "";
            $statusHtml = '';
        }

        if (isset($get['search']) && $get['search'] != "") {
            $keyword = $get['search'];
        }

        if (isset($get['date_from']) && $get['date_from'] != "") {
            $dateFrom = $get['date_from'];
        }

        if (isset($get['date_to']) && $get['date_to'] != "") {
            $dateTo = $get['date_to'];
        }
        
        if (isset($_GET['category']) && $_GET['category'] != "" && $isRecipe && !empty($categories)) {
            $category = (int) $_GET['category'];
            $categoryHtml = RecipeView::DefaultCategoryOptions($categories, $category);
        } elseif ($categories !== null && !empty($categories)) {
            $category = null;
            $categoryHtml = RecipeView::DefaultCategoryOptions($categories, null);
        } else {
            $category = null;
            $categoryHtml = '';
        }

        return [
            $dateFrom,
            $dateTo,
            $keyword,
            $status,
            $statusHtml,
            $category,
            $categoryHtml
        ];
    }

    public static function ParseFilter(?string $keyword, ?int $category, ?string $status, ?string $dateFrom, ?string $dateTo): array
    {
        try {
            $model = new RecipesModel();
            $filteredRecipes = [];

            $filterResult = $model->AdminRecipeFilter($keyword, $category, $status, $dateFrom, $dateTo);
            if($filterResult->num_rows > 0) {
                while ($row = $filterResult->fetch_assoc()) {
                    $filteredRecipes[] = $row;
                }
            }
            
            return $filteredRecipes;

        } catch (\Throwable $th) {
            throw new DBException("Adatbázis hiba.");
        }
    }

}
