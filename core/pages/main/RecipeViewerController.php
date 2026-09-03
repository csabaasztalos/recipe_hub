<?php
class RecipeViewerController implements IPageBase
{
    private Template $template;

    private Model $model;

    public function __construct()
    {
        $this->model = Container::Get("model");
    }


    public function GetTemplate(): Template
    {
        return $this->template;
    }

    public function Run(array $pageData): void
    {
        try {
            global $cfg;
            $this->template = Template::Load("main/".$pageData['template']);
            $userId = $_SESSION[$cfg['permissionSessionKey']]['data']['id'] ?? null;
            ValidateUser::Validate($userId);

            $this->template->AddData("TITLE", $this->model->LoadContent($pageData['pageID'], 'TITLE')['content']);
            $navbarTemplate = Template::Load("shared/navbar.html");
            $navbarTemplate->AddData("MAINPAGE", $this->model->LoadContent($pageData['pageID'], 'MAINPAGE')['content']);
            $navbarTemplate->AddData("RECIPEPAGE", $this->model->LoadContent($pageData['pageID'], 'RECIPEPAGE')['content']);
            $navbarTemplate->AddData("ACCOUNT", $this->model->LoadContent($pageData['pageID'], 'ACCOUNT')['content']);
            $this->template->AddData("NAVBAR", $navbarTemplate);

            $footerTemplate = Template::Load("shared/footer.html");
            $this->template->AddData("FOOTER", $footerTemplate);
            $newsletterTemplate = Template::Load("shared/newsletter.html");
            $footerTemplate->AddData("NEWSLETTER", $newsletterTemplate);

            $mainContentTemplate = Template::Load('main/view-recipe.html');
            $mainContentTemplate->AddData("INGREDIENTS", $this->model->LoadContent($pageData['pageID'], 'INGREDIENTS')['content']);
            $mainContentTemplate->AddData("INSTRUCTIONS", $this->model->LoadContent($pageData['pageID'], 'INSTRUCTIONS')['content']);

            ValidateRecipeUrl::Validate($_GET['id'], $_GET['title']);
            $recipeId = $_GET['id'];
            $userId = $_SESSION[$cfg['permissionSessionKey']]['data']['id'] ?? null;

            [
                $categories, $tags, $ingredients, $instructions,
                $uplodaDate, $title, $servingSize, $description, $creatorId
            ] = RecipeService::GetRecipeDetails($recipeId, $userId);

            $recipeImages = RecipeService::GetRecipeImages($recipeId);
            $categoryHtml = RecipeView::BuildCategoryLabels($categories);
            $ingHtml = RecipeView::BuildChosenIngredients($ingredients);
            $insHtml = RecipeView::BuildInsturctionList($instructions);
            $tagLabels = RecipeView::BuildTagLabels($tags);
            $extraImagesHtml = RecipeView::BuildExtraImagesGallery($recipeImages['extraImages']);
            $userName = UserQueryService::GetUserById($creatorId);

            if ($userId) {
                $userActions = RecipeActionService::GetUserRecipeActions($userId, $recipeId);
                $actionsButton = RecipeView::BuildAcitonButtons($userActions['favourite'], $userActions['marked']);
                $mainContentTemplate->AddData("FAVBTN", $actionsButton['favourite']);
                $mainContentTemplate->AddData("BMBTN", $actionsButton['bookmark']);
            }

            $mainContentTemplate->AddData("MAINIMAGE", $recipeImages["mainImage"]);
            $mainContentTemplate->AddData("RECIPETITLE", $title);
            $mainContentTemplate->AddData("SERVINGS", $servingSize);
            $mainContentTemplate->AddData("SHORTDESC", $description);
            $mainContentTemplate->AddData("AUTHOR", $userName['username'] ?? "törölt felhasználó");
            $mainContentTemplate->AddData("UPLOADDATE", $uplodaDate);
            $mainContentTemplate->AddData("CATEGORY", $categoryHtml);
            $mainContentTemplate->AddData("INGREDIENTSLIST", $ingHtml);
            $mainContentTemplate->AddData("INSTRUCTIONSLIST", $insHtml);
            $mainContentTemplate->AddData("EXTRAIMAGES", $extraImagesHtml);
            $mainContentTemplate->AddData("DIFFICULITY", $tagLabels['DIFFICULITY']);
            $mainContentTemplate->AddData("TIME", $tagLabels['TIME']);
            $mainContentTemplate->AddData("BUDGET", $tagLabels['BUDGET']);
            $mainContentTemplate->AddData("OCCASION", $tagLabels['OCCASION']);
            $mainContentTemplate->AddData("METHOD", $tagLabels['METHOD']);
            $mainContentTemplate->AddData("STYLE", $tagLabels['STYLE']);

            $this->template->AddData("MAINCONTENT", $mainContentTemplate);

            if (isset($_POST['favourite'])) {
                $succes = RecipeActionService::ToggleUserReaction($userId, $recipeId, 'favourite');
                if ($succes) {
                    header("Location: ".$_SERVER['REQUEST_URI']);
                    exit();
                }
            }
            if (isset($_POST['bookmark'])) {
                $succes = RecipeActionService::ToggleUserReaction($userId, $recipeId, 'bookmark');
                if ($succes) {
                    header("Location: ".$_SERVER['REQUEST_URI']);
                    exit();
                }
            }
            if (isset($_POST['search']) && trim($_POST['keyword']) !== "") {
                $keyword = UrlConverter::Slugify($_POST['keyword']);
                header("Location: {$cfg['recipesPage']}&keyword={$keyword}&difTag=&timeTag=&budgetTag=&occTag=&methodTag=&styleTag=&category=&sortBy=id&order=DESC&filter=");
                exit();
            }
            if (isset($_POST['subscribe'])) {
                Newsletter::Subscribe($_POST);
            }
            if (isset($_POST['logout'])) {
                Logout::DestroySession();
            }
        } catch (RecipeActionServiceException | RecipeServiceException | ValidateException | RecipeNotFoundException) {
            $this->template->AddData("MAINCONTENT", $cfg['recipeNotFoundImage']);
        }
    }
}

