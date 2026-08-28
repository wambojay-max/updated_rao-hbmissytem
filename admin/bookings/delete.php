<?php

require_once "../../auth/check_role.php";

requireRole(["admin", "warden", "staff"]);

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

$stmt = $pdo->prepare("DELETE FROM bookings WHERE id = :id");
$stmt->execute(["id" => $id]);
audit($pdo, "delete", "booking", (int) $id);

header("Location: index.php");
exit;

?>