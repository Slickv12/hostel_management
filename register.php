<?php
include("db_connect.php"); // Database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $email = $_POST["email"];
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
    $phone = $_POST["phone"];
    $address = $_POST["address"];

    // Role and status are architecture-locked
    $user_type = "student";
    $status = "pending";

    $query = "INSERT INTO users (name, email, password, phone, address, user_type, status) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssssss", $name, $email, $password, $phone, $address, $user_type, $status);

    if ($stmt->execute()) {
        header("Location: login.php");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="public.css">
</head>
<body id="register-body">
    <header class="public-header">
        <nav class="public-nav">
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="about.php">About Us</a></li>
                <li><a href="fee_structure.php">Fee Structure</a></li>
                <li><a href="login.php">Login</a></li>
                <li><a href="register.php" class="active">Register</a></li>
            </ul>
        </nav>
    </header>

    <div class="public-auth-wrap">
        <div class="public-auth-card">
            <h2>Register</h2>
            <form action="register.php" method="POST">
                <label>Username</label>
                <input type="text" name="name" required>

                <label>Email</label>
                <input type="email" name="email" required>

                <label>Password</label>
                <input type="password" name="password" required>

                <label>Phone</label>
                <input type="text" name="phone" required>

                <label>Address</label>
                <input type="text" name="address" required>

                <button type="submit">Register</button>
            </form>
            <a href="login.php">Already have an account? Login here</a>
        </div>
    </div>

    <footer class="public-footer">
        <p>&copy; 2025 Hostel Management System. All rights reserved.</p>
    </footer>
</body>
</html>
