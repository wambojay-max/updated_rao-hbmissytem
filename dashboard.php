<?php

require_once "config/security.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: auth/login.php");
    exit;
}

$full_name = $_SESSION["full_name"];
$role = $_SESSION["role"];

require_once "config/database.php";
$totalStudents = $pdo
    ->query("SELECT COUNT(*) FROM students")
    ->fetchColumn();

$totalRooms = $pdo
    ->query("SELECT COUNT(*) FROM rooms")
    ->fetchColumn();

$availableRooms = $pdo
    ->query("SELECT COUNT(*) FROM rooms WHERE status = 'Available'")
    ->fetchColumn();

$occupiedRooms = $pdo
    ->query("SELECT COUNT(*) FROM rooms WHERE status = 'Occupied'")
    ->fetchColumn();

$totalBookings = $pdo
    ->query("SELECT COUNT(*) FROM bookings")
    ->fetchColumn();

$activeAllocations = $pdo
    ->query("SELECT COUNT(*) FROM allocations WHERE status = 'Active'")
    ->fetchColumn();

$totalPayments = $pdo
    ->query("SELECT COUNT(*) FROM payments")
    ->fetchColumn();

$totalPaid = $pdo
    ->query(
        "SELECT COALESCE(SUM(amount), 0)
         FROM payments
         WHERE status = 'Completed'"
    )
    ->fetchColumn();

?>

<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Dashboard - RAO Hostel Booking and Management Information System</title>

    <link rel="stylesheet" href="assets/css/style.css">

</head>

<body>

    <!-- Sidebar -->

    <div class="sidebar">

        <h2>RAO Hostel Booking and Management Information System</h2>

        <div class="sidebar-user">
            <span class="sidebar-user-label">Signed in as</span>
            <strong><?php echo htmlspecialchars($full_name); ?></strong>
            <span><?php echo htmlspecialchars(ucfirst($role)); ?></span>
        </div>

        <a href="dashboard.php">
            Dashboard
        </a>

        <div class="section-title">
            Hostel Management
        </div>

        <a href="admin/students/index.php">
            Students
        </a>

        <a href="admin/rooms/index.php">
            Rooms
        </a>

        <a href="admin/bookings/index.php">
            Bookings
        </a>

        <a href="admin/allocations/index.php">
            Allocations
        </a>

       <a href="admin/payments/index.php">
    Payments
</a>

<div class="section-title">
    Reports
</div>

<a href="admin/reports/index.php">
    Reports
</a>

<div class="section-title">
    System
</div>

        <a href="admin/users/index.php">
            Users
        </a>

        <div class="logout">

            <a href="auth/logout.php">
                Logout
            </a>

        </div>

    </div>


    <!-- Main Content -->

    <div class="main-content">

        <div class="header">

            <h1>Dashboard</h1>

            <p>
                Welcome,
                <strong>
                    <?php echo htmlspecialchars($full_name); ?>
                </strong>
            </p>

            <p>
                Role:
                <strong>
                    <?php echo htmlspecialchars($role); ?>
                </strong>
            </p>

        </div>


        <!-- Statistics -->

        <h2>Hostel Overview</h2>

        <div class="stats">

            <div class="card">

                <h3>Total Students</h3>

                <p>
                    <?php echo $totalStudents; ?>
                </p>

            </div>


            <div class="card">

                <h3>Total Rooms</h3>

                <p>
                    <?php echo $totalRooms; ?>
                </p>

            </div>


            <div class="card">

                <h3>Available Rooms</h3>

                <p>
                    <?php echo $availableRooms; ?>
                </p>

            </div>


            <div class="card">

                <h3>Occupied Rooms</h3>

                <p>
                    <?php echo $occupiedRooms; ?>
                </p>

            </div>


            <div class="card">

                <h3>Total Bookings</h3>

                <p>
                    <?php echo $totalBookings; ?>
                </p>

            </div>


            <div class="card">

                <h3>Active Allocations</h3>

                <p>
                    <?php echo $activeAllocations; ?>
                </p>

            </div>


            <div class="card">

                <h3>Total Payments</h3>

                <p>
                    <?php echo $totalPayments; ?>
                </p>

            </div>


            <div class="card">

                <h3>Total Amount Paid</h3>

                <p>
                    KSh <?php echo number_format($totalPaid, 2); ?>
                </p>

            </div>

        </div>


        <!-- Management -->

        <div class="management">

            <h2>Hostel Management</h2>

            <p>
                Use the navigation menu to manage
                students, rooms, bookings, allocations,
                payments and system users.
            </p>

        </div>

        <div class="ai-grid">
            <section class="ai-panel">
                <h2>AI Hostel Assistant</h2>
                <p>Ask about current hostel activity and records.</p>
                <form id="assistant-form">
                    <?php echo csrfField(); ?>
                    <label for="assistant-question">Question</label>
                    <textarea id="assistant-question" name="question" maxlength="500" required
                              placeholder="Which rooms are available right now?"></textarea>
                    <button type="submit">Ask assistant</button>
                </form>
                <div id="assistant-answer" class="ai-result" aria-live="polite"></div>
            </section>

            <section class="ai-panel">
                <h2>Room Recommendations</h2>
                <p>Generate suggestions for confirmed bookings awaiting allocation.</p>
                <button id="recommend-rooms" type="button">Recommend rooms</button>
                <p><a href="admin/allocations/ai_approvals.php">Review saved suggestions</a></p>
                <div id="room-recommendations" class="ai-result" aria-live="polite"></div>
            </section>
        </div>

    </div>

    <script>
        const showResult = (element, message) => {
            element.textContent = message;
        };

        document.getElementById("assistant-form").addEventListener("submit", async (event) => {
            event.preventDefault();
            const result = document.getElementById("assistant-answer");
            showResult(result, "Thinking...");
            try {
                const response = await fetch("api/assistant.php", {
                    method: "POST",
                    body: new FormData(event.target)
                });
                const data = await response.json();
                showResult(result, data.answer || data.error || "No answer received.");
            } catch (error) {
                showResult(result, "The assistant is unavailable.");
            }
        });

        document.getElementById("recommend-rooms").addEventListener("click", async (event) => {
            const result = document.getElementById("room-recommendations");
            event.target.disabled = true;
            showResult(result, "Finding suitable rooms...");
            try {
                const response = await fetch("api/room_recommendations.php");
                const data = await response.json();
                if (data.error || data.message) {
                    showResult(result, data.error || data.message);
                    return;
                }
                result.replaceChildren();
                if (!data.recommendations.length) {
                    result.textContent = "No recommendations found.";
                    return;
                }
                data.recommendations.forEach((item) => {
                    const paragraph = document.createElement("p");
                    paragraph.append(
                        document.createTextNode(`${item.student_id} -> Room ${item.room_number}`),
                        document.createElement("br"),
                        document.createTextNode(item.reason || "No reason provided.")
                    );
                    result.appendChild(paragraph);
                });
            } catch (error) {
                showResult(result, "Recommendations are unavailable.");
            } finally {
                event.target.disabled = false;
            }
        });
    </script>

</body>
</html>