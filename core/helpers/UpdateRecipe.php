<?php
class UpdateRecipe
{
    private DBHandler $db;

    public function __construct()
    {
        $this->db = Container::Get('db');
    }
    public function Update(int $id, array $post, ?array $recipeData,
        ?array $categoriesData, ?array $tagsData,
        ?array $ingredientsData, ?array $imagesData, ?int $submittedBy): bool
    {
        global $cfg;
        $changed = false;
        if ($submittedBy === null) {
            $submittedBy = $cfg["adminUserID"];
        }

        $oldData = RecipeService::FetchRecipeData($id);

        if (isset($recipeData) && is_array($recipeData)) {
            $fields = [];
            $params = [];

            if (isset($recipeData['title']) && $recipeData['title'] !== "" && $oldData['title'] !== $recipeData['title']) {
                $fields[] = "title = ?";
                $params[] = new DBParam(DBTypes::STRING, $recipeData['title']);
            }
            if (isset($recipeData['description']) && $recipeData['description'] !== "" && $oldData['description'] !== $recipeData['description']) {
                $fields[] = "description = ?";
                $params[] = new DBParam(DBTypes::STRING, $recipeData['description']);
            }
            if (isset($recipeData['instructions']) && $recipeData['instructions'] !== "" && $oldData['instructions'] !== $recipeData['instructions']) {
                $fields[] = "instructions = ?";
                $params[] = new DBParam(DBTypes::STRING, $recipeData['instructions']);
            }
            if (isset($recipeData['servings']) && (int) $recipeData['servings'] > 0 && (int) $oldData['serving_size'] !== (int) $recipeData['servings']) {
                $fields[] = "serving_size = ?";
                $params[] = new DBParam(DBTypes::INT, (int) $recipeData['servings']);
            }

            if (! empty($fields)) {
                $fields[] = "status = ?";
                $params[] = new DBParam(DBTypes::STRING, "pending");
                $params[] = new DBParam(DBTypes::INT, $id);
                $sql = "UPDATE `recipes` SET ".implode(',', $fields)." WHERE id = ?";

                $result = $this->db->RunQuery($sql, $params);
                Logger::Log("Editing recipe({$id}): ".($result ? 'SUCCESS' : 'FAILED'), logLvl::Info);
                Logger::Log("Fields to update: ".implode(", ", $fields), logLvl::Warning);

                if (! $result) {
                    throw new RecipeUpdateException("A recept adatainak frissítése sikeretelen volt!");
                }
            }
        }

        //categories
        if (isset($categoriesData) && is_array($categoriesData)) {
            $catModel = new CategoriesModel();
            $oldCategories = array_column(CategoryService::FetchRecipeCategories($id), "id");

            sort($categoriesData);
            sort($oldCategories);
            $diff = $categoriesData !== $oldCategories;

            if ($diff) {
                if (! $catModel->DeleteRecipeCategory($id, $oldCategories)) {
                    throw new RecipeUpdateException("A recept kategóriák törlése sikeretelen volt!");
                }

                if (! $catModel->AddRecipeCategory($id, $categoriesData)) {
                    $catModel->AddRecipeCategory($id, $oldCategories);
                    throw new RecipeUpdateException("A recept kategóriák frissítése sikeretelen volt!");
                }
                $changed = true;
            }
        }

        //tags
        if (isset($tagsData) && is_array($tagsData)) {
            $tagsModel = new TagsModel();
            $oldTags = array_column(TagsService::FetchRecipeTags($id), "id");

            sort($tagsData);
            sort($oldTags);
            $diff = $tagsData !== $oldTags;

            if ($diff) {
                if (! $tagsModel->DeleteRecipeTags($id, $oldTags)) {
                    throw new RecipeUpdateException("A recept címkék törlése sikeretelen volt!");
                }

                if (! $tagsModel->AddRecipeTags($id, $tagsData)) {
                    $tagsModel->AddRecipeTags($id, $oldTags);
                    throw new RecipeUpdateException("A recept címkék frissítése sikeretelen volt!");
                }
                $changed = true;
            }
        }

        //ingredients
        if (isset($ingredientsData) && is_array($ingredientsData)) {
            $ingModel = new IngredientModel();
            $oldIngredients = IngredientService::FetchRecipeIngredients($id);
            $oldIds = array_column($oldIngredients, 'id');
            $newIds = array_column($ingredientsData, 'id');

            sort($oldIds);
            sort($newIds);
            $diff = $oldIds !== $newIds;

            if ($diff) {
                if (! $ingModel->DeleteRecipeIngredients($id, array_column($oldIngredients, 'id'))) {
                    throw new RecipeUpdateException("A recept hozzávalók törlése sikeretelen volt!");
                }

                foreach ($ingredientsData as $ing) {
                    $result = $ingModel->AddRecipeIngredient($id, $ing['id'], $ing['quantity'], $ing['unit']);
                    if (! $result) {
                        $ingModel->DeleteRecipeIngredients($id, array_column($ingredientsData, 'id'));
                        foreach ($oldIngredients as $ing) {
                            $ingModel->AddRecipeIngredient($id, $ing['id'], $ing['quantity'], $ing['unit']);
                        }
                        throw new RecipeUpdateException("A recept hozzávalóinak frissítése sikeretelen volt!");
                    }
                }
                $changed = true;
            }
        }

        //images
        $removedMain = $post['removedMainImage'] ?? '';
        $removedExtras = $post['removedExtraImages'] ?? '';
        $originalMain = $post['originalMainImage'];
        $originalExtras = $post['originalExtraImages'];
        $originalPaths = array_filter(explode(';', $originalExtras));
        $originalExtrasCount = count($originalPaths);

        $newMain = null;
        $newExtras = null;
        if (isset($imagesData) && is_array($imagesData)) {
            ($imagesData['mainImage']['error'] === UPLOAD_ERR_OK) ? ($newMain = $imagesData['mainImage']) : ($newMain = null);
            $errCheck = $imagesData['images']['error'] ?? null;
            $newExtras = (is_array($errCheck) && in_array(UPLOAD_ERR_OK, $errCheck)) ? $imagesData['images'] : null;
        }

        //main
        if ($removedMain && ! $newMain) {
            throw new RecipeUpdateException("Főkép feltöltése kötelező.");
        } elseif ($newMain) {
            $extension = pathinfo($newMain['name'], PATHINFO_EXTENSION);
            $fileName = "main.$extension";
            $mainPath = $cfg['imgDisplayPath']."{$id}/{$fileName}";
            RecipeQueryService::UpdateRecipeImages($id, $mainPath, null);

            $relativePath = str_replace($cfg['imgDisplayPath'], '', $mainPath);
            $fullPath = $cfg['recipeImageDir'].$relativePath;

            $relativeOgPath = str_replace($cfg['imgDisplayPath'], '', $originalMain);
            $fullOgPath = $cfg['recipeImageDir'].$relativeOgPath;
            if (file_exists($fullOgPath)) {
                unlink($fullOgPath);
            }

            if (move_uploaded_file($newMain['tmp_name'], $fullPath)) {
                Logger::Log("$mainPath, is successfully uploaded", logLvl::Info);
            } else {
                Logger::Log("Failed to move uploaded file ($fullPath)", logLvl::Error);
                throw new RecipeUploadException("A kép feltöltése sikertelen.");
            }
            $changed = true;
        }

        //etras
        if ($newExtras && ! $removedExtras) {
            $originalPaths = array_filter(explode(';', $originalExtras));
            $extrasPath = implode(';', $originalPaths);
            $extraCount = count($newExtras['tmp_name']) + $originalExtrasCount;

            if ($extraCount > 5) {
                throw new RecipeUploadException("Maximum 5 db extra képet tölthetsz fel!");
            }

            for ($i = 0; $i < count($newExtras['tmp_name']); $i++) {
                $fileIndex = $originalExtrasCount + $i;
                $extension = pathinfo($newExtras['name'][$i], PATHINFO_EXTENSION);
                $fileName = "extra{$fileIndex}.$extension";
                $filePath = $cfg['imgDisplayPath']."{$id}/{$fileName}";
                $relativePath = str_replace($cfg['imgDisplayPath'], '', $filePath);
                $fullPath = $cfg['recipeImageDir'].$relativePath;

                $extrasPath .= ($extrasPath !== '' ? ';' : '').$filePath;

                if (move_uploaded_file($newExtras['tmp_name'][$i], $fullPath)) {
                    Logger::Log("$fullPath, is successfully uploaded", logLvl::Info);
                } else {
                    Logger::Log("Failed to move uploaded file ($fullPath)", logLvl::Error);
                    throw new RecipeUploadException("A képek feltöltése sikertelen.");
                }
            }

            if ($extrasPath) {
                RecipeQueryService::UpdateRecipeImages($id, null, $extrasPath);
                $changed = true;
            }

        } elseif ($newExtras && $removedExtras) {
            $originalPaths = array_filter(explode(';', $originalExtras));
            $removedPaths = array_filter(explode(';', $removedExtras));

            $remainingPaths = array_values(array_diff($originalPaths, $removedPaths));

            if (count($remainingPaths) + count($newExtras['tmp_name']) > 5) {
                throw new RecipeUploadException("Maximum 5 db extra képet tölthetsz fel!");
            }

            foreach ($removedPaths as $path) {
                $relativePath = str_replace($cfg['imgDisplayPath'], '', $path);
                $fullPath = $cfg['recipeImageDir'].$relativePath;
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                }
            }

            $startIndex = $originalExtrasCount;
            $newPaths = [];
            foreach ($newExtras['tmp_name'] as $i => $tmpName) {
                $ext = pathinfo($newExtras['name'][$i], PATHINFO_EXTENSION);
                $fileName = "extra".($startIndex + $i).".$ext";
                $filePath = $cfg['imgDisplayPath']."$id/$fileName";
                if (move_uploaded_file($tmpName, $filePath)) {
                    $newPaths[] = $filePath;
                } else {
                    throw new RecipeUploadException("A képek feltöltése sikertelen.");
                }
            }

            $allPaths = array_merge($remainingPaths, $newPaths);
            RecipeQueryService::UpdateRecipeImages($id, null, implode(';', $allPaths));
            $changed = true;
        } elseif (! $newExtras && $removedExtras) {
            $originalPaths = array_filter(explode(';', $originalExtras));
            $removedPaths = array_filter(explode(';', $removedExtras));

            $remainingPaths = array_values(array_diff($originalPaths, $removedPaths));

            foreach ($removedPaths as $path) {
                $relativePath = str_replace($cfg['imgDisplayPath'], '', $path);
                $fullPath = $cfg['recipeImageDir'].$relativePath;
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                }
            }

            RecipeQueryService::UpdateRecipeImages($id, null, !empty($remainingPaths) ? implode(';', $remainingPaths) : "");
            $changed = true;
        }

        if ($changed) {
            RecipeQueryService::UpdateRecipeStatus($id, "pending");
        }

        return true;
    }
}
