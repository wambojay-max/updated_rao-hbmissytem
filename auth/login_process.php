<?php

require_once "../config/security.php";
require_once "../config/database.php";
verifyCsrf();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login.php");
    exit;
}

$email = trim($_POST["email"] ?? "");
$password = $_POST["password"] ?? "";

if ($email === "" || $password === "") {
    die("Please enter your email and password.");
}

$sql = "SELECT id, full_name, email, password, role, failed_login_attempts, locked_until
        FROM users
        WHERE email = :email
        LIMIT 1";

$stmt = $pdo->prepare($sql);
$stmt->execute(["email" => $email]);

$user = $stmt->fetch();

if (!$user) {
    die("Invalid email or password.");
}

if ($user["locked_until"] && strtotime($user["locked_until"]) > time()) {
    die("This account is temporarily locked. Please try again later.");
}

if (!password_verify($password, $user["password"])) {
    $attempts = (int) $user["failed_login_attempts"] + 1;
    $lockedUntil = $attempts >= 5 ? date("Y-m-d H:i:s", time() + 900) : null;
    $lockStmt = $pdo->prepare("UPDATE users SET failed_login_attempts = :attempts, locked_until = :locked_until WHERE id = :id");
    $lockStmt->execute(["attempts" => $lockedUntil ? 0 : $attempts, "locked_until" => $lockedUntil, "id" => $user["id"]]);
    die("Invalid email or password.");
}

$pdo->prepare("UPDATE users SET failed_login_attempts = 0, locked_until = NULL WHERE id = :id")
    ->execute(["id" => $user["id"]]);

$_SESSION["user_id"] = $user["id"];
$_SESSION["full_name"] = $user["full_name"];
$_SESSION["email"] = $user["email"];
$_SESSION["role"] = $user["role"];

if ($user["role"] === "student") {
    header("Location: ../student/index.php");
    exit;
}

header("Location: ../dashboard.php");
exit;