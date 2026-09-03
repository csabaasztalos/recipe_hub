<?php
final class CategoryQueryService
{
    public static function GetFilteredCategories(?string $keyword, ?string $status, ?string $dateFrom, ?string $dateTo): array
    {
        $model = new CategoriesModel();
        $result = $model->GetFilteredCategories($keyword, $status, $dateFrom, $dateTo);

        if (! $result) {
            throw new DBException("Adatbázis hiba.");
        }

        $categories = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $categories[] = $row;
            }
        }

        return $categories;
    }

    public static function GetCategoriesBySource(string $source, ?string $status): array
    {
        $model = new CategoriesModel();
        $result = $model->GetCategoriesBySource($source, $status);

        if (! $result) {
            throw new DBException("Adatbázis hiba.");
        }

        $categories = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $categories[] = $row;
            }
        }

        return $categories;
    }

    public static function AddRecipeCategory(int $recipeId, array|string $chosenCategoryIds): void
    {
        $model = new CategoriesModel();
        $allCats = CategoryService::FetchCategories();

        if (is_array($chosenCategoryIds)) {
            if (empty($chosenCategoryIds)) {
                throw new CategoryException("Nem volt megadva kategória.");
            }
            $chosenIds = $chosenCategoryIds;
        } else {
            if (trim($chosenCategoryIds) === "") {
                throw new CategoryException("Nem volt megadva kategória.");
            }
            $chosenIds = array_values(array_filter(array_map('trim', explode(";", $chosenCategoryIds)),
                static fn ($v) => $v !== ''));
        }

        $validIds = array_column($allCats, "id");

        foreach ($chosenIds as $id) {
            if (! in_array((int) $id, $validIds)) {
                throw new CategoryException("Megadott kategória nem található id: {$id}.");
            }
        }

        $result = $model->AddRecipeCategory($recipeId, $chosenIds);
        if (! $result) {
            throw new DBException("Adatbázis hiba.");
        }
    }

    public static function RemoveRecipeCategory(int $id, int $recipeId): string
    {
        try {
            global $cfg;
            $model = new CategoriesModel();
            CategoryService::CheckCategoryId($id);
            RecipeService::CheckRecipeId($recipeId);
            $result = $model->DeleteRecipeCategory($recipeId, [$id]);

            if ($result) {
                header("Location: {$cfg['categoryManagerPage']}");
                exit();
            } else {
                throw new DBException("Adatbázis hiba.");
            }
        } catch (Exception $ex) {
            Logger::Log("Failed to remove recipe category. ".$ex->getMessage(), logLvl::Error);
            throw new RemoveCategoryException("Nem sikerült a kategória eltávolítása.");
        }
    }

    public static function BatchImport(array $categoryNames): void
    {
        $model = new CategoriesModel();

        if (! empty($categoryNames)) {
            $result = $model->BatchImport($categoryNames);

            if (! $result) {
                throw new ImportException("A kategóriák tömeges importálása sikertelen.");
            }
        }
    }
}