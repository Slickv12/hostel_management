<?php
session_start();
include("db_connect.php");

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_type'], ['student', 'rector'], true)) {
    header("Location: login.php");
    exit();
}

$sql = "SELECT n.notice_id, n.message, n.created_at, n.rector_id, u.name AS rector_name
        FROM notices n
        JOIN users u ON n.rector_id = u.user_id
        ORDER BY n.created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notices</title>
    <link rel="stylesheet" href="dashstyle.css">
</head>
<body>
    <div class="dashboard-container">
        <div class="content">
            <div class="content-box">
                <h2>Notices</h2>
                <?php if ($result && $result->num_rows > 0): ?>
                    <table>
                        <tr>
                            <th>Rector ID</th>
                            <th>Rector Name</th>
                            <th>Time</th>
                            <th>Message</th>
                        </tr>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['rector_id']); ?></td>
                                <td><?php echo htmlspecialchars($row['rector_name']); ?></td>
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
