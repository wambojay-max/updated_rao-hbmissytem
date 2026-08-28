<?php
require_once "../auth/check_role.php";
requireRole(["student"]);
require_once "../config/database.php";

$studentStmt = $pdo->prepare("SELECT * FROM students WHERE email = :email LIMIT 1");
$studentStmt->execute(["email" => $_SESSION["email"]]);
$student = $studentStmt->fetch();
if (!$student) {
    exit("Your student profile is not linked to this account.");
}
$e = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
?>
