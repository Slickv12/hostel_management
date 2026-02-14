<?php
session_start();
include("db_connect.php");
if (!isset($_SESSION["user_id"]) || $_SESSION["user_type"] !== "rector") {
    header("Location: login.php");
    exit();
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

if ($search !== '') {
    $like = "%{$search}%";
    $sql = "SELECT r.room_number, u.name AS occupant_name
            FROM rooms r
            LEFT JOIN room_allocation ra ON r.room_id = ra.room_id
            LEFT JOIN users u ON ra.user_id = u.user_id
            WHERE r.room_number LIKE ? OR u.name LIKE ?
            ORDER BY r.room_number";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $like, $like);
} else {
    $sql = "SELECT r.room_number, u.name AS occupant_name
            FROM rooms r
            LEFT JOIN room_allocation ra ON r.room_id = ra.room_id
            LEFT JOIN users u ON ra.user_id = u.user_id
            ORDER BY r.room_number";
    $stmt = $conn->prepare($sql);
}

$stmt->execute();
$result = $stmt->get_result();
$rooms = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $room_number = $row['room_number'];
        $occupant_name = $row['occupant_name'] ?: 'Empty';

        if (!isset($rooms[$room_number])) {
            $rooms[$room_number] = [];
        }
        $rooms[$room_number][] = $occupant_name;
    }
}

$stmt->close();
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
    <?php include("rector_sidebar.php"); ?>

    <!-- Main Content -->
    <div class="content">
        <div class="content-box">
            <h2>Room Allocations</h2>

            <form method="GET">
                <label for="search">Search (Room Number / Occupant):</label>
                <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit">Search</button>
            </form>

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
