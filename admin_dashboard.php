<?php
session_start();
include("db_connect.php");

if (!isset($_SESSION["user_id"]) || $_SESSION["user_type"] !== 'admin') {
    header("Location: login.php");
    exit();
}

$admin_name = $_SESSION["username"];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="assets/css/base.css">
    <?php
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (isset($_SESSION['user_type'])) {
        if ($_SESSION['user_type'] === 'rector') {
            echo '<link rel="stylesheet" href="assets/css/rector.css">';
        } elseif ($_SESSION['user_type'] === 'student') {
            echo '<link rel="stylesheet" href="assets/css/student.css">';
        }
    }
    ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<div class="dashboard-container">
    <!-- Sidebar -->
    <div class="sidebar">
        <h2>Welcome admin</h2>
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
    <div class="content" id="content-area">
            <h2>Welcome to Your Hostel <?php echo $_SESSION['username']; ?>!</h2>
            <p>Select an option from the menu to view details.</p>
        </div>
    </div>

    <script>
        function loadContent(page) {
            $("#content-area").load(page);
        }
    </script>
</body>
</html>