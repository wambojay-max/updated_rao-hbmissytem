<?php

require_once "../../auth/check_role.php";

requireRole(["admin"]);

require_once "../../config/database.php";
$stmt = $pdo->query(
    "SELECT id, full_name, email, role, created_at
     FROM users
     ORDER BY id DESC"
);

$users = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Users - RAO Hostel Booking and Management Information System</title>

</head>

<body>

    <h1>RAO Hostel Booking and Management Information System</h1>

    <h2>Users</h2>

    <p>
        Welcome,
        <strong>
            <?php echo htmlspecialchars($_SESSION["full_name"]); ?>
        </strong>
    </p>

    <a href="add.php">+ Add User</a>

    <br><br>

    <table border="1" cellpadding="8" cellspacing="0">

        <tr>

            <th>#</th>
            <th>Full Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Created At</th>
            <th>Actions</th>

        </tr>

        <?php if (count($users) > 0): ?>

            <?php foreach ($users as $user): ?>

                <tr>

                    <td>
                        <?php echo htmlspecialchars($user["id"]); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($user["full_name"]); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($user["email"]); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($user["role"]); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($user["created_at"]); ?>
                    </td>

                    <td>

    <a href="edit.php?id=<?php echo $user['id']; ?>">
        Edit
    </a>

    &nbsp; | &nbsp;

    <?php if ((int) $user['id'] !== (int) $_SESSION['user_id']): ?>

          <form method="POST"
              action="delete.php"
                            style="display:inline;">

                        <?php echo csrfField(); ?>

            <input
                type="hidden"
                name="id"
                value="<?php echo $user['id']; ?>"
            >

            <button type="submit">
                Delete
            </button>

        </form>

    <?php else: ?>

        <span>Current User</span>

    <?php endif; ?>

</td>

                </tr>

            <?php endforeach; ?>

        <?php else: ?>

            <tr>

                <td colspan="6">
                    No users found.
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