<?php
session_start();
include("db_connect.php");

// Check if the admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Handle Approval/Rejection
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['request_id'])) {
    $request_id = intval($_POST['request_id']);
    $status = $_POST['action'] === "approve" ? "approved" : "rejected";

    $sql = "UPDATE leave_requests SET status = ? WHERE request_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $request_id);

    if ($stmt->execute()) {
        $message = "Request has been $status successfully!";
    } else {
        $message = "Error updating request: " . $conn->error;
    }

    $stmt->close();
}

// Fetch all leave requests
$sql = "SELECT request_id, user_id, reason, start_date, end_date, status FROM leave_requests";
$result = $conn->query($sql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approve Requests</title>
    <link rel="stylesheet" href="adminstyle.css">
</head>
<body>

<div class="dashboard-container">
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

    <div class="content">
        <h2>Approve Leave Requests</h2>
        <div class="content-box">
            <table>
                <thead>
                    <tr>
                        <th>Request ID</th>
                        <th>User ID</th>
                        <th>Reason</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="requestTable">
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr onclick="selectRequest(<?php echo $row['request_id']; ?>, this)" 
                            class="<?php echo ($row['status'] == 'pending') ? 'pending' : strtolower($row['status']); ?>">
                            <td><?php echo $row['request_id']; ?></td>
                            <td><?php echo $row['user_id']; ?></td>
                            <td><?php echo $row['reason']; ?></td>
                            <td><?php echo $row['start_date']; ?></td>
                            <td><?php echo $row['end_date']; ?></td>
                            <td class="<?php echo strtolower($row['status']); ?>">
                                 <?php echo ucfirst($row['status']); ?>
                            </td>

                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <form method="POST" action="">
                <input type="hidden" name="request_id" id="selectedRequestId">
                <button type="submit" name="action" value="approve" id="approveBtn" disabled>✅ Approve</button>
                <button type="submit" name="action" value="reject" id="rejectBtn" disabled>❌ Reject</button>
            </form>

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

<script>
    function selectRequest(id, row) {
        document.getElementById("selectedRequestId").value = id;

        let rows = document.querySelectorAll("#requestTable tr");
        rows.forEach(r => r.classList.remove("selected"));

        row.classList.add("selected");

        document.getElementById("approveBtn").disabled = false;
        document.getElementById("rejectBtn").disabled = false;
    }
</script>

</body>
</html>
