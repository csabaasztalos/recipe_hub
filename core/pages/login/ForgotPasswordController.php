<?php

final class ForgotPasswordController implements IPageBase
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
            $this->template = Template::Load("login/".$pageData['template']);
            $userId = $_SESSION[$cfg['permissionSessionKey']]['data']['id'] ?? null;

            $this->template->AddData('TITLE', $this->model->LoadContent($pageData['pageID'], "TITLE")["content"]);
            $this->template->AddData('HEADER', $this->model->LoadContent($pageData['pageID'], "HEADER")['content']);
            $this->template->AddData('SMALLTEXT', $this->model->LoadContent($pageData['pageID'], "SMALLTEXT")['content']);
            $this->template->AddData('SENDBTN', $this->model->LoadContent($pageData['pageID'], "SENDBTN")['content']);

            if (isset($_POST['send'])) {
                if (isset($_POST['email'])) {
                    $userData = UserService::GetUserByEmail($_POST["email"]);

                    if (! $userData) {
                        throw new UserNotFoundException("A megadott email címmel nem regisztráltak.");
                    }

                    if ($userData['last_reset_sent'] !== null) {
                        $lastSent = strtotime($userData['last_reset_sent']);
                    }

                    if (time() -  $lastSent > 1800) {
                        $email = $userData['email'];
                        $token = bin2hex(random_bytes(32));
                        $expires = date("Y-m-d H:i:s", time() + 1800);
                        $resetLink = $cfg['resetPasswordLink']."{$token}";
                        $lastSent = date('Y-m-d H:i:s', time());
                        $emailTemp = file_get_contents($cfg['emailFolder']."forgotPass.html");
                        $finalEmail = str_replace("CHANGEPASSWORDLINK", $resetLink, $emailTemp);
                        $altBody = "Elfelejtett jelszó \n
                        Ehhez az emailcímhez tartozó ReciepeHub fiókod jelszavát az alábbi linken tudod megváltoztatni:
                        ".$resetLink;

                        SendMail::Send([$email], "Jelszó változtatás", $finalEmail, $altBody);
                        UserService::UpdateResetToken($email, $token, $expires, $lastSent);
                        UserService::ChangeResetStatus($userData['id'], 0);

                        $_SESSION['status'] = "success";
                        $_SESSION['message'] = "Az email sikeresen kiküldve!";
                        $this->template->AddData('RESULT', "Az email sikeresen kiküldve!");
                    } else {
                        $cooldown = 30;
                        $time = (time() - $lastSent) / 60;
                        $remaining = round(max(1, $cooldown - $time));
                        $_SESSION['status'] = "error";
                        $_SESSION['message'] = "Az jelszóváltoztató email újraküldéséhez kérlek várj {$remaining} percet.";
                    }
                } else {
                    $_SESSION['status'] = "error";
                    $_SESSION['message'] = "Add meg a fiókhoz tartozó email címet!";
                }

                header("Location: ".$_SERVER['REQUEST_URI']);
                exit();
            }

            if (isset($_SESSION['status']) && isset($_SESSION['message'])) {
                $status = $_SESSION['status'];
                $message = $_SESSION['message'];
                $this->template->AddData("RESULT", $message);
                $this->template->AddData("RESULTSTATUS", $status);

                unset($_SESSION['status']);
                unset($_SESSION['message']);
            }

        } catch (UserNotFoundException $ex) {
            $this->template->AddData('RESULT', $ex->getMessage());
        } catch (DBException $ex) {
            throw new PageLoadException("Adatbázis hiba miatt az oldal betöltése sikertelen. ");
        } catch (MailException $ex) {
            $this->template->AddData('RESULT', "A folyamat során hiba lépett fel, kérjük próbáld meg később.");
        }
    }
}