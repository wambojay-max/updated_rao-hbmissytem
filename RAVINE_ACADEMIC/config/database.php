<?php
$host = getenv("DB_HOST") ?: "127.0.0.1";
$dbname = getenv("ACADEMIC_DB_NAME") ?: "ravine_academic";
$username = getenv("DB_USER") ?: "root";
$password = getenv("DB_PASSWORD") ?: "";
$charset = getenv("DB_CHARSET") ?: "utf8mb4";
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=$charset", $username, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
} catch (PDOException $error) {
    error_log($error->getMessage());
    exit("Academic system database connection failed.");
}
?>
