<?php
include("db_connect.php");

$message = ""; // Initialize message variable

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_POST["user_id"];
    $room_id = $_POST["room_id"];

    // Step 1: Update `users` table to assign the room
    $query = "UPDATE users SET room_id = ? WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $room_id, $user_id);

    if ($stmt->execute()) {
        $stmt->close();

        // Step 2: Insert into `room_allocation` table
        $alloc_query = "INSERT INTO room_allocation (user_id, room_id) VALUES (?, ?)";
        $alloc_stmt = $conn->prepare($alloc_query);
        $alloc_stmt->bind_param("ii", $user_id, $room_id);

        if ($alloc_stmt->execute()) {
            $alloc_stmt->close();

            // ✅ Step 3: Update `current_occupants` count in `rooms` table
            $update_occupants = "UPDATE rooms SET current_occupants = current_occupants + 1 WHERE room_id = ?";
            $update_stmt = $conn->prepare($update_occupants);
            $update_stmt->bind_param("i", $room_id);
            
            if ($update_stmt->execute()) {
                $message = "<div class='message-box success-msg'>✅ Room assigned successfully! Occupants updated.</div>";
            } else {
                $message = "<div class='message-box error-msg'>❌ Failed to update room occupants: " . $conn->error . "</div>";
            }

            $update_stmt->close();
        } else {
            $message = "<div class='message-box error-msg'>❌ Error inserting into room_allocation: " . $conn->error . "</div>";
        }
    } else {
        $message = "<div class='message-box error-msg'>❌ Error updating user room: " . $conn->error . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Room</title>
    <link rel="stylesheet" href="adminstyle.css">  <!-- Linking the CSS file -->
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

    <!-- Content Section -->
    <div class="content">
        <div class="content-box">
            <h2>Assign Room to Student</h2>
            
            <?php if (!empty($message)) echo $message; ?>

            <form method="POST">
                <label>Select Student:</label>
                <select name="user_id" required>
                    <?php
                    $result = $conn->query("SELECT user_id, name FROM users WHERE user_type = 'student' AND room_id IS NULL");
                    while ($row = $result->fetch_assoc()) {
                        echo "<option value='{$row['user_id']}'>{$row['name']}</option>";
                    }
                    ?>
                </select>

                <label>Select Room:</label>
                <select name="room_id" required>
                    <?php
                    $result = $conn->query("SELECT room_id, room_number FROM rooms WHERE current_occupants < capacity");
                    while ($row = $result->fetch_assoc()) {
                        echo "<option value='{$row['room_id']}'>Room #{$row['room_number']}</option>";
                    }
                    ?>
                </select>

                <button type="submit">Assign Room</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>