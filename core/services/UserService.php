<?php

final class UserService
{
    public static function CreateUser(string $userName, string $userEmail, int $permission): void
    {
        global $cfg;
        $model = new UsersModel();

        try {
            ValidateCreateUser::Validate($userEmail, $userName, $permission);
            $pass = GeneratePassword::Generate(12);
            $hashedPass = password_hash($pass, PASSWORD_DEFAULT);
            $model->CreateUser($userName, $hashedPass, $userEmail, $permission, 1, null, null, null, 'admin', "user created");
            header("Location: {$cfg['userManagerPage']}");
            exit();

        } catch (Exception $ex) {
            throw new CreateUserException("Nem sikerült új felhasználót létrehozni. ".$ex->getMessage());
        }
    }

    public static function RegisterUser(string $userName, string $email, string $password, string $token, string $tokenExpires, string $lastSent): true
    {
        $model = new UsersModel();

        if ($model->CreateUser($userName, $password, $email, 1, 0, $token, $tokenExpires, $lastSent, "self", "user registered")) {
            return true;
        }

        throw new RegisterException("A regisztráció során hiba lépett fel.");
    }

    public static function EditUser(int $id, string $userName, string $userEmail, int $permissionLvl, ?string $password, int $verified): void
    {
        global $cfg;
        $model = new UsersModel();

        try {
            if ($model->GetUser($id)->num_rows !== 1) {
                Logger::Log("This user does not exist.", logLvl::Warning);
                throw new UserServiceException("Ez a felhasználó nem létezik.");
            }

            [$newEmail, $newName, $newPass, $newPermission, $newPass, $newStatus]
                = ValidateEditUserForm::Validate($id, $userEmail, $userName, $password, $permissionLvl, $verified);

            $model->UpdateUser($id, $newName, $newPass, $newPermission, $newEmail, $newStatus, 'admin', 'users info updated');

            if (isset($newPass)) {
                $address = [];
                $address[] = $newEmail ?? $userEmail;
                $mailTemp = file_get_contents($cfg['emailFolder']."generatedPass.html");
                $finalEmail = str_replace("NEWPASS", $password, $mailTemp);

                SendMail::Send($address, "Jelszava módosult", $finalEmail, "Jelszavad módosult\n
                    Az emailcímedhez tartozó fiók a jelszava módosítva lett. A következő jelszóval tudsz belépni: {$password}\n
                    Ez a jelszó autómatikusan generált, kérjük bejelentkezés után módosítsd a fiók beállításokban!\n
                    Ha nem te kezdemnényezted a jelszó változtatást kérjük vedd fel velünk a kapcsolatot!\n
                    recipehub@recipehub.hu");
            }

            header("Location: {$cfg['userManagerPage']}");
            exit();

        } catch (Exception $ex) {
            throw new EditUserException("Nem sikerült módosítani a felhasználót. ".$ex->getMessage());
        }
    }

    public static function DeleteUser(int $id): void
    {
        try {
            global $cfg;
            $model = new UsersModel();

            $result = $model->GetUser($id);
            $userData = $result->fetch_assoc();
            if (! $userData['id']) {
                throw new UserServiceException("Ilyen felhasználó nem létezik.");
            }

            $model->DeleteUser($id, null, "user deleted");
            Logger::Log("User({$id}) deleted.", logLvl::Info);
            header("Location: {$cfg['userManagerPage']}");
            exit();

        } catch (Exception $ex) {
            Logger::Log("Failed to delete user. ".$ex->getMessage(), logLvl::Warning);
            throw new DeleteUserException("Failed to delete user. ".$ex->getMessage());
        }
    }

    public static function CheckUserId(int $id): bool
    {
        try {
            $model = new UsersModel();

            $userData = $model->GetUser($id)->fetch_assoc();
            if (! $userData) {
                Logger::Log("User({$id}) does not exist.", logLvl::Info);
                return false;
            }

            return true;
        } catch (Exception $ex) {
            Logger::Log("Failed to check user by id. ".$ex->getMessage(), logLvl::Warning);
            throw new UserServiceException("Nem sikerült ellenőrizni a felhasználót.");
        }
    }

    public static function UpdateVerificationToken(string $email, string $token, string $tokenExpires, string $lastSent): void
    {
        $model = new UsersModel();

        $userResult = $model->GetUserByEmail($email);
        if (! $userResult) {
            Logger::Log("User does not exist.", logLvl::Info);
            throw new UserNotFoundException("Ilyen felhasználó nem létezik.");
        }

        $userData = $userResult->fetch_assoc();
        if ($userData['verified'] === 0) {
            $result = $model->UpdateVerificationToken($userData['id'], $token, $tokenExpires, $lastSent);

            if (! $result) {
                throw new UserServiceException("Felhasználó token nem került frissítésre.");
            }

            return;
        }

        throw new UserServiceException("A(z) $email email cím egy aktív fiókhoz tartozik.");
    }

    public static function GetUserByVerificationToken(string $token): array
    {
        $model = new UsersModel();

        $result = $model->GetUserByVerificationToken($token);
        $user = $result->fetch_assoc();

        if (! $user) {
            return [];
        }

        return $user;
    }

    public static function GetUserByResetToken(string $token): array
    {
        $model = new UsersModel();

        $result = $model->GetUserByResetToken($token);
        $user = $result->fetch_assoc();

        if (! $user) {
            return [];
        }

        return $user;
    }

    public static function GetUserByEmail(?string $email): array
    {
        $model = new UsersModel();

        $result = $model->GetUserByEmail($email);
        $user = $result->fetch_assoc();

        if (! $user) {
            return [];
        }

        return $user;
    }

    public static function VerifyUser(int $id)
    {
        $model = new UsersModel();

        $result = $model->VerifyUser($id);

        if (! $result) {
            Logger::Log("Could not verify user, id: {$id}.", logLvl::Info);
            throw new UserNotFoundException("Felhasználó aktiválása sikertelen.");
        }
    }

    public static function UpdateEmail(int $id, string $email){
        $model = new UsersModel();
        $result = $model->GetUser($id);

        if (! $result) {
            Logger::Log("User does not exist with this id: {$id}.", logLvl::Info);
            throw new UserNotFoundException("Felhasználó nem létezik.");
        }

        $userData = $result->fetch_assoc();

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

        $model->UpdateUser($id, null, null, null, $email, null, "self", "user updated their profile details");
    }

    public static function UpdateUsername(int $id, string $name){
        $model = new UsersModel();
        $result = $model->GetUser($id);

        if (! $result) {
            Logger::Log("User does not exist with this id: {$id}.", logLvl::Info);
            throw new UserNotFoundException("Felhasználó nem létezik.");
        }

        $userData = $result->fetch_assoc();
        $normalizeResult = normalizer_normalize($name, Normalizer::FORM_C);
        if ($normalizeResult === false) {
            throw new EditUserException("Érvénytelen felhasználónév.");
        }
        $normalized = trim($normalizeResult);

        if (mb_strlen($normalized) > 50 || mb_strlen($normalized) < 5) {
            throw new EditUserException("Érvénytelen felhasználónév. A felhaszálónév hossza: 5-50 karakter.");
        }

        if (preg_match('/\s/u', $normalized)) {
            throw new EditUserException("Érvénytelen felhaszálónév. Szóköz nem megengedett.");
        }

        if (! preg_match('/^\p{L}[\p{L}\p{N}_]*$/u', $normalized)) {
            throw new EditUserException("Érvénytelen felhaszálónév. Megengedett speciális karater: _ , A név nem kezdődhet vele.");
        }

        $userNameResult = $model->CheckUserName($normalized);

        if ($userNameResult->num_rows > 0 && $userData['username'] !== $name) {
            throw new EditUserException("Ez a felhasználónév már foglalt.");
        }

        $model->UpdateUser($id, $name, null, null, null, null, "self", "user updated their profile details");
    }

    public static function UpdatePassword(int $id, string $newPass, string $oldPass){
        $model = new UsersModel();
        $result = $model->GetUser($id);

        if (! $result) {
            Logger::Log("User does not exist with this id: {$id}.", logLvl::Info);
            throw new UserNotFoundException("Felhasználó nem létezik.");
        }
        $userData = $result->fetch_assoc();

        if (mb_strlen($newPass) < 12) {
            throw new EditUserException("A jelszó túl rövid. Minimum 12 karakter hosszú legyen.");
        }

        if (mb_strlen($newPass) > 72) {
            throw new EditUserException("A jelszó túl hosszú. Maximum 72 karakter hosszú lehet.");
        }

        if (!password_verify($oldPass, $userData['password'])) {
            throw new EditUserException("Helytelen régi jelszó");
        }

        if (!password_verify($newPass, $userData['password'])) {
            $newPassHash = password_hash($newPass, PASSWORD_DEFAULT);
        }

        $model->UpdateUser($id, null, $newPassHash, null, null, null, "self", "user updated their profile details");
    }

    public static function UpdateResetToken(string $email, string $token, string $tokenExpires, string $lastSent) {
        $model = new UsersModel();

        $userResult = $model->GetUserByEmail($email);
        if (! $userResult) {
            Logger::Log("User does not exist.", logLvl::Info);
            throw new UserNotFoundException("Ilyen felhasználó nem létezik.");
        }

        $userData = $userResult->fetch_assoc();
        $result = $model->UpdateResetToken($userData['id'], $token, $tokenExpires, $lastSent);

        if (! $result) {
            throw new UserServiceException("Felhasználó token nem került frissítésre.");
        }
    }

    public static function ChangePassword(int $id, string $hashedPassword) {
        $model = new UsersModel();

        $result = $model->UpdatePassword($id, $hashedPassword);
        if (! $result) {
            throw new UserServiceException("A jelszó nem lett megtváltoztatva.");
        }
    }

    public static function ChangeResetStatus(int $id, int $resetStatus) {
        $model = new UsersModel();

        $result = $model->ChangeResetStatus($id, $resetStatus);
        if (! $result) {
            throw new UserServiceException("A reset státusz nem lett megtváltoztatva.");
        }
    }
}

