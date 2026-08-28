<?php
require_once "../../auth/check_role.php";
requireRole(["admin", "warden", "staff"]);
require_once "../../config/database.php";

$type = $_GET["type"] ?? "students";
$queries = [
    "students" => ["students", "SELECT student_id, full_name, gender, email, course, year_of_study FROM students ORDER BY id DESC"],
    "rooms" => ["rooms", "SELECT room_number, room_type, capacity, floor, gender, status FROM rooms ORDER BY id DESC"],
    "bookings" => ["bookings", "SELECT student_id, room_id, booking_date, check_in_date, status FROM bookings ORDER BY id DESC"],
    "payments" => ["payments", "SELECT student_id, amount, payment_date, payment_method, reference_number, status FROM payments ORDER BY id DESC"]
];
if (!isset($queries[$type])) { http_response_code(404); exit("Unknown report."); }
[$filename, $sql] = $queries[$type];
$rows = $pdo->query($sql);
header("Content-Type: text/csv; charset=utf-8");
header("Content-Disposition: attachment; filename={$filename}-report.csv");
$output = fopen("php://output", "w");
$first = true;
while ($row = $rows->fetch(PDO::FETCH_ASSOC)) {
    if ($first) { fputcsv($output, array_keys($row)); $first = false; }
    fputcsv($output, $row);
}
fclose($output);
exit;
?>