<?php
session_start();
require_once "../config/security.php";
require_once "../config/database.php";
$message = "If the email exists, a reset link has been generated. In production, deliver it through your mail provider.";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    verifyCsrf();
    $email = trim($_POST["email"] ?? "");
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(["email" => $email]);
        if ($user = $stmt->fetch()) {
            $token = bin2hex(random_bytes(32));
            $update = $pdo->prepare("UPDATE users SET reset_token_hash = :token, reset_expires_at = DATE_ADD(NOW(), INTERVAL 30 MINUTE) WHERE id = :id");
            $update->execute(["token" => hash("sha256", $token), "id" => $user["id"]]);
            $message .= " Reset URL: reset_password.php?token=" . urlencode($token);
        }
    }
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Reset password | RAO HBMIS</title></head><body><h1>Reset your password</h1><p><?php echo htmlspecialchars($message, ENT_QUOTES, "UTF-8"); ?></p><form method="post"><?php echo csrfField(); ?><label>Email <input type="email" name="email" required></label><button type="submit">Send reset link</button></form><p><a href="login.php">Back to login</a></p></body></html>
