<?php

class MyRecipesController implements IPageBase
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
            $this->template->AddData("NAVBAR", $navbarTemplate);
            $footerTemplate = Template::Load("shared/footer.html");
            $newsletterTemplate = Template::Load("shared/newsletter.html");
            $footerTemplate->AddData("NEWSLETTER", $newsletterTemplate);
            $this->template->AddData("FOOTER", $footerTemplate);
            $mainContentTemplate = Template::Load("account/favourite-recipes.html");
            $mainContentTemplate->AddData("TITLE", $this->model->LoadContent($pageData['pageID'], 'TITLE')['content']);
            $sideMenuTemplate = Template::Load("account/sideMenu.html");
            $sideMenuTemplate->AddData("MENU", $this->model->LoadContent('account', "MENU")['content']);
            $sideMenuTemplate->AddData("SAVED", $this->model->LoadContent('account', "SAVED")['content']);
            $sideMenuTemplate->AddData("LIKED", $this->model->LoadContent('account', "LIKED")['content']);
            $sideMenuTemplate->AddData("OWN", $this->model->LoadContent('account', "OWN")['content']);
            $sideMenuTemplate->AddData("UPLOAD", $this->model->LoadContent('account', "UPLOAD")['content']);
            $sideMenuTemplate->AddData("PROFILE", $this->model->LoadContent('account', "PROFILE")['content']);
            $sideMenuTemplate->AddData("LOGOUT", $this->model->LoadContent('account', "LOGOUT")['content']);
            $mainContentTemplate->AddData("SIDEMENU", $sideMenuTemplate);

            $recipesData = AccountService::GetRecipesData($userId, "user");
            $recipeCardsHtml = AccountView::DrawRecipes($recipesData, true, $userId);

            $mainContentTemplate->AddData("RECIPECARDS", $recipeCardsHtml);
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
        } catch (AccountServiceException $ex) {
            $mainContentTemplate->AddData("RECIPECARDS", "");
            $this->template->AddData("MAINCONTENT", $mainContentTemplate);
        }
    }
}