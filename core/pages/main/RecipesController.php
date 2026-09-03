<?php
class RecipesController implements IPageBase
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
            $navbarTemplate->AddData("MAINPAGE", $this->model->LoadContent("index", 'MAINPAGE')['content']);
            $navbarTemplate->AddData("RECIPEPAGE", $this->model->LoadContent("index", 'RECIPEPAGE')['content']);
            $navbarTemplate->AddData("ACCOUNT", $this->model->LoadContent("index", 'ACCOUNT')['content']);
            $this->template->AddData("NAVBAR", $navbarTemplate);

            $footerTemplate = Template::Load("shared/footer.html");
            $this->template->AddData("FOOTER", $footerTemplate);

            $newsletterTemplate = Template::Load("shared/newsletter.html");
            $footerTemplate->AddData("NEWSLETTER", $newsletterTemplate);
            $mainContentTemplate = Template::Load("main/display-recipes.html");

            $filterTemplate = Template::Load("main/recipesFilter.html");
            $filterTemplate->AddData("SEARCHRECIPES", $this->model->LoadContent($pageData['pageID'], 'SEARCHRECIPES')['content']);

            global $cfg;
            [$categories, $tags] = FilterService::GetFilterOptions();
            $categoryOptions = FilterView::BuildCategoryOptions($categories);
            $tagsOptions = FilterView::BuildTagOptions($tags);

            $filterTemplate->AddData("CATEGORIES", $categoryOptions);
            foreach ($tagsOptions as $key => $value) {
                $filterTemplate->AddData($key, $value);
            }
            $mainContentTemplate->AddData("RECIPESFILTER", $filterTemplate);

            $filters = FilterService::ParseFilter($_GET);
            $recipeCardsHtml = "<b>Nem található ilyen recept.</b>";

            if (empty($filters)) {
                $latestRecipes = RecipeQueryService::GetRecipesByType(null, 'latest', "DESC", null);
                if ($latestRecipes) {
                    $recipesWithImage = RecipeService::ExpandRecipeWithImage($latestRecipes);
                    $recipeCardsHtml = RecipeCardsView::DrawCards($recipesWithImage);
                }
            } else {
                $filteredRecipes = RecipeQueryService::GetRecipesByType(null, 'filtered', "DESC", $filters);
                if ($filteredRecipes) {
                    $recipesWithImage = RecipeService::ExpandRecipeWithImage($filteredRecipes);
                    $recipeCardsHtml = RecipeCardsView::DrawCards($recipesWithImage);
                }
            }

            if (isset($_POST['resetFilter'])) {
                FilterService::ResetFilter();
            }

            $mainContentTemplate->AddData("RECIPECARDS", $recipeCardsHtml);
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

        } catch (RecipeServiceException | RecipeCardsViewException | RecipeQueryServiceException $ex) {
            $recipeCardsHtml = RecipeCardsView::DrawDefaultCards();
            $mainContentTemplate->AddData("RECIPECARDS", $recipeCardsHtml);
            $this->template->AddData("MAINCONTENT", $mainContentTemplate);
        } catch (FilterServiceException $ex) {
            $filterTemplate->AddData("CATEGORIES", "Nincs megjeleníthető kategória");

            $tagsOptions = FilterView::BuildTagOptions([]);
            $filterTemplate->AddData("CATEGORIES", $categoryOptions);
            foreach ($tagsOptions as $key => $value) {
                $filterTemplate->AddData($key, $value);
            }

            $mainContentTemplate->AddData("RECIPESFILTER", $filterTemplate);
            $mainContentTemplate->AddData("RECIPECARDS", "<b>Nem található ilyen recept.</b>");
            $this->template->AddData("MAINCONTENT", $mainContentTemplate);
        }
    }

}
