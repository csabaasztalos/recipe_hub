<?php
final class RecipeManagerView
{
    public static function DrawMainRecipeTable(array $recipes): array
    {
        $desktopHtml = "";
        $buttonsHtml = "";
        $mobileHtml = "";

        if (empty($recipes)) {
            return [
                "<tr><td colspan=\"6\" class=\"text-center text-muted\">Nincs megjeleníthető recept</td></tr>",
                "<p>Nincs megjeleníthető recept</p>"
            ];
        }

        foreach ($recipes as $recipe) {
            $status = $recipe['status'];
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
                case "pending":
                    $color = "bg-warning";
                    $status = "függőben";
                    break;
                default:
                    $color = "bg-secondary";
                    $status = "no status";
                    break;
            }
            $slugifiedTtile = UrlConverter::Slugify($recipe['title']);
            $buttonsHtml = "
                            <div class=\"\">
                                <a class=\"btn btn-info btn-sm\" role=\"button\" target=\"blank\"
                                href=\"index.php?p=view-recipe&id={$recipe['id']}&title={$slugifiedTtile}\">Megnézem</a>
                            </div>
                            <div class=\"\">
                                <form method=\"post\" style=\"display:inline;\">
                                    <button class=\"btn btn-success btn-sm confirm-btn\" name=\"approve\"
                                    data-confirm=\"Biztos elfogadod a receptet?\">Elfogadás</button>
                                    <input type=\"hidden\" name=\"recipe_id\" value=\"{$recipe['id']}\">
                                    <input type=\"hidden\" name=\"userId\" value=\"{$recipe['user_id']}\">
                                </form>
                            </div>
                            <div class=\"\">
                                <form method=\"post\" style=\"display:inline;\">
                                    <button class=\"btn btn-warning btn-sm confirm-btn\" name=\"reject\" data-confirm=\"Biztos elutasítod a receptet?\">Elutasítás</button>
                                    <input type=\"hidden\" name=\"recipe_id\" value=\"{$recipe['id']}\">
                                    <input type=\"hidden\" name=\"userId\" value=\"{$recipe['user_id']}\">
                                </form>
                            </div>
                            <div class=\"\">
                                <a class=\"btn btn-primary btn-sm\" target=\"_blank\"role=\"button\" href=\"index.php?p=edit-recipe&id={$recipe['id']}&title={$slugifiedTtile}\">Módosítás</a>
                            </div>
                            <div class=\"\">
                                <form method=\"post\" style=\"display:inline;\">
                                    <button class=\"btn btn-danger btn-sm confirm-btn\" name=\"delete\" data-confirm=\"Biztos törlöd a receptet?\">Törlés</button>
                                    <input type=\"hidden\" name=\"recipe_id\" value=\"{$recipe['id']}\">
                                    <input type=\"hidden\" name=\"userId\" value=\"{$recipe['user_id']}\">
                                </form>
                            </div>
                            ";

            $userCell = ($recipe['user_id'] == null)
                ? "Nincs megadva"
                : $recipe['username']."(".$recipe['user_id'].")";
            $desktopHtml .= "<tr>
                                <td>".$recipe['id']."</td>
                                <td>".$recipe['title']."</td>
                                <td>".$userCell."</td>
                                <td>".$recipe['created_at']."</td>
                                <td> <span class=\"badge text-dark ".$color."\">".$status."</span></td><td class=\"text-end buttons\">".
                $buttonsHtml."</td>
                            </tr>";


            $mobileHtml .= "
                <div class=\"card recip-card\">
                    <div class=\"card-body\">
                        <h3 class=\"card-title\">{$recipe['title']}</h5>
                        <p class=\"card-text\">Státusz: {$status}</p>
                        <p class=\"card-text\">Felhasználó: {$userCell}</p>
                        <p class=\"card-text\">Feltöltés dátuma: {$recipe['created_at']}</p>
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
}