<?php
final class ValidateCreateUser
{
    public static function Validate(string $email, string $userName, int $permission): bool
    {
        global $cfg;
        $model = new UsersModel();

        //email
        if (mb_strlen($email) > 254) {
            throw new CreateUserException("Az email cím max hossza 254 karakter.");
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new CreateUserException("Érvénytelen email cím.");
        }

        $domain = substr(strrchr($email, "@"), 1);
        if (! checkdnsrr($domain, "MX")) {
            throw new CreateUserException("Érvénytelen email domain.");
        }

        $emailResult = $model->CheckUserEmail($email);
        if ($emailResult->num_rows > 0) {
            throw new CreateUserException("Ezzel az email címmel már regisztráltak.");
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
            throw new CreateUserException("Ez a felhasználónév már foglalt.");
        }

        //permission
        if (! isset($cfg['permissonLevels'][$permission])) {
            Logger::Log("Wrong permission level.", logLvl::Warning);
            throw new CreateUserException("Ilyen jogkör nem létezik.");
        }

        return true;
    }
}
