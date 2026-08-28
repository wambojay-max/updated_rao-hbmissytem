<?php

require_once "../../auth/check_role.php";

requireRole(["admin", "warden", "lecturer"]);

require_once "../../config/database.php";

$id = $_GET["id"] ?? null;

if (!$id) {
    header("Location: index.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM students WHERE id = :id");
$stmt->execute(["id" => $id]);

$student = $stmt->fetch();

if (!$student) {
    die("Student not found.");
}

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
    } else {

        $sql = "UPDATE students SET
                student_id = :student_id,
                full_name = :full_name,
                gender = :gender,
                phone = :phone,
                email = :email,
                course = :course,
                year_of_study = :year_of_study
                WHERE id = :id";

        $stmt = $pdo->prepare($sql);

        try {

            $stmt->execute([
                "student_id" => $student_id,
                "full_name" => $full_name,
                "gender" => $gender,
                "phone" => $phone,
                "email" => $email,
                "course" => $course,
                "year_of_study" => $year_of_study,
                "id" => $id
            ]);

            header("Location: index.php");
            exit;

        } catch (PDOException $e) {

            if ($e->getCode() == 23000) {
                $message = "Student ID already exists.";
            } else {
                $message = "Failed to update student.";
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

    <title>Edit Student - RAO Hostel Booking and Management Information System</title>
</head>

<body>

    <h1>RAO Hostel Booking and Management Information System</h1>

    <h2>Edit Student</h2>

    <?php if ($message !== ""): ?>
        <p><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <form method="POST">
        <?php echo csrfField(); ?>

        <label>Student ID</label><br>
        <input
            type="text"
            name="student_id"
            value="<?php echo htmlspecialchars($student["student_id"]); ?>"
            required
        >

        <br><br>

        <label>Full Name</label><br>
        <input
            type="text"
            name="full_name"
            value="<?php echo htmlspecialchars($student["full_name"]); ?>"
            required
        >

        <br><br>

        <label>Gender</label><br>
        <select name="gender" required>
            <option value="Male" <?php echo $student["gender"] === "Male" ? "selected" : ""; ?>>
                Male
            </option>

            <option value="Female" <?php echo $student["gender"] === "Female" ? "selected" : ""; ?>>
                Female
            </option>
        </select>

        <br><br>

        <label>Phone</label><br>
        <input
            type="text"
            name="phone"
            value="<?php echo htmlspecialchars($student["phone"]); ?>"
            required
        >

        <br><br>

        <label>Email</label><br>
        <input
            type="email"
            name="email"
            value="<?php echo htmlspecialchars($student["email"]); ?>"
            required
        >

        <br><br>

        <label>Course</label><br>
        <input
            type="text"
            name="course"
            value="<?php echo htmlspecialchars($student["course"]); ?>"
            required
        >

        <br><br>

        <label>Year of Study</label><br>
        <input
            type="number"
            name="year_of_study"
            min="1"
            max="10"
            value="<?php echo htmlspecialchars($student["year_of_study"]); ?>"
            required
        >

        <br><br>

        <button type="submit">Update Student</button>

    </form>

    <br>

    <a href="index.php">← Back to Students</a>

</body>

</html>