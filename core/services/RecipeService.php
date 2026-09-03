<?php

final class RecipeService
{
    public static function GetRecipeDetails(int $recipeId, ?int $userId): array
    {
        try {
            $recipeData = self::FetchRecipeData($recipeId);

            if (! $recipeData) {
                throw new RecipeNotFoundException("Recipe does{$recipeId} not exist.");
            }

            if ($recipeData['status'] !== "approved" && $userId !== 1) {
                throw new StatusException("Recipe is yet not approved.");
            }

            $doesExists = null;
            if ($userId) {
                $doesExists = UserService::CheckUserId($userId);
            }

            return [
                CategoryService::FetchRecipeCategories($recipeId),
                TagsService::FetchRecipeTags($recipeId),
                IngredientService::FetchRecipeIngredients($recipeId),
                $recipeData['instructions'],
                date('Y. m. d.', strtotime($recipeData['created_at'])),
                $recipeData['title'],
                $recipeData['serving_size'] ?? 1,
                $recipeData['description'],
                $recipeData['user_id']
            ];
        } catch (Exception $ex) {
            Logger::Log("Could not retrieve recipe details {$recipeId}: ".$ex->getMessage(), logLvl::Error);
            throw new RecipeServiceException("Could not retrieve recipe details. ");
        }

    }

    public static function GetEditRecipeData(int $recipeId, int $userId): array
    {
        global $cfg;

        try {
            $recipeData = self::FetchRecipeData($recipeId);

            if (! $recipeData) {
                throw new RecipeNotFoundException("Recipe does{$recipeId} not exist.");
            }

            if ($userId) {
                UserService::CheckUserId($userId);
            }

            if (! $userId || ($userId !== (int) $recipeData['user_id'] && $userId !== $cfg['adminUserID'])) {
                throw new PermissionException("User doesn't have permission to view this page.");
            }

            return [
                CategoryService::FetchRecipeCategories($recipeId),
                array_column(TagsService::FetchRecipeTags($recipeId), "id"),
                IngredientService::FetchRecipeIngredients($recipeId),
                $recipeData['instructions'],
                $recipeData['title'],
                $recipeData['serving_size'] ?? 1,
                $recipeData['description'],
            ];

        } catch (Exception $ex) {
            Logger::Log("Could not retrieve recipe data {$recipeId}: ".$ex->getMessage(), logLvl::Error);
            throw new RecipeServiceException("Could not retrieve recipe data. ", 0, $ex);
        }

    }

    public static function GetRecipeImages(int $recipeId): array
    {
        try {
            global $cfg;
            $recipesModel = new RecipesModel();

            $recipeImagesResult = $recipesModel->GetRecipesImages($recipeId);

            if (! $recipeImagesResult || $recipeImagesResult->num_rows === 0) {
                return [
                    'mainImage' => $cfg["noImage"],
                    'extraImages' => []
                ];
            }

            $recipeImages = $recipeImagesResult->fetch_assoc();
            $extraImages = [];

            if (isset($recipeImages['extra_images']) && ! empty($recipeImages['extra_images'])) {
                $extraImages = explode(';', $recipeImages['extra_images']);
            }

            return [
                'mainImage' => $recipeImages['main_image'] ?? $cfg["noImage"],
                'extraImages' => $extraImages
            ];
        } catch (Exception $ex) {
            Logger::Log("Could not retrieve recipe images {$recipeId}: ".$ex->getMessage(), logLvl::Error);
            throw new RecipeServiceException("Could not retrieve recipe images. ", 0, $ex);
        }
    }

    public static function ExpandRecipeWithImage(array $recipes): array
    {
        global $cfg;
        $recipesWithImage = [];

        foreach ($recipes as $recipe) {
            $recipe["images"] = self::GetRecipeImages($recipe['id']);
            if (! $recipe["images"]['mainImage']) {
                $recipe["images"]['extraImages'] = $cfg['noImage'];
            }
            $recipesWithImage[] = $recipe;
        }

        return $recipesWithImage;
    }

    public static function CreateRecipe(array $post, array $files): int
    {
        global $cfg;
        $recipesModel = new RecipesModel();
        $userId = $_SESSION[$cfg['permissionSessionKey']]['data']['id'];
        ValidateUploadForm::Validate($post);
        $hasExtra = RecipeImageHandler::Validate($files, true, true);

        try {
            $instructions = implode(";", $post['instructions']);
            $recipeId = $recipesModel->CreateRecipe(
                $post['recipeTitle'], $instructions,
                $post['smallDescription'],
                $userId, $userId,
                "recipe uploaded by user: {$userId}", $post['servings']
            );

            if (! $recipeId) {
                throw new RecipeUploadException("Recipe creation failed.");
            }

            RecipeImageHandler::Save($files, $recipeId, $hasExtra);
            TagsService::AddRecipeTags($recipeId, $post['tags']);
            CategoryQueryService::AddRecipeCategory($recipeId, $post['selectedCategoryIds']);
            IngredientQueryService::AddRecipeIngredient($recipeId, $post['ingredients']);

            return $recipeId;
        } catch (Exception $ex) {
            Logger::Log("Could not create recipe: ".$ex->getMessage(), logLvl::Error);
            if (isset($recipeId)) {
                self::DeleteRecipe($recipeId);
            }
            throw new RecipeServiceException("A recept feltöltése sikertelen.", 0, $ex);
        }

    }

    public static function UpdateRecipe(int $recipeId, int $userId, array $post, array $files): bool
    {
        try {

                $recipeData = [
                    'title' => $post['recipeTitle'] ?? null,
                    'servings' => $post['servings'] ?? null,
                    'description' => $post['smallDescription'] ?? null,
                    'instructions' => !empty($post['instructions']) ? (implode(";", $post['instructions'])): []
                ];
                
                $post['selectedCategoryIds'] ? $categoriesData = explode(";", $post['selectedCategoryIds']) : $categoriesData = null;
                $update = new UpdateRecipe();

                return $update->Update(
                    $recipeId,
                    $post,
                    $recipeData,
                    $categoriesData,
                    $post['tags'],
                    $post['ingredients'],
                    $files,
                    $userId
                );
        } catch (Exception $ex) {
            Logger::Log("Could not update recipe: ".$ex->getMessage(), logLvl::Error);
            throw new RecipeServiceException("Recept módosítása sikeretelen. ".$ex->getMessage());
        }

    }

    public static function ApproveRecipe($id): void
    {
        global $cfg;
        $model = new RecipesModel();

        try {
            self::CheckRecipeId($id);
            $status = self::CheckRecipeStatus($id);

            if ($status !== 'approved') {
                $model->UpdateRecipeStatus($id, "approved", null);
                Logger::Log("Recipe({$id}) has been approved.)", logLvl::Info);
            } else {
                Logger::Log("The recipe({$id}) is already approved.", logLvl::Warning);
            }
        } catch (Exception $ex) {
            Logger::Log("Failed to approve recipe({$id}). ".$ex->getMessage(), logLvl::Error);
            throw new RecipeActionException("A recept elfogadása sikertelen.");
        }
    }

    public static function RejectRecipe($id)
    {
        global $cfg;
        $model = new RecipesModel();

        try {
            self::CheckRecipeId($id);
            $status = self::CheckRecipeStatus($id);

            if ($status !== 'rejected') {
                $model->UpdateRecipeStatus($id, "rejected", null);
                Logger::Log("Recipe({$id}) has been rejected.)", logLvl::Info);
            } else {
                Logger::Log("The recipe({$id}) is already rejected.", logLvl::Warning);
            }

        } catch (Exception $ex) {
            Logger::Log("Failed to reject recipe({$id}). ".$ex->getMessage(), logLvl::Error);
            throw new RecipeActionException("A recept elutasítása sikertelen.");
        }
    }

    public static function DeleteRecipe(int $id)
    {
        global $cfg;
        $model = new RecipesModel();

        try {
            self::CheckRecipeId($id);

            $deleteResult = $model->DeleteRecipe($id, null, 'Recipe deleted');
            if ($deleteResult) {
                Logger::Log("Recipe({$id}) has been deleted.", logLvl::Info);
            } else {
                Logger::Log("The recipe({$id}) could not be deleted.", logLvl::Warning);
            }
        } catch (Exception $ex) {
            Logger::Log("Failed to delete recipe({$id}). ".$ex->getMessage(), logLvl::Error);
            throw new RecipeActionException("A recept nem került törlésre.");
        }
    }

    public static function CheckRecipeId(int $id): void
    {
        try {
            $model = new RecipesModel();

            $recipeData = $model->GetRecipe($id)->fetch_assoc();
            if (! $recipeData) {
                Logger::Log("Recipe({$id}) does not exist.", logLvl::Warning);
                throw new RecipeNotFoundException("Ilyen recept nem létezik.");
            }
        } catch (Exception $ex) {
            Logger::Log("Failed to check recipe by id. ".$ex->getMessage(), logLvl::Error);
            throw new RecipeServiceException("Nem sikerült ellenőrizni a receptet.");
        }
    }

    private static function CheckRecipeStatus(int $id): string
    {
        try {
            global $cfg;
            $model = new RecipesModel();

            $recipeData = $model->GetRecipeStatus($id)->fetch_assoc();
            if (! $recipeData) {
                Logger::Log("Recipe({$id}) does not exist.", logLvl::Error);
                throw new RecipeNotFoundException("Ilyen recept nem létezik.");
            }
            $status = $recipeData['status'];

            if (! in_array($status, $cfg['statuses'])) {
                Logger::Log("Recipe({$id}) status invalid.", logLvl::Warning);
                throw new RecipeNotFoundException("A recept státusza nem valid.");
            }
            return $status;

        } catch (Exception $ex) {
            Logger::Log("Failed to check recipe status by id. ".$ex->getMessage(), logLvl::Error);
            throw new RecipeServiceException("Nem sikerült ellenőrizni a recept státuszát.");
        }
    }

    public static function FetchRecipeData(int $recipeId): array
    {
        $model = new RecipesModel();
        $result = $model->GetRecipeWithUsername($recipeId);

        if (! $result || $result->num_rows === 0) {
            throw new RecipeNotFoundException("Failed to fetch recipe data.");
        }
        return $result->fetch_assoc();
    }

    public static function GetAllRecipes(): array
    {
        $model = new RecipesModel();
        $result = $model->GetAllRecipes();
        $recipeData = [];

        if (! $result) {
            throw new DBException("Nem sikerült lekérni a receptek adatait.");
        }
        if ($result && $result->num_rows > 0) {
            while ($data = $result->fetch_assoc()) {
                $recipeData[] = $data;
            }
        }

        return $recipeData;
    }
}