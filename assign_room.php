<?php
session_start();
include("db_connect.php");

if (!isset($_SESSION["user_id"]) || $_SESSION["user_type"] !== 'rector') {
    header("Location: login.php");
    exit();
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = intval($_POST["user_id"]);
    $room_id = intval($_POST["room_id"]);
    $actor_user_id = intval($_SESSION["user_id"]);

    // Validate pending assignment target is a student
    $student_sql = "SELECT user_id FROM users WHERE user_id = ? AND user_type = 'student'";
    $student_stmt = $conn->prepare($student_sql);
    $student_stmt->bind_param("i", $user_id);
    $student_stmt->execute();
    $student_result = $student_stmt->get_result();

    if ($student_result->num_rows !== 1) {
        $message = "<div class='message-box error-msg'>❌ Invalid student selection.</div>";
    } else {
        // Prevent new assignment if student already has allocation
        $allocation_check_sql = "SELECT room_id FROM room_allocation WHERE user_id = ?";
        $allocation_check_stmt = $conn->prepare($allocation_check_sql);
        $allocation_check_stmt->bind_param("i", $user_id);
        $allocation_check_stmt->execute();
        $allocation_result = $allocation_check_stmt->get_result();

        if ($allocation_result->num_rows > 0) {
            $message = "<div class='message-box error-msg'>❌ Student already has a room allocation. Use Manage Students to change room.</div>";
        } else {
            // Dynamic capacity check: COUNT(room_allocation.user_id) < rooms.capacity
            $capacity_sql = "
                SELECT r.room_id, r.capacity, COUNT(ra.user_id) AS occupants
                FROM rooms r
                LEFT JOIN room_allocation ra ON r.room_id = ra.room_id
                WHERE r.room_id = ?
                GROUP BY r.room_id, r.capacity
            ";
            $capacity_stmt = $conn->prepare($capacity_sql);
            $capacity_stmt->bind_param("i", $room_id);
            $capacity_stmt->execute();
            $capacity_result = $capacity_stmt->get_result();
            $room = $capacity_result->fetch_assoc();

            if (!$room) {
                $message = "<div class='message-box error-msg'>❌ Invalid room selection.</div>";
            } elseif ((int)$room['occupants'] >= (int)$room['capacity']) {
                $message = "<div class='message-box error-msg'>❌ Room is already full.</div>";
            } else {
                $conn->begin_transaction();

                // Insert single allocation row
                $alloc_query = "INSERT INTO room_allocation (user_id, room_id) VALUES (?, ?)";
                $alloc_stmt = $conn->prepare($alloc_query);
                $alloc_stmt->bind_param("ii", $user_id, $room_id);

                if ($alloc_stmt->execute()) {
                    $log_sql = "INSERT INTO activity_logs (action_type, actor_user_id, target_user_id) VALUES ('room_assignment', ?, ?)";
                    $log_stmt = $conn->prepare($log_sql);
                    $log_stmt->bind_param("ii", $actor_user_id, $user_id);

                    if ($log_stmt->execute()) {
                        $conn->commit();
                        $message = "<div class='message-box success-msg'>✅ Room assigned successfully!</div>";
                    } else {
                        $conn->rollback();
                        $message = "<div class='message-box error-msg'>❌ Room assignment failed while writing log.</div>";
                    }

                    $log_stmt->close();
                } else {
                    $conn->rollback();
                    $message = "<div class='message-box error-msg'>❌ Error creating allocation: " . $conn->error . "</div>";
                }

                $alloc_stmt->close();
            }

            $capacity_stmt->close();
        }

        $allocation_check_stmt->close();
    }

    $student_stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Room</title>
    <link rel="stylesheet" href="adminstyle.css">
</head>
<body>

<div class="dashboard-container">
    <div class="sidebar">
        <h2>Rector Panel</h2>
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
        <div class="content-box">
            <h2>Assign Room to Student</h2>

            <?php if (!empty($message)) echo $message; ?>

            <form method="POST">
                <label>Select Student:</label>
                <select name="user_id" required>
                    <?php
                    $student_query = "
                        SELECT u.user_id, u.name
                        FROM users u
                        LEFT JOIN room_allocation ra ON u.user_id = ra.user_id
                        WHERE u.user_type = 'student' AND ra.user_id IS NULL
                        ORDER BY u.name ASC
                    ";
                    $result = $conn->query($student_query);
                    while ($row = $result->fetch_assoc()) {
                        echo "<option value='{$row['user_id']}'>{$row['name']}</option>";
                    }
                    ?>
                </select>

                <label>Select Room:</label>
                <select name="room_id" required>
                    <?php
                    $room_query = "
                        SELECT r.room_id, r.room_number
                        FROM rooms r
                        LEFT JOIN room_allocation ra ON r.room_id = ra.room_id
                        GROUP BY r.room_id, r.room_number, r.capacity
                        HAVING COUNT(ra.user_id) < r.capacity
                        ORDER BY r.room_number ASC
                    ";
                    $result = $conn->query($room_query);
                    while ($row = $result->fetch_assoc()) {
                        echo "<option value='{$row['room_id']}'>Room #{$row['room_number']}</option>";
                    }
                    ?>
                </select>

                <button type="submit">Assign Room</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>
