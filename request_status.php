<?php
session_start();
include("db_connect.php");

if (!isset($_SESSION["user_id"]) || $_SESSION['user_type'] !== 'student') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$message = "";

// Student cancel: allowed only for pending leaves, hard delete row
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['cancel_request_id'])) {
    $cancel_request_id = intval($_POST['cancel_request_id']);

    $delete_sql = "DELETE FROM leave_requests WHERE request_id = ? AND user_id = ? AND status = 'pending'";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("ii", $cancel_request_id, $user_id);

    if ($delete_stmt->execute() && $delete_stmt->affected_rows === 1) {
        $message = "Leave request canceled successfully.";
    } else {
        $message = "Unable to cancel request. Only pending requests can be canceled.";
    }

    $delete_stmt->close();
}

$query = "SELECT request_id, reason, start_date, end_date, status FROM leave_requests WHERE user_id = ? ORDER BY request_id DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="content-box">
    <h2>My Leave Requests</h2>

    <?php if (!empty($message)): ?>
        <p class="<?php echo (strpos($message, 'successfully') !== false) ? 'success-msg' : 'error-msg'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </p>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>Request ID</th>
                <th>Reason</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row["request_id"]) ?></td>
                    <td><?= nl2br(htmlspecialchars($row["reason"])) ?></td>
                    <td><?= htmlspecialchars($row["start_date"]) ?></td>
                    <td><?= htmlspecialchars($row["end_date"]) ?></td>
                    <td class="<?= htmlspecialchars($row["status"]) ?>">
                        <?= ucfirst(htmlspecialchars($row["status"])) ?>
                    </td>
                    <td>
                        <?php if ($row['status'] === 'pending'): ?>
                            <form method="POST" action="request_status.php" style="margin:0;">
                                <input type="hidden" name="cancel_request_id" value="<?= (int)$row['request_id'] ?>">
                                <button type="submit">Cancel</button>
                            </form>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <?php if ($result->num_rows == 0): ?>
        <p style="text-align:center; color:gray;">No leave requests found.</p>
    <?php endif; ?>
</div>
