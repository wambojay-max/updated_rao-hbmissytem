<?php
require_once "_bootstrap.php";
$term = $pdo->query("SELECT name FROM academic_terms WHERE is_current = 1 ORDER BY id DESC LIMIT 1")->fetchColumn() ?: date("Y") . "/" . (date("Y") + 1);
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    verifyCsrf();
    $courseId = (int) ($_POST["course_id"] ?? 0);
    $check = $pdo->prepare("SELECT id FROM courses WHERE id = :id AND is_active = 1");
    $check->execute(["id" => $courseId]);
    if (!$check->fetch()) {
        $message = "Select a valid course.";
    } else {
        $save = $pdo->prepare("INSERT INTO course_registrations (student_id, course_id, term, status) VALUES (:student, :course, :term, 'Submitted') ON DUPLICATE KEY UPDATE status = 'Submitted'");
        $save->execute(["student" => $student["id"], "course" => $courseId, "term" => $term]);
        $message = "Course registration submitted for " . $term . ".";
    }
}

$courses = $pdo->query("SELECT * FROM courses WHERE is_active = 1 ORDER BY code")->fetchAll();
$e = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
?>
<!doctype html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Course registration</title><link rel="stylesheet" href="../assets/style.css"></head><body><header class="topbar"><a href="index.php">RAVINE ACADEMIC</a><nav><a href="index.php">Dashboard</a><a href="results.php">Results</a><a href="https://localhost/RAO_HBMIS/auth/login.php">Hostel</a><a href="../auth/logout.php">Log out</a></nav></header><main class="shell"><a href="index.php">&larr; Dashboard</a><p class="eyebrow">ACADEMIC SERVICES</p><h1>Register courses</h1><p class="lead">Current term: <?php echo $e($term); ?></p><?php if ($message): ?><div class="notice"><?php echo $e($message); ?></div><?php endif; ?><section class="panel"><h2>Available courses</h2><?php foreach ($courses as $course): ?><form method="post" class="course-row"><?php echo csrfField(); ?><input type="hidden" name="course_id" value="<?php echo (int) $course["id"]; ?>"><span><b><?php echo $e($course["code"]); ?></b><small><?php echo $e($course["title"]); ?> · <?php echo (int) $course["credits"]; ?> credits</small></span><button>Register</button></form><?php endforeach; ?></section></main></body></html>
