<?php

require_once "../../auth/check_role.php";

requireRole(["admin", "warden"]);

require_once "../../config/database.php";

$id = $_GET["id"] ?? null;

if (!$id) {
    header("Location: index.php");
    exit;
}

/* Get allocation */
$stmt = $pdo->prepare(
    "SELECT * FROM allocations WHERE id = :id"
);

$stmt->execute([
    "id" => $id
]);

$allocation = $stmt->fetch();

if (!$allocation) {
    die("Allocation not found.");
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
        $sql = "UPDATE allocations SET

                    student_id = :student_id,
                    room_id = :room_id,
                    allocation_date = :allocation_date,
                    check_in_date = :check_in_date,
                    check_out_date = :check_out_date,
                    status = :status

                WHERE id = :id";

        $stmt = $pdo->prepare($sql);

        try {

            $stmt->execute([

                "student_id" => $student_id,
                "room_id" => $room_id,
                "allocation_date" => $allocation_date,
                "check_in_date" => $check_in_date,
                "check_out_date" => $check_out_date,
                "status" => $status,
                "id" => $id

            ]);

            /*
             * Update room status.
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

            } elseif (
                $status === "Completed" ||
                $status === "Cancelled"
            ) {

                $roomUpdate = $pdo->prepare(
                    "UPDATE rooms
                     SET status = 'Available'
                     WHERE id = :room_id"
                );

                $roomUpdate->execute([
                    "room_id" => $room_id
                ]);
            }

            header("Location: index.php");
            exit;

        } catch (PDOException $e) {

            $message = "Failed to update allocation.";
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

    <title>Edit Allocation - RAO Hostel Booking and Management Information System</title>

</head>

<body>

    <h1>RAO Hostel Booking and Management Information System</h1>

    <h2>Edit Room Allocation</h2>

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
                    <?php
                    echo $allocation["student_id"] == $student["id"]
                        ? "selected"
                        : "";
                    ?>
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
                    <?php
                    echo $allocation["room_id"] == $room["id"]
                        ? "selected"
                        : "";
                    ?>
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


        <label>Allocation Date</label>
        <br>

        <input
            type="date"
            name="allocation_date"
            value="<?php echo htmlspecialchars($allocation["allocation_date"]); ?>"
            required
        >


        <br><br>


        <label>Check-in Date</label>
        <br>

        <input
            type="date"
            name="check_in_date"
            value="<?php echo htmlspecialchars($allocation["check_in_date"]); ?>"
            required
        >


        <br><br>


        <label>Check-out Date</label>
        <br>

        <input
            type="date"
            name="check_out_date"
            value="<?php echo htmlspecialchars($allocation["check_out_date"]); ?>"
            required
        >


        <br><br>


        <label>Status</label>
        <br>

        <select name="status" required>

            <option
                value="Active"
                <?php
                echo $allocation["status"] === "Active"
                    ? "selected"
                    : "";
                ?>
            >
                Active
            </option>

            <option
                value="Completed"
                <?php
                echo $allocation["status"] === "Completed"
                    ? "selected"
                    : "";
                ?>
            >
                Completed
            </option>

            <option
                value="Cancelled"
                <?php
                echo $allocation["status"] === "Cancelled"
                    ? "selected"
                    : "";
                ?>
            >
                Cancelled
            </option>

        </select>


        <br><br>


        <button type="submit">
            Update Allocation
        </button>

    </form>


    <br>

    <a href="index.php">
        ← Back to Allocations
    </a>

</body>

</html>