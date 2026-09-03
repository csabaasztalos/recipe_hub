<?php

final class Logout
{
    public static function DestroySession(): void
    {
        global $cfg;
        PermissionHandler::DestroySession();
        header("Location: {$cfg['homePage']}");
        exit();
    }
}