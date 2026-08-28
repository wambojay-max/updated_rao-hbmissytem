<?php
require_once "../../auth/check_role.php";
requireRole(["admin", "warden"]);
require_once "../../config/database.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = (int) ($_POST["id"] ?? 0);
    $status = $_POST["status"] ?? "";
    if (!in_array($status, ["Approved", "Rejected"], true)) {
        exit("Invalid review status.");
    }
    $suggestionStmt = $pdo->prepare("SELECT * FROM ai_allocation_suggestions WHERE id = :id AND status = 'Pending'");
    $suggestionStmt->execute(["id" => $id]);
    $suggestion = $suggestionStmt->fetch();
    if (!$suggestion) {
        exit("Suggestion not found or already reviewed.");
    }
    $pdo->beginTransaction();
    try {
        if ($status === "Approved") {
            $roomStmt = $pdo->prepare("SELECT capacity, status, (SELECT COUNT(*) FROM allocations WHERE room_id = :room_id AND status = 'Active') AS occupants FROM rooms WHERE id = :room_id FOR UPDATE");
            $roomStmt->execute(["room_id" => $suggestion["room_id"]]);
            $room = $roomStmt->fetch();
            if (!$room || $room["status"] !== "Available" || $room["occupants"] >= $room["capacity"]) {
                throw new RuntimeException("The recommended room is no longer available.");
            }
            $insert = $pdo->prepare("INSERT INTO allocations (student_id, room_id, allocation_date, check_in_date, check_out_date, status) VALUES (:student_id, :room_id, CURDATE(), CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), 'Active')");
            $insert->execute(["student_id" => $suggestion["student_id"], "room_id" => $suggestion["room_id"]]);
            $pdo->prepare("UPDATE rooms SET status = 'Occupied' WHERE id = :id")->execute(["id" => $suggestion["room_id"]]);
        }
        $review = $pdo->prepare("UPDATE ai_allocation_suggestions SET status = :status, reviewed_by = :reviewed_by, reviewed_at = NOW() WHERE id = :id");
        $review->execute(["status" => $status, "reviewed_by" => $_SESSION["user_id"], "id" => $id]);
        audit($pdo, "ai_suggestion_" . strtolower($status), "ai_allocation_suggestions", $id);
        $pdo->commit();
    } catch (Throwable $error) {
        $pdo->rollBack();
        exit(htmlspecialchars($error->getMessage(), ENT_QUOTES, "UTF-8"));
    }
    header("Location: ai_approvals.php"); exit;
}

$suggestions = $pdo->query("SELECT a.*, s.student_id AS student_number, s.full_name, r.room_number FROM ai_allocation_suggestions a INNER JOIN students s ON s.id = a.student_id INNER JOIN rooms r ON r.id = a.room_id ORDER BY a.id DESC")->fetchAll();
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>AI allocation approvals</title></head><body><h1>AI allocation approvals</h1><p><a href="../../dashboard.php">Dashboard</a></p><table border="1" cellpadding="8"><tr><th>Student</th><th>Room</th><th>Reason</th><th>Status</th><th>Review</th></tr><?php foreach ($suggestions as $suggestion): ?><tr><td><?php echo htmlspecialchars($suggestion["student_number"] . " - " . $suggestion["full_name"], ENT_QUOTES, "UTF-8"); ?></td><td><?php echo htmlspecialchars($suggestion["room_number"], ENT_QUOTES, "UTF-8"); ?></td><td><?php echo htmlspecialchars($suggestion["reason"], ENT_QUOTES, "UTF-8"); ?></td><td><?php echo htmlspecialchars($suggestion["status"], ENT_QUOTES, "UTF-8"); ?></td><td><?php if ($suggestion["status"] === "Pending"): ?><form method="POST" style="display:inline"><?php echo csrfField(); ?><input type="hidden" name="id" value="<?php echo (int) $suggestion["id"]; ?>"><button name="status" value="Approved">Approve</button><button name="status" value="Rejected">Reject</button></form><?php endif; ?></td></tr><?php endforeach; ?></table></body></html>
