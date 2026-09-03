<?php
final class CategoryManagerView
{
    public static function DrawMainCategoryTable(array $categories): array
    {
        $desktopHtml = "";
        $buttonsHtml = "";
        $mobileHtml = "";


        if (empty($categories)) {
            return [
                "<tr><td colspan=\"6\" class=\"text-center text-muted\">Nincs megjeleníthető kategória</td></tr>",
                "Nincs megjeleníthető kategória"
            ];
        }

        foreach ($categories as $category) {
            $status = $category['status'];
            $color = "";
            switch ($status) {
                case "approved":
                    $color = "bg-success";
                    $status = "elfogadva";
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
                        <button class=\"btn btn-success btn-sm confirm-btn\" name=\"approve\" data-confirm=\"Biztos elfogadod a kategóriát?\">Jóváhagyás</button>
                        <input type=\"hidden\" name=\"category_id\" value=\"{$category['id']}\">
                    </form>
                </div>
                <div>
                    <form method=\"post\" style=\"display:inline;\">
                        <button class=\"btn btn-warning btn-sm confirm-btn\" name=\"reject\" data-confirm=\"Biztos elutasítod a kategóriát?\">Elutasítás</button>
                        <input type=\"hidden\" name=\"category_id\" value=\"{$category['id']}\">
                    </form>
                </div>
                <div>
                    <button class=\"btn btn-primary btn-sm edit-btn\" data-bs-toggle=\"modal\"
                        data-bs-target=\"#editCategoryModal\">Módosítás</button>
                </div>
                <div>
                    <form method=\"post\" style=\"display:inline;\">
                        <button class=\"btn btn-danger btn-sm confirm-btn\" name=\"delete\" data-confirm=\"Biztos törlöd a kategóriát?\">Törlés</button>
                        <input type=\"hidden\" name=\"category_id\" value=\"{$category['id']}\">
                    </form>
                </div>";

            $userCell = ($category['submitted_by'] == null)
                ? "Nincs megadva"
                : $category['username']."(#".$category['submitted_by'].")";
            $desktopHtml .= "<tr>
                                <td class=\"catId\"><input type=\"checkbox\" name=\"recordIDS[]\" value=\"{$category['id']}\">#".$category['id']."</td>
                                <td class=\"catName\">".$category['name']."</td>
                                <td class=\"userCell\">".$userCell."</td>
                                <td class=\"submittedAt\">".$category['submitted_at']."</td>
                                <td class=\"catStatus\"> <span class=\"badge text-dark ".$color."\" data-status-raw='".$status."'>".$status."</span></td><td class=\"text-end buttons\">".
                $buttonsHtml."
                            </td></tr>";


            $mobileHtml .= "
                <div class=\"card recip-card\">
                    <div class=\"card-body\">
                        <h3 class=\"card-title\">{$category['name']}</h5>
                        <p class=\"card-text\">Státusz: {$status}</p>
                        <p class=\"card-text\">Felhasználó: {$userCell}</p>
                        <p class=\"card-text\">Feltöltés dátuma: {$category['submitted_at']}</p>
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

    public static function DrawImportTable(array $stagedCategories): string
    {
        $categoriesHtml = "";
        if (! empty($stagedCategories)) {
            foreach ($stagedCategories as $category) {
                $categoriesHtml .= "<tr>
                                        <td>#".$category['id']."</td>
                                        <td>".$category['name']."</td>
                                        <td>".$category['status']."</td>
                                        <td><input type=\"checkbox\" name=\"recordIDS[]\" value=\"{$category['id']}\"></td>
                                    </tr>";
            }
            $categoriesHtml .= "</form>";
        } else {
            $categoriesHtml = "<tr><td colspan=\"4\" class=\"text-center text-muted\">Nincs új kategória importáláshoz</td></tr>";
        }
        return $categoriesHtml;
    }

    public static function BuildRecipeList(array $recipes): string
    {
        if (empty($recipes))
            return "<option value=\"\">Nincs megjeleníthető recept</option>";

        $categoriesModel = new CategoriesModel();
        $recipeHtml = "";

        foreach ($recipes as $recipe) {
            $assignedCategories = [];
            $catResult = $categoriesModel->GetRecipeCategories($recipe['id']);
            if ($catResult && $catResult->num_rows > 0) {
                while ($catRow = $catResult->fetch_assoc()) {
                    $assignedCategories[] = $catRow['id'];
                }
            }
            $categoriesStr = implode(',', $assignedCategories);
            $recipeHtml .= "<li data-value=\"{$recipe['id']}\" data-categories=\"{$categoriesStr}\">{$recipe['title']}</li>";
        }
        return $recipeHtml;
    }

    public static function BuildCategoryList(array $categories, ?int $selected): string
    {
        if (empty($categories))
            return "<option value=\"\">Nincs megjeleníthető kategória</option>";

        $categoryHtml = "";

        foreach ($categories as $category) {
            $select = "";
            if ($category['id'] === $selected) {
                $select = "selected";
            }
            $categoryHtml .= "<option value=\"{$category['id']}\" {$select}>{$category['name']}</option>";
        }
        return $categoryHtml;
    }
}