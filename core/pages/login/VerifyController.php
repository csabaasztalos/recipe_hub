<?php
final class VerifyController implements IPageBase
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
            ValidateUser::Validate($userId);

            $this->template->AddData('TITLE', $this->model->LoadContent($pageData['pageID'], "TITLE")["content"]);
            $this->template->AddData('VERIFICATION', $this->model->LoadContent($pageData['pageID'], "VERIFICATION")["content"]);
            $this->template->AddData('RESENDBTN', $this->model->LoadContent($pageData['pageID'], "RESENDBTN")["content"]);
            $this->template->AddData('HOME', $this->model->LoadContent($pageData['pageID'], "HOME")["content"]);

            if (! isset($_GET['token'])) {
                header("Location: ".$cfg['homePage']);
                exit();
            }
            $userData = UserService::GetUserByVerificationToken($_GET['token']);

            if ($_SERVER['REQUEST_METHOD'] === 'POST' && $userData) {
                if (isset($_POST['resendBtn']) && $userData['verified'] === 0) {

                    if (time() < $userData["verification_token_expires"]) {
                        $address = $userData['email'];
                        $token = bin2hex(random_bytes(32));
                        $expires = date('Y-m-d H:i:s', time() + 3600);
                        $emailTemp = file_get_contents($cfg['emailFolder']."verification.html");
                        $verificationLink = $cfg['verificationlink']."{$token}";
                        $finalEmail = str_replace("ACTIVATIONLINK", $verificationLink, $emailTemp);
                        $lastSent = date('Y-m-d H:i:s', time());

                        SendMail::Send([$address], "Visszaigazoló", $finalEmail, "Sikeres regisztráció!\nA fiókod aktiválásához kattints a linkre.\n".$verificationLink);
                        UserService::UpdateVerificationToken($address, $token, $expires, $lastSent);

                        $maskedEmail = MaskEmail::Mask($address);
                        $_SESSION['status'] = "success";
                        $_SESSION['message'] = "A visszaigazoló sikeresen elküldve a $maskedEmail címre.";
                    } else {
                        $_SESSION['status'] = "error";
                        $_SESSION['message'] = "A visszaigazoló újraküldéséhez kérlek várj.";
                    }

                    header("Location: ".$_SERVER['REQUEST_URI']);
                    exit();
                }
            } else {
                if ($userData) {
                    if (strtotime($userData['verification_token_expires']) > time() && $userData['verified'] === 0) {
                        UserService::VerifyUser($userData['id']);
                        $_SESSION['verified'] = true;

                        if (isset($_SESSION['verified']) && $_SESSION['verified'] === true) {
                            PermissionHandler::StartSession(['loginTime' => (new DateTime('now'))->format('Y-m-d H:i:s'),
                                'id' => $userData['id'], 'name' => $userData['username'], 'email' => $userData['email']], $userData['permission_level']);
                        }

                        $_SESSION['status'] = "success";
                        $_SESSION['message'] = "Sikeresen akitváltad a fiókodat, hamarosan átirányítunk a főoldalra, bejelentkezve! (3s)";
                    } elseif (strtotime($userData['verification_token_expires']) < time() && $userData['verified'] === 0) {
                        $_SESSION['status'] = "neutral";
                        $_SESSION['message'] = "Az aktiválási időszak letelt(1 óra).";
                    } elseif ($userData['verified'] === 1) {
                        $_SESSION['status'] = "neutralMessage";
                        $_SESSION['message'] = "Ez a link már nem érvényes";
                    }
                } else {
                    $_SESSION['status'] = "error";
                    $_SESSION['message'] = "Ez a link már nem érvényes. ";
                }
            }

            if (isset($_SESSION['status']) && isset($_SESSION['message'])) {
                $status = $_SESSION['status'];
                $message = $_SESSION['message'];
                $this->template->AddData("VERIFICATIONTEXT", $message);
                $this->template->AddData("VERIFYCLASS", $status);

                unset($_SESSION['status']);
                unset($_SESSION['message']);
            }

        } catch (UserNotFoundException $ex) {
            $_SESSION['status'] = "error";
            $_SESSION['message'] = $ex->getMessage();
        } catch (DBException $ex) {
            throw new PageLoadException("Adatbázis hiba miatt az oldal betöltése sikertelen. ");
        } catch (MailException $ex) {
            $this->template->AddData('RESULT', "A regisztráció során hiba lépett fel.");
        }
    }
}
