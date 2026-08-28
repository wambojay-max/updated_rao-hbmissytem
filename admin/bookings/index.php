<?php

require_once "../../auth/check_role.php";

requireRole(["admin", "warden", "staff"]);

require_once "../../config/database.php";

$sql = "SELECT 
            bookings.*,
            students.full_name AS student_name,
            students.student_id AS student_number,
            rooms.room_number
        FROM bookings
        INNER JOIN students ON bookings.student_id = students.id
        INNER JOIN rooms ON bookings.room_id = rooms.id
        ORDER BY bookings.id DESC";

$stmt = $pdo->query($sql);
$bookings = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Bookings - RAO Hostel Booking and Management Information System</title>
</head>

<body>

    <h1>RAO Hostel Booking and Management Information System</h1>

    <h2>Bookings</h2>

    <p>
        Welcome,
        <strong><?php echo htmlspecialchars($_SESSION["full_name"]); ?></strong>
    </p>

    <a href="add.php">+ Create Booking</a>

    <br><br>

    <table border="1" cellpadding="8" cellspacing="0">

        <tr>
            <th>#</th>
            <th>Student ID</th>
            <th>Student Name</th>
            <th>Room</th>
            <th>Booking Date</th>
            <th>Check-in Date</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>

        <?php if (count($bookings) > 0): ?>

            <?php foreach ($bookings as $booking): ?>

                <tr>

                    <td>
                        <?php echo htmlspecialchars($booking["id"]); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($booking["student_number"]); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($booking["student_name"]); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($booking["room_number"]); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($booking["booking_date"]); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($booking["check_in_date"]); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($booking["status"]); ?>
                    </td>

                    <td>

    <a href="edit.php?id=<?php echo $booking['id']; ?>">
        Edit
    </a>

    &nbsp; | &nbsp;

    <form method="POST"
          action="delete.php"
                    style="display:inline;">

                <?php echo csrfField(); ?>

        <input type="hidden"
               name="id"
               value="<?php echo $booking['id']; ?>">

        <button type="submit">
            Delete
        </button>

    </form>

</td>

                </tr>

            <?php endforeach; ?>

        <?php else: ?>

            <tr>
                <td colspan="8">
                    No bookings found.
                </td>
            </tr>

        <?php endif; ?>

    </table>

    <br>

    <a href="../../dashboard.php">← Back to Dashboard</a>

</body>

</html>