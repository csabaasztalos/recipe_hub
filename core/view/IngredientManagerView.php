<?php
final class IngredientManagerView
{
    public static function DrawMainIngredientTable(array $ingredients): array
    {
        $desktopHtml = "";
        $buttonsHtml = "";
        $mobileHtml = "";

        if (empty($ingredients)) {
            return [
                "<tr><td colspan=\"6\" class=\"text-center text-muted\">Nincs megjeleníthető hozzávaló</td></tr>",
                "<p>Nincs megjeleníthető hozzávaló</p>"
            ];
        }

        foreach ($ingredients as $ingredient) {
            $status = $ingredient['status'];
            $color = "";
            switch ($status) {
                case "approved":
                    $color = "bg-success";
                    $status = "jóváhagyva";
                    break;
                case "rejected":
                    $color = "bg-danger";
                    $status = "elutasítva";
                    break;
                case "staging":
                    $color = "bg-secondary text-white";
                    $status = "importált";
                    break;
                default:
                    $color = "bg-warning";
                    $status = "függőben";
                    break;
            }
            $buttonsHtml = "
                        <div>
                            <form method=\"post\" style=\"display:inline;\">
                                <button class=\"btn btn-success btn-sm confirm-btn\" name=\"approve\" data-confirm=\"Biztos elfogadod a hozzávalót?\">Jóváhagyás</button>
                                <input type=\"hidden\" name=\"ingredient_id\" value=\"{$ingredient['id']}\">
                            </form>
                        </div>
                        <div>
                            <form method=\"post\" style=\"display:inline;\">
                                <button class=\"btn btn-warning btn-sm confirm-btn\" name=\"reject\" data-confirm=\"Biztos elutasítod a hozzávalót?\">Elutasítás</button>
                                <input type=\"hidden\" name=\"ingredient_id\" value=\"{$ingredient['id']}\">
                            </form>
                        </div>
                        <div>
                            <button class=\"btn btn-primary btn-sm edit-btn\" data-bs-toggle=\"modal\"
                                data-bs-target=\"#editCategoryModal\">Módosítás</button>
                        </div>
                        <div>
                            <form method=\"post\" style=\"display:inline;\">
                                <button class=\"btn btn-danger btn-sm confirm-btn\" name=\"delete\" data-confirm=\"Biztos törlöd a hozzávalót?\">Törlés</button>
                                <input type=\"hidden\" name=\"ingredient_id\" value=\"{$ingredient['id']}\">
                            </form>
                        </div>
                    ";

            $userCell = ($ingredient['submitted_by'] == null)
                ? "Nincs megadva"
                : $ingredient['username']."(#".$ingredient['submitted_by'].")";
            $desktopHtml .= "<tr>
                                <td class=\"catId\"><input type=\"checkbox\" name=\"recordIDS[]\" value=\"{$ingredient['id']}\">#".$ingredient['id']."</td>
                                <td class=\"catName\">".$ingredient['name']."</td>
                                <td class=\"userCell\">".$userCell."</td>
                                <td class=\"submittedAt\">".$ingredient['submitted_at']."</td>
                                <td class=\"catStatus\"><span class=\"badge text-dark ".$color."\" data-status-raw='".$status."'>".$status."</span></td><td class=\"text-end buttons\">".
                $buttonsHtml."
                            </td></tr>";

            $mobileHtml .= "
                <div class=\"card recip-card\">
                    <div class=\"card-body\">
                        <h3 class=\"card-title\">{$ingredient['name']}</h5>
                        <p class=\"card-text\">Státusz: {$status}</p>
                        <p class=\"card-text\">Felhasználó: {$userCell}</p>
                        <p class=\"card-text\">Feltöltés dátuma: {$ingredient['submitted_at']}</p>
                    </div>
                    <div class=\"card-body\">
                        <p>Műveletek:</p>
                        <div class=\"buttons\">{$buttonsHtml}</div>
                    </div>
                </div>
                ";
        }

        return [$desktopHtml, $mobileHtml];
    }

    public static function DrawImportTable(array $stagedIngredients): string
    {
        $ingredientsHtml = "";
        if (! empty($stagedIngredients)) {
            foreach ($stagedIngredients as $ingredient) {
                $ingredientsHtml .= "<tr>
                                        <td>#".$ingredient['id']."</td>
                                        <td>".$ingredient['name']."</td>
                                        <td>".$ingredient['status']."</td>
                                        <td><input type=\"checkbox\" name=\"recordIDS[]\" value=\"{$ingredient['id']}\"></td>
                                    </tr>";
            }
            $ingredientsHtml .= "</form>";
        } else {
            $ingredientsHtml = "<tr><td colspan=\"4\" class=\"text-center text-muted\">Nincs új hozzávaló importáláshoz</td></tr>";
        }
        return $ingredientsHtml;
    }

    public static function BuildRecipeList(array $recipes): string
    {
        if (empty($recipes)) {
            return "<option value=\"\">Nincs megjeleníthető recept</option>";
        }

        $recipeHtml = "";

        foreach ($recipes as $recipe) {
            $assignedIngredients = [];
            $recipeIngredients = IngredientService::FetchRecipeIngredients($recipe['id']);
            if (! empty($recipeIngredients)) {
                foreach ($recipeIngredients as $ingredient) {
                    $assignedIngredients[] = $ingredient['id'];
                }
            }
            $ingredientsStr = implode(',', $assignedIngredients);
            $recipeHtml .= "<li data-value=\"{$recipe['id']}\" data-ingredients=\"{$ingredientsStr}\">{$recipe['title']}</li>";
        }

        return $recipeHtml;
    }

    public static function BuildIngredientList(array $ingredients, ?int $selected): string
    {
        if (empty($ingredients))
            return "<option value=\"\">Nincs megjeleníthető kategória</option>";

        $ingredientHtml = "";

        foreach ($ingredients as $ingredient) {
            $select = "";
            if ($selected === $ingredient['id']) {
                $select = "selected";
            }
            $ingredientHtml .= "<option value=\"{$ingredient['id']}\" $select>{$ingredient['name']}</option>";
        }
        return $ingredientHtml;
    }
}