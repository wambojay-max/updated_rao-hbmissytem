<?php
require_once "../config/security.php";
require_once "../config/database.php";
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "student") { header("Location: ../auth/login.php"); exit; }
$stmt = $pdo->prepare("SELECT * FROM students WHERE email = :email LIMIT 1");
$stmt->execute(["email" => $_SESSION["email"]]);
$student = $stmt->fetch();
if (!$student) exit("Student profile not found.");
$e = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
?>
