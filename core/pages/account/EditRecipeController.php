<?php
class EditRecipeController implements IPageBase
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
            $this->template = Template::Load("account/".$pageData['template']);
            $userId = $_SESSION[$cfg['permissionSessionKey']]['data']['id'] ?? null;
            ValidateUser::Validate($userId);

            $this->template->AddData("TITLE", $this->model->LoadContent($pageData['pageID'], 'TITLE')['content']);
            $navbarTemplate = Template::Load("shared/navbar.html");
            $navbarTemplate->AddData("MAINPAGE", $this->model->LoadContent('account', 'MAINPAGE')['content']);
            $navbarTemplate->AddData("RECIPEPAGE", $this->model->LoadContent('account', 'RECIPEPAGE')['content']);
            $navbarTemplate->AddData("ACCOUNT", $this->model->LoadContent('account', 'ACCOUNT')['content']);
            $this->template->AddData("NAVBAR", $navbarTemplate);
            $footerTemplate = Template::Load("shared/footer.html");
            $newsletterTemplate = Template::Load("shared/newsletter.html");
            $footerTemplate->AddData("NEWSLETTER", $newsletterTemplate);
            $this->template->AddData("FOOTER", $footerTemplate);
            $mainContentTemplate = Template::Load("account/edit-recipe.html");
            $sideMenuTemplate = Template::Load("account/sideMenu.html");
            $sideMenuTemplate->AddData("MENU", $this->model->LoadContent('account', "MENU")['content']);
            $sideMenuTemplate->AddData("SAVED", $this->model->LoadContent('account', "SAVED")['content']);
            $sideMenuTemplate->AddData("LIKED", $this->model->LoadContent('account', "LIKED")['content']);
            $sideMenuTemplate->AddData("OWN", $this->model->LoadContent('account', "OWN")['content']);
            $sideMenuTemplate->AddData("UPLOAD", $this->model->LoadContent('account', "UPLOAD")['content']);
            $sideMenuTemplate->AddData("PROFILE", $this->model->LoadContent('account', "PROFILE")['content']);
            $sideMenuTemplate->AddData("LOGOUT", $this->model->LoadContent('account', "LOGOUT")['content']);
            $mainContentTemplate->AddData("SIDEMENU", $sideMenuTemplate);
            $mainContentTemplate->AddData("UPLOADRECIPE", $this->model->LoadContent($pageData['pageID'], 'TITLE')['content']);
            $mainContentTemplate->AddData("DEFAULTTAGOPTION", $this->model->LoadContent($pageData['pageID'], 'DEFAULTTAGOPTION')['content']);
            $mainContentTemplate->AddData("RECIPETITLE", $this->model->LoadContent('upload-recipe', 'RECIPETITLE')['content']);
            $mainContentTemplate->AddData("SERVINGSIZE", $this->model->LoadContent('upload-recipe', 'SERVINGSIZE')['content']);
            $mainContentTemplate->AddData("SERVINGTEXT", $this->model->LoadContent('upload-recipe', 'SERVINGTEXT')['content']);
            $mainContentTemplate->AddData("INGREDIENTS", $this->model->LoadContent('upload-recipe', 'INGREDIENTS')['content']);
            $mainContentTemplate->AddData("INGREDIENTTEXT", $this->model->LoadContent('upload-recipe', 'INGREDIENTTEXT')['content']);
            $mainContentTemplate->AddData("ADDINGREDIENT", $this->model->LoadContent('upload-recipe', 'ADDINGREDIENT')['content']);
            $mainContentTemplate->AddData("SHORTDESC", $this->model->LoadContent('upload-recipe', 'SHORTDESC')['content']);
            $mainContentTemplate->AddData("INSTRUCTIONS", $this->model->LoadContent('upload-recipe', 'INSTRUCTIONS')['content']);
            $mainContentTemplate->AddData("INSTRUCTIONTEXT", $this->model->LoadContent('upload-recipe', 'INSTRUCTIONTEXT')['content']);
            $mainContentTemplate->AddData("NEWINSTURCTION", $this->model->LoadContent('upload-recipe', 'NEWINSTURCTION')['content']);
            $mainContentTemplate->AddData("CATEGORIES", $this->model->LoadContent('upload-recipe', 'CATEGORIES')['content']);
            $mainContentTemplate->AddData("CATEGORYTEXT", $this->model->LoadContent('upload-recipe', 'CATEGORYTEXT')['content']);
            $mainContentTemplate->AddData("DEAFULTCATEGORYOPTION", $this->model->LoadContent($pageData['pageID'], 'DEAFULTCATEGORYOPTION')['content']);
            $mainContentTemplate->AddData("ADDCATEGORY", $this->model->LoadContent('upload-recipe', 'ADDCATEGORY')['content']);
            $mainContentTemplate->AddData("TAGS", $this->model->LoadContent('upload-recipe', 'TAGS')['content']);
            $mainContentTemplate->AddData("TAGSTEXT", $this->model->LoadContent('upload-recipe', 'TAGSTEXT')['content']);
            $mainContentTemplate->AddData("DIFFLABEL", $this->model->LoadContent('upload-recipe', 'DIFFLABEL')['content']);
            $mainContentTemplate->AddData("TIMELABEL", $this->model->LoadContent('upload-recipe', 'TIMELABEL')['content']);
            $mainContentTemplate->AddData("BUDGETLABEL", $this->model->LoadContent('upload-recipe', 'BUDGETLABEL')['content']);
            $mainContentTemplate->AddData("OCCLABEL", $this->model->LoadContent('upload-recipe', 'OCCLABEL')['content']);
            $mainContentTemplate->AddData("METHODLABEL", $this->model->LoadContent('upload-recipe', 'METHODLABEL')['content']);
            $mainContentTemplate->AddData("STYLELABEL", $this->model->LoadContent('upload-recipe', 'STYLELABEL')['content']);
            $mainContentTemplate->AddData("MAINIMAGE", $this->model->LoadContent('upload-recipe', 'MAINIMAGE')['content']);
            $mainContentTemplate->AddData("MAINIMAGETEXT", $this->model->LoadContent('upload-recipe', 'MAINIMAGETEXT')['content']);
            $mainContentTemplate->AddData("EXTRAIMAGES", $this->model->LoadContent('upload-recipe', 'EXTRAIMAGES')['content']);
            $mainContentTemplate->AddData("EXTRAIMAGESTEXT", $this->model->LoadContent('upload-recipe', 'EXTRAIMAGESTEXT')['content']);
            $mainContentTemplate->AddData("SAVE", $this->model->LoadContent($pageData['pageID'], 'SAVE')['content']);

            ValidateRecipeUrl::Validate($_GET['id'], $_GET['title']);
            $recipeId = $_GET['id'];

            [
                $recipeCategories, $recipeTags, $recipeIngredients, $instructions,
                $title, $servingSize, $description
            ] = RecipeService::GetEditRecipeData($_GET['id'], $userId);

            $insArray = ! empty($instructions) ? (explode(";", $instructions)) : [];
            $ingredients = IngredientService::FetchIngredients();
            $categories = CategoryService::FetchCategories();
            $tags = TagsService::FetchTags();
            $tagCats = TagsService::FetchTagCategories();


            $ingredientHtml = RecipeView::EditIngredientBlocks($recipeIngredients, $ingredients);
            $insHtml = RecipeView::EditInstructionBlocks($insArray);
            [$catListHtml, $catLabelsHtml, $selectedCategoryIds] = RecipeView::BuildCategoryOptions($recipeCategories, $categories);
            $tagsArray = RecipeView::BuildSelectedTags($tags, $tagCats, $recipeTags);
            $images = RecipeService::GetRecipeImages($recipeId);
            [$mainImageHtml, $extraImagesHtml] = RecipeView::BuildImageGallery($images);

            $originalExtras = ! empty($images['extraImages']) ? (implode(";", $images['extraImages'])) : "";

            $mainContentTemplate->AddData("RECIPETITLEVALUE", $title);
            $mainContentTemplate->AddData("SERVINGSVALUE", $servingSize ?? 1);
            $mainContentTemplate->AddData("SHORTDESCVALUE", $description);
            $mainContentTemplate->AddData("INGREDIENTLIST", $ingredientHtml);
            $mainContentTemplate->AddData("INSTRUCTIONLIST", $insHtml);
            $mainContentTemplate->AddData("SELECTEDCATEGORIES", $catLabelsHtml);
            $mainContentTemplate->AddData("SELECTEDCATEGORYIDSVALUE", $selectedCategoryIds);
            $mainContentTemplate->AddData("CATEGORYLIST", $catListHtml);
            $mainContentTemplate->AddData("EXISTINGMAINIMAGE", $mainImageHtml);
            $mainContentTemplate->AddData("EXISTINGEXTRAIMAGES", $extraImagesHtml);
            $mainContentTemplate->AddData("ORIGINALMAINIMAGE", $images['mainImage']);
            $mainContentTemplate->AddData("ORIGINALEXTRAIMAGES", $originalExtras);

            foreach ($tagsArray as $key => $value) {
                $mainContentTemplate->AddData($key, $value);
            }

            if (isset($_POST['updateRecipe'])) {
                ValidateUploadForm::Validate($_POST);

                if ($_FILES['mainImage']['error'] === UPLOAD_ERR_OK) {
                    RecipeImageHandler::Validate($_FILES, true, false);
                }
                if ($_FILES['images']['error'] === UPLOAD_ERR_OK) {
                    RecipeImageHandler::Validate($_FILES, false, true);
                }

                $isSuccess = RecipeService::UpdateRecipe($recipeId, $userId, $_POST, $_FILES);
                if ($isSuccess) {
                    header("Location: {$cfg['accoutPage']}&edit_success=1");
                    exit();
                } else {
                    throw new RecipeUpdateException("A recepet módosítása sikertelen. ");
                }
            }

            $this->template->AddData("MAINCONTENT", $mainContentTemplate);
            
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

        } catch (ValidateException | IngredientServiceException | IngredientException | DBException | TagException | RecipeNotFoundException $ex) {
            $this->template->AddData("MAINCONTENT", $cfg['recipeNotFoundImage']);
        } catch (RecipeUploadException | RecipeUpdateException | RecipeServiceException $ex) {
            Logger::Log("Display error: ".$ex->getMessage(), logLvl::Error);
            $mainContentTemplate->AddData("UPLOADRESULT", '<div class="alert alert-danger">'.$ex->getMessage().'</div>');
            $this->template->AddData("MAINCONTENT", $mainContentTemplate);
        }
    }
}