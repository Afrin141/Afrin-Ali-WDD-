<?php
// Database connection parameters
$servername = "127.0.0.1";
$username = "root"; // default XAMPP username
$password = ""; // default XAMPP password is empty
$dbname = "ali_enterprise";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set character set
$conn->set_charset("utf8mb4");
?>