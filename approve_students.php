<?php
session_start();
include("db_connect.php");

if (!isset($_SESSION["user_id"]) || $_SESSION["user_type"] !== 'rector') {
    header("Location: login.php");
    exit();
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["approve_student"])) {
    $student_id = intval($_POST["student_id"]);
    $actor_user_id = $_SESSION["user_id"];

    $conn->begin_transaction();

    $update_sql = "UPDATE users SET status = 'active' WHERE user_id = ? AND user_type = 'student' AND status = 'pending'";
    $update_stmt = $conn->prepare($update_sql);

    if ($update_stmt) {
        $update_stmt->bind_param("i", $student_id);
        $update_stmt->execute();

        if ($update_stmt->affected_rows === 1) {
            $fee_creation_ok = true;

            // Check if fee record already exists
            $fee_check_sql = "SELECT user_id FROM fees WHERE user_id = ?";
            $fee_check_stmt = $conn->prepare($fee_check_sql);

            if ($fee_check_stmt) {
                $fee_check_stmt->bind_param("i", $student_id);
                $fee_check_stmt->execute();
                $fee_check_result = $fee_check_stmt->get_result();

                if ($fee_check_result->num_rows === 0) {
                    $fee_insert_sql = "INSERT INTO fees (user_id, amount_due, due_date, status) VALUES (?, 0.00, NULL, 'pending')";
                    $fee_insert_stmt = $conn->prepare($fee_insert_sql);

                    if ($fee_insert_stmt) {
                        $fee_insert_stmt->bind_param("i", $student_id);

                        if (!$fee_insert_stmt->execute()) {
                            $conn->rollback();
                            $message = "Fee record creation failed.";
                            $fee_creation_ok = false;
                        }

                        $fee_insert_stmt->close();
                    } else {
                        $conn->rollback();
                        $message = "Approval failed while preparing fee record.";
                        $fee_creation_ok = false;
                    }
                }

                $fee_check_stmt->close();
            } else {
                $conn->rollback();
                $message = "Approval failed while preparing fee check.";
                $fee_creation_ok = false;
            }

            if ($fee_creation_ok) {
                $log_sql = "INSERT INTO activity_logs (action_type, actor_user_id, target_user_id) VALUES ('student_approval', ?, ?)";
                $log_stmt = $conn->prepare($log_sql);

                if ($log_stmt) {
                    $log_stmt->bind_param("ii", $actor_user_id, $student_id);

                    if ($log_stmt->execute()) {
                        $conn->commit();
                        $message = "Student approved successfully.";
                    } else {
                        $conn->rollback();
                        $message = "Approval failed while writing activity log.";
                    }

                    $log_stmt->close();
                } else {
                    $conn->rollback();
                    $message = "Approval failed while preparing activity log.";
                }
            }
        } else {
            $conn->rollback();
            $message = "Student not found or already approved.";
        }

        $update_stmt->close();
    } else {
        $conn->rollback();
        $message = "Approval failed while preparing student update.";
    }
}

$pending_sql = "SELECT user_id, name, email, phone, address FROM users WHERE user_type = 'student' AND status = 'pending' ORDER BY user_id ASC";
$pending_result = $conn->query($pending_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approve Students</title>
    <link rel="stylesheet" href="adminstyle.css">
</head>
<body>

<div class="dashboard-container">
    <?php include("rector_sidebar.php"); ?>

    <div class="content">
        <h2>Pending Student Approvals</h2>

        <?php if (!empty($message)): ?>
            <div class="message-box <?php echo (strpos($message, 'successfully') !== false) ? 'success-msg' : 'error-msg'; ?>" id="messageBox" onclick="this.style.display='none';">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="content-box">
            <table border="1">
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Address</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($pending_result && $pending_result->num_rows > 0): ?>
                        <?php while ($row = $pending_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['user_id']); ?></td>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo htmlspecialchars($row['phone']); ?></td>
                                <td><?php echo htmlspecialchars($row['address']); ?></td>
                                <td>
                                    <form method="POST" action="">
                                        <input type="hidden" name="student_id" value="<?php echo (int)$row['user_id']; ?>">
                                        <button type="submit" name="approve_student">Approve</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">No pending students found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
