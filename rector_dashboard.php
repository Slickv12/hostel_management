<?php
session_start();
include("db_connect.php");

if (!isset($_SESSION["user_id"]) || $_SESSION["user_type"] !== 'rector') {
    header("Location: login.php");
    exit();
}

$rector_name = $_SESSION["username"];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rector Dashboard</title>
    <link rel="stylesheet" href="adminstyle.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<div class="dashboard-container">
    <div class="sidebar">
        <h2>Welcome rector</h2>
        <ul>
            <li><a href="approve_students.php">Approve Students</a></li>
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

    <div class="content" id="content-area">
        <h2>Welcome to Your Hostel <?php echo $rector_name; ?>!</h2>
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
