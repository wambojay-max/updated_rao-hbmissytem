<?php

require_once "../../auth/check_role.php";

requireRole(["admin", "warden"]);

require_once "../../config/database.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $student_id = trim($_POST["student_id"]);
    $full_name = trim($_POST["full_name"]);
    $gender = trim($_POST["gender"]);
    $phone = trim($_POST["phone"]);
    $email = trim($_POST["email"]);
    $course = trim($_POST["course"]);
    $year_of_study = (int) $_POST["year_of_study"];

   if (
    $student_id === "" ||
    $full_name === "" ||
    $gender === "" ||
    $phone === "" ||
    $email === "" ||
    $course === "" ||
    $year_of_study <= 0
) {

    $message = "Please fill in all fields.";

} elseif (strlen($student_id) > 30) {

    $message = "Student ID must not exceed 30 characters.";

} elseif (strlen($full_name) > 100) {

    $message = "Full name must not exceed 100 characters.";

} elseif (!in_array($gender, ["Male", "Female"], true)) {

    $message = "Invalid gender selected.";

} elseif (strlen($phone) > 20) {

    $message = "Phone number must not exceed 20 characters.";

} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

    $message = "Please enter a valid email address.";

} elseif (strlen($email) > 100) {

    $message = "Email must not exceed 100 characters.";

} elseif (strlen($course) > 100) {

    $message = "Course must not exceed 100 characters.";

} elseif ($year_of_study < 1 || $year_of_study > 10) {

    $message = "Year of study must be between 1 and 10.";

} else {

        $sql = "INSERT INTO students
                (student_id, full_name, gender, phone, email, course, year_of_study)
                VALUES
                (:student_id, :full_name, :gender, :phone, :email, :course, :year_of_study)";

        $stmt = $pdo->prepare($sql);

        try {

            $stmt->execute([
                "student_id" => $student_id,
                "full_name" => $full_name,
                "gender" => $gender,
                "phone" => $phone,
                "email" => $email,
                "course" => $course,
                "year_of_study" => $year_of_study
            ]);

            header("Location: index.php");
            exit;

        } catch (PDOException $e) {

            if ($e->getCode() == 23000) {
                $message = "Student ID already exists.";
            } else {
                $message = "Failed to add student.";
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Add Student - RAO Hostel Booking and Management Information System</title>
</head>

<body>

    <h1>RAO Hostel Booking and Management Information System</h1>

    <h2>Add Student</h2>

    <?php if ($message !== ""): ?>

        <p>
            <?php echo htmlspecialchars($message); ?>
        </p>

    <?php endif; ?>

    <form method="POST">
        <?php echo csrfField(); ?>

        <label>Student ID</label><br>
        <input type="text" name="student_id" required>

        <br><br>

        <label>Full Name</label><br>
        <input type="text" name="full_name" required>

        <br><br>

        <label>Gender</label><br>
        <select name="gender" required>
            <option value="">Select Gender</option>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
        </select>

        <br><br>

        <label>Phone</label><br>
        <input type="text" name="phone" required>

        <br><br>

        <label>Email</label><br>
        <input type="email" name="email" required>

        <br><br>

        <label>Course</label><br>
        <input type="text" name="course" required>

        <br><br>

        <label>Year of Study</label><br>
        <input type="number" name="year_of_study" min="1" max="10" required>

        <br><br>

        <button type="submit">Add Student</button>

    </form>

    <br>

    <a href="index.php">← Back to Students</a>

</body>

</html>