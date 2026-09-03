<?php
final class ValidateEditUserForm
{
    public static function Validate(int $id, string $email, string $userName, ?string $password, int $permissionLvl, int $verified): array
    {
        global $cfg;
        $model = new UsersModel();
        $userResult = $model->GetUser($id);
        $userData = $userResult->fetch_assoc();
        $newEmail = null;
        $newName = null;
        $newPass = null;
        $newPermission = null;
        $newPass = null;
        $newStatus = null;

        //email
        if (mb_strlen($email) > 254) {
            throw new EditUserException("Az email cím max hossza 254 karakter.");
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new EditUserException("Érvénytelen email cím.");
        }

        $domain = substr(strrchr($email, "@"), 1);
        if (! checkdnsrr($domain, "MX")) {
            throw new EditUserException("Érvénytelen email domain.");
        }

        $emailResult = $model->CheckUserEmail($email);
        if ($emailResult->num_rows > 0) {
            $userByEmail = $emailResult->fetch_assoc();
            if ($userByEmail['verified'] === 1 && $userData['email'] !== $email) {
                throw new EditUserException("Ezzel az email címmel már regisztráltak.");
            }
        }

        $newEmail = $email;

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

        if ($userNameResult->num_rows > 0 && $userData['username'] !== $userName) {
            throw new EditUserException("Ez a felhasználónév már foglalt.");
        }

        $newName = $userName;

        //password
        if (isset($password)) {
            if (mb_strlen($password) < 12) {
                throw new EditUserException("A jelszó túl rövid. Minimum 12 karakter hosszú legyen.");
            }

            if (mb_strlen($password) > 72) {
                throw new EditUserException("A jelszó túl hosszú. Maximum 72 karakter hosszú lehet.");
            }

            $newPass = password_hash($password, PASSWORD_DEFAULT);
        }

        if (! isset($cfg['newUserPermissionOptions'][$permissionLvl])) {
            throw new EditUserException("Ilyen jogkörrel nem rendelkezhet felhasználó.");
        } elseif ($permissionLvl !== $userData['permission_level']) {
            $newPermission = $permissionLvl;
        }

        if (($verified === 1 || $verified === 0) && ($verified !== $userData['verified'])) {
            $newStatus = $verified;
        } elseif ($verified !== $userData['verified']) {
            throw new EditUserException("Ilyen felhasználó státusz nem létezik.");
        }

        return [
            $newEmail,
            $newName,
            $newPass,
            $newPermission,
            $newPass,
            $newStatus
        ];
    }
}
