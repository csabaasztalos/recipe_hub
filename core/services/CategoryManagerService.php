<?php

final class CategoryManagerService
{
    public static function AssignRecipeCategory(int $id, $recipeId): array
    {
        try {
            global $cfg;
            self::ValidateAssignForm($recipeId, $id);
            CategoryQueryService::AddRecipeCategory($recipeId, [$id]);

            header("Location: {$cfg['categoryManagerPage']}");
            exit();

        } catch (Exception $e) {
            Logger::Log("Nem sikerült hozzárendeli a kategóriát. ".$e->getMessage(), logLvl::Error);
            throw new AssignCategoryException("Nem sikerült hozzáadni a kategóriát. ".$e->getMessage());
        }
    }

    private static function ValidateAssignForm(int $recipeId, int $id): void
    {
        RecipeService::CheckRecipeId($recipeId);
        CategoryService::CheckCategoryId($id);

        $recipeCategories = CategoryService::FetchRecipeCategories($recipeId);

        if (in_array($id, $recipeCategories)) {
            throw new CategoryServiceException("Ez a kategória korábban már hozzá lett adava a recepthez.");
        }
    }

    public static function ImportCategories(): array
    {
        try {
            global $cfg;

            FetchData::FetchByType('categories');
            header("Location: {$cfg['categoryManagerPage']}");
            exit();

        } catch (Exception $ex) {
            throw new ImportException("Kategóriák importálása sikertelen.");
        }
    }

    public static function DeleteImportedCategories(array $recordIds): string
    {
        try {
            global $cfg;

            if (! empty($recordIds)) {
                foreach ($recordIds as $id) {
                    CategoryService::DeleteCategory($id);
                }
                header("Location: {$cfg['categoryManagerPage']}");
                exit();
            } else {
                throw new CategoryServiceException("Jelölj ki kategóriát a törléshez.");
            }

        } catch (Exception $ex) {
            throw new ImportException("Staged kategóriák törlése sikertelen.");
        }
    }

    public static function SaveImportedCategories(array $recordIds): string
    {
        try {
            global $cfg;
            if (! empty($recordIds)) {
                CategoryService::UpdateCategoryStatus($recordIds, "approved");
                header("Location: {$cfg['categoryManagerPage']}");
                exit();
            } else {
                throw new CategoryServiceException("Jelölj ki kategóriát a mentéshez.");
            }
        } catch (Exception $ex) {
            throw new ImportException("Nem sikerült az importált kategóriák mentése.");
        }
    }
}

