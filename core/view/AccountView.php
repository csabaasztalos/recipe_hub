<?php

final class AccountView
{
    public static function DrawRecipes(array $expandedRecipes, bool $ownRecipes, int $userId)
    {
        global $cfg;
        $recipeCardsHtml = "";
        $viewBtn = "";

        if (! empty($expandedRecipes)) {
            foreach ($expandedRecipes as $recipe) {
                $slugifiedTitle = UrlConverter::Slugify($recipe['title']);
                $createdAt = $recipe['recipe_created_at'] ?? $recipe['created_at'] ?? null;
                $formatedDate = $createdAt ? date("Y. m. d.", strtotime($createdAt)) : 'N/A';
                if($ownRecipes || $userId === $cfg['adminUserID']) {
                    $viewBtn = "<a href=\"index.php?p=edit-recipe&id={$recipe['id']}&title={$slugifiedTitle}\" class=\"editBtn\">Szerkesztés</a>";
                }
                else {
                    $viewBtn = "";
                }

                $recipeCardsHtml .= "
                        <div class=\"recipeCard col-sm-6 col-md-4 col-lg-3\">
                            <div class=\"recipeCardImage\">
                                <img src=\"{$recipe['images']['mainImage']}\" alt=\"{$recipe['title']}\">
                                <div class=\"recipeCardOverlay\">
                                    <a href=\"index.php?p=view-recipe&id={$recipe['id']}&title={$slugifiedTitle}\" class=\"viewBtn\">Megtekintés</a>".$viewBtn."
                                </div>
                            </div>
                            <div class = \"recipeCardIcons\">
                                <i class=\"bi bi-heart-fill\" id=\"favouriteBtn\" title=\"Kedvelések száma\">{$recipe["likesCount"]}</i>
                                <i class=\"bi bi-bookmark-fill\"  id=\"bookmarkBtn\" title=\"Mentések száma\">{$recipe["bookmarksCount"]}</i>
                            </div>
                            <div class=\"recipeCardContent\">
                                <h3>{$recipe['title']}</h3>
                                <div class=\"recipeCardMeta\">
                                    <span><i class=\"bi bi-calendar\"></i> {$formatedDate}</span>
                                </div>
                            </div>
                        </div>";
            }
        }
        return $recipeCardsHtml;
    }
}