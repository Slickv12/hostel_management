<?php
session_start();
include("db_connect.php");

// Check if the admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$admin_id = $_SESSION['user_id'];
$message = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $notice_message = trim($_POST['message']);

    if (!empty($title) && !empty($notice_message)) {
        // Insert notice into database
        $sql = "INSERT INTO notices (admin_id, title, message) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iss", $admin_id, $title, $notice_message);

        if ($stmt->execute()) {
            $message = "Notice sent successfully!";
        } else {
            $message = "Error: " . $conn->error;
        }

        $stmt->close();
    } else {
        $message = "Please fill in all fields.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Notices</title>
    <link rel="stylesheet" href="adminstyle.css">
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
        <h2>Send Notice</h2>
        <div class="content-box">
            <form method="POST" action="">
                <label for="message">Message:</label>
                <textarea id="message" name="message" rows="1" required></textarea>

                <button type="submit">Send Notice</button>
            </form>

            <!-- Success/Error Message -->
            <?php if (!empty($message)): ?>
                <div class="message-box <?php echo (strpos($message, 'successfully') !== false) ? 'success-msg' : 'error-msg'; ?>" id="messageBox" onclick="this.style.display='none';">
                    <?php echo $message; ?>
                </div>

                <script>
                    setTimeout(function() {
                        document.getElementById('messageBox').style.display = 'none';
                    }, 3000);
                </script>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>
