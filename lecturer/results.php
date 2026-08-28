<?php
require_once "../auth/check_role.php";
requireRole(["lecturer"]);
require_once "../config/database.php";
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    verifyCsrf();
    $studentId = (int) ($_POST["student_id"] ?? 0);
    $courseId = (int) ($_POST["course_id"] ?? 0);
    $academicYear = trim($_POST["academic_year"] ?? "");
    $score = (float) ($_POST["score"] ?? -1);
    $grade = strtoupper(trim($_POST["grade"] ?? ""));
    $resultId = (int) ($_POST["result_id"] ?? 0);

    if ($studentId <= 0 || $courseId <= 0 || $academicYear === "" || $score < 0 || $score > 100 || !preg_match('/^[A-F][+\-]?$/', $grade)) {
        $message = "Enter a valid student, course, academic year, score, and grade.";
    } elseif ($resultId > 0) {
        $stmt = $pdo->prepare("UPDATE student_results SET student_id = :student_id, course_id = :course_id, academic_year = :academic_year, score = :score, grade = :grade, status = 'Published' WHERE id = :id");
        $stmt->execute(["student_id" => $studentId, "course_id" => $courseId, "academic_year" => $academicYear, "score" => $score, "grade" => $grade, "id" => $resultId]);
        audit($pdo, "update", "student_result", $resultId);
        $message = "Result updated.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO student_results (student_id, course_id, academic_year, score, grade, status) VALUES (:student_id, :course_id, :academic_year, :score, :grade, 'Published')");
        $stmt->execute(["student_id" => $studentId, "course_id" => $courseId, "academic_year" => $academicYear, "score" => $score, "grade" => $grade]);
        audit($pdo, "create", "student_result", (int) $pdo->lastInsertId());
        $message = "Result published.";
    }
}

$students = $pdo->query("SELECT id, student_id, full_name FROM students ORDER BY full_name")->fetchAll();
$courses = $pdo->query("SELECT id, code, title FROM courses WHERE is_active = 1 ORDER BY code")->fetchAll();
$results = $pdo->query("SELECT r.*, s.student_id AS student_number, s.full_name, c.code, c.title FROM student_results r INNER JOIN students s ON s.id = r.student_id INNER JOIN courses c ON c.id = r.course_id ORDER BY r.id DESC")->fetchAll();
$e = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Lecturer results | RAO HBMIS</title><link rel="stylesheet" href="../assets/css/style.css"></head><body><main class="main-content lecturer-page"><div class="header"><h1>Lecturer workspace</h1><p>Enter and publish student examination results.</p><p><a href="../dashboard.php">Dashboard</a> | <a href="../auth/logout.php">Log out</a></p></div><?php if ($message): ?><div class="portal-notice"><?php echo $e($message); ?></div><?php endif; ?><section class="portal-panel"><h2>Enter result</h2><form method="post" class="lecturer-form"><?php echo csrfField(); ?><label>Student<select name="student_id" required><option value="">Choose student</option><?php foreach ($students as $student): ?><option value="<?php echo (int) $student["id"]; ?>"><?php echo $e($student["student_id"] . " - " . $student["full_name"]); ?></option><?php endforeach; ?></select></label><label>Course<select name="course_id" required><option value="">Choose course</option><?php foreach ($courses as $course): ?><option value="<?php echo (int) $course["id"]; ?>"><?php echo $e($course["code"] . " - " . $course["title"]); ?></option><?php endforeach; ?></select></label><label>Academic year<input name="academic_year" placeholder="2026/2027" required></label><label>Score<input type="number" name="score" min="0" max="100" step="0.01" required></label><label>Grade<input name="grade" maxlength="3" placeholder="A" required></label><button type="submit">Publish result</button></form></section><section class="portal-panel"><h2>Published results</h2><div class="portal-table-wrap"><table class="portal-table"><thead><tr><th>Student</th><th>Course</th><th>Year</th><th>Score</th><th>Grade</th><th>Student details</th></tr></thead><tbody><?php foreach ($results as $result): ?><tr><td><?php echo $e($result["student_number"] . " - " . $result["full_name"]); ?></td><td><?php echo $e($result["code"]); ?></td><td><?php echo $e($result["academic_year"]); ?></td><td><?php echo $e($result["score"]); ?>%</td><td><?php echo $e($result["grade"]); ?></td><td><a href="../admin/students/edit.php?id=<?php echo (int) $result["student_id"]; ?>">Edit student</a></td></tr><?php endforeach; ?></tbody></table></div></section></main></body></html>
