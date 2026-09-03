<?php

final class IngredientService
{
    public static function EditIngredient(int $id, string $ingredientName, string $status): string
    {
        try {
            global $cfg;
            $model = new IngredientModel();

            self::CheckIngredientId($id);
            $isNameValid = self::CheckIngredientName($id, $ingredientName);
            $ingStatus = $model->GetIngredientStatus($id)->fetch_assoc();

            if (! in_array($status, $cfg['statuses'])) {
                throw new IngredientServiceException("Hibás státusz.");
            }

            if ($isNameValid) {
                $model->UpdateIngredient($id, $ingredientName, null, null);
            }
            if ($ingStatus['status'] !== $status) {
                $model->UpdateIngredientStatus($id, $status, null);
            }

            header("Location: {$cfg['ingredientManagerPage']}");
            exit();

        } catch (Exception $ex) {
            Logger::Log("Failed to edit ingredient. ".$ex->getMessage(), logLvl::Error);
            throw new EditIngredientException("A hozzávaló módosítása sikertelen. ".$ex->getMessage());
        }
    }

    public static function ApproveIngredient(int $id)
    {
        try {
            global $cfg;
            $model = new IngredientModel();
            self::CheckIngredientId($id);
            $status = self::GetIngredientStatus($id);

            if ($status !== "approved") {
                $model->UpdateIngredientStatus($id, "approved", null);
                Logger::Log("The ingredient({$id}) is approved.", logLvl::Info);
                header("Location: {$cfg['ingredientManagerPage']}");
                exit();
            } else {
                Logger::Log("The ingredient({$id} is already approved.", logLvl::Warning);
            }

        } catch (Exception $ex) {
            Logger::Log("Failed to approve ingredient({$id}). ".$ex->getMessage(), logLvl::Error);
            throw new IngredientActionException("A hozzávaló elfogadása sikertelen. ".$ex->getMessage());
        }
    }

    public static function AddNewIngredient(string $ingName, string $status, int $userId, string $source, ?string $desc): void
    {
        try {
            global $cfg;
            $model = new IngredientModel();

            $doesExist = $model->GetIngredientByName($ingName);
            if ($doesExist->num_rows > 0) {
                throw new IngredientServiceException("A hozzávaló már létezik.");
            }

            if (! in_array($source, $cfg['sources'])) {
                throw new IngredientServiceException("A forrás nem megfelelő.");
            }

            if ($desc) {
                $desc = htmlspecialchars($desc);
            } else {
                $desc = "new ingredient created";
            }

            if ($ingName && trim($ingName) !== '') {
                $normalizedText = str_replace("\r\n", "\n", $ingName);
                $visibleChars = preg_replace('/\s+/u', '', $normalizedText);

                if ($visibleChars < 3) {
                    throw new IngredientServiceException("A hozzávaló Legalább 3 karakter hosszú legyen.");
                }

                $model->CreateIngredient($ingName, $userId, $status, "manual", $desc);
            }

            header("Location: {$cfg['ingredientManagerPage']}");
            exit();
        } catch (Exception $ex) {
            Logger::Log("Failed to edit category. ".$ex->getMessage(), logLvl::Error);
            throw new NewIngredientException("A hozzávaló létrehozása sikertelen. ".$ex->getMessage());
        }
    }
    public static function RejectIngredient($id)
    {
        try {
            global $cfg;
            $model = new IngredientModel();
            self::CheckIngredientId($id);
            $status = self::GetIngredientStatus($id);

            if ($status !== "rejected") {
                $model->UpdateIngredientStatus($id, "rejected", null);
                Logger::Log("The ingredient({$id}) is rejected.", logLvl::Info);
                header("Location: {$cfg['ingredientManagerPage']}");
                exit();
            } else {
                Logger::Log("The ingredient({$id} is already rejected.", logLvl::Warning);
            }

        } catch (Exception $ex) {
            Logger::Log("Failed to rejected ingredient({$id}). ".$ex->getMessage(), logLvl::Error);
            throw new IngredientActionException("A hozzávaló elutasítása sikertelen. ".$ex->getMessage());
        }
    }

    public static function DeleteIngredient(array|int $ids): void
    {
        try {
            global $cfg;
            $model = new IngredientModel();
            $model->DeleteIngredient($ids, null, 'Ingredients deleted');
            header("Location: {$cfg['ingredientManagerPage']}");
            exit();

        } catch (Exception $ex) {
            Logger::Log("Deleting ingredients failed. ".$ex->getMessage(), logLvl::Error);
            throw new IngredientActionException("A hozzávalók törlése sikertelen. ".$ex->getMessage());
        }
    }

    public static function CheckIngredientId(int $id): void
    {
        try {
            $data = self::FetchIngredients();

            if (! in_array($id, array_column($data, 'id'))) {
                Logger::Log("Ingredient({$id}) does not exist.", logLvl::Warning);
                throw new IngredientNotFoundException("Ilyen hozzávaló nem létezik.");
            }

        } catch (Exception $ex) {
            Logger::Log("Failed to check ingredient by id. ".$ex->getMessage(), logLvl::Error);
            throw new IngredientServiceException("Nem sikerült ellenőrizni a hozzávalót.");
        }
    }

    private static function CheckIngredientName(int $id, string $name): bool
    {
        $model = new IngredientModel();
        $allIngredients = self::FetchIngredients();
        $data = $model->GetIngredient($id)->fetch_assoc();
        if (! $data) {
            Logger::Log("Ingredient({$id}) does not exist.", logLvl::Warning);
            throw new RecipeNotFoundException("Ilyen hozzávaló nem létezik.");
        }

        if (in_array($name, array_column($allIngredients, "name")) && $data['name'] !== $name) {
            Logger::Log("Ingredient({$id}) does not exist.", logLvl::Warning);
            throw new RecipeNotFoundException("Ez a név foglalt.");
        }

        if (mb_strlen($name) > 2 && trim($name) !== "") {
            $normalizedText = str_replace("\r\n", "\n", $name);
            $visibleChars = preg_replace('/\s+/u', '', $normalizedText);

            if ($visibleChars < 2) {
                throw new CategoryServiceException("A hozzávaló Legalább 2 karakter hosszú legyen.");
            }

            return true;
        }

        return false;
    }

    public static function UpdateIngredientStatus(array $ids, string $status): void
    {
        try {
            global $cfg;
            $model = new IngredientModel();

            if (! in_array($status, $cfg['statuses'])) {
                throw new StatusException("A megadott státusz nem valid.");
            }

            if (empty($ids)) {
                throw new CategoryServiceException("A megadott azonosítók hibásak.");
            }

            foreach ($ids as $id) {
                if (! ctype_digit($id)) {
                    throw new IngredientServiceException("A megadott azonosítók hibásak.");
                }
                $model->UpdateIngredientStatus($id, $status, null);
            }

        } catch (Exception $ex) {
            Logger::Log("Faild to update ingredient. ".$ex->getMessage(), logLvl::Error);
            throw new IngredientServiceException("Nem sikerült módosítani a hozzévaló státuszát.");
        }
    }

    public static function FetchRecipeIngredients(int $recipeId): array
    {
        $model = new IngredientModel();
        $result = $model->GetRecipeIngredients($recipeId);
        $ingredients = [];

        if (! $result) {
            throw new DBException("Failed to fetch recipe ingredients.");
        }
        if ($result && $result->num_rows > 0) {
            while ($ingredient = $result->fetch_assoc()) {
                $ingredients[] = $ingredient;
            }
        }

        return $ingredients;
    }

    public static function FetchIngredients(): array
    {
        $model = new IngredientModel();
        $result = $model->GetAllIngredients();
        $ingredients = [];

        if (! $result) {
            throw new DBException("Adatbázis hiba.");
        }
        if ($result && $result->num_rows > 0) {
            while ($ingredient = $result->fetch_assoc()) {
                $ingredients[] = $ingredient;
            }
        }

        return $ingredients;
    }

    public static function FetchIngredientsByStatus(string $status): array
    {
        global $cfg;
        $model = new IngredientModel();
        $ingredients = [];

        if (! in_array($status, $cfg['statuses'])) {
            throw new DBException("Given status does not exist.");
        }

        $result = $model->GetIngredientStatus($status);

        if (! $result) {
            throw new DBException("Failed to fetch ingredients.");
        }

        if ($result && $result->num_rows > 0) {
            while ($ingredient = $result->fetch_assoc()) {
                $ingredients[] = $ingredient;
            }
        }

        return $ingredients;
    }

    private static function GetIngredientStatus(int $id): string
    {
        global $cfg;
        $model = new IngredientModel();

        $data = $model->GetIngredientStatus($id)->fetch_assoc();
        if (! $data) {
            Logger::Log("Ingredient({$id}) does not exist.", logLvl::Error);
            throw new RecipeNotFoundException("Ilyen hozzávaló nem létezik.");
        }
        $status = $data['status'];

        if (! in_array($status, $cfg['statuses'])) {
            Logger::Log("Ingredient({$id}) status invalid.", logLvl::Warning);
            throw new RecipeNotFoundException("A hozzávaló státusza nem valid.");
        }
        return $status;
    }
}