<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include("db_connect.php");

if (!isset($_SESSION["user_id"]) || $_SESSION['user_type'] !== 'student') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $reason = trim($_POST["reason"]);
    $start_date = $_POST["start_date"];
    $end_date = $_POST["end_date"];

    if (empty($reason) || empty($start_date) || empty($end_date)) {
        $error = "All fields are required!";
    } elseif ($start_date > $end_date) {
        $error = "Start date cannot be after end date!";
    } else {
        // Prevent overlap with pending or approved leaves for this student
        $overlap_sql = "
            SELECT request_id
            FROM leave_requests
            WHERE user_id = ?
              AND status IN ('pending', 'approved')
              AND (start_date <= ? AND end_date >= ?)
            LIMIT 1
        ";
        $overlap_stmt = $conn->prepare($overlap_sql);
        $overlap_stmt->bind_param("iss", $user_id, $end_date, $start_date);
        $overlap_stmt->execute();
        $overlap_result = $overlap_stmt->get_result();

        if ($overlap_result->num_rows > 0) {
            $error = "Leave dates overlap with an existing pending/approved request.";
        } else {
            $query = "INSERT INTO leave_requests (user_id, reason, start_date, end_date) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("isss", $user_id, $reason, $start_date, $end_date);

            if ($stmt->execute()) {
                $success = "Leave request submitted successfully!";
            } else {
                $error = "Error submitting request: " . $stmt->error;
            }
            $stmt->close();
        }

        $overlap_stmt->close();
    }
}
?>

<div class="content-box">
    <h2>Leave Request</h2>

    <?php if (!empty($error)) echo "<p class='error-msg'>" . htmlspecialchars($error) . "</p>"; ?>
    <?php if (!empty($success)) echo "<p class='success-msg'>" . htmlspecialchars($success) . "</p>"; ?>

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
