<?php
include("db_connect.php"); // Database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $email = $_POST["email"];
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
    $phone = $_POST["phone"];
    $address = $_POST["address"];
    $user_type = $_POST["user_type"];

    $query = "INSERT INTO users (name, email, password, phone, address, user_type) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssss", $name, $email, $password, $phone, $address, $user_type);
    
    if ($stmt->execute()) {
        $user_id = $stmt->insert_id; // Get the newly registered user's ID

        // If the user is a student, insert a record into the fees table
        if ($user_type === "student") {
            $fee_query = "INSERT INTO fees (user_id, amount_due, due_date, status) VALUES (?, 0, DATE_ADD(NOW(), INTERVAL 1 MONTH), 'pending')";
            $fee_stmt = $conn->prepare($fee_query);
            $fee_stmt->bind_param("i", $user_id);
            $fee_stmt->execute();
            $fee_stmt->close();
        }

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
    <link rel="stylesheet" href="styles.css">
</head>
<body id="register-body"> <!-- Added an ID for JavaScript targeting -->
    <div class="container">
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

            <label>User Type</label>
            <select name="user_type" id="user_type"> <!-- Added an ID for JavaScript -->
                <option value="student">Student</option>
                
            </select>

            <button type="submit">Register</button>
        </form>
        <a href="login.php">Already have an account? Login here</a>
    </div>

    <script>
        document.getElementById("user_type").addEventListener("change", function() {
            let body = document.getElementById("register-body");
            if (this.value === "admin") {
                body.style.background = "linear-gradient(to right,#f51414, #e97b50)";
                
            } else if (this.value === "student") {
                body.style.background = "linear-gradient(to right,rgb(32, 80, 240),rgb(158, 78, 211))";
                
            }
        });
    </script>
</body>
</html>
