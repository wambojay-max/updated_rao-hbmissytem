<?php

require_once "../../auth/check_role.php";

requireRole(["admin", "warden", "staff"]);

require_once "../../config/database.php";

/* Students */
$totalStudents = $pdo
    ->query("SELECT COUNT(*) FROM students")
    ->fetchColumn();

/* Rooms */
$totalRooms = $pdo
    ->query("SELECT COUNT(*) FROM rooms")
    ->fetchColumn();

$availableRooms = $pdo
    ->query(
        "SELECT COUNT(*)
         FROM rooms
         WHERE status = 'Available'"
    )
    ->fetchColumn();

$occupiedRooms = $pdo
    ->query(
        "SELECT COUNT(*)
         FROM rooms
         WHERE status = 'Occupied'"
    )
    ->fetchColumn();

/* Bookings */
$totalBookings = $pdo
    ->query("SELECT COUNT(*) FROM bookings")
    ->fetchColumn();

$confirmedBookings = $pdo
    ->query(
        "SELECT COUNT(*)
         FROM bookings
         WHERE status = 'Confirmed'"
    )
    ->fetchColumn();

/* Allocations */
$totalAllocations = $pdo
    ->query("SELECT COUNT(*) FROM allocations")
    ->fetchColumn();

$activeAllocations = $pdo
    ->query(
        "SELECT COUNT(*)
         FROM allocations
         WHERE status = 'Active'"
    )
    ->fetchColumn();

/* Payments */
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

    <title>Reports - RAO Hostel Booking and Management Information System</title>

    <link rel="stylesheet" href="../../assets/css/style.css">

</head>

<body>

    <div class="sidebar">

        <h2>RAO Hostel Booking and Management Information System</h2>

        <a href="../../dashboard.php">
            Dashboard
        </a>

        <div class="section-title">
            Hostel Management
        </div>

        <a href="../students/index.php">
            Students
        </a>

        <a href="../rooms/index.php">
            Rooms
        </a>

        <a href="../bookings/index.php">
            Bookings
        </a>

        <a href="../allocations/index.php">
            Allocations
        </a>

        <a href="../payments/index.php">
            Payments
        </a>

        <div class="section-title">
            Reports
        </div>

        <a href="index.php">
            Reports
        </a>

        <div class="section-title">
            System
        </div>

        <a href="../users/index.php">
            Users
        </a>

        <div class="logout">

            <a href="../../auth/logout.php">
                Logout
            </a>

        </div>

    </div>


    <div class="main-content">

        <div class="header">

            <h1>Reports</h1>

            <p>
                RAO Hostel Booking and Management Information System reports and summaries.
            </p>

        </div>

        <p>Download spreadsheets:
            <a href="export.php?type=students">Students</a> |
            <a href="export.php?type=rooms">Rooms</a> |
            <a href="export.php?type=bookings">Bookings</a> |
            <a href="export.php?type=payments">Payments</a>
        </p>


        <h2>Students</h2>

        <div class="stats">

            <div class="card">

                <h3>Total Students</h3>

                <p>
                    <?php echo $totalStudents; ?>
                </p>

            </div>

        </div>


        <h2>Rooms</h2>

        <div class="stats">

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

        </div>


        <h2>Bookings</h2>

        <div class="stats">

            <div class="card">

                <h3>Total Bookings</h3>

                <p>
                    <?php echo $totalBookings; ?>
                </p>

            </div>


            <div class="card">

                <h3>Confirmed Bookings</h3>

                <p>
                    <?php echo $confirmedBookings; ?>
                </p>

            </div>

        </div>


        <h2>Allocations</h2>

        <div class="stats">

            <div class="card">

                <h3>Total Allocations</h3>

                <p>
                    <?php echo $totalAllocations; ?>
                </p>

            </div>


            <div class="card">

                <h3>Active Allocations</h3>

                <p>
                    <?php echo $activeAllocations; ?>
                </p>

            </div>

        </div>


        <h2>Payments</h2>

        <div class="stats">

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


        <h2>Detailed Reports</h2>

<div class="stats">

    <div class="card">

        <h3>Student Report</h3>

        <p>
            View all registered students.
        </p>

        <a href="students/index.php">
            View Report →
        </a>

    </div>


    <div class="card">

        <h3>Room Report</h3>

        <p>
            View rooms and their status.
        </p>

        <a href="rooms/index.php">
            View Report →
        </a>

    </div>


    <div class="card">

        <h3>Booking Report</h3>

        <p>
            View hostel bookings.
        </p>

        <a href="bookings/index.php">
            View Report →
        </a>

    </div>


    <div class="card">

        <h3>Allocation Report</h3>

        <p>
            View student room allocations.
        </p>

        <a href="allocations/index.php">
            View Report →
        </a>

    </div>


    <div class="card">

        <h3>Payment Report</h3>

        <p>
            View recorded payments.
        </p>

        <a href="payments/index.php">
            View Report →
        </a>

    </div>

</div>


<br>

<a href="../../dashboard.php">
    ← Back to Dashboard
</a>
    </div>

</body>

</html>