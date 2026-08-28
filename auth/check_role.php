<?php

require_once __DIR__ . "/../config/security.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    verifyCsrf();
}

/*
|--------------------------------------------------------------------------
| Check if user is logged in
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["user_id"])) {

    header("Location: login.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Check user role
|--------------------------------------------------------------------------
*/

function requireRole(array $allowedRoles): void
{
    if (!isset($_SESSION["role"])) {

        header("Location: login.php");
        exit;
    }

    $currentRole = $_SESSION["role"];

    if (!in_array($currentRole, $allowedRoles, true)) {

        http_response_code(403);

        die("
            <h1>Access Denied</h1>

            <p>
                You do not have permission to access this page.
            </p>

            <a href='../dashboard.php'>
                Return to Dashboard
            </a>
        ");
    }
}

?>