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

try {

    $stmt = $pdo->prepare(
        "DELETE FROM students WHERE id = :id"
    );

    $stmt->execute([
        "id" => $id
    ]);
    audit($pdo, "delete", "student", (int) $id);

} catch (PDOException $e) {

    if ($e->getCode() == 23000) {

        die(
            "Cannot delete this student because they have existing "
            . "bookings, allocations or payments."
        );

    } else {

        die("Failed to delete student.");
    }
}

header("Location: index.php");
exit;

?>