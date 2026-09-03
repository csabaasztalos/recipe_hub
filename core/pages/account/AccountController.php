<?php

class AccountController implements IPageBase
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
            $this->template->AddData("FOOTER", $footerTemplate);

            $newsletterTemplate = Template::Load("shared/newsletter.html");
            $footerTemplate->AddData("NEWSLETTER", $newsletterTemplate);

            $mainContentTemplate = Template::Load("account/accountMainContent.html");
            $sideMenuTemplate = Template::Load("account/sideMenu.html");
            $sideMenuTemplate->AddData("MENU", $this->model->LoadContent($pageData['pageID'], "MENU")['content']);
            $sideMenuTemplate->AddData("SAVED", $this->model->LoadContent($pageData['pageID'], "SAVED")['content']);
            $sideMenuTemplate->AddData("LIKED", $this->model->LoadContent($pageData['pageID'], "LIKED")['content']);
            $sideMenuTemplate->AddData("OWN", $this->model->LoadContent($pageData['pageID'], "OWN")['content']);
            $sideMenuTemplate->AddData("UPLOAD", $this->model->LoadContent($pageData['pageID'], "UPLOAD")['content']);
            $sideMenuTemplate->AddData("PROFILE", $this->model->LoadContent($pageData['pageID'], "PROFILE")['content']);
            $sideMenuTemplate->AddData("LOGOUT", $this->model->LoadContent($pageData['pageID'], "LOGOUT")['content']);
            $mainContentTemplate->AddData("SIDEMENU", $sideMenuTemplate);
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

        } catch (Exception) {
            return;
        }
    }
}