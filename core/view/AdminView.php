<?php

final class AdminView
{
    public static function DrawPendingTable(array $pendingRecipes): array
    {
        $mainTemplate = [];
        $pendingListBody = "";
        $pendingListHeader = "<tr><th>Cím</th><th>Feltöltötte</th></tr><tbody>";

        if (! empty($pendingRecipes)) {
            foreach ($pendingRecipes as $recipe) {
                $rowHtml = "<tr><td>".$recipe['title']."</td>"."<td>".$recipe['username']."</td></tr>";
                $pendingListBody .= $rowHtml;
            }
            $pendingListBody .= "</tbody>";
            $mainTemplate["PENDINGLISTHEADER"] = $pendingListHeader;
            $mainTemplate["PENDINGLISTBODY"] = $pendingListBody;
        } else {
            $mainTemplate["PENDINGLISTBODY"] = "Jelenleg nincs recept függő státuszban...";
        }

        return $mainTemplate;
    }

    public static function DrawActivityTable(array $activityLog): string
    {
        $activityHeader = "<tr>
                            <th>Felhasználó</th>
                            <th>Művelet</th>
                            <th>Tábla</th>
                            <th>Rekord ID</th>
                            <th>Időpont</th>
                        </tr>";
        $activityBody = "";

        if (! empty($activityLog)) {
            foreach ($activityLog as $row) {
                $activityBody .= "<tr>
                                    <td>".$row['username']."</td>
                                    <td>".$row['action']."</td>
                                    <td>".$row['table_name']."</td>
                                    <td>".$row['record_id']."</td>
                                    <td>".$row['changed_at']."</td>
                                </tr>";
            }
        } else {
            $activityBody = "<tr><td colspan=\"5\">Nincs megjelníthető aktivítás.</td></tr>";
        }

        return $activityHeader . $activityBody;
    }

    public static function GenerateCatChart(array $chartData): array
    {
        $hiddenInputsTemplate = [];

        if (! empty($chartData)) {
            $hiddenInputsTemplate['CATEGORYLABELS'] = '<input type="hidden" id="categoryLabels" value=\''.htmlspecialchars(json_encode($chartData["labels"]), ENT_QUOTES, "UTF-8").'\'>';
            $hiddenInputsTemplate['CATEGORYDATA'] = '<input type="hidden" id="categoryData" value=\''.htmlspecialchars(json_encode($chartData["data"]), ENT_QUOTES, "UTF-8").'\'>';
        }

        return $hiddenInputsTemplate;
    }

    public static function GenerateUserChart(array $chartData): array
    {
        $hiddenInputsTemplate = [];

        if (! empty($chartData)) {
            $labels = array_column($chartData, 'labels');
            $data = array_column($chartData, 'data');
            $hiddenInputsTemplate['USERLABEL'] = '<input type="hidden" id="userLabels" value=\''.htmlspecialchars(json_encode($labels), ENT_QUOTES, "UTF-8").'\'>';
            $hiddenInputsTemplate['USERDATA'] = '<input type="hidden" id="userData" value=\''.htmlspecialchars(json_encode($data), ENT_QUOTES, "UTF-8").'\'>';
        }

        return $hiddenInputsTemplate;
    }

    public static function GenerateRelationChart(array $chartData, string $relationType): array
    {
        $hiddenInputsTemplate = [];

        if ($relationType === 'favourite') {
            $labelKey = "FAVOURITELABEL";
            $dataKey = "FAVOURITEDATA";
            $labelId = "favouriteLabels";
            $dataId = "favouriteData";
        } elseif ($relationType === 'bookmark') {
            $labelKey = "BOOKMARKLABEL";
            $dataKey = "BOOKMARKDATA";
            $labelId = "bookmarkLabels";
            $dataId = "bookmarkData";
        }

        if (! empty($chartData)) {
            $labels = array_column($chartData, 'labels');
            $data = array_column($chartData, 'data');
            $hiddenInputsTemplate[$labelKey] = "<input type=\"hidden\" id=\"{$labelId}\" value=\"".htmlspecialchars(json_encode($labels), ENT_QUOTES, "UTF-8")."\"/>";
            $hiddenInputsTemplate[$dataKey] = "<input type=\"hidden\" id=\"{$dataId}\" value=\"".htmlspecialchars(json_encode($data), ENT_QUOTES, "UTF-8")."\"/>";
        }

        return $hiddenInputsTemplate;
    }

    public static function BuildStatusOptions($status): string
    {
        global $cfg;
        $statusList = $cfg['statuses'];
        $statushtml = "";
        foreach ($statusList as $item) {
            $selected = ($status === $item) ? ' selected' : '';

            match ($item) {
                "approved" => $newStatus = "Elfogadva",
                "rejected" => $newStatus = "Elutasítva",
                "pending" => $newStatus = "Függőben",
                "staging" => $newStatus = "Importált"
            };

            $statushtml .= "<option value=\"{$item}\"$selected>{$newStatus}</option>";
        }
        return $statushtml;
    }
}