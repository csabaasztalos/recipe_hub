<?php

final class CategoryService
{
    public static function EditCategory(int $id, string $catName, string $status): string
    {
        try {
            global $cfg;
            $model = new CategoriesModel();

            self::CheckCategoryId($id);
            $isNameValid = self::CheckCategoryName($id, $catName);
            $catStatus = $model->GetCategoryStatus($id)->fetch_assoc();

            if (! in_array($status, $cfg['statuses'])) {
                throw new CategoryServiceException("Hibás státusz.");
            }

            if ($isNameValid) {
                $model->UpdateCategory($id, $catName, null, null);
            }
            if ($catStatus['status'] !== $status) {
                $model->UpdateCategoryStatus($id, $status);
            }

            header("Location: {$cfg['categoryManagerPage']}");
            exit();

        } catch (Exception $ex) {
            Logger::Log("Failed to edit category. ".$ex->getMessage(), logLvl::Error);
            throw new EditCategoryException("A kategória módosítása sikertelen: ".$ex->getMessage());
        }
    }

    public static function AddNewCategory(string $catName, string $status, int $userId, string $source, ?string $desc): void
    {
        try {
            global $cfg;
            $model = new CategoriesModel();

            $doesExist = $model->GetCategoryByName($catName);
            if ($doesExist->num_rows > 0) {
                throw new CategoryServiceException("A kategória már létezik.");
            }

            if (! in_array($source, $cfg['sources'])) {
                throw new CategoryServiceException("A forrás nem megfelelő.");
            }

            if ($desc) {
                $desc = htmlspecialchars($desc);
            } else {
                $desc = "new category created";
            }

            if ($catName && trim($catName) !== '') {
                $normalizedText = str_replace("\r\n", "\n", $catName);
                $visibleChars = preg_replace('/\s+/u', '', $normalizedText);

                if ($visibleChars < 3) {
                    throw new CategoryServiceException("A kategória Legalább 3 karakter hosszú legyen.");
                }

                $model->CreateCategory($catName, $userId, $status, "manual", $desc);
            }

            header("Location: {$cfg['categoryManagerPage']}");
            exit();
        } catch (Exception $ex) {
            Logger::Log("Failed to edit category. ".$ex->getMessage(), logLvl::Error);
            throw new NewCategoryException("A kategória létrehozása sikertelen. ".$ex->getMessage());
        }
    }

    public static function ApproveCategory(int $id)
    {
        try {
            global $cfg;
            $model = new CategoriesModel();
            self::CheckCategoryId($id);
            $status = self::GetCategoryStatus($id);

            if ($status !== "approved") {
                $model->UpdateCategoryStatus($id, "approved", null);
                Logger::Log("The category({$id}) is approved.", logLvl::Info);
                header("Location: {$cfg['categoryManagerPage']}");
                exit();
            } else {
                Logger::Log("The category({$id} is already approved.", logLvl::Warning);
            }

        } catch (Exception $ex) {
            Logger::Log("Failed to approve category({$id}). ".$ex->getMessage(), logLvl::Error);
            throw new CategoryActionException("A kategória elfogadása sikertelen. ".$ex->getMessage());
        }
    }

    public static function RejectCategory($id)
    {
        try {
            global $cfg;
            $model = new CategoriesModel();
            self::CheckCategoryId($id);
            $status = self::GetCategoryStatus($id);

            if ($status !== "rejected") {
                $model->UpdateCategoryStatus($id, "rejected", null);
                Logger::Log("The category({$id}) is rejected.", logLvl::Info);
                header("Location: {$cfg['categoryManagerPage']}");
                exit();
            } else {
                Logger::Log("The category({$id} is already rejected.", logLvl::Warning);
            }

        } catch (Exception $ex) {
            Logger::Log("Failed to rejected category({$id}). ".$ex->getMessage(), logLvl::Error);
            throw new CategoryActionException("A kategória elutasítása sikertelen. ".$ex->getMessage());
        }
    }

    public static function DeleteCategory(array|int $ids): void
    {
        try {
            global $cfg;
            $model = new CategoriesModel();
            $model->DeleteCategory($ids, null, "category deleted");
            header("Location: {$cfg['categoryManagerPage']}");
            exit();

        } catch (Exception $ex) {
            Logger::Log("Deleting categories failed. ".$ex->getMessage(), logLvl::Error);
            throw new CategoryActionException("A kategóriák törlése sikertelen. ".$ex->getMessage());
        }
    }

    public static function FetchCategories(): array
    {
        $model = new CategoriesModel();
        $categories = [];

        $result = $model->GetAllCategories();
        if (! $result) {
            throw new DBException("Adatbázis hiba.");
        }
        if ($result && $result->num_rows > 0) {
            while ($category = $result->fetch_assoc()) {
                $categories[] = $category;
            }
        }

        return $categories;
    }

    public static function FetchRecipeCategories(int $recipeId): array
    {
        $model = new CategoriesModel();
        $categories = [];

        $result = $model->GetRecipeCategories($recipeId);
        if (! $result) {
            throw new DBException("Failed to fetch recipe categories.");
        }
        if ($result && $result->num_rows > 0) {
            while ($category = $result->fetch_assoc()) {
                $categories[] = $category;
            }
        }

        return $categories;
    }

    public static function ExpandCategories(array $categoryIds): array
    {
        try {
            $model = new CategoriesModel();
            $categories = [];

            if (! empty($categoryIds)) {
                foreach ($categoryIds as $cat) {
                    $catData = $model->GetCategory($cat)->fetch_assoc();
                    $categories[] = $catData;
                }
                return $categories;
            }

            throw new CategoryException("Array of category ids was empty.");
        } catch (Exception $th) {
            throw new CategoryException("Could not expand category ids.");
        }
    }

    public static function CheckCategoryId(int $id): void
    {
        try {
            $data = self::FetchCategories();

            if (! in_array($id, array_column($data, 'id'))) {
                Logger::Log("Category({$id}) does not exist.", logLvl::Warning);
                throw new CategoryNotFoundException("Ilyen kategória nem létezik.");
            }

        } catch (Exception $ex) {
            Logger::Log("Failed to check category by id. ".$ex->getMessage(), logLvl::Error);
            throw new CategoryException("Nem sikerült ellenőrizni a kategóriát.");
        }
    }

    private static function CheckCategoryName(int $id, string $name): bool
    {
        $model = new CategoriesModel();
        $allCats = self::FetchCategories();
        $data = $model->GetCategory($id)->fetch_assoc();
        if (! $data) {
            Logger::Log("Category({$id}) does not exist.", logLvl::Warning);
            throw new CategoryNotFoundException("Ilyen kategória nem létezik.");
        }

        if (in_array($name, array_column($allCats, "name")) && $data['name'] !== $name) {
            Logger::Log("Category({$name}) name is already in use.", logLvl::Warning);
            throw new CategoryServiceException("Ez a név foglalt.");
        }

        if (mb_strlen($name) > 1 && trim($name) !== "") {
            $normalizedText = str_replace("\r\n", "\n", $name);
            $visibleChars = preg_replace('/\s+/u', '', $normalizedText);

            if ($visibleChars < 3) {
                throw new CategoryServiceException("A kategória Legalább 3 karakter hosszú legyen.");
            }

            return true;
        }

        return false;
    }

    private static function GetCategoryStatus(int $id): string
    {
        try {
            global $cfg;
            $model = new CategoriesModel();

            $data = $model->GetCategoryStatus($id)->fetch_assoc();
            if (! $data) {
                Logger::Log("Category({$id}) does not exist.", logLvl::Error);
                throw new CategoryNotFoundException("Ilyen kategória nem létezik.");
            }
            $status = $data['status'];

            if (! in_array($status, $cfg['statuses'])) {
                Logger::Log("Category({$id}) status invalid.", logLvl::Warning);
                throw new StatusException("A kategória státusza nem valid.");
            }
            return $status;

        } catch (Exception $ex) {
            Logger::Log("Failed to check category status by id. ".$ex->getMessage(), logLvl::Error);
            throw new CategoryServiceException("Nem sikerült ellenőrizni a kategória státuszát.");
        }
    }

    public static function GetCategoriesByStatus(string $status): array
    {
        try {
            global $cfg;
            $categories = [];

            if (! in_array($status, $cfg['statuses'])) {
                Logger::Log("Status invalid.", logLvl::Warning);
                throw new StatusException("A megadott státusz nem valid.");
            }

            $data = self::FetchCategories();
            if (! $data) {
                Logger::Log("Could not retrive categories data.", logLvl::Error);
                throw new DBException("Adatbázis hiba.");
            }

            foreach ($data as $cat) {
                if ($cat['status'] === $status) {
                    $categories[] = $cat;
                }
            }

            return $categories;

        } catch (Exception $ex) {
            Logger::Log("Failed to modify category status by id. ".$ex->getMessage(), logLvl::Error);
            throw new CategoryServiceException("Nem sikerült lekérdezni a kategóriák státuszát.");
        }
    }

    public static function UpdateCategoryStatus(array $ids, string $status)
    {
        global $cfg;
        $model = new CategoriesModel();

        if (! in_array($status, $cfg['statuses'])) {
            throw new StatusException("A megadott státusz nem valid.");
        }

        if(empty($ids)) {
            throw new CategoryServiceException("A megadott azonosítók hibásak.");
        }

        foreach ($ids as $id) {
            if (!ctype_digit($id)) {
                throw new CategoryServiceException("A megadott azonosítók hibásak.");
            }
            $model->UpdateCategoryStatus($id, $status);
        }
    }
}