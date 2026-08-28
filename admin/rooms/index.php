<?php

require_once "../../auth/check_role.php";

requireRole(["admin", "warden", "staff"]);

require_once "../../config/database.php";

$sql = "SELECT * FROM rooms ORDER BY id DESC";
$stmt = $pdo->query($sql);

$rooms = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Rooms - RAO Hostel Booking and Management Information System</title>
</head>

<body>

    <h1>RAO Hostel Booking and Management Information System</h1>

    <h2>Rooms</h2>

    <p>
        Welcome,
        <strong><?php echo htmlspecialchars($_SESSION["full_name"]); ?></strong>
    </p>

    <a href="add.php">+ Add Room</a>

    <br><br>

    <table border="1" cellpadding="8" cellspacing="0">

        <tr>
            <th>#</th>
            <th>Room Number</th>
            <th>Room Type</th>
            <th>Capacity</th>
            <th>Floor</th>
            <th>Gender</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>

        <?php if (count($rooms) > 0): ?>

            <?php foreach ($rooms as $room): ?>

                <tr>

                    <td>
                        <?php echo htmlspecialchars($room["id"]); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($room["room_number"]); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($room["room_type"]); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($room["capacity"]); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($room["floor"]); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($room["gender"]); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($room["status"]); ?>
                    </td>

                    <td>

    <a href="edit.php?id=<?php echo $room['id']; ?>">
        Edit
    </a>

    &nbsp; | &nbsp;

    <form method="POST"
          action="delete.php"
                    style="display:inline;">

                <?php echo csrfField(); ?>

        <input type="hidden"
               name="id"
               value="<?php echo $room['id']; ?>">

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
                    No rooms found.
                </td>
            </tr>

        <?php endif; ?>

    </table>

    <br>

    <a href="../../dashboard.php">← Back to Dashboard</a>

</body>

</html>