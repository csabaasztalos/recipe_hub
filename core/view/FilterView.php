<?php

final class FilterView
{
    public static function BuildCategoryOptions(array $categories): string
    {
        $categoriesOptionsHtml = "";

        if (! empty($categories)) {
            foreach ($categories as $category) {
                $categoriesOptionsHtml .= "<option value=\"{$category['id']}\">{$category['name']}</option>";
            }
        }

        $categoriesOptionsHtml .= "<option value=\"\">Nincs megjeleníthető kategória.</option>";

        return $categoriesOptionsHtml;
    }
    
    public static function BuildTagOptions(array $tags): array
    {
        $diffTagOptions = "";
        $timeTagOptions = "";
        $budgetTagOptions = "";
        $occTagOptions = "";
        $methodTagsOptions = "";
        $styleTagOptions = "";
        $tagsListHTML = "";

        if (! empty($tags)) {
            foreach ($tags as $t) {
                $tagsListHTML = "<option value=\"{$t['id']}\">{$t['name']}</option>";
                switch ($t['tag_category']) {
                    case 'Nehézség':
                        $diffTagOptions .= $tagsListHTML;
                        break;
                    case 'Elkészítési idő':
                        $timeTagOptions .= $tagsListHTML;
                        break;
                    case 'Költségvetés':
                        $budgetTagOptions .= $tagsListHTML;
                        break;
                    case 'Alkalom':
                        $occTagOptions .= $tagsListHTML;
                        break;
                    case 'Elkészítési mód':
                        $methodTagsOptions .= $tagsListHTML;
                        break;
                    case 'Stílus':
                        $styleTagOptions .= $tagsListHTML;
                        break;
                    default:
                        break;
                }
            }
        }
        else {
            $diffTagOptions = "<option value=\"\">Nincs megjeleníthető címke.</option>";
            $timeTagOptions = "<option value=\"\">Nincs megjeleníthető címke.</option>";
            $budgetTagOptions = "<option value=\"\">Nincs megjeleníthető címke.</option>";
            $occTagOptions = "<option value=\"\">Nincs megjeleníthető címke.</option>";
            $methodTagsOptions = "<option value=\"\">Nincs megjeleníthető címke.</option>";
            $styleTagOptions = "<option value=\"\">Nincs megjeleníthető címke.</option>";
        }

        return [
            "DIFTAGS" => $diffTagOptions,
            "TIMETAGS" => $timeTagOptions,
            "BUDGETTAGS" => $budgetTagOptions,
            "OCCTAGS" => $occTagOptions,
            "METHODTAGS" => $methodTagsOptions,
            "STYLETAGS" => $styleTagOptions
        ];
    }
}