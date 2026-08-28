<?php

require_once "../../auth/check_role.php";

requireRole(["admin", "warden", "staff"]);

require_once "../../config/database.php";

$search = trim($_GET["q"] ?? "");
$page = max(1, (int) ($_GET["page"] ?? 1));
$perPage = 10;
$where = $search === "" ? "" : "WHERE student_id LIKE :search OR full_name LIKE :search OR email LIKE :search OR course LIKE :search";
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM students $where");
if ($search !== "") $countStmt->execute(["search" => "%$search%"]); else $countStmt->execute();
$totalPages = max(1, (int) ceil($countStmt->fetchColumn() / $perPage));
$page = min($page, $totalPages);
$stmt = $pdo->prepare("SELECT * FROM students $where ORDER BY id DESC LIMIT :limit OFFSET :offset");
if ($search !== "") $stmt->bindValue(":search", "%$search%", PDO::PARAM_STR);
$stmt->bindValue(":limit", $perPage, PDO::PARAM_INT);
$stmt->bindValue(":offset", ($page - 1) * $perPage, PDO::PARAM_INT);
$stmt->execute();

$students = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Students - RAO Hostel Booking and Management Information System</title>
</head>

<body>

    <h1>RAO Hostel Booking and Management Information System</h1>

    <h2>Students</h2>

    <p>
        Welcome,
        <strong><?php echo htmlspecialchars($_SESSION["full_name"]); ?></strong>
    </p>

    <a href="add.php">+ Add Student</a>

    <form method="GET">
        <input type="search" name="q" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search students">
        <button type="submit">Search</button>
    </form>

    <br><br>

    <table border="1" cellpadding="8" cellspacing="0">

        <tr>
            <th>#</th>
            <th>Student ID</th>
            <th>Full Name</th>
            <th>Gender</th>
            <th>Phone</th>
            <th>Email</th>
            <th>Course</th>
            <th>Year</th>
            <th>Actions</th>
        </tr>

        <?php if (count($students) > 0): ?>

            <?php foreach ($students as $student): ?>

                <tr>

                    <td>
                        <?php echo htmlspecialchars($student["id"]); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($student["student_id"]); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($student["full_name"]); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($student["gender"]); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($student["phone"]); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($student["email"]); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($student["course"]); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($student["year_of_study"]); ?>
                    </td>

                    <td>

    <a href="edit.php?id=<?php echo $student['id']; ?>">
        Edit
    </a>

    &nbsp; | &nbsp;

    <form method="POST" action="delete.php" style="display:inline;">
        <?php echo csrfField(); ?>

        <input type="hidden"
               name="id"
               value="<?php echo $student['id']; ?>">

        <button type="submit">
            Delete
        </button>

    </form>

</td>

                </tr>

            <?php endforeach; ?>

        <?php else: ?>

            <tr>
                <td colspan="9">
                    No students found.
                </td>
            </tr>

        <?php endif; ?>

    </table>

    <p>Page <?php echo $page; ?> of <?php echo $totalPages; ?>
        <?php if ($page > 1): ?><a href="?q=<?php echo urlencode($search); ?>&page=<?php echo $page - 1; ?>">Previous</a><?php endif; ?>
        <?php if ($page < $totalPages): ?><a href="?q=<?php echo urlencode($search); ?>&page=<?php echo $page + 1; ?>">Next</a><?php endif; ?>
    </p>

    <br>

    <a href="../../dashboard.php">← Back to Dashboard</a>

</body>
</html>