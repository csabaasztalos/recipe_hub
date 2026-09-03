<?php

final class IngredientManagerService
{
    public static function AssignRecipeIngredient(int $id, array $ingredient, int $recipeId): string
    {
        try {
            global $cfg;
            self::ValidateAssignForm($recipeId, $id, $ingredient['qty'], $ingredient['unit']);
            IngredientQueryService::AddRecipeIngredient($recipeId, $id, $ingredient['qty'], $ingredient['unit']);

            header("Location: {$cfg['ingredientManagerPage']}");
            exit();

        } catch (Exception $ex) {
            Logger::Log("Failed to assign ingredient to recipe. ".$ex->getMessage(), logLvl::Error);
            throw new AssignIngredientException("A hozzávaló recepthez adása sikertelen. ".$ex->getMessage());
        }

    }

    private static function ValidateAssignForm(int $recipeId, int $id, int $qty, string $unit): void
    {
        RecipeService::CheckRecipeId($recipeId);
        IngredientService::CheckIngredientId($id);

        if ($qty < 1) {
            throw new IngredientManagerServiceException("A legkissebb megengedett mértékegység: 1.");
        }

        if (trim($unit) === "" || mb_strlen($unit) > 100) {
            throw new IngredientManagerServiceException("A megengedett mennyiség karakterszám: 1-100.");
        }

        $recipeIngredients = IngredientService::FetchRecipeIngredients($recipeId);
        if (in_array($id, $recipeIngredients)) {
            throw new IngredientManagerServiceException("Ez a hozzávaló korábban már hozzá lett adava a recepthez.");
        }
    }

    public static function ImportIngredients(): void
    {
        try {
            global $cfg;

            FetchData::FetchByType('ingredients');
            header("Location: {$cfg['ingredientManagerPage']}");
            exit();

        } catch (Exception $ex) {
            throw new ImportException("Hozzávalók importálása sikertelen.");
        }
    }

    public static function DeleteImportedIngredients(array $recordIds): string
    {
        try {
            global $cfg;

            if (! empty($recordIds)) {
                foreach ($recordIds as $id) {
                    IngredientService::DeleteIngredient($id);
                }
            }
            header("Location: {$cfg['ingredientManagerPage']}");
            exit();
        } catch (Exception $ex) {
            throw new ImportException("Staged hozzávalók törlése sikertelen.");
        }
    }

    public static function SaveImportedIngredients(array $recordIds): string
    {
        try {
            global $cfg;
            if (! empty($recordIds)) {
                IngredientService::UpdateIngredientStatus($recordIds, "approved");
                header("Location: {$cfg['ingredientManagerPage']}");
                exit();
            }
        } catch (Exception $ex) {
            throw new ImportException("Nem sikerült az importált hozzávalók mentése.");
        }
    }
}

