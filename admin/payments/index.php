<?php

require_once "../../auth/check_role.php";

requireRole(["admin", "warden", "staff"]);

require_once "../../config/database.php";

$sql = "SELECT
            payments.*,
            students.student_id AS student_number,
            students.full_name AS student_name
        FROM payments
        INNER JOIN students
            ON payments.student_id = students.id
        ORDER BY payments.id DESC";

$stmt = $pdo->query($sql);

$payments = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Payments - RAO Hostel Booking and Management Information System</title>

</head>

<body>

    <h1>RAO Hostel Booking and Management Information System</h1>

    <h2>Payments</h2>

    <p>
        Welcome,
        <strong>
            <?php echo htmlspecialchars($_SESSION["full_name"]); ?>
        </strong>
    </p>

    <a href="add.php">+ Record Payment</a>

    <br><br>

    <table border="1" cellpadding="8" cellspacing="0">

        <tr>

            <th>#</th>
            <th>Student ID</th>
            <th>Student Name</th>
            <th>Amount</th>
            <th>Payment Date</th>
            <th>Payment Method</th>
            <th>Reference Number</th>
            <th>Status</th>
            <th>Actions</th>

        </tr>

        <?php if (count($payments) > 0): ?>

            <?php foreach ($payments as $payment): ?>

                <tr>

                    <td>
                        <?php echo htmlspecialchars($payment["id"]); ?>
                    </td>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $payment["student_number"]
                        );
                        ?>
                    </td>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $payment["student_name"]
                        );
                        ?>
                    </td>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $payment["amount"]
                        );
                        ?>
                    </td>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $payment["payment_date"]
                        );
                        ?>
                    </td>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $payment["payment_method"]
                        );
                        ?>
                    </td>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $payment["reference_number"]
                        );
                        ?>
                    </td>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $payment["status"]
                        );
                        ?>
                    </td>

                   <td>

    <a href="edit.php?id=<?php echo $payment['id']; ?>">
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
            value="<?php echo $payment['id']; ?>"
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
                    No payments found.
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