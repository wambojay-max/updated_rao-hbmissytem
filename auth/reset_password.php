<?php
session_start();
require_once "../config/security.php";
require_once "../config/database.php";
$token = $_GET["token"] ?? $_POST["token"] ?? "";
$stmt = $pdo->prepare("SELECT id FROM users WHERE reset_token_hash = :token AND reset_expires_at > NOW() LIMIT 1");
$stmt->execute(["token" => hash("sha256", $token)]);
$user = $stmt->fetch();
$message = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    verifyCsrf();
    $password = $_POST["password"] ?? "";
    if (!$user || strlen($password) < 6) {
        $message = "The reset link is invalid or the password is too short.";
    } else {
        $update = $pdo->prepare("UPDATE users SET password = :password, reset_token_hash = NULL, reset_expires_at = NULL, failed_login_attempts = 0, locked_until = NULL WHERE id = :id");
        $update->execute(["password" => password_hash($password, PASSWORD_DEFAULT), "id" => $user["id"]]);
        header("Location: login.php"); exit;
    }
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Choose password | RAO HBMIS</title></head><body><h1>Choose a new password</h1><?php if ($message): ?><p><?php echo htmlspecialchars($message, ENT_QUOTES, "UTF-8"); ?></p><?php endif; ?><form method="post"><?php echo csrfField(); ?><input type="hidden" name="token" value="<?php echo htmlspecialchars($token, ENT_QUOTES, "UTF-8"); ?>"><label>New password <input type="password" name="password" minlength="6" required></label><button type="submit">Update password</button></form></body></html>
