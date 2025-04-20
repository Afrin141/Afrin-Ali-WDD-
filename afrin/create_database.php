<?php
$conn = mysqli_connect("localhost", "root", "");

// Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS afrin";
if (mysqli_query($conn, $sql)) {
    echo "Database created successfully<br>";
} else {
    echo "Error creating database: " . mysqli_error($conn) . "<br>";
}

// Select the database
mysqli_select_db($conn, "afrin");

// Create products table
$sql = "CREATE TABLE IF NOT EXISTS products (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255) NOT NULL,
    description TEXT
)";

if (mysqli_query($conn, $sql)) {
    echo "Table products created successfully<br>";
} else {
    echo "Error creating table: " . mysqli_error($conn) . "<br>";
}

mysqli_close($conn);
echo "Database setup completed!";
?>
