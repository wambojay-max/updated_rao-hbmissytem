<?php

require_once "../auth/check_role.php";

requireRole(["student"]);

require_once "../config/database.php";

$studentStmt = $pdo->prepare(
    "SELECT * FROM students WHERE email = :email LIMIT 1"
);
$studentStmt->execute(["email" => $_SESSION["email"]]);
$student = $studentStmt->fetch();

if (!$student) {
    die("Your student profile is not linked to this account. Please contact hostel administration.");
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";

    if ($action === "cancel_booking") {
        $cancelStmt = $pdo->prepare(
            "UPDATE bookings SET status = 'Cancelled'
             WHERE id = :id AND student_id = :student_id AND status = 'Pending'"
        );
        $cancelStmt->execute([
            "id" => (int) ($_POST["booking_id"] ?? 0),
            "student_id" => $student["id"]
        ]);
        $message = $cancelStmt->rowCount() ? "Booking cancelled." : "Only pending bookings can be cancelled.";
    }

    if ($action === "book_room") {
        $roomId = (int) ($_POST["room_id"] ?? 0);
        $checkInDate = $_POST["check_in_date"] ?? "";
        $roomStmt = $pdo->prepare(
            "SELECT id, gender, status, capacity FROM rooms WHERE id = :id LIMIT 1"
        );
        $roomStmt->execute(["id" => $roomId]);
        $room = $roomStmt->fetch();

        $activeStmt = $pdo->prepare(
            "SELECT COUNT(*) FROM bookings
             WHERE student_id = :student_id AND status IN ('Pending', 'Confirmed')"
        );
        $activeStmt->execute(["student_id" => $student["id"]]);

        if (!$room || $room["status"] !== "Available") {
            $message = "That room is no longer available.";
        } elseif ($room["gender"] !== $student["gender"]) {
            $message = "Please choose a room assigned to your gender.";
        } elseif ($checkInDate === "" || $checkInDate < date("Y-m-d")) {
            $message = "Choose a valid future check-in date.";
        } elseif ((int) $activeStmt->fetchColumn() > 0) {
            $message = "You already have an active or pending booking.";
        } else {
            $bookStmt = $pdo->prepare(
                "INSERT INTO bookings (student_id, room_id, booking_date, check_in_date, status)
                 VALUES (:student_id, :room_id, :booking_date, :check_in_date, 'Pending')"
            );
            $bookStmt->execute([
                "student_id" => $student["id"],
                "room_id" => $roomId,
                "booking_date" => date("Y-m-d"),
                "check_in_date" => $checkInDate
            ]);
            $message = "Booking request submitted for review.";
        }
    }
}

$bookingStmt = $pdo->prepare(
    "SELECT b.*, r.room_number, r.room_type
     FROM bookings b INNER JOIN rooms r ON r.id = b.room_id
     WHERE b.student_id = :student_id ORDER BY b.id DESC"
);
$bookingStmt->execute(["student_id" => $student["id"]]);
$bookings = $bookingStmt->fetchAll();

$allocationStmt = $pdo->prepare(
    "SELECT a.*, r.room_number, r.room_type
     FROM allocations a INNER JOIN rooms r ON r.id = a.room_id
     WHERE a.student_id = :student_id AND a.status = 'Active' LIMIT 1"
);
$allocationStmt->execute(["student_id" => $student["id"]]);
$allocation = $allocationStmt->fetch();

$paymentStmt = $pdo->prepare(
    "SELECT * FROM payments WHERE student_id = :student_id ORDER BY payment_date DESC, id DESC"
);
$paymentStmt->execute(["student_id" => $student["id"]]);
$payments = $paymentStmt->fetchAll();

$roomStmt = $pdo->prepare(
    "SELECT r.*, COALESCE((SELECT COUNT(*) FROM allocations a WHERE a.room_id = r.id AND a.status = 'Active'), 0) AS occupants
     FROM rooms r WHERE r.status = 'Available' AND r.gender = :gender ORDER BY r.room_number"
);
$roomStmt->execute(["gender" => $student["gender"]]);
$rooms = $roomStmt->fetchAll();

$e = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Portal | RAO HBMIS</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="portal-body">
    <header class="portal-topbar">
        <a class="portal-brand" href="index.php"><span>RAO</span> Student Portal</a>
        <div class="portal-user"><span><?php echo $e($student["full_name"]); ?></span><a href="../auth/logout.php">Log out</a></div>
    </header>

    <main class="portal-main">
        <section class="portal-intro">
            <div><p class="eyebrow">STUDENT HOME</p><h1>Good day, <?php echo $e(explode(" ", $student["full_name"])[0]); ?>.</h1><p>Keep your room request, residence details, and payments in one place.</p></div>
            <div class="student-code">Student ID <strong><?php echo $e($student["student_id"]); ?></strong></div>
        </section>

        <?php if ($message !== ""): ?><div class="portal-notice"><?php echo $e($message); ?></div><?php endif; ?>

        <section class="portal-stats">
            <div><span>Current room</span><strong><?php echo $allocation ? $e($allocation["room_number"]) : "Not allocated"; ?></strong></div>
            <div><span>Booking requests</span><strong><?php echo count($bookings); ?></strong></div>
            <div><span>Payments recorded</span><strong><?php echo count($payments); ?></strong></div>
        </section>

        <div class="portal-grid">
            <section class="portal-panel portal-panel-wide"><div class="panel-heading"><div><p class="eyebrow">RESIDENCE</p><h2><?php echo $allocation ? "Your active allocation" : "Find your room"; ?></h2></div></div>
                <?php if ($allocation): ?>
                    <div class="allocation-callout"><strong>Room <?php echo $e($allocation["room_number"]); ?></strong><span><?php echo $e($allocation["room_type"]); ?> room · Check in <?php echo $e($allocation["check_in_date"]); ?></span></div>
                <?php else: ?>
                    <form method="POST" class="room-request"><?php echo csrfField(); ?><input type="hidden" name="action" value="book_room"><label>Available room<select name="room_id" required><option value="">Choose a room</option><?php foreach ($rooms as $room): ?><option value="<?php echo $e($room["id"]); ?>">Room <?php echo $e($room["room_number"]); ?> · <?php echo $e($room["room_type"]); ?> · <?php echo $e($room["capacity"] - $room["occupants"]); ?> spaces</option><?php endforeach; ?></select></label><label>Check-in date<input type="date" name="check_in_date" min="<?php echo date("Y-m-d", strtotime("+1 day")); ?>" required></label><button type="submit">Request room</button></form>
                <?php endif; ?>
            </section>

            <section class="portal-panel"><div class="panel-heading"><div><p class="eyebrow">PROFILE</p><h2>Your details</h2></div></div><dl class="profile-list"><dt>Email</dt><dd><?php echo $e($student["email"]); ?></dd><dt>Course</dt><dd><?php echo $e($student["course"]); ?></dd><dt>Year</dt><dd><?php echo $e($student["year_of_study"]); ?></dd><dt>Phone</dt><dd><?php echo $e($student["phone"]); ?></dd></dl></section>

            <section class="portal-panel portal-panel-wide"><div class="panel-heading"><div><p class="eyebrow">ACTIVITY</p><h2>Booking requests</h2></div></div><div class="portal-table-wrap"><table class="portal-table"><thead><tr><th>Room</th><th>Check-in</th><th>Status</th><th></th></tr></thead><tbody><?php if (!$bookings): ?><tr><td colspan="4" class="empty-state">No booking requests yet.</td></tr><?php else: foreach ($bookings as $booking): ?><tr><td>Room <?php echo $e($booking["room_number"]); ?><small><?php echo $e($booking["room_type"]); ?></small></td><td><?php echo $e($booking["check_in_date"]); ?></td><td><span class="status status-<?php echo strtolower($e($booking["status"])); ?>"><?php echo $e($booking["status"]); ?></span></td><td><?php if ($booking["status"] === "Pending"): ?><form method="POST"><input type="hidden" name="action" value="cancel_booking"><input type="hidden" name="booking_id" value="<?php echo $e($booking["id"]); ?>"><button class="text-button" type="submit">Cancel</button></form><?php endif; ?></td></tr><?php endforeach; endif; ?></tbody></table></div></section>

            <section class="portal-panel"><div class="panel-heading"><div><p class="eyebrow">FINANCE</p><h2>Payments</h2></div></div><div class="payment-list"><?php if (!$payments): ?><p class="empty-state">No payments recorded.</p><?php else: foreach (array_slice($payments, 0, 4) as $payment): ?><div><span><?php echo $e($payment["payment_date"]); ?><small><?php echo $e($payment["payment_method"]); ?></small></span><strong>KES <?php echo number_format((float) $payment["amount"], 2); ?></strong></div><?php endforeach; endif; ?></div></section>
        </div>
    </main>
</body>
<script>
document.querySelectorAll('form[method="POST"]').forEach((form) => {
    if (!form.querySelector('input[name="csrf_token"]')) {
        const token = document.createElement('input');
        token.type = 'hidden';
        token.name = 'csrf_token';
        token.value = <?php echo json_encode(csrfToken()); ?>;
        form.appendChild(token);
    }
});
</script>
</html>
