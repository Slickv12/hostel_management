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
    <?php include("rector_sidebar.php"); ?>

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
