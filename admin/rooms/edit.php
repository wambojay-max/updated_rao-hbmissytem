<?php

require_once "../../auth/check_role.php";

requireRole(["admin", "warden"]);

require_once "../../config/database.php";

$id = $_GET["id"] ?? null;

if (!$id) {
    header("Location: index.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = :id");
$stmt->execute(["id" => $id]);

$room = $stmt->fetch();

if (!$room) {
    die("Room not found.");
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $room_number = trim($_POST["room_number"]);
    $room_type = trim($_POST["room_type"]);
    $capacity = (int) $_POST["capacity"];
    $floor = (int) $_POST["floor"];
    $gender = trim($_POST["gender"]);
    $status = trim($_POST["status"]);
if (
    $room_number === "" ||
    $room_type === "" ||
    $capacity <= 0 ||
    $floor < 0 ||
    $gender === "" ||
    $status === ""
) {

    $message = "Please fill in all fields correctly.";

} elseif (strlen($room_number) > 20) {

    $message = "Room number must not exceed 20 characters.";

} elseif (!in_array($room_type, ["Single", "Double", "Triple", "Shared"], true)) {

    $message = "Invalid room type selected.";

} elseif ($capacity < 1 || $capacity > 20) {

    $message = "Room capacity must be between 1 and 20.";

} elseif ($floor > 100) {

    $message = "Invalid floor number.";

} elseif (!in_array($gender, ["Male", "Female"], true)) {

    $message = "Invalid gender selected.";

} elseif (!in_array($status, ["Available", "Occupied", "Maintenance"], true)) {

    $message = "Invalid room status.";

} else {

        $sql = "UPDATE rooms SET
                room_number = :room_number,
                room_type = :room_type,
                capacity = :capacity,
                floor = :floor,
                gender = :gender,
                status = :status
                WHERE id = :id";

        $stmt = $pdo->prepare($sql);

        try {

            $stmt->execute([
                "room_number" => $room_number,
                "room_type" => $room_type,
                "capacity" => $capacity,
                "floor" => $floor,
                "gender" => $gender,
                "status" => $status,
                "id" => $id
            ]);

            header("Location: index.php");
            exit;

        } catch (PDOException $e) {

            if ($e->getCode() == 23000) {
                $message = "Room number already exists.";
            } else {
                $message = "Failed to update room.";
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

    <title>Edit Room - RAO Hostel Booking and Management Information System</title>
</head>

<body>

    <h1>RAO Hostel Booking and Management Information System</h1>

    <h2>Edit Room</h2>

    <?php if ($message !== ""): ?>

        <p>
            <?php echo htmlspecialchars($message); ?>
        </p>

    <?php endif; ?>

    <form method="POST">
        <?php echo csrfField(); ?>

        <label>Room Number</label><br>
        <input
            type="text"
            name="room_number"
            value="<?php echo htmlspecialchars($room["room_number"]); ?>"
            required
        >

        <br><br>

        <label>Room Type</label><br>

        <select name="room_type" required>

            <option value="Single" <?php echo $room["room_type"] === "Single" ? "selected" : ""; ?>>
                Single
            </option>

            <option value="Double" <?php echo $room["room_type"] === "Double" ? "selected" : ""; ?>>
                Double
            </option>

            <option value="Triple" <?php echo $room["room_type"] === "Triple" ? "selected" : ""; ?>>
                Triple
            </option>

            <option value="Shared" <?php echo $room["room_type"] === "Shared" ? "selected" : ""; ?>>
                Shared
            </option>

        </select>

        <br><br>

        <label>Capacity</label><br>
        <input
            type="number"
            name="capacity"
            min="1"
            value="<?php echo htmlspecialchars($room["capacity"]); ?>"
            required
        >

        <br><br>

        <label>Floor</label><br>
        <input
            type="number"
            name="floor"
            min="0"
            value="<?php echo htmlspecialchars($room["floor"]); ?>"
            required
        >

        <br><br>

        <label>Gender</label><br>

        <select name="gender" required>

            <option value="Male" <?php echo $room["gender"] === "Male" ? "selected" : ""; ?>>
                Male
            </option>

            <option value="Female" <?php echo $room["gender"] === "Female" ? "selected" : ""; ?>>
                Female
            </option>

        </select>

        <br><br>

        <label>Status</label><br>

        <select name="status" required>

            <option value="Available" <?php echo $room["status"] === "Available" ? "selected" : ""; ?>>
                Available
            </option>

            <option value="Occupied" <?php echo $room["status"] === "Occupied" ? "selected" : ""; ?>>
                Occupied
            </option>

            <option value="Maintenance" <?php echo $room["status"] === "Maintenance" ? "selected" : ""; ?>>
                Maintenance
            </option>

        </select>

        <br><br>

        <button type="submit">Update Room</button>

    </form>

    <br>

    <a href="index.php">← Back to Rooms</a>

</body>

</html>