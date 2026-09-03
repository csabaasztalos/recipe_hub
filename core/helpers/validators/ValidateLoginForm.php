<?php
final class ValidateLoginForm {
    public static function Validate (string $email, string $password): true {
        $model = new UsersModel();
        
        //email
        if (mb_strlen($email) > 254) {
            throw new LoginException("Az email cím max hossza 254 karakter.");
        }
        
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new LoginException("Érvénytelen email cím.");
        }

        $domain = substr(strrchr($email, "@"), 1);
        if (!checkdnsrr($domain, "MX")) {
            throw new LoginException("Érvénytelen email domain.");
        }

        //password
        if(mb_strlen($password) < 12) {
            throw new LoginException("A jelszó túl rövid. Minimum 12 karakter hosszú legyen.");
        }

        if(mb_strlen($password) > 72) {
            throw new LoginException("A jelszó túl hosszú. Maximum 72 karakter hosszú lehet.");
        }

        //both
        $emailResult = $model->CheckUserEmail($email);
        if ($emailResult->num_rows > 0) {
            $userData = $emailResult->fetch_assoc();
            if ($userData['verified'] === 0) {
                throw new LoginException("A fiók nincs aktiválva.");
            }
            if (! password_verify($password, $userData['password'])) {
                throw new LoginException("A jelszó vagy email cím helytelen.");
            }
            return true;
        }

        throw new LoginException("A jelszó vagy email cím helytelen.");
    }
}
