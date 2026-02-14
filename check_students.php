<?php
session_start();
include("db_connect.php");
if (!isset($_SESSION["user_id"]) || $_SESSION["user_type"] !== "rector") {
    header("Location: login.php");
    exit();
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

if ($search !== '') {
    $like = "%{$search}%";
    $sql = "SELECT user_id, name, email, phone, address FROM users WHERE user_type = 'student' AND (name LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $like, $like, $like);
} else {
    $sql = "SELECT user_id, name, email, phone, address FROM users WHERE user_type = 'student'";
    $stmt = $conn->prepare($sql);
}

$stmt->execute();
$result = $stmt->get_result();
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
    <?php include("rector_sidebar.php"); ?>

    <!-- Main Content -->
    <div class="content">
        <div class="content-box">
            <h2>Registered Students</h2>

            <form method="GET">
                <label for="search">Search (Name / Email / Phone):</label>
                <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit">Search</button>
            </form>

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
                            <td colspan="5">No students found</td>
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
$stmt->close();
$conn->close();
?>
