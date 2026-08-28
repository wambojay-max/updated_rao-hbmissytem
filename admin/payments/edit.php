<?php

require_once "../../auth/check_role.php";

requireRole(["admin", "warden"]);

require_once "../../config/database.php";

$id = $_GET["id"] ?? null;

if (!$id) {
    header("Location: index.php");
    exit;
}

/* Get payment */
$stmt = $pdo->prepare(
    "SELECT * FROM payments WHERE id = :id"
);

$stmt->execute([
    "id" => $id
]);

$payment = $stmt->fetch();

if (!$payment) {
    die("Payment not found.");
}

/* Get students */
$studentStmt = $pdo->query(
    "SELECT id, student_id, full_name
     FROM students
     ORDER BY full_name ASC"
);

$students = $studentStmt->fetchAll();

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $student_id = (int) $_POST["student_id"];
    $amount = trim($_POST["amount"]);
    $payment_date = $_POST["payment_date"];
    $payment_method = trim($_POST["payment_method"]);
    $reference_number = trim($_POST["reference_number"]);
    $status = trim($_POST["status"]);

    if (
    $student_id <= 0 ||
    $amount === "" ||
    $payment_date === "" ||
    $payment_method === "" ||
    $reference_number === "" ||
    $status === ""
) {

    $message = "Please fill in all fields.";

} elseif (!is_numeric($amount) || $amount <= 0) {

    $message = "Please enter a valid payment amount.";

} elseif (strlen($reference_number) > 50) {

    $message = "Reference number must not exceed 50 characters.";

} elseif (!in_array($payment_method, ["Cash", "M-Pesa", "Bank", "Card"], true)) {

    $message = "Invalid payment method.";

} elseif (!in_array($status, ["Pending", "Completed", "Failed", "Cancelled"], true)) {

    $message = "Invalid payment status.";

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
    }

        $sql = "UPDATE payments SET
                    student_id = :student_id,
                    amount = :amount,
                    payment_date = :payment_date,
                    payment_method = :payment_method,
                    reference_number = :reference_number,
                    status = :status
                WHERE id = :id";

        $stmt = $pdo->prepare($sql);

        try {

            $stmt->execute([
                "student_id" => $student_id,
                "amount" => $amount,
                "payment_date" => $payment_date,
                "payment_method" => $payment_method,
                "reference_number" => $reference_number,
                "status" => $status,
                "id" => $id
            ]);

            header("Location: index.php");
            exit;

        } catch (PDOException $e) {

            if ($e->getCode() == 23000) {
                $message = "Reference number already exists.";
            } else {
                $message = "Failed to update payment.";
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

    <title>Edit Payment - RAO Hostel Booking and Management Information System</title>

</head>

<body>

    <h1>RAO Hostel Booking and Management Information System</h1>

    <h2>Edit Payment</h2>

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
                    echo $payment["student_id"] == $student["id"]
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


        <label>Amount</label>
        <br>

        <input
            type="number"
            name="amount"
            step="0.01"
            min="0.01"
            value="<?php echo htmlspecialchars($payment["amount"]); ?>"
            required
        >


        <br><br>


        <label>Payment Date</label>
        <br>

        <input
            type="date"
            name="payment_date"
            value="<?php echo htmlspecialchars($payment["payment_date"]); ?>"
            required
        >


        <br><br>


        <label>Payment Method</label>
        <br>

        <select name="payment_method" required>

            <option value="Cash"
                <?php echo $payment["payment_method"] === "Cash" ? "selected" : ""; ?>>
                Cash
            </option>

            <option value="M-Pesa"
                <?php echo $payment["payment_method"] === "M-Pesa" ? "selected" : ""; ?>>
                M-Pesa
            </option>

            <option value="Bank"
                <?php echo $payment["payment_method"] === "Bank" ? "selected" : ""; ?>>
                Bank
            </option>

            <option value="Card"
                <?php echo $payment["payment_method"] === "Card" ? "selected" : ""; ?>>
                Card
            </option>

        </select>


        <br><br>


        <label>Reference Number</label>
        <br>

        <input
            type="text"
            name="reference_number"
            maxlength="50"
            value="<?php echo htmlspecialchars($payment["reference_number"]); ?>"
            required
        >


        <br><br>


        <label>Status</label>
        <br>

        <select name="status" required>

            <option value="Pending"
                <?php echo $payment["status"] === "Pending" ? "selected" : ""; ?>>
                Pending
            </option>

            <option value="Completed"
                <?php echo $payment["status"] === "Completed" ? "selected" : ""; ?>>
                Completed
            </option>

            <option value="Failed"
                <?php echo $payment["status"] === "Failed" ? "selected" : ""; ?>>
                Failed
            </option>

            <option value="Cancelled"
                <?php echo $payment["status"] === "Cancelled" ? "selected" : ""; ?>>
                Cancelled
            </option>

        </select>


        <br><br>


        <button type="submit">
            Update Payment
        </button>

    </form>


    <br>

    <a href="index.php">
        ← Back to Payments
    </a>

</body>

</html>