<?php
require_once "../config/security.php";
require_once "../config/database.php";
if ($_SERVER["REQUEST_METHOD"] !== "POST") { header("Location: login.php"); exit; }
verifyCsrf();
$email = trim($_POST["email"] ?? "");
$password = $_POST["password"] ?? "";
$stmt = $pdo->prepare("SELECT id, full_name, email, password, role FROM users WHERE email = :email LIMIT 1");
$stmt->execute(["email" => $email]);
$user = $stmt->fetch();
if (!$user || !password_verify($password, $user["password"])) exit("Invalid email or password.");
if (!in_array($user["role"], ["student", "lecturer", "admin"], true)) exit("This account does not have academic access.");
$_SESSION["user_id"] = $user["id"];
$_SESSION["full_name"] = $user["full_name"];
$_SESSION["email"] = $user["email"];
$_SESSION["role"] = $user["role"];
$destination = $user["role"] === "lecturer"
	? "lecturer/results.php"
	: ($user["role"] === "admin" ? "registrar/approve_results.php" : "student/index.php");
header("Location: ../" . $destination);
exit;
?>
