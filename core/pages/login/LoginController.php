<?php
class LoginController implements IPageBase
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
        global $cfg;
        if (isset($_SESSION[$cfg['permissionSessionKey']])) {
            header("Location: ".$cfg['homePage']);
        }

        $this->template = Template::Load("login/".$pageData['template']);
        try {
            $this->template->AddData('TITLE', $this->model->LoadContent($pageData['pageID'], "TITLE")["content"]);
            $this->template->AddData('WELCOME', $this->model->LoadContent($pageData['pageID'], "WELCOME")["content"]);
            $this->template->AddData('WELCOMETEXT', $this->model->LoadContent($pageData['pageID'], "WELCOMETEXT")["content"]);
            $this->template->AddData('REGISTERBTN', $this->model->LoadContent($pageData['pageID'], "REGISTERBTN")["content"]);
            $this->template->AddData('LOGINHEADER', $this->model->LoadContent($pageData['pageID'], "LOGINHEADER")["content"]);
            $this->template->AddData('LOGINBTN', $this->model->LoadContent($pageData['pageID'], "LOGINBTN")["content"]);

            if (isset($_POST['login'])) {
                if (isset($_POST['email']) && isset($_POST['pass']) && trim($_POST['email']) !== '' && trim($_POST['pass']) !== '') {
                    $email = $_POST['email'];
                    $pass = $_POST['pass'];

                    $isValid = ValidateLoginForm::Validate($email, $pass);
                    $userData = UserQueryService::GetUserByEmail($email);
                    $userId = $userData['id'];
                    $userName = $userData['username'];
                    $permissionLvl = $userData['permission_level'];

                    if ($isValid) {
                        PermissionHandler::StartSession(['loginTime' => (new DateTime('now'))->format('Y-m-d H:i:s'),
                            'id' => $userId, 'name' => $userName, 'email' => $email], $permissionLvl);
                        $this->template->AddData('RESULT', "Sikeres Bejelentkezés!");
                    }

                    if ($_SESSION[$cfg['permissionSessionKey']]['permission'] === 1) {
                        sleep(1);
                        header("Location: {$cfg['homePage']}");
                        exit();
                    } elseif ($_SESSION[$cfg['permissionSessionKey']]['permission'] === 2) {
                        sleep(1);
                        header("Location: {$cfg['adminPage']}");
                        exit();
                    }
                } else {
                    $this->template->AddData('RESULT', "Töltsd ki az összes mezőt!");
                }
            }
        } catch (LoginException $ex) {
            $this->template->AddData('RESULT', $ex->getMessage());

        } catch (DBException $ex) {
            throw new PageLoadException("Adatbázis hiba miatt az oldal betöltése sikertelen. ");
        }
    }
}