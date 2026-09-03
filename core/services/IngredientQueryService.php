<?php
final class IngredientQueryService
{
    public static function GetIngredientName(int $id): array
    {
        try {
            $model = new IngredientModel();
            IngredientService::CheckIngredientId($id);
            $result = $model->GetIngredientName($id);

            if (! $result || $result->num_rows === 0) {
                throw new DBException("Could not find ingredient by id($id).");
            }

            return $result->fetch_assoc();
        } catch (\Throwable $th) {
            throw new IngredientException("Failed to fetch ingredient name.");
        }
    }

    public static function GetFilteredIngredients(?string $keyword, ?string $status, ?string $dateFrom, ?string $dateTo): array
    {
        $model = new IngredientModel();
        $result = $model->GetFilteredIngredients($keyword, $status, $dateFrom, $dateTo);

        if (! $result) {
            throw new DBException("Adatbázis hiba.");
        }

        $ingredients = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $ingredients[] = $row;
            }
        }

        return $ingredients;
    }

    public static function GetIngredientsBySource(string $source, ?string $status): array
    {
        $model = new IngredientModel();
        $result = $model->GetIngredientBySource($source, $status);

        if (! $result) {
            throw new DBException("Error during db query.");
        }

        $ingredients = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $ingredients[] = $row;
            }
        }

        return $ingredients;
    }


    public static function AddRecipeIngredient(int $recipeId, int $id, int $qty, string $unit): void
    {
        try {
            $model = new IngredientModel();
            $allIngredients = IngredientService::FetchIngredients();

            if (! in_array($id, array_column($allIngredients, "id"))) {
                throw new IngredientException("Nem létezik ilyen hozzávaló, id: {$id}.");
            }

            $result = $model->AddRecipeIngredient($recipeId, $id, $qty, $unit);

            if (! $result) {
                throw new DBException("Adatbázis hiba.");
            }
        } catch (Exception $ex) {
            throw new IngredientException($ex->getMessage());
        }
    }

    public static function RemoveRecipeIngredient(int $id, int $recipeId): string
    {
        try {
            global $cfg;
            $model = new IngredientModel();
            IngredientService::CheckIngredientId($id);
            RecipeService::CheckRecipeId($recipeId);
            $result = $model->DeleteRecipeIngredients($recipeId, $id);

            if ($result) {
                header("Location: {$cfg['ingredientManagerPage']}");
                exit();
            } else {
                throw new DBException("Adatbázis hiba.");
            }
        } catch (Exception $ex) {
            Logger::Log("Failed to remove recipe ingredient. " . $ex->getMessage(), logLvl::Error);
            throw new RemoveIngredientException("Nem sikerült a hozzávaló eltávoltítása. " . $ex->getMessage());
        }
    }

    public static function BatchImport(array $ingNames): void
    {
        $model = new IngredientModel();

        if (! empty($ingNames)) {
            $result = $model->BatchImport($ingNames);

            if (! $result) {
                throw new ImportException("A hozzávalók tömeges importálása sikertelen.");
            }
        }
    }
}