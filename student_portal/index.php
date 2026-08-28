<?php require_once "_bootstrap.php"; ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Portal | RAO HBMIS</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="portal-body">
<header class="portal-topbar"><a class="portal-brand" href="index.php"><span>RAO</span> Student Portal</a><div class="portal-user"><span><?php echo $e($student["full_name"]); ?></span><a href="../auth/logout.php">Log out</a></div></header>
<main class="portal-main">
    <section class="portal-intro"><div><p class="eyebrow">STUDENT SERVICES</p><h1>Welcome, <?php echo $e(explode(" ", $student["full_name"])[0]); ?>.</h1><p>Manage your academic journey and accommodation from one place.</p></div><div class="student-code">Student ID<strong><?php echo $e($student["student_id"]); ?></strong></div></section>
    <div class="student-service-grid">
        <a class="student-service-card" href="courses.php"><span class="service-number">01</span><h2>Register courses</h2><p>Choose and submit your courses for the current academic term.</p><strong>Open registration &rarr;</strong></a>
        <a class="student-service-card" href="results.php"><span class="service-number">02</span><h2>View results</h2><p>Check your published examination results and academic record.</p><strong>View academic record &rarr;</strong></a>
        <a class="student-service-card service-accommodation" href="accommodation.php"><span class="service-number">03</span><h2>Hostel accommodation</h2><p>Connect to RAO Hostel to request a room and track your booking.</p><strong>Open hostel services &rarr;</strong></a>
    </div>
    <section class="portal-panel portal-summary"><p class="eyebrow">ACCOUNT</p><h2><?php echo $e($student["course"]); ?></h2><p>Year <?php echo $e($student["year_of_study"]); ?> student · <?php echo $e($student["email"]); ?></p></section>
</main>
</body>
</html>
