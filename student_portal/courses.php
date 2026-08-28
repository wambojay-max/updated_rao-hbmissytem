<?php
require_once "_bootstrap.php";
$message = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    verifyCsrf();
    $courseId = (int) ($_POST["course_id"] ?? 0);
    $courseStmt = $pdo->prepare("SELECT id FROM courses WHERE id = :id AND is_active = 1");
    $courseStmt->execute(["id" => $courseId]);
    if (!$courseStmt->fetch()) {
        $message = "Please select a valid course.";
    } else {
        $registration = $pdo->prepare("INSERT INTO course_registrations (student_id, course_id, term, status) VALUES (:student_id, :course_id, :term, 'Submitted') ON DUPLICATE KEY UPDATE status = 'Submitted'");
        $registration->execute(["student_id" => $student["id"], "course_id" => $courseId, "term" => date("Y") . "/" . (date("Y") + 1)]);
        $message = "Course registration submitted.";
    }
}
$courses = $pdo->query("SELECT * FROM courses WHERE is_active = 1 ORDER BY code")->fetchAll();
$registeredStmt = $pdo->prepare("SELECT r.*, c.code, c.title, c.credits FROM course_registrations r INNER JOIN courses c ON c.id = r.course_id WHERE r.student_id = :student_id ORDER BY c.code");
$registeredStmt->execute(["student_id" => $student["id"]]);
$registered = $registeredStmt->fetchAll();
?>
<!doctype html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Course registration | RAO HBMIS</title><link rel="stylesheet" href="../assets/css/style.css"></head><body class="portal-body"><header class="portal-topbar"><a class="portal-brand" href="index.php"><span>RAO</span> Student Portal</a><a href="../auth/logout.php">Log out</a></header><main class="portal-main"><p><a href="index.php">&larr; Student portal</a></p><section class="portal-intro"><div><p class="eyebrow">ACADEMICS</p><h1>Register courses</h1><p>Select a course to submit for the current academic term.</p></div></section><?php if ($message): ?><div class="portal-notice"><?php echo $e($message); ?></div><?php endif; ?><section class="portal-panel"><h2>Available courses</h2><div class="course-list"><?php foreach ($courses as $course): ?><form method="post" class="course-row"><?php echo csrfField(); ?><input type="hidden" name="course_id" value="<?php echo (int) $course["id"]; ?>"><span><strong><?php echo $e($course["code"]); ?></strong><small><?php echo $e($course["title"]); ?> · <?php echo $e($course["credits"]); ?> credits</small></span><button type="submit">Register</button></form><?php endforeach; ?></div></section><section class="portal-panel"><p class="eyebrow">YOUR SUBMISSIONS</p><h2>Registered courses</h2><div class="course-list"><?php if (!$registered): ?><p class="empty-state">No courses registered yet.</p><?php else: foreach ($registered as $course): ?><div class="course-row"><span><strong><?php echo $e($course["code"]); ?></strong><small><?php echo $e($course["title"]); ?> · <?php echo $e($course["term"]); ?></small></span><span class="status status-confirmed"><?php echo $e($course["status"]); ?></span></div><?php endforeach; endif; ?></div></section></main></body></html>
