<?php
// Enable error reporting for troubleshooting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include database connection
require_once 'db_connect.php';

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Initialize variables
$success = false;
$error = "";

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate input
    if (
        isset($_POST['product_id']) && !empty($_POST['product_id']) &&
        isset($_POST['reviewer_name']) && !empty($_POST['reviewer_name']) &&
        isset($_POST['rating']) && !empty($_POST['rating']) &&
        isset($_POST['review_content']) && !empty($_POST['review_content'])
    ) {
        // Sanitize input
        $product_id = intval($_POST['product_id']);
        $reviewer_name = trim($_POST['reviewer_name']);
        $rating = intval($_POST['rating']);
        $review_content = trim($_POST['review_content']);
        
        // Validate rating range
        if ($rating < 1 || $rating > 5) {
            $error = "Invalid rating. Rating must be between 1 and 5.";
        } else {
            // Check if the reviews table exists, create it if it doesn't
            $sql = "CREATE TABLE IF NOT EXISTS reviews (
                id INT(11) AUTO_INCREMENT PRIMARY KEY,
                product_id INT(11) NOT NULL,
                reviewer_name VARCHAR(255) NOT NULL,
                rating INT(1) NOT NULL,
                review_content TEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
            )";
            
            if ($conn->query($sql) === FALSE) {
                $error = "Error creating reviews table: " . $conn->error;
            } else {
                // Insert review
                $sql = "INSERT INTO reviews (product_id, reviewer_name, rating, review_content)
                        VALUES (?, ?, ?, ?)";
                
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("isis", $product_id, $reviewer_name, $rating, $review_content);
                
                if ($stmt->execute()) {
                    $success = true;
                } else {
                    $error = "Error submitting review: " . $stmt->error;
                }
                
                $stmt->close();
            }
        }
    } else {
        $error = "All fields are required.";
    }
}

// Close connection
$conn->close();

// Redirect back to product detail page
if ($success) {
    header("Location: product_detail.php?id=" . $product_id . "&review=success");
} else {
    header("Location: product_detail.php?id=" . $product_id . "&review=error&message=" . urlencode($error));
}
exit();
?>