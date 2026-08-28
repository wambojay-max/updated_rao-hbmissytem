<?php

require_once __DIR__ . "/security.php";

$host = envValue("DB_HOST", "localhost");
$dbname = envValue("DB_NAME", "rao_hbmis");
$username = envValue("DB_USER", "root");
$password = envValue("DB_PASSWORD", "");
$charset = envValue("DB_CHARSET", "utf8mb4");

$host = "localhost";
$dbname = "rao_hbmis";
$username = "root";
$password = "";

try {
    $pdo = new PDO(
            "mysql:host=$host;dbname=$dbname;charset=$charset",
        $username,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
    );

} catch (PDOException $e) {
    error_log($e->getMessage());
    die("Database connection failed.");
}
?>