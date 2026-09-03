<?php

final class ChangePasswordController implements IPageBase
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
        $this->template = Template::Load("account/".$pageData['template']);
        try {
            $this->template->AddData('TITLE', $this->model->LoadContent($pageData['pageID'], "TITLE")["content"]);
            $this->template->AddData('CHANGEPASSWORD', $this->model->LoadContent($pageData['pageID'], "CHANGEPASSWORD")['content']);
            $this->template->AddData('NEWPASSTEXT', $this->model->LoadContent($pageData['pageID'], "NEWPASSTEXT")['content']);
            $this->template->AddData('PASSCONFIRMTEXT', $this->model->LoadContent($pageData['pageID'], "PASSCONFIRMTEXT")['content']);
            $this->template->AddData('RESENDBTN', $this->model->LoadContent($pageData['pageID'], "RESENDBTN")['content']);
            $this->template->AddData('RESETBTN', $this->model->LoadContent($pageData['pageID'], "RESETBTN")['content']);

            if (! isset($_GET["token"])) {
                header("Location: ".$cfg['homePage']);
                exit();
            }
            $userData = UserService::GetUserByResetToken($_GET["token"]);

            if ($_SERVER['REQUEST_METHOD'] === "POST" && $userData) {
                if (isset($_POST['resendBtn'])) {
                    $lastSent = strtotime($userData['last_reset_sent']);

                    if (time() - $lastSent > 1800) {
                        $email = $userData['email'];
                        $token = bin2hex(random_bytes(32));
                        $expires = date("Y-m-d H:i:s", time() + 1800);
                        $resetLink = $cfg['resetPasswordLink']."{$token}";
                        $lastSent = date('Y-m-d H:i:s', time());
                        $resetUsed = 0;
                        $emailTemp = file_get_contents($cfg['emailFolder']."forgotPass.html");
                        $finalEmail = str_replace("CHANGEPASSWORDLINK", $resetLink, $emailTemp);
                        $altBody = "Elfelejtett jelszó \n
                    Ehhez az emailcímhez tartozó ReciepeHub fiókod jelszavát az alábbi linken tudod megváltoztatni:
                    ".$resetLink;

                        SendMail::Send([$email], "Jelszó változtatás", $finalEmail, $altBody);
                        UserService::UpdateResetToken($email, $token, $expires, $lastSent);
                        UserService::ChangeResetStatus($userData['id'], $resetUsed);

                        $maskedEmail = MaskEmail::Mask($email);
                        $_SESSION['status'] = "success";
                        $_SESSION['message'] = "Az jelszóváltoztató email sikeresen kiküldve a $maskedEmail címre!";
                    } else {
                        $cooldown = 30;
                        $time = (time() - $lastSent) / 60;
                        $remaining = round(max(1, $cooldown - $time));
                        $_SESSION['status'] = "error";
                        $_SESSION['message'] = "Az jelszóváltoztató email újraküldéséhez kérlek várj {$remaining} percet.";
                    }
                }
            }

            if ($userData && $userData['reset_used'] === 0) {
                if (isset($_POST['resetBtn']) && strtotime($userData['reset_token_expires']) > time()){
                    if (isset($_POST['newPassword']) && isset($_POST['passwordConfirm'])) {
                        $password = $_POST['newPassword'];
                        $passwordAgain = $_POST['passwordConfirm'];
                        ValidatePassword::Validate($password, $passwordAgain);

                        $passwordHashed = password_hash($password, PASSWORD_DEFAULT);
                        $resetStatus = 1;
                        UserService::ChangePassword($userData['id'], $passwordHashed);
                        UserService::ChangeResetStatus($userData['id'], $resetStatus);

                        $_SESSION['status'] = "success";
                        $_SESSION['message'] = "Sikeresen megváltoztattad a jelszavad! Hamarosan átirányítunk a főoldalra (3s).";
                    }
                    else {
                        $_SESSION['status'] = "error";
                        $_SESSION['message'] = "A két jelszó nem egyezik!";
                    }
                } elseif (strtotime($userData['reset_token_expires']) < time()) {
                    $_SESSION['status'] = "neutral";
                    $_SESSION['message'] = "A link érvényességi ideje letelt(30 perc), szükség esetén kattints az újraküldés gombra.";
                }
            } else {
                $_SESSION['status'] = "error";
                $_SESSION['message'] = "Ez a link már nem érvényes. ";
            }

            if (isset($_SESSION['status']) && isset($_SESSION['message'])) {
                $status = $_SESSION['status'];
                $message = $_SESSION['message'];
                $this->template->AddData("RESETTEXT", $message);
                $this->template->AddData("RESETCLASS", $status);

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
