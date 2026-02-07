<?php
session_start();
include("db_connect.php");
if (!isset($_SESSION["user_id"]) || $_SESSION["user_type"] !== "admin") {
    header("Location: login.php");
    exit();
}


$sql = "SELECT r.room_number, u.name AS occupant_name
        FROM rooms r
        LEFT JOIN room_allocation ra ON r.room_id = ra.room_id
        LEFT JOIN users u ON ra.user_id = u.user_id
        ORDER BY r.room_number";

$result = $conn->query($sql);
$rooms = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $room_number = $row['room_number'];
        $occupant_name = $row['occupant_name'] ?: 'Empty'; // Show "Empty" if no occupant

        if (!isset($rooms[$room_number])) {
            $rooms[$room_number] = [];
        }
        $rooms[$room_number][] = $occupant_name;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check Rooms</title>
    <link rel="stylesheet" href="adminstyle.css"> <!-- Ensure this CSS file exists -->
</head>
<body>

<div class="dashboard-container">
    <!-- Sidebar -->
    <div class="sidebar">
        <h2>Admin Panel</h2>
        <ul>
            
        <li><a href="check_rooms.php">Check Rooms</a></li>
            <li><a href="check_students.php">Check Students</a></li>
            <li><a href="manage_students.php">Manage Students</a></li>
            <li><a href="send_notices.php">Send Notice</a></li>
            <li><a href="approve_leave.php">Approve Leave</a></li>
            <li><a href="assign_room.php">Assign rooms</a></li>
            <li><a href="update_fees.php">Update fees</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="content">
        <div class="content-box">
            <h2>Room Allocations</h2>
            <table border="1">
                <thead>
                    <tr>
                        <th>Room Number</th>
                        <th>Occupants</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($rooms)): ?>
                        <?php foreach ($rooms as $room_number => $occupants): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($room_number); ?></td>
                                <td>
                                    <?php echo implode(", ", array_map('htmlspecialchars', $occupants)); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="2">No room data available</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>