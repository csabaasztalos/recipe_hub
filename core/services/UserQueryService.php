<?php

final class UserQueryService {
    public static function GetFilteredUsers(?string $keyword, ?string $dateFrom, ?string $dateTo): array {
        $model = new UsersModel();
        $result = $model->GetFilteredUsers($keyword, $dateFrom, $dateTo);

        if (! $result) {
            throw new UserNotFoundException("A felhasználók nem találhatóak.");
        }

        $users = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $users[] = $row;
            }
        }

        return $users;
    }

    public static function GetUserByEmail(string $email): array {
        $model = new UsersModel();
        $result = $model->GetUserByEmail($email);
        $user = $result->fetch_assoc();

        if (! $user) {
            throw new UserNotFoundException("A felhasználó nem található.");
        }

        return $user;
    }

    public static function GetUserById(int $id): array {
        $model = new UsersModel();
        $result = $model->GetUser($id);
        $user = $result->fetch_assoc();

        if (! $user) {
            throw new UserNotFoundException("A felhasználó nem található.");
        }

        return $user;
    }
}