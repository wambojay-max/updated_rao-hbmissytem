<?php

require_once "../../auth/check_role.php";

requireRole(["admin", "warden", "staff"]);

require_once "../../config/database.php";

$id = $_GET["id"] ?? null;

if (!$id) {
    header("Location: index.php");
    exit;
}

/* Get booking */
$stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = :id");
$stmt->execute(["id" => $id]);

$booking = $stmt->fetch();

if (!$booking) {
    die("Booking not found.");
}

/* Get students */
$studentStmt = $pdo->query(
    "SELECT id, student_id, full_name
     FROM students
     ORDER BY full_name ASC"
);

$students = $studentStmt->fetchAll();

/* Get rooms */
$roomStmt = $pdo->query(
    "SELECT id, room_number, room_type, capacity, gender, status
     FROM rooms
     ORDER BY room_number ASC"
);

$rooms = $roomStmt->fetchAll();

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $student_id = (int) $_POST["student_id"];
    $room_id = (int) $_POST["room_id"];
    $booking_date = $_POST["booking_date"];
    $check_in_date = $_POST["check_in_date"];
    $status = trim($_POST["status"]);

 if (
    $student_id <= 0 ||
    $room_id <= 0 ||
    $booking_date === "" ||
    $check_in_date === "" ||
    $status === ""
) {

    $message = "Please fill in all fields.";

} elseif (!in_array($status, ["Pending", "Confirmed", "Cancelled"], true)) {

    $message = "Invalid booking status.";

} elseif ($check_in_date < $booking_date) {

    $message = "Check-in date cannot be before booking date.";

} else {

    /* Verify student exists */
    $studentCheck = $pdo->prepare(
        "SELECT id FROM students WHERE id = :id"
    );

    $studentCheck->execute([
        "id" => $student_id
    ]);

    if (!$studentCheck->fetch()) {

        $message = "Selected student does not exist.";

    } else {

        /* Verify room exists */
        $roomCheck = $pdo->prepare(
            "SELECT id FROM rooms WHERE id = :id"
        );

        $roomCheck->execute([
            "id" => $room_id
        ]);

        if (!$roomCheck->fetch()) {

            $message = "Selected room does not exist.";

        } else {
        }
        }

        $sql = "UPDATE bookings SET
                student_id = :student_id,
                room_id = :room_id,
                booking_date = :booking_date,
                check_in_date = :check_in_date,
                status = :status
                WHERE id = :id";

        $stmt = $pdo->prepare($sql);

        try {

            $stmt->execute([
                "student_id" => $student_id,
                "room_id" => $room_id,
                "booking_date" => $booking_date,
                "check_in_date" => $check_in_date,
                "status" => $status,
                "id" => $id
            ]);

            header("Location: index.php");
            exit;

        } catch (PDOException $e) {

            $message = "Failed to update booking.";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Edit Booking - RAO Hostel Booking and Management Information System</title>

</head>

<body>

    <h1>RAO Hostel Booking and Management Information System</h1>

    <h2>Edit Booking</h2>

    <?php if ($message !== ""): ?>

        <p>
            <?php echo htmlspecialchars($message); ?>
        </p>

    <?php endif; ?>


    <form method="POST">
        <?php echo csrfField(); ?>

        <label>Student</label>
        <br>

        <select name="student_id" required>

            <?php foreach ($students as $student): ?>

                <option
                    value="<?php echo $student["id"]; ?>"
                    <?php echo $booking["student_id"] == $student["id"] ? "selected" : ""; ?>
                >

                    <?php
                    echo htmlspecialchars(
                        $student["student_id"] .
                        " - " .
                        $student["full_name"]
                    );
                    ?>

                </option>

            <?php endforeach; ?>

        </select>


        <br><br>


        <label>Room</label>
        <br>

        <select name="room_id" required>

            <?php foreach ($rooms as $room): ?>

                <option
                    value="<?php echo $room["id"]; ?>"
                    <?php echo $booking["room_id"] == $room["id"] ? "selected" : ""; ?>
                >

                    <?php
                    echo htmlspecialchars(
                        $room["room_number"] .
                        " - " .
                        $room["room_type"] .
                        " - " .
                        $room["gender"]
                    );
                    ?>

                </option>

            <?php endforeach; ?>

        </select>


        <br><br>


        <label>Booking Date</label>
        <br>

        <input
            type="date"
            name="booking_date"
            value="<?php echo htmlspecialchars($booking["booking_date"]); ?>"
            required
        >


        <br><br>


        <label>Check-in Date</label>
        <br>

        <input
            type="date"
            name="check_in_date"
            value="<?php echo htmlspecialchars($booking["check_in_date"]); ?>"
            required
        >


        <br><br>


        <label>Status</label>
        <br>

        <select name="status" required>

            <option
                value="Pending"
                <?php echo $booking["status"] === "Pending" ? "selected" : ""; ?>
            >
                Pending
            </option>

            <option
                value="Confirmed"
                <?php echo $booking["status"] === "Confirmed" ? "selected" : ""; ?>
            >
                Confirmed
            </option>

            <option
                value="Cancelled"
                <?php echo $booking["status"] === "Cancelled" ? "selected" : ""; ?>
            >
                Cancelled
            </option>

        </select>


        <br><br>


        <button type="submit">
            Update Booking
        </button>

    </form>


    <br>

    <a href="index.php">
        ← Back to Bookings
    </a>

</body>

</html>