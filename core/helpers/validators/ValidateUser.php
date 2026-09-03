<?php
final class ValidateUser
{
    public static function Validate(?int $id)
    {
        try {
            if ($id === null) {
                return;
            } else {
                $isValid = UserService::CheckUserId($id);
                if (! $isValid) {
                    Logout::DestroySession();
                }
            }
        } catch (\Throwable $th) {
            throw new DBException("Adatbazis hiba.");
        }

    }
}
