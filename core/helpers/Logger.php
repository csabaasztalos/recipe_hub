<?php

final class Logger
{
    public static function Log(string $message, logLvl $level): void
    {
        global $cfg;

        $filename = "logs_".date("Y-m-d").".log";
        $log = fopen($cfg["contentFolder"]."/logs/".$filename, "a");
        fputs($log, "[".date("H:i:s")."][".$level->name."] - $message\n");
        fclose($log);
    }

    private static function DeleteLogs(): void
    {
        global $cfg;
        $logsDir = $cfg["contentFolder"]."/logs/";
        $files = array_diff(scandir($logsDir), ['.', '..']);

    }
}