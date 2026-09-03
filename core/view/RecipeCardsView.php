<?php

final class RecipeCardsView
{
    public static function DrawCards(array $recipesWithImage): string
    {
        if (empty($recipesWithImage)) {
            throw new RecipeCardsViewException("Megadott tömb üres.");
        }

        $colSize = self::GetColumnSize(count($recipesWithImage));
        $recipeCardsHtml = "";

        foreach ($recipesWithImage as $recipe) {
            $slugifiedTitle = UrlConverter::Slugify($recipe['title']);
            $recipeCardsHtml .= "
                            <div class=\"recipesDiv {$colSize}\">
                                <div class=\"recipeCardLargeInner\">
                                    <a href=\"index.php?p=view-recipe&id={$recipe['id']}&title={$slugifiedTitle}\" class=\"imageLink\">
                                        <div class=\"recipeCardLargeImage\" style=\"background-image: url('{$recipe['images']['mainImage']}')\"></div>
                                    </a>
                                    <div class=\"recipeCardLargeTitle\">
                                        <h4>{$recipe['title']}</h4>
                                    </div>
                                </div>
                            </div>";
        }
        return $recipeCardsHtml;
    }

    private static function GetColumnSize(int $count): string
    {
        return match ($count) {
            3 => "col-md-4 col-lg-4",
            2 => "col-md-6 col-lg-4",
            1 => "col-md-6 col-lg-6",
            default => "col-sm-6 col-md-6 col-lg-3"
        };
    }

    public static function DrawDefaultCards(): string
    {
        global $cfg;
        $colSize = self::GetColumnSize(4);
        $recipeCardsHtml = "";

        for ($i=0; $i < 4; $i++) {
            $recipeCardsHtml .= "
                            <div class=\"recipesDiv {$colSize}\">
                                <div class=\"recipeCardLargeInner\">
                                    <a href=\"\" class=\"imageLink\">
                                        <div class=\"recipeCardLargeImage\" style=\"background-image: url({$cfg['noImage']})\"></div>
                                    </a>
                                    <div class=\"recipeCardLargeTitle\">
                                        <h4>Minta Recept</h4>
                                    </div>
                                </div>
                            </div>";
        }
        return $recipeCardsHtml;
    }
}

