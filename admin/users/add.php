<?php

require_once "../../auth/check_role.php";

requireRole(["admin"]);

require_once "../../config/database.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $full_name = trim($_POST["full_name"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $role = trim($_POST["role"]);

    if (
        $full_name === "" ||
        $email === "" ||
        $password === "" ||
        $role === ""
    ) {

        $message = "Please fill in all fields.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";

    } elseif (strlen($password) < 6) {

        $message = "Password must be at least 6 characters.";

    } else {

        $checkStmt = $pdo->prepare(
            "SELECT id FROM users WHERE email = :email"
        );

        $checkStmt->execute([
            "email" => $email
        ]);

        if ($checkStmt->fetch()) {

            $message = "Email already exists.";

        } else {

            $hashedPassword = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $stmt = $pdo->prepare(
                "INSERT INTO users
                (full_name, email, password, role)
                VALUES
                (:full_name, :email, :password, :role)"
            );

            try {

                $stmt->execute([
                    "full_name" => $full_name,
                    "email" => $email,
                    "password" => $hashedPassword,
                    "role" => $role
                ]);

                header("Location: index.php");
                exit;

            } catch (PDOException $e) {

                $message = "Failed to create user.";
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

    <title>Add User - RAO Hostel Booking and Management Information System</title>

</head>

<body>

    <h1>RAO Hostel Booking and Management Information System</h1>

    <h2>Add User</h2>

    <?php if ($message !== ""): ?>

        <p>
            <?php echo htmlspecialchars($message); ?>
        </p>

    <?php endif; ?>


    <form method="POST">
        <?php echo csrfField(); ?>

        <label>Full Name</label>
        <br>

        <input
            type="text"
            name="full_name"
            maxlength="100"
            required
        >


        <br><br>


        <label>Email</label>
        <br>

        <input
            type="email"
            name="email"
            maxlength="100"
            required
        >


        <br><br>


        <label>Password</label>
        <br>

        <input
            type="password"
            name="password"
            minlength="6"
            required
        >


        <br><br>


        <label>Role</label>
        <br>

        <select name="role" required>

            <option value="">
                Select Role
            </option>

            <option value="admin">
                Admin
            </option>

            <option value="warden">
                Warden
            </option>

            <option value="staff">
                Staff
            </option>

            <option value="student">
                Student
            </option>

            <option value="lecturer">
                Lecturer
            </option>

        </select>


        <br><br>


        <button type="submit">
            Create User
        </button>

    </form>


    <br>

    <a href="index.php">
        ← Back to Users
    </a>

</body>

</html>