<?php
session_start();
include("db_connect.php");

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'rector') {
    header("Location: login.php");
    exit();
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['request_id'], $_POST['action'])) {
    $request_id = intval($_POST['request_id']);
    $rector_id = intval($_SESSION['user_id']);
    $status = $_POST['action'] === "approve" ? "approved" : "rejected";
    $action_type = $status === 'approved' ? 'leave_approved' : 'leave_rejected';

    // Resolve target student from leave request first
    $target_sql = "SELECT user_id FROM leave_requests WHERE request_id = ?";
    $target_stmt = $conn->prepare($target_sql);
    $target_stmt->bind_param("i", $request_id);
    $target_stmt->execute();
    $target_result = $target_stmt->get_result();
    $target = $target_result->fetch_assoc();
    $target_stmt->close();

    if (!$target) {
        $message = "Leave request not found.";
    } else {
        $target_user_id = intval($target['user_id']);

        $conn->begin_transaction();

        $sql = "UPDATE leave_requests SET status = ?, approved_by = ?, approved_at = CURRENT_TIMESTAMP WHERE request_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sii", $status, $rector_id, $request_id);

        if ($stmt->execute() && $stmt->affected_rows >= 1) {
            $log_sql = "INSERT INTO activity_logs (action_type, actor_user_id, target_user_id) VALUES (?, ?, ?)";
            $log_stmt = $conn->prepare($log_sql);
            $log_stmt->bind_param("sii", $action_type, $rector_id, $target_user_id);

            if ($log_stmt->execute()) {
                $conn->commit();
                $message = "Request has been $status successfully!";
            } else {
                $conn->rollback();
                $message = "Error logging leave action.";
            }

            $log_stmt->close();
        } else {
            $conn->rollback();
            $message = "Error updating request.";
        }

        $stmt->close();
    }
}

$sql = "SELECT request_id, user_id, reason, start_date, end_date, status, approved_by, approved_at FROM leave_requests ORDER BY request_id DESC";
$result = $conn->query($sql);
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
        <h2>Rector Dashboard</h2>
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
                        <th>Approved By</th>
                        <th>Approved At</th>
                    </tr>
                </thead>
                <tbody id="requestTable">
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr onclick="selectRequest(<?php echo (int)$row['request_id']; ?>, this)"
                            class="<?php echo ($row['status'] == 'pending') ? 'pending' : strtolower($row['status']); ?>">
                            <td><?php echo htmlspecialchars($row['request_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['user_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['reason']); ?></td>
                            <td><?php echo htmlspecialchars($row['start_date']); ?></td>
                            <td><?php echo htmlspecialchars($row['end_date']); ?></td>
                            <td class="<?php echo strtolower($row['status']); ?>"><?php echo ucfirst($row['status']); ?></td>
                            <td><?php echo htmlspecialchars($row['approved_by'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($row['approved_at'] ?? ''); ?></td>
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
                    <?php echo htmlspecialchars($message); ?>
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
