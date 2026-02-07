<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);


include("db_connect.php");

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$error = "";
$success = ""; // Add this line

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $reason = trim($_POST["reason"]);
    $start_date = $_POST["start_date"];
    $end_date = $_POST["end_date"];
    
    
    if (empty($reason) || empty($start_date) || empty($end_date)) {
        $error = "All fields are required!";
    } elseif ($start_date > $end_date) {
        $error = "Start date cannot be after end date!";
    } else {
        $query = "INSERT INTO leave_requests (user_id, reason, start_date, end_date) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("isss", $user_id, $reason, $start_date, $end_date);

        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }
        

        if ($stmt->execute()) {
            $_SESSION["success_message"] = "Leave request submitted successfully!";
            header("Location: sdashboard.php");
            exit();
        } else {
            $_SESSION["error_message"] = "Error submitting request: " . $stmt->error;
            header("Location: sdashboard.php");
            exit();
        }
        $stmt->close();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Request</title>
    <link rel="stylesheet" href="dashstyle.css">
</head>
<body>

<div class="dashboard-container">
    <!-- Sidebar (same as other pages) -->
     

    <!-- Main Content -->
    <div class="content">
        <div class="content-box">
            <h2>Leave Request</h2>

            <?php if (!empty($error)) echo "<p class='error-msg'>$error</p>"; ?>
            <?php if (!empty($success)) echo "<p class='success-msg'>$success</p>"; ?>

            <form method="POST" action="leave_request.php">
                <label for="reason">Reason:</label>
                <textarea name="reason" id="reason" required></textarea>

                <label for="start_date">Start Date:</label>
                <input type="date" name="start_date" id="start_date" required>

                <label for="end_date">End Date:</label>
                <input type="date" name="end_date" id="end_date" required>

                <button type="submit">Submit Request</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>
