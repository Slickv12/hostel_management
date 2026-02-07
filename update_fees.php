<?php
include("db_connect.php"); // Database connection

$message = ""; // To show success/error messages

// Check if the form is submitted to update fees
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update_fee"])) {
    $user_id = $_POST["user_id"];
    $amount_due = $_POST["amount_due"];
    $due_date = $_POST["due_date"];
    $status = $_POST["status"];

    $update_query = "UPDATE fees SET amount_due = ?, due_date = ?, status = ? WHERE user_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("dssi", $amount_due, $due_date, $status, $user_id);

    if ($stmt->execute()) {
        $message = "<div class='message-box success-msg' onclick='this.style.display=\"none\";'>Fee details updated successfully!</div>";
    } else {
        $message = "<div class='message-box error-msg' onclick='this.style.display=\"none\";'>Error updating fees: " . $conn->error . "</div>";
    }
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
