<?php

class UploadRecipeController implements IPageBase
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
            $this->template = Template::Load("account/".$pageData['template']);
            $userId = $_SESSION[$cfg['permissionSessionKey']]['data']['id'] ?? null;
            ValidateUser::Validate($userId);

            $this->template->AddData("TITLE", $this->model->LoadContent($pageData['pageID'], 'TITLE')['content']);
            $navbarTemplate = Template::Load("shared/navbar.html");
            $navbarTemplate->AddData("MAINPAGE", $this->model->LoadContent($pageData['pageID'], 'MAINPAGE')['content']);
            $navbarTemplate->AddData("RECIPEPAGE", $this->model->LoadContent($pageData['pageID'], 'RECIPEPAGE')['content']);
            $navbarTemplate->AddData("ACCOUNT", $this->model->LoadContent($pageData['pageID'], 'ACCOUNT')['content']);
            $renderedNavbar = $navbarTemplate->Render();
            $this->template->AddData("NAVBAR", $renderedNavbar);
            $footerTemplate = Template::Load("shared/footer.html");
            $newsletterTemplate = Template::Load("shared/newsletter.html");
            $footerTemplate->AddData("NEWSLETTER", $newsletterTemplate);
            $this->template->AddData("FOOTER", $footerTemplate);
            $mainContentTemplate = Template::Load("account/uploadRecipe.html");
            $sideMenuTemplate = Template::Load("account/sideMenu.html");
            $sideMenuTemplate->AddData("MENU", $this->model->LoadContent('account', "MENU")['content']);
            $sideMenuTemplate->AddData("SAVED", $this->model->LoadContent('account', "SAVED")['content']);
            $sideMenuTemplate->AddData("LIKED", $this->model->LoadContent('account', "LIKED")['content']);
            $sideMenuTemplate->AddData("OWN", $this->model->LoadContent('account', "OWN")['content']);
            $sideMenuTemplate->AddData("UPLOAD", $this->model->LoadContent('account', "UPLOAD")['content']);
            $sideMenuTemplate->AddData("PROFILE", $this->model->LoadContent('account', "PROFILE")['content']);
            $sideMenuTemplate->AddData("LOGOUT", $this->model->LoadContent('account', "LOGOUT")['content']);
            $mainContentTemplate->AddData("SIDEMENU", $sideMenuTemplate);
            $mainContentTemplate->AddData("UPLOADRECIPE", $this->model->LoadContent($pageData['pageID'], 'UPLOADRECIPE')['content']);
            $mainContentTemplate->AddData("RECIPETITLE", $this->model->LoadContent($pageData['pageID'], 'RECIPETITLE')['content']);
            $mainContentTemplate->AddData("SERVINGSIZE", $this->model->LoadContent($pageData['pageID'], 'SERVINGSIZE')['content']);
            $mainContentTemplate->AddData("SERVINGTEXT", $this->model->LoadContent($pageData['pageID'], 'SERVINGTEXT')['content']);
            $mainContentTemplate->AddData("INGREDIENTS", $this->model->LoadContent($pageData['pageID'], 'INGREDIENTS')['content']);
            $mainContentTemplate->AddData("INGREDIENTTEXT", $this->model->LoadContent($pageData['pageID'], 'INGREDIENTTEXT')['content']);
            $mainContentTemplate->AddData("ADDINGREDIENT", $this->model->LoadContent($pageData['pageID'], 'ADDINGREDIENT')['content']);
            $mainContentTemplate->AddData("SHORTDESC", $this->model->LoadContent($pageData['pageID'], 'SHORTDESC')['content']);
            $mainContentTemplate->AddData("INSTRUCTIONS", $this->model->LoadContent($pageData['pageID'], 'INSTRUCTIONS')['content']);
            $mainContentTemplate->AddData("INSTRUCTIONTEXT", $this->model->LoadContent($pageData['pageID'], 'INSTRUCTIONTEXT')['content']);
            $mainContentTemplate->AddData("NEWINSTURCTION", $this->model->LoadContent($pageData['pageID'], 'NEWINSTURCTION')['content']);
            $mainContentTemplate->AddData("CATEGORIES", $this->model->LoadContent($pageData['pageID'], 'CATEGORIES')['content']);
            $mainContentTemplate->AddData("CATEGORYTEXT", $this->model->LoadContent($pageData['pageID'], 'CATEGORYTEXT')['content']);
            $mainContentTemplate->AddData("DEAFULTCATEGORYOPTION", $this->model->LoadContent($pageData['pageID'], 'DEAFULTCATEGORYOPTION')['content']);
            $mainContentTemplate->AddData("ADDCATEGORY", $this->model->LoadContent($pageData['pageID'], 'ADDCATEGORY')['content']);
            $mainContentTemplate->AddData("TAGS", $this->model->LoadContent($pageData['pageID'], 'TAGS')['content']);
            $mainContentTemplate->AddData("TAGSTEXT", $this->model->LoadContent($pageData['pageID'], 'TAGSTEXT')['content']);
            $mainContentTemplate->AddData("DIFFLABEL", $this->model->LoadContent($pageData['pageID'], 'DIFFLABEL')['content']);
            $mainContentTemplate->AddData("TIMELABEL", $this->model->LoadContent($pageData['pageID'], 'TIMELABEL')['content']);
            $mainContentTemplate->AddData("BUDGETLABEL", $this->model->LoadContent($pageData['pageID'], 'BUDGETLABEL')['content']);
            $mainContentTemplate->AddData("OCCLABEL", $this->model->LoadContent($pageData['pageID'], 'OCCLABEL')['content']);
            $mainContentTemplate->AddData("METHODLABEL", $this->model->LoadContent($pageData['pageID'], 'METHODLABEL')['content']);
            $mainContentTemplate->AddData("STYLELABEL", $this->model->LoadContent($pageData['pageID'], 'STYLELABEL')['content']);
            $mainContentTemplate->AddData("MAINIMAGE", $this->model->LoadContent($pageData['pageID'], 'MAINIMAGE')['content']);
            $mainContentTemplate->AddData("MAINIMAGETEXT", $this->model->LoadContent($pageData['pageID'], 'MAINIMAGETEXT')['content']);
            $mainContentTemplate->AddData("EXTRAIMAGES", $this->model->LoadContent($pageData['pageID'], 'EXTRAIMAGES')['content']);
            $mainContentTemplate->AddData("EXTRAIMAGESTEXT", $this->model->LoadContent($pageData['pageID'], 'EXTRAIMAGESTEXT')['content']);
            $mainContentTemplate->AddData("UPLOAD", $this->model->LoadContent($pageData['pageID'], 'UPLOAD')['content']);
            $mainContentTemplate->AddData("DEFAULTTAGOPTION", $this->model->LoadContent($pageData['pageID'], 'DEFAULTTAGOPTION')['content']);

            $ingredients = IngredientService::FetchIngredients();
            $categories = CategoryService::FetchCategories();
            $tags = TagsService::FetchTags();
            $tagCats = TagsService::FetchTagCategories();

            if (isset($_POST['recipeTitle']) && ($_POST['recipeTitle'])) {
                $mainContentTemplate->AddData("RECIPETITLEVALUE", $_POST['recipeTitle']);
            }

            if (isset($_POST['servings']) && ($_POST['servings'])) {
                $mainContentTemplate->AddData("SERVINGSVALUE", $_POST['servings']);
            }

            if (isset($_POST['smallDescription']) && $_POST['smallDescription']) {
                $mainContentTemplate->AddData("SHORTDESCVALUE", $_POST['smallDescription']);
            }

            if (isset($_POST['ingredients']) && $_POST['ingredients']) {
                $ingredientBlock = RecipeView::BuildIngredientBlocks($_POST['ingredients'], $ingredients);
                $mainContentTemplate->AddData("INGREDIENTLIST", $ingredientBlock);
            } else {
                $ingredientBlock = RecipeView::DefaultIngredientBlock($ingredients);
                $mainContentTemplate->AddData("INGREDIENTLIST", $ingredientBlock);
            }

            if (isset($_POST['instructions']) && $_POST['instructions']) {
                $insturctionBlock = RecipeView::BuildInstructionBlocks($_POST['instructions']);
                $mainContentTemplate->AddData("INSTRUCTIONLIST", $insturctionBlock);
            } else {
                $insturctionBlock = RecipeView::DefaultInstructionBlocks();
                $mainContentTemplate->AddData("INSTRUCTIONLIST", $insturctionBlock);
            }

            if (isset($_POST['selectedCategoryIds']) && $_POST['selectedCategoryIds']) {
                $categoryIds = explode(";", $_POST['selectedCategoryIds']);
                $expandedCategories = CategoryService::ExpandCategories($categoryIds);
                [$allOptions, $catLabels, $selectedIds] = RecipeView::BuildCategoryOptions($expandedCategories, $categories);

                $mainContentTemplate->AddData("CATEGORYLIST", $allOptions);
                $mainContentTemplate->AddData("SELECTEDCATEGORIES", $catLabels);
                $mainContentTemplate->AddData("SELECTEDCATEGORYIDSVALUE", $selectedIds);
            } else {
                $catBlock = RecipeView::DefaultCategoryOptions($categories, null);
                $mainContentTemplate->AddData("CATEGORYLIST", $catBlock);
            }

            if (isset($_POST['tags']) && $_POST['tags']) {
                $tagsData = RecipeView::BuildSelectedTags($tags, $tagCats, $_POST['tags']);
                foreach ($tagsData as $key => $value) {
                    $mainContentTemplate->AddData($key, $value);
                }
            } else {
                $tagsData = RecipeView::DefaultSelectedTags($tags, $tagCats);
                foreach ($tagsData as $key => $value) {
                    $mainContentTemplate->AddData($key, $value);
                }
            }

            if (isset($_POST['uploadRecipe'])) {
                RecipeService::CreateRecipe($_POST, $_FILES);
                header("Location: {$cfg['accoutPage']}&upload_success=1");
                exit();
            }

            if (isset($_POST['logout'])) {
                Logout::DestroySession();
            }
            if (isset($_POST['subscribe'])) {
                Newsletter::Subscribe($_POST);
            }
            if (isset($_POST['search']) && trim($_POST['keyword']) !== "") {
                $keyword = UrlConverter::Slugify($_POST['keyword']);
                header("Location: {$cfg['recipesPage']}&keyword={$keyword}&difTag=&timeTag=&budgetTag=&occTag=&methodTag=&styleTag=&category=&sortBy=id&order=DESC&filter=");
                exit();
            }

        } catch (RecipeServiceException | RecipeUploadException | IngredientServiceException | IngredientException $ex) {
            Logger::Log("Recipe upload error: ".$ex->getMessage(), logLvl::Error);
            $mainContentTemplate->AddData("UPLOADRESULT",
                '<div class="alert alert-danger">'.$ex->getMessage().'</div>');
        } catch (DBException $ex) {
            Logger::Log("Database error: ".$ex->getMessage(), logLvl::Error);
            $mainContentTemplate->AddData("UPLOADRESULT",
                'Hiba történt az adatok lekérése közben.'.$ex->getMessage().'</div>');
        } finally {
            $this->template->AddData("MAINCONTENT", $mainContentTemplate);
        }
    }
}