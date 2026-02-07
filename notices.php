<?php
require 'config.php'; // Database connection

$sql = "SELECT n.admin_id, u.name AS admin_name, n.created_at, n.message 
        FROM notices n 
        JOIN users u ON n.admin_id = u.user_id
        ORDER BY n.created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notices</title>
    <link rel="stylesheet" href="dashstyle.css"> <!-- Link to your CSS file -->
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar Navigation -->
    
        <!-- Notices Content -->
        <div class="content">
            <div class="content-box">
                <h2>Notices</h2>
                <?php if ($result->num_rows > 0): ?>
                    <table>
                        <tr>
                            <th>Admin ID</th>
                            <th>Admin Name</th>
                            <th>Time</th>
                            <th>Message</th>
                        </tr>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['admin_id']); ?></td>
                                <td><?php echo htmlspecialchars($row['admin_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                                <td><?php echo htmlspecialchars($row['message']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </table>
                <?php else: ?>
                    <p>No notices available.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
