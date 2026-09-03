<?php
final class RecipeImageHandler
{
    public static function Validate(array $files, bool $main, bool $extra): bool
    {
        global $cfg;

        if ($main) {
            $mainImage = $files['mainImage'] ?? null;
            if (! $mainImage) {
                throw new RecipeUploadException("Legalább egy képet (fő képet) kötelező feltölteni!");
            }

            if ($mainImage['error'] !== UPLOAD_ERR_OK) {
                throw new RecipeUploadException("A főkép feltöltése sikeretelen volt!");
            }

            if ($mainImage['error'] === UPLOAD_ERR_NO_FILE) {
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mainImageMimeType = $finfo->file($mainImage["tmp_name"]);

                if (! in_array($mainImageMimeType, $cfg['allowedIMGMimeTypes'])) {
                    throw new RecipeUploadException("Nem elfgoadott fájltípus! A megengedett típusok: JPG, PNG, SVG, WEBP");
                }
                if ($mainImage['size'] > 5 * 1024 * 1024) {
                    throw new RecipeUploadException("A főkép mérete túl nagy! (Max 5MB)");
                }
            }

        }

        if ($extra) {
            $extraImages = $files['images']['tmp_name'] ?? null;
            $hasExtra = $files['images'] ?? null;

            if (count($extraImages) > 5) {
                throw new RecipeUploadException("Maximum 5 db extra képet tölthetsz fel!");
            }

            $imageCounter = 0;

            if ($hasExtra) {
                for ($i = 0; $i < count($extraImages); $i++) {
                    if ($hasExtra['error'][$i] === UPLOAD_ERR_NO_FILE) {
                        continue;
                    }
                    if ($hasExtra['error'][$i] !== UPLOAD_ERR_OK) {
                        throw new RecipeUploadException("Az extra képek feltöltése sikeretelen volt!");
                    }

                    $imgMimeType = $finfo->file($hasExtra['tmp_name'][$i]);
                    if (! in_array($imgMimeType, $cfg['allowedIMGMimeTypes'])) {
                        throw new RecipeUploadException("Nem elfgoadott fájltípus! A megengedett típusok: JPG, PNG, SVG, WEBP");
                    }
                    if ($hasExtra['size'][$i] > 5 * 1024 * 1024) {
                        throw new RecipeUploadException("Az egyik extra kép mérete túl nagy! (Max 5MB)");
                    }
                    $imageCounter++;
                }
            }
        }

        return $imageCounter !== 0 ? true : false;
    }

    public static function Save(array $files, int $recipeId, bool $extraImage): void
    {
        global $cfg;
        $mainImage = $files['mainImage'];
        $extra = $files['images'];
        $uploadFolder = $cfg['recipeImageDir'].$recipeId;
        $extraCount = count($files['images']['tmp_name']);

        try {
            if (! is_dir($uploadFolder)) {
                mkdir($uploadFolder, 0755, true);
            }

            $extension = pathinfo($mainImage['name'], PATHINFO_EXTENSION);
            $fileName = "main.$extension";
            $filePath = "$uploadFolder/$fileName";
            $mainPath = $cfg['imgDisplayPath']."{$recipeId}/{$fileName}";

            if (move_uploaded_file($mainImage['tmp_name'], $filePath)) {
                Logger::Log("$filePath, is successfully uploaded", logLvl::Info);
            } else {
                Logger::Log("Failed to move uploaded file ($filePath/$fileName)", logLvl::Error);
                throw new RecipeUploadException("A képek feltöltése sikertelen.");
            }

            if ($extraImage) {
                $extraPath = "";
                for ($i = 0; $i < $extraCount; $i++) {
                    $extension = pathinfo($extra['name'][$i], PATHINFO_EXTENSION);
                    $fileName = "extra$i.$extension";
                    $filePath = "$uploadFolder/$fileName";

                    if ($i < ($extraCount - 1)) {
                        $extraPath .= $cfg['imgDisplayPath']."{$recipeId}/{$fileName};";
                    } else {
                        $extraPath .= $cfg['imgDisplayPath']."{$recipeId}/{$fileName}";
                    }

                    if (move_uploaded_file($extra['tmp_name'][$i], $filePath)) {
                        Logger::Log("$filePath, is successfully uploaded", logLvl::Info);
                    } else {
                        Logger::Log("Failed to move uploaded file ($filePath/$fileName)", logLvl::Error);
                        throw new RecipeUploadException("A képek feltöltése sikertelen.");
                    }
                }
            }
            $extraPath ?? null;
            RecipeQueryService::UploadRecipeImages($recipeId, $mainPath, $extraPath);
        } catch (Exception $ex) {
            throw new RecipeUploadException("A képek feltöltése hibába ütközött.");
        }
    }
}