<?php
session_start();
include("db_connect.php");

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$query = "SELECT request_id, reason, start_date, end_date, status FROM leave_requests WHERE user_id = ? ORDER BY request_id DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Status</title>
    <link rel="stylesheet" href="dashstyle.css">
</head>
<body>

<div class="dashboard-container">
     <!-- Ensure the sidebar is included -->

    <div class="content">
        <div class="content-box">
            <h2>My Leave Requests</h2>
            
            <table>
                <thead>
                    <tr>
                        <th>Request ID</th>
                        <th>Reason</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row["request_id"]) ?></td>
                            <td><?= nl2br(htmlspecialchars($row["reason"])) ?></td>
                            <td><?= htmlspecialchars($row["start_date"]) ?></td>
                            <td><?= htmlspecialchars($row["end_date"]) ?></td>
                            <td class="<?= $row["status"] ?>">
                                <?= ucfirst($row["status"]) ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            
            <?php if ($result->num_rows == 0): ?>
                <p style="text-align:center; color:gray;">No leave requests found.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>
