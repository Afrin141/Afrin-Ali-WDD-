<?php
session_start();
require_once 'db_connect.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            $name = $_POST['name'];
            $contact = $_POST['contact'];
            $email = $_POST['email'];
            $address = $_POST['address'];
            
            $stmt = $conn->prepare("INSERT INTO suppliers (name, contact, email, address) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $contact, $email, $address);
            $stmt->execute();
        } elseif ($_POST['action'] === 'update') {
            $id = $_POST['id'];
            $name = $_POST['name'];
            $contact = $_POST['contact'];
            $email = $_POST['email'];
            $address = $_POST['address'];
            
            $stmt = $conn->prepare("UPDATE suppliers SET name=?, contact=?, email=?, address=? WHERE id=?");
            $stmt->bind_param("ssssi", $name, $contact, $email, $address, $id);
            $stmt->execute();
        } elseif ($_POST['action'] === 'delete') {
            $id = $_POST['id'];
            $stmt = $conn->prepare("DELETE FROM suppliers WHERE id=?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
        }
    }
}

// Fetch all suppliers
$result = $conn->query("SELECT * FROM suppliers ORDER BY name");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Manager</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .supplier-form {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .supplier-list {
            margin-top: 30px;
        }
        .supplier-item {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 5px;
        }
        .action-buttons {
            margin-top: 10px;
        }
        .action-buttons button {
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Supplier Manager</h1>
        
        <!-- Add/Edit Supplier Form -->
        <div class="supplier-form">
            <h2>Add New Supplier</h2>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="form-group">
                    <label for="name">Supplier Name:</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="contact">Contact Person:</label>
                    <input type="text" id="contact" name="contact" required>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="address">Address:</label>
                    <textarea id="address" name="address" required></textarea>
                </div>
                <button type="submit">Add Supplier</button>
            </form>
        </div>

        <!-- Supplier List -->
        <div class="supplier-list">
            <h2>Existing Suppliers</h2>
            <?php while ($supplier = $result->fetch_assoc()): ?>
                <div class="supplier-item">
                    <h3><?php echo htmlspecialchars($supplier['name']); ?></h3>
                    <p><strong>Contact:</strong> <?php echo htmlspecialchars($supplier['contact']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($supplier['email']); ?></p>
                    <p><strong>Address:</strong> <?php echo htmlspecialchars($supplier['address']); ?></p>
                    <div class="action-buttons">
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo $supplier['id']; ?>">
                            <button type="submit" onclick="return confirm('Are you sure you want to delete this supplier?')">Delete</button>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</body>
</html> 