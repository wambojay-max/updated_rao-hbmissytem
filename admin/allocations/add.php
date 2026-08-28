```php
<?php

require_once "../../auth/check_role.php";

requireRole(["admin", "warden"]);

require_once "../../config/database.php";

$message = "";

/* Get students with confirmed bookings and no active allocation */
$studentStmt = $pdo->query(
    "SELECT DISTINCT
            s.id,
            s.student_id,
            s.full_name
     FROM students s
     INNER JOIN bookings b
        ON b.student_id = s.id
     WHERE b.status = 'Confirmed'
       AND NOT EXISTS (
           SELECT 1
           FROM allocations a
           WHERE a.student_id = s.id
             AND a.status = 'Active'
       )
     ORDER BY s.full_name ASC"
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
    $allocation_date = $_POST["allocation_date"];
    $check_in_date = $_POST["check_in_date"];
    $check_out_date = $_POST["check_out_date"];
    $status = trim($_POST["status"]);

    if (
        $student_id <= 0 ||
        $room_id <= 0 ||
        $allocation_date === "" ||
        $check_in_date === "" ||
        $check_out_date === "" ||
        $status === ""
    ) {

        $message = "Please fill in all fields.";

    } elseif (!in_array($status, ["Active", "Completed", "Cancelled"], true)) {

        $message = "Invalid allocation status.";

    } elseif ($check_in_date < $allocation_date) {

        $message = "Check-in date cannot be before allocation date.";

    } elseif ($check_out_date < $check_in_date) {

        $message = "Check-out date cannot be before check-in date.";

    } else {

        /* Get student gender */
        $studentCheck = $pdo->prepare(
            "SELECT id, gender
             FROM students
             WHERE id = :id"
        );

        $studentCheck->execute([
            "id" => $student_id
        ]);

        $student = $studentCheck->fetch();

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

                $message = "Selected room is no longer available.";

            } elseif ($student["gender"] !== $room["gender"]) {

                $message = "Student gender does not match room gender.";

            } else {

                /* Create allocation */
                $sql = "INSERT INTO allocations
                        (
                            student_id,
                            room_id,
                            allocation_date,
                            check_in_date,
                            check_out_date,
                            status
                        )
                        VALUES
                        (
                            :student_id,
                            :room_id,
                            :allocation_date,
                            :check_in_date,
                            :check_out_date,
                            :status
                        )";

                $stmt = $pdo->prepare($sql);

                try {

                    $stmt->execute([
                        "student_id" => $student_id,
                        "room_id" => $room_id,
                        "allocation_date" => $allocation_date,
                        "check_in_date" => $check_in_date,
                        "check_out_date" => $check_out_date,
                        "status" => $status
                    ]);

                    /*
                     * Mark room as occupied
                     * when allocation is active.
                     */
                    if ($status === "Active") {

                        $roomUpdate = $pdo->prepare(
                            "UPDATE rooms
                             SET status = 'Occupied'
                             WHERE id = :room_id"
                        );

                        $roomUpdate->execute([
                            "room_id" => $room_id
                        ]);
                    }

                    header("Location: index.php");
                    exit;

                } catch (PDOException $e) {

                    $message = "Failed to create allocation.";
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

    <title>Create Allocation - RAO Hostel Booking and Management Information System</title>

</head>

<body>

    <h1>RAO Hostel Booking and Management Information System</h1>

    <h2>Create Room Allocation</h2>

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


        <label>Allocation Date</label>
        <br>

        <input
            type="date"
            name="allocation_date"
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


        <label>Check-out Date</label>
        <br>

        <input
            type="date"
            name="check_out_date"
            required
        >


        <br><br>


        <label>Status</label>
        <br>

        <select name="status" required>

            <option value="">
                Select Status
            </option>

            <option value="Active">
                Active
            </option>

            <option value="Completed">
                Completed
            </option>

            <option value="Cancelled">
                Cancelled
            </option>

        </select>


        <br><br>


        <button type="submit">
            Create Allocation
        </button>

    </form>


    <br>

    <a href="index.php">
        ← Back to Allocations
    </a>

</body>

</html>
```
