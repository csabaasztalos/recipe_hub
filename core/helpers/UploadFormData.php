<?php
final class UploadFormData
{
    public static function GetFormData(): array
    {
        // Ingredients
        $ingredientListHTML = "";
        $ingredientModel = new IngredientModel();
        $ingredientResult = $ingredientModel->GetAllIngredients();
        if (!$ingredientResult) {
            throw new DBException("Nem sikerült lekérni a hozzávalókat az adatbázisból.");
        }
        if ($ingredientResult->num_rows > 0) {
            while ($row = $ingredientResult->fetch_assoc()) {
                $ingredientListHTML .= "<option value=\"{$row['id']}\">{$row['name']}</option>";
            }
        } else {
            $ingredientListHTML .= "<option value=\"\">Nincs megjeleníthető hozzávaló</option>";
        }

        // Categories
        $categoryListHTML = "";
        $categoriesModel = new CategoriesModel();
        $categoryResult = $categoriesModel->GetAllCategories();
        if (!$ingredientResult) {
            throw new DBException("Nem sikerült lekérni a kategóriákat az adatbázisból.");
        }
        if ($categoryResult->num_rows > 0) {
            while ($row = $categoryResult->fetch_assoc()) {
                if ($row['status'] === "approved") {
                    $categoryListHTML .= "<option value=\"{$row['id']}\">{$row['name']}</option>";
                }
            }
        } else {
            $categoryListHTML .= "<option value=\"\">Nincs megjeleníthető kategória</option>";
        }

        // Tags
        $tagsSelect = [];
        $tagsList = [];
        $tagsModel = new TagsModel();
        $tagCategories = $tagsModel->GetTagCategories();
        if (!$tagCategories) {
            throw new DBException("Nem sikerült lekérni a címke kategóriákat az adatbázisból.");
        }
        $tagsResult = $tagsModel->GetAllTags();
        if (!$tagsResult) {
            throw new DBException("Nem sikerült lekérni a címkéket az adatbázisból.");
        }
        $tagsData = [
            "DIFTAGS" => "",
            "TIMETAGS" => "",
            "BUDGETTAGS" => "",
            "OCCTAGS" => "",
            "METHODTAGS" => "",
            "STYLETAGS" => ""
        ];

        if ($tagCategories->num_rows > 0) {
            while ($row = $tagCategories->fetch_assoc()) {
                $tagsSelect[] = $row['tag_category'];
            }
            if ($tagsResult->num_rows > 0) {
                while ($row = $tagsResult->fetch_assoc()) {
                    if ($row['status'] === "approved") {
                        $tagsList[] = $row;
                    }
                }
            }

            for ($i = 0; $i < count($tagsSelect); $i++) {
                $tagsListHTML = "";
                foreach ($tagsList as $tag) {
                    if ($tag['tag_category'] === $tagsSelect[$i]) {
                        $tagsListHTML .= "<option value=\"{$tag['id']}\">{$tag['name']}</option>";
                    }
                }
                switch ($tagsSelect[$i]) {
                    case 'Nehézség':
                        $tagsData["DIFTAGS"] = $tagsListHTML;
                        break;
                    case 'Elkészítési idő':
                        $tagsData["TIMETAGS"] = $tagsListHTML;
                        break;
                    case 'Költségvetés':
                        $tagsData["BUDGETTAGS"] = $tagsListHTML;
                        break;
                    case 'Alkalom':
                        $tagsData["OCCTAGS"] = $tagsListHTML;
                        break;
                    case 'Elkészítési mód':
                        $tagsData["METHODTAGS"] = $tagsListHTML;
                        break;
                    case 'Stílus':
                        $tagsData["STYLETAGS"] = $tagsListHTML;
                        break;
                }
            }
        }

        return [
            "INGREDIENTLIST" => $ingredientListHTML,
            "CATEGORYLIST" => $categoryListHTML,
            "DIFTAGS" => $tagsData["DIFTAGS"],
            "TIMETAGS" => $tagsData["TIMETAGS"],
            "BUDGETTAGS" => $tagsData["BUDGETTAGS"],
            "OCCTAGS" => $tagsData["OCCTAGS"],
            "METHODTAGS" => $tagsData["METHODTAGS"],
            "STYLETAGS" => $tagsData["STYLETAGS"]
        ];
    }
}