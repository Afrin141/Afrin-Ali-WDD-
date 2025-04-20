<?php
session_start();
include 'db_connect.php';

// Create a user ID based on session if not logged in
if (!isset($_SESSION['user_id'])) {
    // Using session ID as temporary user ID for guests
    if (!isset($_SESSION['temp_user_id'])) {
        $_SESSION['temp_user_id'] = session_id();
    }
    $user_id = $_SESSION['temp_user_id'];
} else {
    $user_id = $_SESSION['user_id'];
}

// Handle POST request for adding to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if product_id is provided
    if (isset($_POST['product_id'])) {
        $product_id = $_POST['product_id'];
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
        
        // Validate product exists
        $stmt = $conn->prepare("SELECT id FROM products WHERE id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Check if product already in cart
            $check_stmt = $conn->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
            $check_stmt->bind_param("si", $user_id, $product_id);
            $check_stmt->execute();
            $cart_result = $check_stmt->get_result();
            
            if ($cart_result->num_rows > 0) {
                // Update quantity if product already in cart
                $cart_item = $cart_result->fetch_assoc();
                $new_quantity = $cart_item['quantity'] + $quantity;
                
                $update_stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
                $update_stmt->bind_param("ii", $new_quantity, $cart_item['id']);
                
                if ($update_stmt->execute()) {
                    $response = ['success' => true, 'message' => 'Cart updated successfully'];
                } else {
                    $response = ['success' => false, 'message' => 'Failed to update cart'];
                }
                $update_stmt->close();
            } else {
                // Add new item to cart
                $add_stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
                $add_stmt->bind_param("sii", $user_id, $product_id, $quantity);
                
                if ($add_stmt->execute()) {
                    $response = ['success' => true, 'message' => 'Product added to cart'];
                } else {
                    $response = ['success' => false, 'message' => 'Failed to add product to cart'];
                }
                $add_stmt->close();
            }
            
            $check_stmt->close();
        } else {
            $response = ['success' => false, 'message' => 'Invalid product'];
        }
        
        $stmt->close();
    } else {
        $response = ['success' => false, 'message' => 'Product ID is required'];
    }
    
    // Return JSON response for AJAX requests
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Redirect to home page if accessed directly
header('Location: home.php');
exit;
?>