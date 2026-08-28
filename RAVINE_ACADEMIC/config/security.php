<?php
if (session_status() === PHP_SESSION_NONE) {
    session_name("ravine_academic_session");
    session_set_cookie_params([
        "secure" => true,
        "httponly" => true,
        "samesite" => "Lax"
    ]);
    session_start();
}
function csrfToken(): string
{
    if (empty($_SESSION["csrf_token"])) $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
    return $_SESSION["csrf_token"];
}
function csrfField(): string
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrfToken(), ENT_QUOTES, "UTF-8") . '">';
}
function verifyCsrf(): void
{
    if (!isset($_SESSION["csrf_token"], $_POST["csrf_token"]) || !hash_equals($_SESSION["csrf_token"], $_POST["csrf_token"])) exit("Invalid security token.");
}
?>
