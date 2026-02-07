<?php
session_start();
include("db_connect.php");

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

// Fetch room details
$query = "
    SELECT r.room_number, u.name, u.phone
    FROM room_allocation ra
    JOIN rooms r ON ra.room_id = r.room_id
    JOIN users u ON ra.user_id = u.user_id
    WHERE ra.room_id = (SELECT room_id FROM room_allocation WHERE user_id = ?)
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$roommates = [];
$room_number = "Not Assigned";

while ($row = $result->fetch_assoc()) {
    if (isset($_SESSION["username"]) && $row["name"] == $_SESSION["username"]) {
        $room_number = $row["room_number"];
    }
    $roommates[] = $row;
}

$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Room Details</title>
    <link rel="stylesheet" href="dashstyle.css">
</head>
<body>

<div class="dashboard-container">
    <!-- Sidebar -->


    <!-- Main Content -->
    <div class="content"> <!-- This ensures proper centering -->
        <div class="content-box">  <!-- Single Box Wrapper -->
            <h2>My Room Details</h2>
            <p><strong>Room Number:</strong> <?php echo $room_number; ?></p>

            <h3>Roommates</h3>
            <table>
                <tr>
                    <th>Name</th>
                    <th>Phone Number</th>
                </tr>
                <?php foreach ($roommates as $mate): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($mate["name"]); ?></td>
                        <td><?php echo htmlspecialchars($mate["phone"]); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
</div>

</body>
</html>
