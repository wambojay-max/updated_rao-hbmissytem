<?php
require_once "../config/security.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RAO Hostel Booking and Management Information System - Login</title>
</head>

<body>

    <h2>RAO Hostel Booking and Management Information System</h2>
    <h3>Hostel Management Information System</h3>

    <form method="POST" action="login_process.php">

        <?php echo csrfField(); ?>

        <label>Email:</label><br>
        <input type="email" name="email" required><br><br>

        <label>Password:</label><br>
        <input type="password" name="password" required><br><br>

        <button type="submit">Login</button>

    </form>

    <p><a href="forgot_password.php">Forgot your password?</a></p>
    <p><a href="https://localhost/UPDATED_RAVINE_ACADEMIC/auth/login.php">Go to Academic System</a></p>

</body>
</html>