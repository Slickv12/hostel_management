<?php
session_start();
include("db_connect.php"); // Database connection

// Ensure user is logged in
if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}

// Get logged-in user's ID
$username = $_SESSION["username"];
$query = "SELECT user_id FROM users WHERE name = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$user_id = $user["user_id"];

// Fetch fee details for the logged-in user
$query = "SELECT amount_due, due_date, status FROM fees WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$fee = $result->fetch_assoc();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fee Status</title>
    <link rel="stylesheet" href="assets/css/base.css">
    <?php
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (isset($_SESSION['user_type'])) {
        if ($_SESSION['user_type'] === 'rector') {
            echo '<link rel="stylesheet" href="assets/css/rector.css">';
        } elseif ($_SESSION['user_type'] === 'student') {
            echo '<link rel="stylesheet" href="assets/css/student.css">';
        }
    }
    ?>
</head>
<body>
    <div class="content-box">
        <h2>Fee Status</h2>
        <?php if ($fee): ?>
            <table>
                <tr>
                    <th>Amount Due</th>
                    <th>Due Date</th>
                    <th>Status</th>
                </tr>
                <tr>
                    <td>₹<?php echo number_format($fee['amount_due'], 2); ?></td>
                    <td><?php echo date("d-M-Y", strtotime($fee['due_date'])); ?></td>
                    <td class="<?php echo ($fee['status'] == 'paid') ? 'paid' : 'pending'; ?>">
                        <?php echo ucfirst($fee['status']); ?>
                    </td>
                </tr>
            </table>
        <?php else: ?>
            <p>No fee records found.</p>
        <?php endif; ?>
    </div>
</body>
</html>
