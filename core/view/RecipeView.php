<?php

final class RecipeView
{
    public static function BuildCategoryLabels(array $categoriesData): string
    {
        $catHTML = "";
        if (! empty($categoriesData)) {
            foreach ($categoriesData as $category) {
                $catHTML .= "<span class=\"categoryTag\">{$category['name']}</span> ";
            }
        } else {
            $catHTML = "<span class=\"categoryTag\">Nincs recepthez rendelt kategória</span>";
        }
        return $catHTML;
    }

    public static function BuildChosenIngredients(array $ingredientsData): string
    {
        $ingHTML = "";
        if (! empty($ingredientsData)) {
            foreach ($ingredientsData as $ingredient) {
                $ingHTML .= "<li>{$ingredient['name']}: <span class=\"ingredient-quantity\">{$ingredient['quantity']} {$ingredient['unit']}</span></li> ";
            }
        } else {
            $ingHTML = "<li>Nincs recepthez rendelt  hozzávaló</li>";
        }
        return $ingHTML;
    }

    public static function BuildInsturctionList(string $instructionsData): string
    {
        $insHTML = "";
        if ($instructionsData !== "") {
            $instructions = explode(";", $instructionsData);
            foreach ($instructions as $ins) {
                $insHTML .= "<li>".$ins."</li>";
            }
        } else {
            $insHTML .= "<li>Ehhez a recepthez nem adtak utasítást.</li>";
        }
        return $insHTML;
    }

    public static function BuildTagLabels(array $tagsData): array
    {
        $tagFlags = [
            "DIFFICULITY" => "",
            "TIME" => "",
            "BUDGET" => "",
            "OCCASION" => "",
            "METHOD" => "",
            "STYLE" => ""
        ];
        if (! empty($tagsData)) {
            $tagFlags = [];
            foreach ($tagsData as $tag) {
                switch ($tag['tag_category']) {
                    case 'Nehézség':
                        $tagFlags["DIFFICULITY"] = $tag['name'];
                        break;
                    case 'Elkészítési idő':
                        $tagFlags["TIME"] = $tag['name'];
                        break;
                    case 'Költségvetés':
                        $tagFlags["BUDGET"] = $tag['name'];
                        break;
                    case 'Alkalom':
                        $tagFlags["OCCASION"] = $tag['name'];
                        break;
                    case 'Elkészítési mód':
                        $tagFlags["METHOD"] = $tag['name'];
                        break;
                    case 'Stílus':
                        $tagFlags["STYLE"] = $tag['name'];
                        break;
                    default:
                        break;
                }
            }
        }
        return $tagFlags;
    }

    public static function BuildExtraImagesGallery(array $extraImages): string
    {
        if (empty($extraImages)) {
            return "<p>Ehhez a recepthez nem töltöttek fel további képeket</p>";
        }

        $html = '';
        foreach ($extraImages as $img) {
            $html .= '<img src="'.$img.'" alt="Extra image">';
        }

        return $html;
    }

    public static function BuildIngredientBlocks(array $ingredients, array $ingredientOptions): string
    {
        $ingHtml = "";
        $ingOptHtml = "";

        if (! empty($ingredientOptions) && $ingredientOptions) {
            foreach ($ingredientOptions as $ingOpt) {
                $ingOptHtml .= "<option value=\"{$ingOpt['id']}\">{$ingOpt['name']}</option>";
            }
        } else {
            $ingOptHtml = "<option value=\"\">Nincs megjeleníthető hozzávaló</option>";
        }
        try {
            if (! empty($ingredients)) {
                $ingredientIndex = 0;
                foreach ($ingredients as $ing) {
                    $qty = (int) $ing['quantity'] ?? 1;
                    $unit = $ing['unit'] ?? "";
                    $id = (int) $ing['id'] ?? null;
                    $nameResult = IngredientQueryService::GetIngredientName($id);
                    $name = $nameResult['name'];

                    $ingHtml .=
                        "<div class=\"ingredients ingredientBlock\" id=\"ingredients{$ingredientIndex}\">
                        <label for=\"ingredientName{$ingredientIndex}\"><b>Hozzávaló neve</b></label>
                        <select id=\"ingredientName{$ingredientIndex}\" name=\"ingredients[".$ingredientIndex."][id]\" class=\"form-select\">
                        <option value=\"{$id}\">{$name}</option>
                        {$ingOptHtml}
                        </select>
                        <div class=\"ingredientNumbers\">
                            <div class=\"ingredientField\">
                                <label for=\"ingredientQuantity{$ingredientIndex}\"><b>Mennyisége: </b></label>
                                <input id=\"ingredientQuantity{$ingredientIndex}\" name=\"ingredients[".$ingredientIndex."][quantity]\" value=\"$qty\" class=\"form-control\" required
                                type=\"number\" min=\"1\">
                            </div>
                            <div class=\"ingredientField\">
                                <label for=\"ingredientUnit{$ingredientIndex}\"><b>Mértékegysége: </b></label>
                                <input id=\"ingredientUnit{$ingredientIndex}\" name=\"ingredients[".$ingredientIndex."][unit]\" class=\"form-control\" required type=\"text\"
                                maxlength=\"90\" minlength=\"1\" value=\"$unit\">
                            </div>
                        </div>
                    </div>";
                    $ingredientIndex++;
                }
            }
            return $ingHtml;
        } catch (\Throwable $th) {
            return self::DefaultIngredientBlock($ingredientOptions);
        }
    }

    public static function EditIngredientBlocks(array $ingredients, array $ingredientOptions): string
    {
        $ingHtml = "";
        $ingOptHtml = "";

        if (! empty($ingredientOptions) && $ingredientOptions) {
            foreach ($ingredientOptions as $ingOpt) {
                $ingOptHtml .= "<option value=\"{$ingOpt['id']}\">{$ingOpt['name']}</option>";
            }
        } else {
            $ingOptHtml = "<option value=\"\">Nincs megjeleníthető hozzávaló</option>";
        }
        $button = "";
        try {
            if (! empty($ingredients)) {
                $ingredientIndex = 0;
                foreach ($ingredients as $ing) {
                    if($ingredientIndex !== 0) {$button = "<button type=\"button\" class=\"ingredientDeleteBtn\" title=\"Törlés\">&times;</button>";}
                    $qty = (int) $ing['quantity'] ?? 1;
                    $unit = $ing['unit'] ?? "";
                    $id = (int) $ing['id'] ?? null;
                    $nameResult = IngredientQueryService::GetIngredientName($id);
                    $name = $nameResult['name'];

                    $ingHtml .=
                        "<div class=\"ingredients ingredientBlock\" id=\"ingredients{$ingredientIndex}\">
                        $button
                        <label for=\"ingredientName{$ingredientIndex}\"><b>Hozzávaló neve</b></label>
                        <select id=\"ingredientName{$ingredientIndex}\" name=\"ingredients[".$ingredientIndex."][id]\" class=\"form-select\">
                        <option value=\"{$id}\">{$name}</option>
                        {$ingOptHtml}
                        </select>
                        <div class=\"ingredientNumbers\">
                            <div class=\"ingredientField\">
                                <label for=\"ingredientQuantity{$ingredientIndex}\"><b>Mennyisége: </b></label>
                                <input id=\"ingredientQuantity{$ingredientIndex}\" name=\"ingredients[".$ingredientIndex."][quantity]\" value=\"$qty\" class=\"form-control\" required
                                type=\"number\" min=\"1\">
                            </div>
                            <div class=\"ingredientField\">
                                <label for=\"ingredientUnit{$ingredientIndex}\"><b>Mértékegysége: </b></label>
                                <input id=\"ingredientUnit{$ingredientIndex}\" name=\"ingredients[".$ingredientIndex."][unit]\" class=\"form-control\" required type=\"text\"
                                maxlength=\"90\" minlength=\"1\" value=\"$unit\">
                            </div>
                        </div>
                    </div>";
                    $ingredientIndex++;
                }
            }
            return $ingHtml;
        } catch (\Throwable $th) {
            throw new IngredientException("A recept hozzávalói nem megjeleníthetőek.");
        }
    }

    public static function BuildInstructionBlocks(array $instructions): string
    {
        $insHtml = '';
        $counter = 1;

        if (! empty($instructions)) {
            foreach ($instructions as $instruction) {
                $instructionValue = trim($instruction);

                $insHtml .= "
                    <div class=\"instruction\" id=\"instruction{$counter}\">
                        <label for=\"instructionText{$counter}\">{$counter}. Lépés</label>
                        <input id=\"instructionText{$counter}\" name=\"instructions[]\" class=\"form-control\" required type=\"text\"
                        value=\"{$instructionValue}\" >
                    </div>";
                $counter++;
            }
        }

        return $insHtml;
    }

    public static function EditInstructionBlocks(array $instructions): string
    {
        $insHtml = '';
        $button = "";
        $counter = 1;

        if (! empty($instructions)) {
            foreach ($instructions as $instruction) {
                if($counter > 1) $button = "<button type=\"button\" class=\"instructionDeleteBtn\" title=\"Törlés\">&times;</button>";
                $instructionValue = trim($instruction);

                $insHtml .= "
                    <div class=\"instruction\" id=\"instruction{$counter}\">
                        $button
                        <label for=\"instructionText{$counter}\">{$counter}. Lépés</label>
                        <input id=\"instructionText{$counter}\" name=\"instructions[]\" class=\"form-control\" required type=\"text\"
                        value=\"{$instructionValue}\" >
                    </div>";
                $counter++;
            }
        }

        return $insHtml;
    }

    public static function BuildCategoryOptions(array $categories, array $allCategories): array
    {
        $catListHtml = "";

        if (! empty($allCategories)) {
            foreach ($allCategories as $cat) {
                if ($cat['status'] === "approved") {
                    $catListHtml .= "<option value=\"{$cat['id']}\">{$cat['name']}</option>";
                }
            }
        } else {
            $catListHtml .= "<option value=\"\">Nincs megjeleníthető kategória</option>";
        }

        $catLabelsHtml = "";
        $selectedCategoryIds = "";
        $catCounter = 0;

        if (! empty($categories)) {
            foreach ($categories as $cat) {
                $catLabelsHtml .= '
            <div class="categoryTag" data-category-id="'.$cat['id'].'">
                '.$cat['name'].'
                <button type="button" class="removeTag" onclick="removeCategory('.$cat['id'].')">×</button>
            </div>';
                $catCounter == 0 ? $selectedCategoryIds .= $cat['id'] : $selectedCategoryIds .= ";".$cat['id'];
                $catCounter++;
            }
        }

        return [
            $catListHtml,
            $catLabelsHtml,
            $selectedCategoryIds
        ];
    }

    public static function BuildSelectedTags(array $tags, array $tagCategories, array $recipeTags): array
    {
        $tagsData = [];
        $orderedtags = [];

        for ($i = 0; $i < count($tagCategories); $i++) {
            $categoryName = $tagCategories[$i]['tag_category'];
            $tagsData[$categoryName]['html'] = "";


            foreach ($tags as $tag) {
                if ($tag['tag_category'] === $categoryName) {
                    $selected = in_array($tag['id'], $recipeTags) ? "selected" : "";
                    $tagsData[$categoryName]['html'] .= "<option value=\"{$tag['id']}\" {$selected}>{$tag['name']}</option>";
                }
            }

            switch ($categoryName) {
                case 'Nehézség':
                    $orderedtags["DIFTAGS"] = $tagsData[$categoryName]['html'];
                    break;
                case 'Elkészítési idő':
                    $orderedtags["TIMETAGS"] = $tagsData[$categoryName]['html'];
                    break;
                case 'Költségvetés':
                    $orderedtags["BUDGETTAGS"] = $tagsData[$categoryName]['html'];
                    break;
                case 'Alkalom':
                    $orderedtags["OCCTAGS"] = $tagsData[$categoryName]['html'];
                    break;
                case 'Elkészítési mód':
                    $orderedtags["METHODTAGS"] = $tagsData[$categoryName]['html'];
                    break;
                case 'Stílus':
                    $orderedtags["STYLETAGS"] = $tagsData[$categoryName]['html'];
                    break;
                default:
                    break;
            }
        }

        return $orderedtags;
    }

    public static function BuildAcitonButtons(bool $isFavourite, bool $isMarked): array
    {
        $favTitle = $isFavourite ? "Törlés a kedvencekből" : "Kedvencekhez adás";
        $markTitle = $isMarked ? "Törlés a mentettekből" : "Recept Mentése";

        $favActive = $favTitle === "Törlés a kedvencekből" ? "active" : "";
        $markActive = $markTitle === "Törlés a mentettekből" ? "active" : "";

        return [

            "favourite" => "<button id=\"favourite\" class=\"actionBtn {$favActive}\" title = \"{$favTitle}\" type=\"submit\" name=\"favourite\">
                                    <i class=\"bi bi-heart-fill\"></i>
                                </button>",
            "bookmark" => "<button id=\"bookmark\" class=\"actionBtn {$markActive}\" title = \"{$markTitle}\" type=\"submit\" name=\"bookmark\">
                                        <i class=\"bi bi-bookmark-fill\"></i>
                                    </button>"
        ];

    }

    public static function DefaultIngredientBlock(array $ingredientOptions): string
    {
        $ingredientListHTML = "";

        if (! empty($ingredientOptions)) {
            foreach ($ingredientOptions as $ing) {
                $ingredientListHTML .= "<option value=\"{$ing['id']}\">{$ing['name']}</option>";
            }
        } else {
            $ingredientListHTML = "<option value=\"\">Nincs megjeleníthető hozzávaló</option>";
        }

        return "
                <div class=\"ingredients ingredientBlock\" id=\"ingredients0\">
                    <label for=\"ingredientName0\"><b>Hozzávaló neve</b></label>
                    <select id=\"ingredientName0\" name=\"ingredients[0][id]\" class=\"form-select\">
                    <option value=\"\">Válassz 1 hozzávalót...</option>
                    {$ingredientListHTML}
                    </select>
                    <div class=\"ingredientNumbers\">
                        <div class=\"ingredientField\">
                            <label for=\"ingredientQuantity0\"><b>Mennyisége:</b></label>
                            <input id=\"ingredientQuantity0\" name=\"ingredients[0][quantity]\" class=\"form-control\" required
                            type=\"number\" min=\"1\">
                        </div>
                        <div class=\ingredientField\">
                            <label for=\"ingredientUnit0\"><b>Mértékegysége:</b></label>
                            <input id=\"ingredientUnit0\" name=\"ingredients[0][unit]\" class=\"form-control\" required type=\"text\"
                            maxlength=\"90\" minlength=\"1\">
                        </div>
                    </div>
                </div>";
    }

    public static function DefaultSelectedTags(array $tags, array $tagCategories): array
    {
        $tagsData = [];
        $orderedtags = [];

        for ($i = 0; $i < count($tagCategories); $i++) {
            $categoryName = $tagCategories[$i]['tag_category'];
            $tagsData[$categoryName]['html'] = "";

            foreach ($tags as $tag) {
                if ($tag['tag_category'] === $categoryName) {
                    $tagsData[$categoryName]['html'] .= "<option value=\"{$tag['id']}\">{$tag['name']}</option>";
                }
            }

            switch ($categoryName) {
                case 'Nehézség':
                    $orderedtags["DIFTAGS"] = $tagsData[$categoryName]['html'];
                    break;
                case 'Elkészítési idő':
                    $orderedtags["TIMETAGS"] = $tagsData[$categoryName]['html'];
                    break;
                case 'Költségvetés':
                    $orderedtags["BUDGETTAGS"] = $tagsData[$categoryName]['html'];
                    break;
                case 'Alkalom':
                    $orderedtags["OCCTAGS"] = $tagsData[$categoryName]['html'];
                    break;
                case 'Elkészítési mód':
                    $orderedtags["METHODTAGS"] = $tagsData[$categoryName]['html'];
                    break;
                case 'Stílus':
                    $orderedtags["STYLETAGS"] = $tagsData[$categoryName]['html'];
                    break;
                default:
                    break;
            }
        }

        return $orderedtags;
    }

    public static function DefaultInstructionBlocks(): string
    {
        return "
            <div class=\"instruction\" id=\"instruction0\">
              <label for=\"instructionText0\">1. Lépés</label>
              <input id=\"instructionText0\" name=\"instructions[]\" class=\"form-control\" required type=\"text\" minlength=\"10\">
            </div>
            ";
    }

    public static function DefaultCategoryOptions(array $allCategories, ?int $chosenCategory): string
    {
        $catListHtml = "";

        if (! empty($allCategories)) {
            foreach ($allCategories as $cat) {
                if ($cat['status'] === "approved") {
                    $selected = '';
                    if($cat['id'] === $chosenCategory) {
                        $selected = "selected";
                    }
                    $catListHtml .= "<option value=\"{$cat['id']}\" {$selected}>{$cat['name']}</option>";
                }
            }
        } else {
            $catListHtml .= "<option value=\"\">Nincs megjeleníthető kategória</option>";
        }

        return $catListHtml;
    }

    public static function BuildImageGallery(array $images): array
    {
        global $cfg;
        $mainImageHtml = '';
        $extraImagesHtml = '';

        if (! empty($images)) {
            if ($images['mainImage'] !== $cfg['noImage']) {
                $path = htmlspecialchars($images['mainImage']);
                $mainImageHtml = '
                <div class="existing-image-item" data-image-path="'.$path.'">
                    <img src="'.$path.'" alt="Főkép" onclick="viewImage(\''.$path.'\')">
                    <button type="button" class="image-delete-btn" onclick="removeExistingImage(this, \'main\')">Törlés</button>
                </div>';
            }

            if (! empty($images['extraImages'])) {
                foreach ($images['extraImages'] as $i => $extraPath) {
                $path = htmlspecialchars($extraPath);
                $extraImagesHtml .= '
                <div class="existing-image-item" data-image-path="' . $path . '">
                    <img src="' . $path . '" alt="Extra kép ' . ($i + 1) . '" onclick="viewImage(\''.$path.'\')">
                    <button type="button" class="image-delete-btn" onclick="removeExistingImage(this, \'extra\', ' . $i . ')">Törlés</button>
                </div>';
                }
            }
        }

        return [$mainImageHtml, $extraImagesHtml];
    }
}