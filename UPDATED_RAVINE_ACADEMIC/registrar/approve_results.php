<?php
require_once "../config/security.php";
require_once "../config/database.php";
if (!isset($_SESSION["user_id"]) || !in_array($_SESSION["role"], ["admin"], true)) { header("Location: ../auth/login.php"); exit; }
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    verifyCsrf();
    $id = (int) ($_POST["id"] ?? 0);
    $status = $_POST["status"] ?? "";
    if (in_array($status, ["Published", "Rejected"], true)) {
        $stmt = $pdo->prepare("UPDATE student_results SET status = :status, reviewed_by = :reviewed_by, reviewed_at = NOW() WHERE id = :id AND status = 'Draft'");
        $stmt->execute(["status" => $status, "reviewed_by" => $_SESSION["user_id"], "id" => $id]);
    }
    header("Location: approve_results.php"); exit;
}
$results = $pdo->query("SELECT r.*, s.student_id, s.full_name, c.code, c.title FROM student_results r INNER JOIN students s ON s.id = r.student_id INNER JOIN courses c ON c.id = r.course_id WHERE r.status = 'Draft' ORDER BY r.id DESC")->fetchAll();
$e = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
?>
<!doctype html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Approve results</title><link rel="stylesheet" href="../assets/style.css"></head><body><header class="topbar"><a href="approve_results.php">RAVINE ACADEMIC</a><nav><a href="../auth/logout.php">Log out</a></nav></header><main class="shell"><p class="eyebrow">REGISTRAR REVIEW</p><h1>Approve results</h1><p><a href="../student/index.php">Student dashboard</a></p><section class="panel"><table><thead><tr><th>Student</th><th>Course</th><th>Academic year</th><th>Score</th><th>Grade</th><th>Action</th></tr></thead><tbody><?php if (!$results): ?><tr><td colspan="6">No results are waiting for approval.</td></tr><?php else: foreach ($results as $result): ?><tr><td><?php echo $e($result["student_id"] . " - " . $result["full_name"]); ?></td><td><?php echo $e($result["code"] . " - " . $result["title"]); ?></td><td><?php echo $e($result["academic_year"]); ?></td><td><?php echo $e($result["score"]); ?>%</td><td><?php echo $e($result["grade"]); ?></td><td><form method="post"><?php echo csrfField(); ?><input type="hidden" name="id" value="<?php echo (int) $result["id"]; ?>"><button name="status" value="Published">Approve</button> <button name="status" value="Rejected">Reject</button></form></td></tr><?php endforeach; endif; ?></tbody></table></section></main></body></html>
