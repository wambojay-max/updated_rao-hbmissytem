```php
<?php

require_once "../../auth/check_role.php";

requireRole(["admin", "warden", "staff"]);

require_once "../../config/database.php";
require_once "../../config/notifications.php";

$message = "";

/* Get students */
$studentStmt = $pdo->query(
    "SELECT id, student_id, full_name
     FROM students
     ORDER BY full_name ASC"
);

$students = $studentStmt->fetchAll();

/* Get available rooms */
$roomStmt = $pdo->query(
    "SELECT id, room_number, room_type, capacity, gender
     FROM rooms
     WHERE status = 'Available'
     ORDER BY room_number ASC"
);

$rooms = $roomStmt->fetchAll();


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

        /* Get student gender */
   $roomCheck = $pdo->prepare(
    "SELECT id, gender, status, capacity
     FROM rooms
     WHERE id = :id"
);

$roomCheck->execute([
    "id" => $room_id
]);

$room = $roomCheck->fetch();

        if (!$student) {

            $message = "Selected student does not exist.";

        } else {

            /* Get room details */
            $roomCheck = $pdo->prepare(
                "SELECT id, gender, status
                 FROM rooms
                 WHERE id = :id"
            );

            $roomCheck->execute([
                "id" => $room_id
            ]);

            $room = $roomCheck->fetch();

            if (!$room) {

                $message = "Selected room does not exist.";

            } elseif ($room["status"] !== "Available") {

                $message = "Selected room is not available.";

           } elseif ($student["gender"] !== $room["gender"]) {

    $message = "Student gender does not match room gender.";

} else {

    /* Check room capacity */
    $occupantCheck = $pdo->prepare(
        "SELECT COUNT(*) 
         FROM allocations
         WHERE room_id = :room_id
         AND status = 'Active'"
    );

    $occupantCheck->execute([
        "room_id" => $room_id
    ]);

    $currentOccupants = (int) $occupantCheck->fetchColumn();

    if ($currentOccupants >= (int) $room["capacity"]) {

        $message = "Selected room is already full.";

    } else {}

                /* Create booking */
                $sql = "INSERT INTO bookings
                        (
                            student_id,
                            room_id,
                            booking_date,
                            check_in_date,
                            status
                        )
                        VALUES
                        (
                            :student_id,
                            :room_id,
                            :booking_date,
                            :check_in_date,
                            :status
                        )";

                $stmt = $pdo->prepare($sql);

                try {

                    $stmt->execute([
                        "student_id" => $student_id,
                        "room_id" => $room_id,
                        "booking_date" => $booking_date,
                        "check_in_date" => $check_in_date,
                        "status" => $status
                    ]);

                    $recipientStmt = $pdo->prepare("SELECT email FROM students WHERE id = :id");
                    $recipientStmt->execute(["id" => $student_id]);
                    $recipient = $recipientStmt->fetchColumn();
                    if ($recipient) {
                        queueBookingNotification($pdo, (int) $pdo->lastInsertId(), $recipient, "Your RAO hostel booking request has been received.");
                    }

                    header("Location: index.php");
                    exit;

                } catch (PDOException $e) {

                    $message = "Failed to create booking.";
                }
            }
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

    <title>Create Booking - RAO Hostel Booking and Management Information System</title>

</head>

<body>

    <h1>RAO Hostel Booking and Management Information System</h1>

    <h2>Create Booking</h2>

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

            <option value="">
                Select Student
            </option>

            <?php foreach ($students as $student): ?>

                <option value="<?php echo $student["id"]; ?>">

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

            <option value="">
                Select Available Room
            </option>

            <?php foreach ($rooms as $room): ?>

                <option value="<?php echo $room["id"]; ?>">

                    <?php
                    echo htmlspecialchars(
                        $room["room_number"] .
                        " - " .
                        $room["room_type"] .
                        " - Capacity: " .
                        $room["capacity"] .
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
            required
        >


        <br><br>


        <label>Check-in Date</label>
        <br>

        <input
            type="date"
            name="check_in_date"
            required
        >


        <br><br>


        <label>Status</label>
        <br>

        <select name="status" required>

            <option value="">
                Select Status
            </option>

            <option value="Pending">
                Pending
            </option>

            <option value="Confirmed">
                Confirmed
            </option>

            <option value="Cancelled">
                Cancelled
            </option>

        </select>


        <br><br>


        <button type="submit">
            Create Booking
        </button>

    </form>


    <br>

    <a href="index.php">
        ← Back to Bookings
    </a>

</body>

</html>
```
