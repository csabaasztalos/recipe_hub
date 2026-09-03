<?php

final class PermissionHandler
{

    public static function StartSession(array $sessionData, int $permission): void
    {
        global $cfg;
        $_SESSION[$cfg['permissionSessionKey']]['data'] = $sessionData;
        $_SESSION[$cfg['permissionSessionKey']]['permission'] = $permission;
    }


    public static function DestroySession(): void
    {
        global $cfg;
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        unset($_SESSION[$cfg['permissionSessionKey']]);
        session_destroy();

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
    }


    public static function CheckPermission(int $checkTo): bool
    {
        global $cfg;

        return isset($_SESSION[$cfg['permissionSessionKey']]) &&
            $_SESSION[$cfg['permissionSessionKey']]['permission'] >=
            $checkTo || $checkTo === 0;
    }
}