<?php
session_start();
include("db_connect.php");

if (!isset($_SESSION["user_id"]) || $_SESSION["user_type"] !== "rector") {
    header("Location: login.php");
    exit();
}

// Handle Remove Student Request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['remove_student'])) {
    $user_id = intval($_POST['user_id']);
    $actor_user_id = intval($_SESSION['user_id']);

    // Check if student exists
    $check_sql = "SELECT user_id FROM users WHERE user_id = ? AND user_type = 'student'";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $conn->begin_transaction();

        // Delete from users table (CASCADE will also remove room allocation)
        $delete_sql = "DELETE FROM users WHERE user_id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $user_id);

        if ($delete_stmt->execute() && $delete_stmt->affected_rows === 1) {
            $log_sql = "INSERT INTO activity_logs (action_type, actor_user_id, target_user_id) VALUES ('student_deleted', ?, ?)";
            $log_stmt = $conn->prepare($log_sql);
            $log_stmt->bind_param("ii", $actor_user_id, $user_id);

            if ($log_stmt->execute()) {
                $conn->commit();
                $message = "Student removed successfully!";
            } else {
                $conn->rollback();
                $message = "Error removing student log.";
            }

            $log_stmt->close();
        } else {
            $conn->rollback();
            $message = "Error removing student.";
        }

        $delete_stmt->close();
    } else {
        $message = "Student not found!";
    }

    $stmt->close();
}

// Handle Update Room Request (overwrite existing allocation row)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_room'])) {
    $user_id = intval($_POST['user_id']);
    $room_id = intval($_POST['room_id']);
    $actor_user_id = intval($_SESSION['user_id']);

    // Check if student exists
    $check_sql = "SELECT user_id FROM users WHERE user_id = ? AND user_type = 'student'";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Dynamic capacity check
        $capacity_sql = "
            SELECT r.room_id, r.capacity, COUNT(ra.user_id) AS occupants
            FROM rooms r
            LEFT JOIN room_allocation ra ON r.room_id = ra.room_id
            WHERE r.room_id = ?
            GROUP BY r.room_id, r.capacity
        ";
        $cap_stmt = $conn->prepare($capacity_sql);
        $cap_stmt->bind_param("i", $room_id);
        $cap_stmt->execute();
        $room_result = $cap_stmt->get_result();
        $room_row = $room_result->fetch_assoc();

        if (!$room_row) {
            $message = "Invalid Room ID!";
        } else {
            // Check existing allocation to allow overwrite and avoid counting same student as full
            $existing_sql = "SELECT room_id FROM room_allocation WHERE user_id = ?";
            $existing_stmt = $conn->prepare($existing_sql);
            $existing_stmt->bind_param("i", $user_id);
            $existing_stmt->execute();
            $existing_result = $existing_stmt->get_result();
            $existing = $existing_result->fetch_assoc();

            $is_same_room = $existing && (int)$existing['room_id'] === $room_id;
            $room_full = ((int)$room_row['occupants'] >= (int)$room_row['capacity']) && !$is_same_room;

            if ($room_full) {
                $message = "Room is full. Choose another room.";
            } else {
                $conn->begin_transaction();

                if ($existing) {
                    $update_sql = "UPDATE room_allocation SET room_id = ? WHERE user_id = ?";
                    $update_stmt = $conn->prepare($update_sql);
                    $update_stmt->bind_param("ii", $room_id, $user_id);
                    $ok = $update_stmt->execute();
                    $update_stmt->close();
                } else {
                    $insert_sql = "INSERT INTO room_allocation (user_id, room_id) VALUES (?, ?)";
                    $insert_stmt = $conn->prepare($insert_sql);
                    $insert_stmt->bind_param("ii", $user_id, $room_id);
                    $ok = $insert_stmt->execute();
                    $insert_stmt->close();
                }

                if ($ok) {
                    $log_sql = "INSERT INTO activity_logs (action_type, actor_user_id, target_user_id) VALUES ('room_assignment', ?, ?)";
                    $log_stmt = $conn->prepare($log_sql);
                    $log_stmt->bind_param("ii", $actor_user_id, $user_id);

                    if ($log_stmt->execute()) {
                        $conn->commit();
                        $message = "Room updated successfully!";
                    } else {
                        $conn->rollback();
                        $message = "Error updating room log.";
                    }

                    $log_stmt->close();
                } else {
                    $conn->rollback();
                    $message = "Error updating room.";
                }

                $existing_stmt->close();
            }
        }

        $cap_stmt->close();
    } else {
        $message = "Student not found!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students</title>
    <link rel="stylesheet" href="adminstyle.css"> <!-- Use admin dashboard CSS -->
</head>
<body>

<div class="dashboard-container">
    <!-- Sidebar -->
    <?php include("rector_sidebar.php"); ?>

    <!-- Content -->
    <div class="content">
        <h2>Manage Students</h2>

        <!-- Success/Error Message -->
        <?php if (isset($message)): ?>
    <div class="message-box <?php echo (strpos($message, 'successfully') !== false) ? 'success-msg' : 'error-msg'; ?>" id="messageBox" onclick="this.style.display='none';">
        <?php echo $message; ?>
    </div>

    <script>
        // Auto-hide message box after 3 seconds
        setTimeout(function() {
            document.getElementById('messageBox').style.display = 'none';
        }, 3000);
    </script>
<?php endif; ?>

        <!-- Remove Student Form -->
        <div class="content-box">
            <h3>Remove Student</h3>
            <form method="POST">
                <label>User ID:</label>
                <input type="number" name="user_id" required>
                <button type="submit" name="remove_student">Remove Student</button>
            </form>
       
            <h3>Update Room</h3>
            <form method="POST">
                <label>User ID:</label>
                <input type="number" name="user_id" required>
                <label>New Room ID:</label>
                <input type="number" name="room_id" required>
                <button type="submit" name="update_room">Update Room</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>
