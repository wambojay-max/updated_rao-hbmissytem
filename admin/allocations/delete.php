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

/*
 * Get the room before deleting the allocation.
 */
$stmt = $pdo->prepare(
    "SELECT room_id FROM allocations WHERE id = :id"
);

$stmt->execute([
    "id" => $id
]);

$allocation = $stmt->fetch();

if (!$allocation) {
    header("Location: index.php");
    exit;
}

/*
 * Delete allocation.
 */
$deleteStmt = $pdo->prepare(
    "DELETE FROM allocations WHERE id = :id"
);

$deleteStmt->execute([
    "id" => $id
]);
audit($pdo, "delete", "allocation", (int) $id);

/*
 * Make the room available again.
 */
$roomStmt = $pdo->prepare(
    "UPDATE rooms
     SET status = 'Available'
     WHERE id = :room_id"
);

$roomStmt->execute([
    "room_id" => $allocation["room_id"]
]);

header("Location: index.php");
exit;

?>