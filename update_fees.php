<?php
session_start();
include("db_connect.php"); // Database connection

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'rector') {
    header("Location: login.php");
    exit();
}

$message = ""; // To show success/error messages

// Check if the form is submitted to update fees
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update_fee"])) {
    $user_id = intval($_POST["user_id"]);
    $amount_due = $_POST["amount_due"];
    $due_date = $_POST["due_date"];
    $status = $_POST["status"];
    $actor_user_id = intval($_SESSION['user_id']);

    // Validate target user is an existing student
    $user_check_query = "SELECT user_id FROM users WHERE user_id = ? AND user_type = 'student'";
    $user_stmt = $conn->prepare($user_check_query);
    $user_stmt->bind_param("i", $user_id);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();

    if ($user_result->num_rows !== 1) {
        $message = "<div class='message-box error-msg' onclick='this.style.display=\"none\";'>Invalid student user ID.</div>";
    } else {
        $conn->begin_transaction();

        $update_query = "UPDATE fees SET amount_due = ?, due_date = ?, status = ? WHERE user_id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("dssi", $amount_due, $due_date, $status, $user_id);

        if ($stmt->execute()) {
            $log_query = "INSERT INTO activity_logs (action_type, actor_user_id, target_user_id, metadata) VALUES ('fee_update', ?, ?, NULL)";
            $log_stmt = $conn->prepare($log_query);
            $log_stmt->bind_param("ii", $actor_user_id, $user_id);

            if ($log_stmt->execute()) {
                $conn->commit();
                $message = "<div class='message-box success-msg' onclick='this.style.display=\"none\";'>Fee details updated successfully!</div>";
            } else {
                $conn->rollback();
                $message = "<div class='message-box error-msg' onclick='this.style.display=\"none\";'>Error logging fee update.</div>";
            }

            $log_stmt->close();
        } else {
            $conn->rollback();
            $message = "<div class='message-box error-msg' onclick='this.style.display=\"none\";'>Error updating fees: " . $conn->error . "</div>";
        }

        $stmt->close();
    }

    $user_stmt->close();
}

// Fetch fee details if user_id is provided
$fee_data = null;
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["fetch_fee"])) {
    $user_id = $_POST["user_id"];

    $fetch_query = "SELECT * FROM fees WHERE user_id = ?";
    $stmt = $conn->prepare($fetch_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $fee_data = $result->fetch_assoc();

    if (!$fee_data) {
        $message = "<div class='message-box error-msg' onclick='this.style.display=\"none\";'>No fee record found for this User ID.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Fees</title>
    <link rel="stylesheet" href="adminstyle.css"> <!-- Your CSS file -->
</head>
<body>

<div class="dashboard-container">
    <!-- Sidebar -->
    <?php include("rector_sidebar.php"); ?>

    <!-- Content Area -->
    <div class="content">
        <div class="content-box">
            <h2>Update Fee Details</h2>
            <?= $message; ?> <!-- Show success/error messages -->

            <!-- Form to Fetch Fee Details -->
            <form method="POST">
                <label for="user_id">Enter User ID:</label>
                <input type="number" name="user_id" required>
                <button type="submit" name="fetch_fee">Fetch Details</button>
            </form>

            <?php if ($fee_data) { ?>
            <!-- Form to Update Fee Details -->
            <form method="POST">
                <input type="hidden" name="user_id" value="<?= $fee_data['user_id']; ?>">

                <label for="amount_due">Amount Due:</label>
                <input type="number" name="amount_due" value="<?= $fee_data['amount_due']; ?>" step="0.01" required>

                <label for="due_date">Due Date:</label>
                <input type="date" name="due_date" value="<?= $fee_data['due_date']; ?>" required>

                <label for="status">Status:</label>
                <select name="status">
                    <option value="pending" <?= ($fee_data['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                    <option value="paid" <?= ($fee_data['status'] == 'paid') ? 'selected' : ''; ?>>Paid</option>
                </select>

                <button type="submit" name="update_fee">Update Fees</button>
            </form>
            <?php } ?>
        </div>
    </div>
</div>

</body>
</html>
