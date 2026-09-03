<?php

class ProfileController implements IPageBase
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
            $navbarTemplate->AddData("MAINPAGE", $this->model->LoadContent('account', 'MAINPAGE')['content']);
            $navbarTemplate->AddData("RECIPEPAGE", $this->model->LoadContent('account', 'RECIPEPAGE')['content']);
            $navbarTemplate->AddData("ACCOUNT", $this->model->LoadContent('account', 'ACCOUNT')['content']);
            $this->template->AddData("NAVBAR", $navbarTemplate);

            $footerTemplate = Template::Load("shared/footer.html");
            $this->template->AddData("FOOTER", $footerTemplate);

            $newsletterTemplate = Template::Load("shared/newsletter.html");
            $footerTemplate->AddData("NEWSLETTER", $newsletterTemplate);

            $mainContentTemplate = Template::Load("account/profile.html");
            $sideMenuTemplate = Template::Load("account/sideMenu.html");
            $sideMenuTemplate->AddData("MENU", $this->model->LoadContent('account', "MENU")['content']);
            $sideMenuTemplate->AddData("SAVED", $this->model->LoadContent('account', "SAVED")['content']);
            $sideMenuTemplate->AddData("LIKED", $this->model->LoadContent('account', "LIKED")['content']);
            $sideMenuTemplate->AddData("OWN", $this->model->LoadContent('account', "OWN")['content']);
            $sideMenuTemplate->AddData("UPLOAD", $this->model->LoadContent('account', "UPLOAD")['content']);
            $sideMenuTemplate->AddData("PROFILE", $this->model->LoadContent('account', "PROFILE")['content']);
            $sideMenuTemplate->AddData("LOGOUT", $this->model->LoadContent('account', "LOGOUT")['content']);
            $mainContentTemplate->AddData("TITLE", $this->model->LoadContent($pageData['pageID'], 'PAGETITLE')['content']);
            $mainContentTemplate->AddData("MODIFYEMAIL", $this->model->LoadContent($pageData['pageID'], 'MODIFYEMAIL')['content']);
            $mainContentTemplate->AddData("MODIFYNAME", $this->model->LoadContent($pageData['pageID'], 'MODIFYNAME')['content']);
            $mainContentTemplate->AddData("OLDPASS", $this->model->LoadContent($pageData['pageID'], 'OLDPASS')['content']);
            $mainContentTemplate->AddData("NEWPASS", $this->model->LoadContent($pageData['pageID'], 'NEWPASS')['content']);
            $mainContentTemplate->AddData("PASSHELP", $this->model->LoadContent($pageData['pageID'], 'PASSHELP')['content']);
            $mainContentTemplate->AddData("FORGOTPASSLINK", $this->model->LoadContent($pageData['pageID'], 'FORGOTPASSLINK')['content']);
            $mainContentTemplate->AddData("FORGOTPASS", $this->model->LoadContent($pageData['pageID'], 'FORGOTPASS')['content']);
            $mainContentTemplate->AddData("SAVE", $this->model->LoadContent($pageData['pageID'], 'SAVE')['content']);
            $mainContentTemplate->AddData("SIDEMENU", $sideMenuTemplate);
            

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

            if(isset($_GET['edit_success']) && (int) $_GET['edit_success'] === 1) {
                $mainContentTemplate->AddData("SUCCESSMESSAGET",
                '<div class="alert alert-success">Sikeres Módosítás</div>');
            }

            $userMail = $_SESSION[$cfg['permissionSessionKey']]['data']['email'];
            $userdata = UserService::GetUserByEmail($userMail);
            $userId = $userdata['id'];
            $mainContentTemplate->AddData("USEREMAIL", $userdata['email']);
            $mainContentTemplate->AddData("USERNAME", $userdata['username']);

            if(isset($_POST['save'])) {
                if(isset($_POST['email'])) {
                    UserService::UpdateEmail($userId, $_POST['email']);
                    header("Location: {$cfg['profilePage']}&edit_success=1");
                    exit();
                }

                if(isset($_POST['name'])) {
                    UserService::UpdateUsername($userId, $_POST['name']);
                    header("Location: {$cfg['profilePage']}&edit_success=1");
                    exit();
                }

                if(isset($_POST['newPass']) && isset($_POST['oldPass'])) {
                    UserService::UpdatePassword($userId, $_POST['newPass'], $_POST['oldPass']);
                    header("Location: {$cfg['profilePage']}&edit_success=1");
                    exit();
                }
            }

        } catch (EditUserException $ex) {
             $mainContentTemplate->AddData("UPLOADRESULT",
                '<div class="alert alert-danger">'.$ex->getMessage().'</div>');
        } catch (DBException $ex) {
            Logger::Log("Database error: ".$ex->getMessage(), logLvl::Error);
            $mainContentTemplate->AddData("UPLOADRESULT",
                'Hiba történt az adatok lekérése közben.'.$ex->getMessage());
        } finally {
            $this->template->AddData("MAINCONTENT", $mainContentTemplate);
        }
    }
}