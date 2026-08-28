<?php

require_once "../../auth/check_role.php";

requireRole(["admin"]);

require_once "../../config/database.php";

$id = $_GET["id"] ?? null;

if (!$id) {
    header("Location: index.php");
    exit;
}

/* Get user */
$stmt = $pdo->prepare(
    "SELECT id, full_name, email, role
     FROM users
     WHERE id = :id"
);

$stmt->execute([
    "id" => $id
]);

$user = $stmt->fetch();

if (!$user) {
    die("User not found.");
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $full_name = trim($_POST["full_name"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $role = trim($_POST["role"]);

    if (
        $full_name === "" ||
        $email === "" ||
        $role === ""
    ) {

        $message = "Please fill in all required fields.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";

    } else {

        /* Check duplicate email */
        $checkStmt = $pdo->prepare(
            "SELECT id
             FROM users
             WHERE email = :email
             AND id != :id"
        );

        $checkStmt->execute([
            "email" => $email,
            "id" => $id
        ]);

        if ($checkStmt->fetch()) {

            $message = "Email already exists.";

        } else {

            try {

                /*
                 * If password is left empty,
                 * keep the existing password.
                 */

                if ($password !== "") {

                    if (strlen($password) < 6) {

                        $message =
                            "Password must be at least 6 characters.";

                    } else {

                        $hashedPassword = password_hash(
                            $password,
                            PASSWORD_DEFAULT
                        );

                        $updateStmt = $pdo->prepare(
                            "UPDATE users SET
                                full_name = :full_name,
                                email = :email,
                                password = :password,
                                role = :role
                             WHERE id = :id"
                        );

                        $updateStmt->execute([
                            "full_name" => $full_name,
                            "email" => $email,
                            "password" => $hashedPassword,
                            "role" => $role,
                            "id" => $id
                        ]);

                        header("Location: index.php");
                        exit;
                    }

                } else {

                    $updateStmt = $pdo->prepare(
                        "UPDATE users SET
                            full_name = :full_name,
                            email = :email,
                            role = :role
                         WHERE id = :id"
                    );

                    $updateStmt->execute([
                        "full_name" => $full_name,
                        "email" => $email,
                        "role" => $role,
                        "id" => $id
                    ]);

                    header("Location: index.php");
                    exit;
                }

            } catch (PDOException $e) {

                $message = "Failed to update user.";
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

    <title>Edit User - RAO Hostel Booking and Management Information System</title>

</head>

<body>

    <h1>RAO Hostel Booking and Management Information System</h1>

    <h2>Edit User</h2>

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
            value="<?php echo htmlspecialchars($user["full_name"]); ?>"
            required
        >


        <br><br>


        <label>Email</label>
        <br>

        <input
            type="email"
            name="email"
            maxlength="100"
            value="<?php echo htmlspecialchars($user["email"]); ?>"
            required
        >


        <br><br>


        <label>New Password</label>
        <br>

        <input
            type="password"
            name="password"
            minlength="6"
        >

        <small>
            Leave blank to keep the current password.
        </small>


        <br><br>


        <label>Role</label>
        <br>

        <select name="role" required>

            <option value="admin"
                <?php
                echo $user["role"] === "admin"
                    ? "selected"
                    : "";
                ?>>
                Admin
            </option>

            <option value="warden"
                <?php
                echo $user["role"] === "warden"
                    ? "selected"
                    : "";
                ?>>
                Warden
            </option>

            <option value="staff" <?php echo $user["role"] === "staff" ? "selected" : ""; ?>>
                Staff
            </option>

            <option value="student" <?php echo $user["role"] === "student" ? "selected" : ""; ?>>
                Student
            </option>

            <option value="lecturer" <?php echo $user["role"] === "lecturer" ? "selected" : ""; ?>>
                Lecturer
            </option>

        </select>


        <br><br>


        <button type="submit">
            Update User
        </button>

    </form>


    <br>

    <a href="index.php">
        ← Back to Users
    </a>

</body>

</html>