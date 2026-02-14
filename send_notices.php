<?php
session_start();
include("db_connect.php");

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'rector') {
    header("Location: login.php");
    exit();
}

$rector_id = intval($_SESSION['user_id']);
$message = "";

// Ensure activity_logs ENUM contains notice actions
$enum_query = "SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'activity_logs' AND COLUMN_NAME = 'action_type'";
$enum_result = $conn->query($enum_query);
if ($enum_result && $enum_result->num_rows === 1) {
    $enum_row = $enum_result->fetch_assoc();
    $column_type = $enum_row['COLUMN_TYPE'];

    preg_match_all("/'([^']+)'/", $column_type, $matches);
    $enum_values = $matches[1];

    $required_actions = ['notice_created', 'notice_updated', 'notice_deleted'];
    $needs_update = false;

    foreach ($required_actions as $action) {
        if (!in_array($action, $enum_values, true)) {
            $enum_values[] = $action;
            $needs_update = true;
        }
    }

    if ($needs_update) {
        $escaped = array_map(function ($value) use ($conn) {
            return "'" . $conn->real_escape_string($value) . "'";
        }, $enum_values);

        $alter_sql = "ALTER TABLE activity_logs MODIFY COLUMN action_type ENUM(" . implode(',', $escaped) . ") NOT NULL";
        $conn->query($alter_sql);
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Create notice
    if (isset($_POST['create_notice'])) {
        $notice_message = trim($_POST['message']);

        if (empty($notice_message)) {
            $message = "Please fill in message.";
        } else {
            $conn->begin_transaction();

            $insert_sql = "INSERT INTO notices (rector_id, message) VALUES (?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("is", $rector_id, $notice_message);

            if ($insert_stmt->execute()) {
                $log_sql = "INSERT INTO activity_logs (action_type, actor_user_id, target_user_id, metadata) VALUES ('notice_created', ?, NULL, NULL)";
                $log_stmt = $conn->prepare($log_sql);
                $log_stmt->bind_param("i", $rector_id);

                if ($log_stmt->execute()) {
                    $conn->commit();
                    $message = "Notice sent successfully!";
                } else {
                    $conn->rollback();
                    $message = "Error logging notice creation.";
                }

                $log_stmt->close();
            } else {
                $conn->rollback();
                $message = "Error creating notice.";
            }

            $insert_stmt->close();
        }
    }

    // Update notice
    if (isset($_POST['update_notice'])) {
        $notice_id = intval($_POST['notice_id']);
        $notice_message = trim($_POST['message']);

        if (empty($notice_message)) {
            $message = "Message cannot be empty.";
        } else {
            $conn->begin_transaction();

            $update_sql = "UPDATE notices SET message = ? WHERE notice_id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("si", $notice_message, $notice_id);

            if ($update_stmt->execute() && $update_stmt->affected_rows >= 1) {
                $log_sql = "INSERT INTO activity_logs (action_type, actor_user_id, target_user_id, metadata) VALUES ('notice_updated', ?, NULL, NULL)";
                $log_stmt = $conn->prepare($log_sql);
                $log_stmt->bind_param("i", $rector_id);

                if ($log_stmt->execute()) {
                    $conn->commit();
                    $message = "Notice updated successfully!";
                } else {
                    $conn->rollback();
                    $message = "Error logging notice update.";
                }

                $log_stmt->close();
            } else {
                $conn->rollback();
                $message = "Error updating notice.";
            }

            $update_stmt->close();
        }
    }

    // Delete notice
    if (isset($_POST['delete_notice'])) {
        $notice_id = intval($_POST['notice_id']);

        $conn->begin_transaction();

        $delete_sql = "DELETE FROM notices WHERE notice_id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $notice_id);

        if ($delete_stmt->execute() && $delete_stmt->affected_rows === 1) {
            $log_sql = "INSERT INTO activity_logs (action_type, actor_user_id, target_user_id, metadata) VALUES ('notice_deleted', ?, NULL, NULL)";
            $log_stmt = $conn->prepare($log_sql);
            $log_stmt->bind_param("i", $rector_id);

            if ($log_stmt->execute()) {
                $conn->commit();
                $message = "Notice deleted successfully!";
            } else {
                $conn->rollback();
                $message = "Error logging notice deletion.";
            }

            $log_stmt->close();
        } else {
            $conn->rollback();
            $message = "Error deleting notice.";
        }

        $delete_stmt->close();
    }
}

$notices_sql = "SELECT notice_id, message, created_at FROM notices ORDER BY created_at DESC";
$notices_result = $conn->query($notices_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Notices</title>
    <link rel="stylesheet" href="adminstyle.css">
</head>
<body>

<div class="dashboard-container">
    <?php include("rector_sidebar.php"); ?>

    <div class="content">
        <h2>Send Notice</h2>
        <div class="content-box">
            <form method="POST" action="">
                <label for="message">Message:</label>
                <textarea id="message" name="message" rows="1" required></textarea>
                <button type="submit" name="create_notice">Send Notice</button>
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

            <h3>Existing Notices</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Message</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($notices_result && $notices_result->num_rows > 0): ?>
                        <?php while ($row = $notices_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['notice_id']); ?></td>
                                <td>
                                    <form method="POST" action="" style="display:flex; gap:8px; align-items:center;">
                                        <input type="hidden" name="notice_id" value="<?php echo (int)$row['notice_id']; ?>">
                                        <textarea name="message" rows="1" required><?php echo htmlspecialchars($row['message']); ?></textarea>
                                        <button type="submit" name="update_notice">Update</button>
                                    </form>
                                </td>
                                <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                                <td>
                                    <form method="POST" action="" style="margin:0;">
                                        <input type="hidden" name="notice_id" value="<?php echo (int)$row['notice_id']; ?>">
                                        <button type="submit" name="delete_notice">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4">No notices available.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
