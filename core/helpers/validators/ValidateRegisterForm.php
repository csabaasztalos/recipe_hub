<?php
final class ValidateRegisterForm
{
    public static function Validate(string $email, string $userName, string $password, string $confirmPass): bool
    {
        $model = new UsersModel();

        //email
        if (mb_strlen($email) > 254) {
            throw new RegisterException("Az email cím max hossza 254 karakter.");
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RegisterException("Érvénytelen email cím.");
        }

        $domain = substr(strrchr($email, "@"), 1);
        if (! checkdnsrr($domain, "MX")) {
            throw new RegisterException("Érvénytelen email domain.");
        }

        $emailResult = $model->CheckUserEmail($email);
        if ($emailResult->num_rows > 0) {
            $userData = $emailResult->fetch_assoc();
            if ($userData['verified'] === 1) {
                throw new RegisterException("Ezzel az email címmel már regisztráltak.");
            }
            return false;
        }

        //userName
        $normalizeResult = normalizer_normalize($userName, Normalizer::FORM_C);
        if ($normalizeResult === false) {
            throw new CreateUserException("Érvénytelen felhasználónév.");
        }
        $normalized = trim($normalizeResult);

        if (mb_strlen($normalized) > 50 || mb_strlen($normalized) < 5) {
            throw new CreateUserException("Érvénytelen felhasználónév. A felhaszálónév hossza: 5-50 karakter.");
        }

        if (preg_match('/\s/u', $normalized)) {
            throw new CreateUserException("Érvénytelen felhaszálónév. Szóköz nem megengedett.");
        }

        if (! preg_match('/^\p{L}[\p{L}\p{N}_]*$/u', $normalized)) {
            throw new CreateUserException("Érvénytelen felhaszálónév. Megengedett speciális karater: _ , A név nem kezdődhet vele.");
        }


        $userNameResult = $model->CheckUserName($normalized);

        if ($userNameResult->num_rows > 0) {
            throw new RegisterException("Ez a felhasználónév már foglalt.");
        }

        //password
        if (mb_strlen($password) < 12) {
            throw new RegisterException("A jelszó túl rövid. Minimum 12 karakter hosszú legyen.");
        }

        if (mb_strlen($password) > 72) {
            throw new RegisterException("A jelszó túl hosszú. Maximum 72 karakter hosszú lehet.");
        }

        if ($password !== $confirmPass) {
            throw new RegisterException("A két jelszó nem egyezik.");
        }

        return true;
    }
}
