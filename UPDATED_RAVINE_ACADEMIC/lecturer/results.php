<?php
require_once "../config/security.php";
require_once "../config/database.php";

if (!isset($_SESSION["user_id"]) || !in_array($_SESSION["role"], ["lecturer", "admin"], true)) {
    header("Location: ../auth/login.php");
    exit;
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    verifyCsrf();
    $studentId = (int) ($_POST["student_id"] ?? 0);
    $courseId = (int) ($_POST["course_id"] ?? 0);
    $academicYear = trim($_POST["academic_year"] ?? "");
    $score = (float) ($_POST["score"] ?? -1);
    $grade = strtoupper(trim($_POST["grade"] ?? ""));

    if (!$studentId || !$courseId || !$academicYear || $score < 0 || $score > 100 || !preg_match('/^[A-F][+\-]?$/', $grade)) {
        $message = "Enter valid result details.";
    } else {
        $stmt = $pdo->prepare(
            "INSERT INTO student_results (student_id, course_id, academic_year, score, grade, status)
             VALUES (:student, :course, :year, :score, :grade, 'Draft')"
        );
        $stmt->execute([
            "student" => $studentId,
            "course" => $courseId,
            "year" => $academicYear,
            "score" => $score,
            "grade" => $grade
        ]);
        $message = "Result submitted for registrar approval.";
    }
}

$students = $pdo->query("SELECT id, student_id, full_name FROM students ORDER BY full_name")->fetchAll();
$courses = $pdo->query("SELECT id, code, title FROM courses WHERE is_active = 1 ORDER BY code")->fetchAll();
$e = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Lecturer workspace</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<header class="topbar"><a href="results.php">RAVINE ACADEMIC</a><nav><?php if ($_SESSION["role"] === "admin"): ?><a href="../registrar/approve_results.php">Approve results</a><?php endif; ?><a href="../auth/logout.php">Log out</a></nav></header>
<main class="shell">
    <p class="eyebrow">LECTURER WORKSPACE</p>
    <h1>Enter student results</h1>
    <?php if ($message): ?><div class="notice"><?php echo $e($message); ?></div><?php endif; ?>
    <section class="panel">
        <form method="post" class="lecturer-form">
            <?php echo csrfField(); ?>
            <label>Student<select name="student_id" required><option value="">Choose student</option><?php foreach ($students as $student): ?><option value="<?php echo (int) $student["id"]; ?>"><?php echo $e($student["student_id"] . " - " . $student["full_name"]); ?></option><?php endforeach; ?></select></label>
            <label>Course<select name="course_id" required><option value="">Choose course</option><?php foreach ($courses as $course): ?><option value="<?php echo (int) $course["id"]; ?>"><?php echo $e($course["code"] . " - " . $course["title"]); ?></option><?php endforeach; ?></select></label>
            <label>Academic year<input name="academic_year" placeholder="2026/2027" required></label>
            <label>Score<input type="number" name="score" min="0" max="100" step="0.01" required></label>
            <label>Grade<input name="grade" maxlength="3" placeholder="A" required></label>
            <button>Submit for approval</button>
        </form>
    </section>
</main>
</body>
</html>
