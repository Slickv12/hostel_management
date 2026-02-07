<?php
$host = "localhost";  // Change this if your database is hosted remotely
$user = "root";  // Default user for XAMPP
$password = "";  // Default password is empty
$database = "hostel_management";  // Your database name

// Create connection
$conn = new mysqli($host, $user, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
