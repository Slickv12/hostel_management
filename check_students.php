<?php
session_start();
include("db_connect.php");
if (!isset($_SESSION["user_id"]) || $_SESSION["user_type"] !== "admin") {
    header("Location: login.php");
    exit();
}



// Fetch all students
$sql = "SELECT user_id, name, email, phone, address FROM users WHERE user_type = 'student'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check Students</title>
    <link rel="stylesheet" href="adminstyle.css"> <!-- Admin Dashboard Styles -->
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

    <!-- Main Content -->
    <div class="content">
        <div class="content-box">
            <h2>Registered Students</h2>
            <table border="1">
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>phone</th>
                        <th>address</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['user_id']); ?></td>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo htmlspecialchars($row['phone']); ?></td>
                                <td><?php echo htmlspecialchars($row['address']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3">No students found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>

<?php
$conn->close();
?>
