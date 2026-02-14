<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include("db_connect.php");

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    if (empty($email) || empty($password)) {
        $error_message = "Email and password cannot be empty!";
    } else {
        $query = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($query);

        if (!$stmt) {
            $error_message = "SQL Error: " . $conn->error;
        } else {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows == 1) {
                $user = $result->fetch_assoc();

                if (!isset($user["status"]) || $user["status"] !== "active") {
                    $error_message = "Your account is pending approval by rector.";
                } elseif (!in_array($user["user_type"], ["student", "rector"], true)) {
                    $error_message = "Invalid account role.";
                } elseif (password_verify($password, $user["password"])) {
                    $_SESSION["user_id"] = $user["user_id"];
                    $_SESSION["user_type"] = $user["user_type"];
                    $_SESSION["username"] = $user["name"];

                    if ($user["user_type"] === "rector") {
                        header("Location: rector_dashboard.php");
                    } else {
                        header("Location: sdashboard.php");
                    }
                    exit();
                } else {
                    $error_message = "Invalid password.";
                }
            } else {
                $error_message = "User not found.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Login</h2>
        <?php if (!empty($error_message)): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Login</button>
        </form>
        <a href="register.php">Don't have an account? Register here</a>
    </div>
</body>
</html>
