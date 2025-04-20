<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "afrin";

try {
    $conn = mysqli_connect($servername, $username, $password);
    
    // Create database if it doesn't exist
    $sql = "CREATE DATABASE IF NOT EXISTS $dbname";
    mysqli_query($conn, $sql);
    
    // Select the database
    mysqli_select_db($conn, $dbname);
    
} catch (Exception $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
