<?php

require_once "../../auth/check_role.php";

requireRole(["admin", "warden"]);

require_once "../../config/database.php";

$sql = "SELECT
            allocations.*,
            students.student_id AS student_number,
            students.full_name AS student_name,
            rooms.room_number
        FROM allocations
        INNER JOIN students
            ON allocations.student_id = students.id
        INNER JOIN rooms
            ON allocations.room_id = rooms.id
        ORDER BY allocations.id DESC";

$stmt = $pdo->query($sql);

$allocations = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Allocations - RAO Hostel Booking and Management Information System</title>

</head>

<body>

    <h1>RAO Hostel Booking and Management Information System</h1>

    <h2>Room Allocations</h2>

    <p>
        Welcome,
        <strong>
            <?php echo htmlspecialchars($_SESSION["full_name"]); ?>
        </strong>
    </p>

    <a href="add.php">+ Create Allocation</a>

    <br><br>

    <table border="1" cellpadding="8" cellspacing="0">

        <tr>

            <th>#</th>
            <th>Student ID</th>
            <th>Student Name</th>
            <th>Room</th>
            <th>Allocation Date</th>
            <th>Check-in Date</th>
            <th>Check-out Date</th>
            <th>Status</th>
            <th>Actions</th>

        </tr>

        <?php if (count($allocations) > 0): ?>

            <?php foreach ($allocations as $allocation): ?>

                <tr>

                    <td>
                        <?php echo htmlspecialchars($allocation["id"]); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($allocation["student_number"]); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($allocation["student_name"]); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($allocation["room_number"]); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($allocation["allocation_date"]); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($allocation["check_in_date"]); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($allocation["check_out_date"]); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($allocation["status"]); ?>
                    </td>

                    <td>

    <a href="edit.php?id=<?php echo $allocation['id']; ?>">
        Edit
    </a>

    &nbsp; | &nbsp;

    <form method="POST"
          action="delete.php"
                    style="display:inline;">

                <?php echo csrfField(); ?>

        <input
            type="hidden"
            name="id"
            value="<?php echo $allocation['id']; ?>"
        >

        <button type="submit">
            Delete
        </button>

    </form>

</td>

                </tr>

            <?php endforeach; ?>

        <?php else: ?>

            <tr>

                <td colspan="9">
                    No allocations found.
                </td>

            </tr>

        <?php endif; ?>

    </table>

    <br>

    <a href="../../dashboard.php">
        ← Back to Dashboard
    </a>

</body>

</html>