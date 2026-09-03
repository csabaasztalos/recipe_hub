<?php
final class RegisterController implements IPageBase
{

    private Template $template;

    private Model $model;
    private DBHandler $db;

    public function __construct()
    {
        $this->model = Container::Get("model");
        $this->db = Container::Get('db');
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
            exit();
        }

        $this->template = Template::Load("login/".$pageData['template']);

        try {
            $this->template->AddData('TITLE', $this->model->LoadContent($pageData['pageID'], "TITLE")["content"]);
            $this->template->AddData('WELCOME', $this->model->LoadContent($pageData['pageID'], "WELCOME")["content"]);
            $this->template->AddData('WELCOMETEXT', $this->model->LoadContent($pageData['pageID'], "WELCOMETEXT")["content"]);
            $this->template->AddData('REGISTERBTN', $this->model->LoadContent($pageData['pageID'], "REGISTERBTN")["content"]);
            $this->template->AddData('REGISTERHEADER', $this->model->LoadContent($pageData['pageID'], "REGISTERHEADER")["content"]);
            $this->template->AddData('LOGINBTN', $this->model->LoadContent($pageData['pageID'], "LOGINBTN")["content"]);

            if (isset($_POST['register'])) {
                if (isset($_POST['email']) && isset($_POST['user']) && isset($_POST['pass']) && isset($_POST['passConfirm'])) {
                    $email = $_POST['email'];
                    $userName = $_POST['user'];
                    $password = $_POST['pass'];
                    $confirmPassword = $_POST['passConfirm'];

                    $newUser = ValidateRegisterForm::Validate($email, $userName, $password, $confirmPassword);
                    $token = bin2hex(random_bytes(32));
                    $expires = date('Y-m-d H:i:s', time() + 3600);
                    $emailTemp = file_get_contents($cfg['emailFolder']."verification.html");
                    $verificationLink = $cfg['verificationlink']."{$token}";
                    $lastSent = date('Y-m-d H:i:s', time());

                    $finalEmail = str_replace("ACTIVATIONLINK", $verificationLink, $emailTemp);
                    SendMail::Send([$email], "Visszaigazoló", $finalEmail, "Sikeres regisztráció!\nA fiókod aktiválásához kattints a linkre.\n".$verificationLink);
                    $_SESSION['verificationPendingEmail'] = $email;

                    if (! $newUser) {
                        $_SESSION['status'] = 'success2';
                        UserService::UpdateVerificationToken($email, $token, $expires, $lastSent);
                    } else {
                        $_SESSION['status'] = 'success';
                        $hashedPass = password_hash($password, PASSWORD_DEFAULT);
                        UserService::RegisterUser($userName, $email, $hashedPass, $token, $expires, $lastSent);
                    }

                    header("Location: ".$cfg['registerPage']);
                    exit();
                } else {
                    throw new RegisterException("A regisztrációhoz töltsd ki az összes mezőt.");
                }
            }

            $status = $_SESSION['status'] ?? null;
            if ($status === 'success') {
                $this->template->AddData('RESULT', "Sikeres regisztráció!\n A visszigazoló emailben tudod aktiválni fiókodat.");
                unset($_SESSION['status']);
            } elseif ($status === 'success2') {
                $this->template->AddData('RESULT', "Az email címhez már tartozik fiók. \n Újraküldtük a visszaigazoló emailt.");
                unset($_SESSION['status']);
            }

        } catch (RegisterException $ex) {
            $this->template->AddData('RESULT', $ex->getMessage());
        } catch (DBException $ex) {
            throw new PageLoadException("Adatbázis hiba miatt az oldal betöltése sikertelen. ");
        } catch (MailException $ex) {
            $id = $this->db->GetLastInsertId();
            UserService::DeleteUser($id);
            $this->template->AddData('RESULT', "A regisztráció során hiba lépett fel.");
        }
    }
}
