<?php
include("db_connect.php");
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
    header("Location: login.php");
    exit();
}

if (isset($_SESSION["success_message"]) || isset($_SESSION["error_message"])): ?>
    <div class="message-box <?= isset($_SESSION["success_message"]) ? 'success-msg' : 'error-msg' ?>" onclick="this.style.display='none';">
        <?php
        if (isset($_SESSION["success_message"])) {
            echo $_SESSION["success_message"];
            unset($_SESSION["success_message"]);
        }
        if (isset($_SESSION["error_message"])) {
            echo $_SESSION["error_message"];
            unset($_SESSION["error_message"]);
        }
        ?>
    </div>
<?php endif;

$user_id = $_SESSION["user_id"];

// Fetch room details
$query = "SELECT room_id FROM room_allocation WHERE user_id = ? LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($room_id);
$stmt->fetch();
$stmt->close();

if (!$room_id) { // Only show message when no room is assigned
    echo "<div class='room-error'>No information found. Contact the admin.</div>";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="dashstyle.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar Navigation -->
        <div class="sidebar">
            <h2>Welcome, <?php echo $_SESSION['username']; ?></h2>
            <ul>
                <li><a href="#" onclick="loadContent('my_room_details.php')">My Room Details</a></li>
                <li><a href="#" onclick="loadContent('fee_status.php')">Fees Status</a></li>
                <li><a href="#" onclick="loadContent('notices.php')">Notices</a></li>
                <li><a href="#" onclick="loadContent('leave_request.php')">Leave Request</a></li>
                <li><a href="#" onclick="loadContent('request_status.php')">Request status</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>

        <!-- Main Content Area -->
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
