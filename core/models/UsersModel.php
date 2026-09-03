<?php
class UsersModel
{
    //TODO:: MORE USER FUNCITONS pl. getUserRecipes
    private DBHandler $db;
    private ActivityLogModel $activityLog;

    public function __construct()
    {
        $this->db = Container::Get('db');
        $this->activityLog = Container::Get('activityLog');
    }


    public function GetUser($id): mysqli_result
    {
        return $this->db->RunQuery("SELECT * FROM `users` WHERE id = ?",
            [new DBParam(DBTypes::INT, $id)]);
    }

    public function GetAllUsers(): mysqli_result
    {
        return $this->db->RunQuery("SELECT * FROM `users` ORDER BY id");

    }

    public function GetFilteredUsers(mixed $keyword, ?string $dateFrom, ?string $dateTo): mysqli_result
    {
        $where = [];
        $params = [];

        // Keyword filter (search in title and username)
        if ($keyword !== null && $keyword !== '') {
            $where[] = "(users.username LIKE ? OR users.email LIKE ?)";
            $params[] = new DBParam(DBTypes::STRING, '%'.$keyword.'%');
            $params[] = new DBParam(DBTypes::STRING, '%'.$keyword.'%');
            if (is_numeric($keyword)) {
                $params[] = new DBParam(DBTypes::INT, $keyword);
            }
        }

        //registered at
        if ($dateFrom !== null && $dateFrom !== '' && $dateTo !== null && $dateTo !== '') {
            $where[] = "DATE(users.created_at) BETWEEN ? AND ?";
            $params[] = new DBParam(DBTypes::STRING, $dateFrom);
            $params[] = new DBParam(DBTypes::STRING, $dateTo);
        } elseif ($dateFrom !== null && $dateFrom !== '') {
            $where[] = "DATE(users.created_at) >= ?";
            $params[] = new DBParam(DBTypes::STRING, $dateFrom);
        } elseif ($dateTo !== null && $dateTo !== '') {
            $where[] = "DATE(users.created_at) <= ?";
            $params[] = new DBParam(DBTypes::STRING, $dateTo);
        }

        $whereSql = '';
        if (! empty($where)) {
            $whereSql = 'WHERE '.implode(' AND ', $where);
        }

        $sql = "SELECT * FROM `users` $whereSql ORDER BY `id` DESC";

        return $this->db->RunQuery($sql, $params);
    }

    public function GetUsersEmail(int $id): bool|mysqli_result
    {
        return $this->db->RunQuery("SELECT `email` FROM `users` WHERE id = ?",
            [new DBParam(DBTypes::INT, $id)]);
    }

    public function GetUserByEmail(string $email): bool|mysqli_result
    {
        return $this->db->RunQuery("SELECT * FROM `users` WHERE email = ?",
            [new DBParam(DBTypes::STRING, $email)]);
    }

    public function GetUsersName(int $id): bool|mysqli_result
    {
        return $this->db->RunQuery("SELECT `username` FROM `users` WHERE id = ?",
            [new DBParam(DBTypes::INT, $id)]);
    }


    public function CheckUserEmail(string $email): mysqli_result
    {

        return $this->db->RunQuery("SELECT * FROM `users` WHERE email = ?",
            [new DBParam(DBTypes::STRING, $email)]);
    }

    public function CheckUserPermisssion(int $id): mysqli_result
    {
        return $this->db->RunQuery("SELECT `permission_level` FROM `users` WHERE id = ?",
            [new DBParam(DBTypes::INT, $id)]);
    }

    public function CheckUserName(string $username): mysqli_result
    {

        return $this->db->RunQuery("SELECT * FROM `users` WHERE username = ?",
            [new DBParam(DBTypes::STRING, $username)]);
    }

    public function CreateUser(string $user, string $pass, string $email, int $permissionLevel, int $verified, ?string $token, ?string $tokenExpires, ?string $lastSent, string $source = 'self', string $description = ''): int|bool
    {
        global $cfg;

        $result = $this->db->RunQuery("INSERT INTO users (username, password, email, permission_level, verified, verification_token, verification_token_expires, last_verification_sent) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            [
                new DBParam(DBTypes::STRING, $user),
                new DBParam(DBTypes::STRING, $pass),
                new DBParam(DBTypes::STRING, $email),
                new DBParam(DBTypes::INT, $permissionLevel),
                new DBParam(DBTypes::INT, $verified),
                new DBParam(DBTypes::STRING, $token),
                new DBParam(DBTypes::STRING, $tokenExpires),
                new DBParam(DBTypes::STRING, $lastSent)
            ]);

        if ($result) {
            $id = $this->db->GetLastInsertId();
            if ($source === 'self') {
                $action = 'registered';
                $submitted_by = $id;
                $description == '' ? $description = "user registered" : $description;
            } else {
                $action = 'created';
                $submitted_by = $cfg["adminUserID"];
                $description == '' ? $description = "User created" : $description;
            }
            $this->activityLog->ActivityLog("users", $id, $action, $submitted_by, $description);
            return $id;
        }
        return false;
    }

    public function VerifyUser(int $id): int|bool
    {
        $result = $this->db->RunQuery("UPDATE users SET verified = 1, verification_token = NULL, verification_token_expires = NULL WHERE id = ?",
            [
                new DBParam(DBTypes::INT, $id)
            ]);

        if ($result) {
            return $id;
        }
        return false;
    }

    public function SubToNewsletter($email): bool
    {
        global $cfg;
        $result = $this->db->RunQuery("INSERT INTO `newsletter` (email) VALUES(?)",
            [new DBParam(DBTypes::STRING, $email)]);

        if ($result) {
            $changedBy = $cfg["adminUserID"];
            $id = $this->db->GetLastInsertId();
            $this->activityLog->ActivityLog("newsletter", $id, 'subscribed', $changedBy, "email has been added to the newsletter list");
            return true;
        }
        return false;
    }

    public function UpdateUser(int $id, ?string $username, ?string $password, ?int $permissionLevel, ?string $email, ?int $verified, string $source = 'self', string $description = ''): bool
    {
        global $cfg;
        $fields = [];
        $params = [];

        if ($username !== null) {
            $fields[] = "username = ?";
            $params[] = new DBParam(DBTypes::STRING, $username);
        }
        if ($password !== null) {
            $fields[] = "password = ?";
            $params[] = new DBParam(DBTypes::STRING, $password);
        }
        if ($permissionLevel !== null) {
            $fields[] = "permission_level = ?";
            $params[] = new DBParam(DBTypes::STRING, $permissionLevel);
        }
        if ($email !== null) {
            $fields[] = "email = ?";
            $params[] = new DBParam(DBTypes::STRING, $email);
        }
        if ($verified !== null) {
            $fields[] = "verified = ?";
            $params[] = new DBParam(DBTypes::INT, $verified);
        }

        if (empty($fields)) {
            return false;
        }

        $params[] = new DBParam(DBTypes::INT, $id);

        $sql = "UPDATE `users` SET ".implode(',', $fields)." WHERE id = ?";
        $result = $this->db->RunQuery($sql, $params);

        if ($result) {
            if ($source === 'self') {
                $submitted_by = $id;
            } else {
                $submitted_by = $cfg["adminUserID"];
            }
            $this->activityLog->ActivityLog("users", $id, 'updated', $submitted_by, $description);
            return true;
        }
        return false;
    }

    public function UpdateVerificationToken(int $id, string $token, string $tokenExpires, string $lastSent): bool
    {
        $result = $this->db->RunQuery("UPDATE `users` SET `verification_token` = ?, `verification_token_expires` = ?, `last_verification_sent` = ? WHERE id = ?",
            [
                new DBParam(DBTypes::STRING, $token),
                new DBParam(DBTypes::STRING, $tokenExpires),
                new DBParam(DBTypes::STRING, $lastSent),
                new DBParam(DBTypes::INT, $id),
            ]);

        if ($result) {
            $this->activityLog->ActivityLog("users", $id, 'updated', $id, "new account verification token generated");
            return true;
        }
        return false;
    }

    public function UpdateVerification(string $email, string $token, string $expires): bool
    {
        $result = $this->db->RunQuery("UPDATE `users` SET `verification_token` = ?, `verification_token_expires` = ?, `last_verification_sent` = NOW() WHERE email = ?",
            [
                new DBParam(DBTypes::STRING, $token),
                new DBParam(DBTypes::STRING, $expires),
                new DBParam(DBTypes::STRING, $email)
            ]);

        if ($result) {
            return true;
        }
        return false;
    }

    public function UpdateResetToken(int $id, string $token, string $tokenExpires, string $lastSent): bool
    {
        $result = $this->db->RunQuery("UPDATE `users` SET `reset_token` = ?, `reset_token_expires` = ?, `last_reset_sent` = ? WHERE id = ?",
            [
                new DBParam(DBTypes::STRING, $token),
                new DBParam(DBTypes::STRING, $tokenExpires),
                new DBParam(DBTypes::STRING, $lastSent),
                new DBParam(DBTypes::INT, $id),
            ]);

        if ($result) {
            $this->activityLog->ActivityLog("users", $id, 'updated', $id, "new password reset token generated");
            return true;
        }
        return false;
    }


    public function GetUserByVerificationToken(string $token): mysqli_result
    {
        return $this->db->RunQuery("SELECT * FROM `users` WHERE verification_token = ?",
            [
                new DBParam(DBTypes::STRING, $token),
            ]);
    }

    public function GetUserByResetToken(string $token): mysqli_result
    {
        return $this->db->RunQuery("SELECT * FROM `users` WHERE reset_token = ?",
            [
                new DBParam(DBTypes::STRING, $token),
            ]);
    }

    public function DeleteUser(int $id, ?int $submittedBy, string $description = ''): bool
    {
        global $cfg;
        //TODO:: delete from everywhere
        $result = $this->db->RunQuery("DELETE FROM `users` WHERE id = ?",
            [new DBParam(DBTypes::INT, $id)]);

        if ($result) {
            $changedBy = $submittedBy ?: $cfg["adminUserID"];
            $this->activityLog->ActivityLog("users", $id, 'deleted', $changedBy, $description);
            return true;
        }
        return false;
    }

    public function UpdatePassword(int $id, string $hashedPass) {
        return $this->db->RunQuery("UPDATE users SET password = ? WHERE id = ?",
        [
            new DBParam(DBTypes::STRING, $hashedPass),
            new DBParam(DBTypes::INT, $id)
        ]);
    }

    public function ChangeResetStatus(int $id, int $resetStatus) {
        return $this->db->RunQuery("UPDATE users SET reset_used = ? WHERE id = ?",
        [
            new DBParam(DBTypes::INT, $resetStatus),
            new DBParam(DBTypes::INT, $id)
        ]);
    }
}
