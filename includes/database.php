<?php

require_once __DIR__ . "/functions.php";

// Make mysqli behave the same on every PHP version (functions return false instead of throwing).
mysqli_report(MYSQLI_REPORT_OFF);

$configFile = __DIR__ . "/config.php";

if(!is_file($configFile)){

    http_response_code(500);

    die("Configuration file is missing. Copy includes/config.example.php to includes/config.php and fill in your database settings.");

}

$dbConfig = require $configFile;

$conn = @mysqli_connect(
    $dbConfig['host'],
    $dbConfig['username'],
    $dbConfig['password'],
    $dbConfig['database']
);

// Optional connection character set (see config.example.php). Not set = server default.
$dbCharset = isset($dbConfig['charset']) ? (string)$dbConfig['charset'] : "";

// Do not keep the credentials in memory/variables after connecting.
unset($dbConfig, $configFile);

if(!$conn){

    // The technical reason goes to the server log only, never to the visitor.
    error_log("CareerCraft: database connection failed: " . mysqli_connect_error());

    http_response_code(500);

    die("We could not connect to the database. Please check includes/config.php and make sure MySQL is running.");

}

if($dbCharset !== "" && preg_match('/^[A-Za-z0-9_]+$/', $dbCharset)){

    if(!mysqli_set_charset($conn, $dbCharset)){

        error_log("CareerCraft: could not set the database character set to " . $dbCharset);

    }

}

unset($dbCharset);

?>