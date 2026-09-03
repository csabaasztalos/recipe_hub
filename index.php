<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('allow_url_fopen', 1);
error_reporting(E_ALL);

session_start();
require_once 'config.php';
require_once 'autoload.php';

try {
    $dbHandler = new DBHandler(
        $cfg["host"],
        $cfg["user"],
        $cfg["pass"],
        $cfg["dbname"],
        $cfg["port"]
    );

    Container::Set('db', $dbHandler);
    Container::Set('activityLog', new ActivityLogModel());
    Container::Set('model', new Model());
    Controller::Route();
} catch (Exception $e) {
    echo "Fatal Error: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}