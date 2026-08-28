<?php

require_once "../../auth/check_role.php";

requireRole(["admin"]);

require_once "../../config/database.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

$id = $_POST["id"] ?? null;

if (!$id) {
    header("Location: index.php");
    exit;
}

/*
 * Prevent the currently logged-in administrator
 * from accidentally deleting their own account.
 */
if ((int) $id === (int) $_SESSION["user_id"]) {
    die("You cannot delete your own account while logged in.");
}

$stmt = $pdo->prepare(
    "DELETE FROM users WHERE id = :id"
);

$stmt->execute([
    "id" => $id
]);
audit($pdo, "delete", "user", (int) $id);

header("Location: index.php");
exit;

?>