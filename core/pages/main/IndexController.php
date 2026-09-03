<?php

class IndexController implements IPageBase
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

    /**
     * @throws TemplateException
     */
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

            $firstContentBlockTemplate = Template::Load("main/firstContentBlock.html");
            $secondContentBlockTemplate = Template::Load("main/secondContentBlock.html");
            $thirdContentBlockTemplate = Template::Load("main/thirdContentBlock.html");

            $mainContentTemplate = Template::Load('main/homeMainContent.html');
            $mainContentTemplate->AddData("FIRSTBLOCK", $firstContentBlockTemplate);
            $mainContentTemplate->AddData("SECONDBLOCK", $secondContentBlockTemplate);
            $mainContentTemplate->AddData("THIRDBLOCK", $thirdContentBlockTemplate);

            $footerTemplate = Template::Load("shared/footer.html");
            $this->template->AddData("FOOTER", $footerTemplate);

            $newsletterTemplate = Template::Load("shared/newsletter.html");
            $footerTemplate->AddData("NEWSLETTER", $newsletterTemplate);

            $heroTemplate = Template::Load("main/home-hero.html");
            $this->template->AddData("HOMEHERO", $heroTemplate);

            $latestRecipes = RecipeQueryService::GetRecipesByType(4, 'latest', "DESC", null);
            $recipesWithImage = RecipeService::ExpandRecipeWithImage($latestRecipes);
            $latestRecipesHtml = RecipeCardsView::DrawCards($recipesWithImage);
            $firstContentBlockTemplate->AddData("RECIPECARDS", $latestRecipesHtml);

            $randomRecipe = SuggestedRecipe::SelectRecipe();
            $randomRecipeHtml = SuggestedRecipeView::DisplayRandomRecipe($randomRecipe);
            $secondContentBlockTemplate->AddData("RANDOMRECIPE", $randomRecipeHtml);

            $popularRecipes = RecipeQueryService::GetRecipesByType(4, 'popular', "DESC", null);
            $popularRecipesWithImages = RecipeService::ExpandRecipeWithImage($popularRecipes);
            $popularRecipesHtml = RecipeCardsView::DrawCards($popularRecipesWithImages);
            $thirdContentBlockTemplate->AddData("RECIPECARDS", $popularRecipesHtml);

            $this->template->AddData("MAINCONTENT", $mainContentTemplate);

            if (isset($_POST['search']) && trim($_POST['keyword']) !== "") {
                $keyword = UrlConverter::Slugify($_POST['keyword']);
                header("Location: {$cfg['recipesPage']}&keyword={$keyword}&difTag=&timeTag=&budgetTag=&occTag=&methodTag=&styleTag=&category=&sortBy=id&order=DESC&filter=");
                exit();
            }
            if (isset($_POST['logout'])) {
                Logout::DestroySession();
            }
            if (isset($_POST['subscribe'])) {
                Newsletter::Subscribe($_POST);
            }

        } catch (RecipeServiceException | RecipeNotFoundException | RecipeCardsViewException $ex) {
            Logger::Log("".$ex->getMessage(), logLvl::Error);
            $recipeHtml = RecipeCardsView::DrawDefaultCards();
            $firstContentBlockTemplate->AddData("RECIPECARDS", $recipeHtml);
            $secondContentBlockTemplate->AddData("RANDOMRECIPE", $recipeHtml);
            $thirdContentBlockTemplate->AddData("RECIPECARDS", $recipeHtml);
        } catch (Exception $ex) {
            Logger::Log("".$ex, logLvl::Error);
            return;
        }
    }
}

