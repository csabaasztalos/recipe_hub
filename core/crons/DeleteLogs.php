<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
file_put_contents(__DIR__ . '/test.txt', "Ran at: " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
/*
$logsDir = $cfg["contentFolder"]."/logs/";
$files = glob("$logsDir*");
$maxAge = 30 * 86400;
$now = time();

if (! is_dir($logsDir)) {
    return;
}

foreach ($files as $file) {
    if (is_file($file) && ($now - filemtime($file)) > $maxAge) {
        if (unlink($file)) {
            Logger::Log("Deleted old log: $file", logLvl::Info);
        }
        Logger::Log("Could not delete old log: $file", logLvl::Info);
    }
}
*/