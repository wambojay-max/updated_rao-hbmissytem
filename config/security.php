<?php

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        "secure" => true,
        "httponly" => true,
        "samesite" => "Lax"
    ]);
    session_start();
}

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrfField(): string
{
    return '<input type="hidden" name="csrf_token" value="'
        . htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') . '">';
}

function verifyCsrf(): void
{
    $submitted = $_POST['csrf_token'] ?? '';
    $stored = $_SESSION['csrf_token'] ?? '';

    if (!$stored || !$submitted || !hash_equals($stored, $submitted)) {
        http_response_code(419);
        exit('Your session security token is invalid or expired. Please go back and try again.');
    }
}

function audit(PDO $pdo, string $action, string $entity, ?int $entityId = null, array $details = []): void
{
    $stmt = $pdo->prepare(
        'INSERT INTO audit_logs (user_id, action, entity, entity_id, details, ip_address)
         VALUES (:user_id, :action, :entity, :entity_id, :details, :ip_address)'
    );
    $stmt->execute([
        'user_id' => $_SESSION['user_id'] ?? null,
        'action' => $action,
        'entity' => $entity,
        'entity_id' => $entityId,
        'details' => json_encode($details, JSON_UNESCAPED_SLASHES),
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null
    ]);
}

function envValue(string $key, ?string $default = null): ?string
{
    $value = getenv($key);
    return $value === false || $value === '' ? $default : $value;
}

?>
