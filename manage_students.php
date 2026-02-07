<?php
session_start();
include("db_connect.php");

// Handle Remove Student Request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['remove_student'])) {
    $user_id = $_POST['user_id'];

    // Check if student exists
    $check_sql = "SELECT * FROM users WHERE user_id = ? AND user_type = 'student'";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Delete from users table (CASCADE will also remove room allocation)
        $delete_sql = "DELETE FROM users WHERE user_id = ?";
        $stmt = $conn->prepare($delete_sql);
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            $message = "Student removed successfully!";
        } else {
            $message = "Error removing student.";
        }
    } else {
        $message = "Student not found!";
    }
}

// Handle Update Room Request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_room'])) {
    $user_id = $_POST['user_id'];
    $room_id = $_POST['room_id'];

    // Check if student exists
    $check_sql = "SELECT * FROM users WHERE user_id = ? AND user_type = 'student'";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Check if room exists
        $check_room_sql = "SELECT * FROM rooms WHERE room_id = ?";
        $stmt = $conn->prepare($check_room_sql);
        $stmt->bind_param("i", $room_id);
        $stmt->execute();
        $room_result = $stmt->get_result();

        if ($room_result->num_rows > 0) {
            // Update room_allocation table
            $update_sql = "UPDATE room_allocation SET room_id = ? WHERE user_id = ?";
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param("ii", $room_id, $user_id);
            if ($stmt->execute()) {
                $message = "Room updated successfully!";
            } else {
                $message = "Error updating room.";
            }
        } else {
            $message = "Invalid Room ID!";
        }
    } else {
        $message = "Student not found!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students</title>
    <link rel="stylesheet" href="adminstyle.css"> <!-- Use admin dashboard CSS -->
</head>
<body>

<div class="dashboard-container">
    <!-- Sidebar -->
    <div class="sidebar">
        <h2>Admin Dashboard</h2>
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

    <!-- Content -->
    <div class="content">
        <h2>Manage Students</h2>

        <!-- Success/Error Message -->
        <?php if (isset($message)): ?>
    <div class="message-box <?php echo (strpos($message, 'successfully') !== false) ? 'success-msg' : 'error-msg'; ?>" id="messageBox" onclick="this.style.display='none';">
        <?php echo $message; ?>
    </div>

    <script>
        // Auto-hide message box after 3 seconds
        setTimeout(function() {
            document.getElementById('messageBox').style.display = 'none';
        }, 3000);
    </script>
<?php endif; ?>

        <!-- Remove Student Form -->
        <div class="content-box">
            <h3>Remove Student</h3>
            <form method="POST">
                <label>User ID:</label>
                <input type="number" name="user_id" required>
                <button type="submit" name="remove_student">Remove Student</button>
            </form>
       
            <h3>Update Room</h3>
            <form method="POST">
                <label>User ID:</label>
                <input type="number" name="user_id" required>
                <label>New Room ID:</label>
                <input type="number" name="room_id" required>
                <button type="submit" name="update_room">Update Room</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>
