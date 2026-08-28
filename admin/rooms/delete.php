<?php

require_once "../../auth/check_role.php";

requireRole(["admin", "warden"]);

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

$stmt = $pdo->prepare("DELETE FROM rooms WHERE id = :id");
$stmt->execute(["id" => $id]);
audit($pdo, "delete", "room", (int) $id);

header("Location: index.php");
exit;

?>