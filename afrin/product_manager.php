<?php
// Include database connection
require_once 'db_connect.php';

// Initialize variables
$name = $price = $description = $is_featured = "";
$image_paths = array(); 
$stock_quantity = 0;
$error = "";
$success = "";

// Define allowed image types
$allowed_image_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

// Define the directory where images will be stored
$target_dir = "Img/";

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if we're adding a new product
    if (isset($_POST['add_product'])) {
        // Validate and sanitize input
        $name = trim($_POST['name']);
        $price = floatval($_POST['price']);
        $description = trim($_POST['description']);
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;
        $stock_quantity = intval($_POST['stock_quantity']);

        // Handle image uploads
        $all_uploads_successful = true;
        for ($i = 1; $i <= 5; $i++) {
            if (isset($_FILES["image_file_$i"]) && $_FILES["image_file_$i"]["error"] == 0) {
                // Check if the file type is allowed
                $file_type = $_FILES["image_file_$i"]["type"];
                if (!in_array($file_type, $allowed_image_types)) {
                    $error = "Error: Only JPG, JPEG, PNG, GIF, and WEBP files are allowed for image $i.";
                    $all_uploads_successful = false;
                    break; 
                } else {
                    // Get file info
                    $file_name = basename($_FILES["image_file_$i"]["name"]);
                    $target_file = $target_dir . $file_name;
                    $uploadOk = 1;

                    // Check file size
                    if ($_FILES["image_file_$i"]["size"] > 5000000) { 
                        $error = "Error: Sorry, your file (image $i) is too large.";
                        $all_uploads_successful = false;
                        break; 
                    }

                    // Check if $uploadOk is set to 0 by an error
                    if ($uploadOk == 0) {
                        $error = "Error: Sorry, your file (image $i) was not uploaded.";
                        $all_uploads_successful = false;
                        break; 
                    } else {
                        // if everything is ok, try to upload file
                        if (move_uploaded_file($_FILES["image_file_$i"]["tmp_name"], $target_file)) {
                            $image_paths[] = $target_file; 
                        } else {
                            $error = "Error: Sorry, there was an error uploading your file (image $i).";
                            $all_uploads_successful = false;
                            break; 
                        }
                    }
                }
            } else {
                $image_paths[] = ""; 
            }
        }

        // If all uploads were successful, proceed to save to the database
        if ($all_uploads_successful) {
            // Serialize image paths to store in database
            $image_path_string = implode(",", $image_paths);

            // Insert new product
            $sql = "INSERT INTO products (name, price, image_path, description, stock_quantity, is_featured) 
                    VALUES (?, ?, ?, ?, ?, ?)";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sdssii", $name, $price, $image_path_string, $description, $stock_quantity, $is_featured);

            if ($stmt->execute()) {
                $success = "Product added successfully";
                // Clear form fields after successful submission
                $name = $price = $description = $is_featured = "";
                $image_paths = array(); 
            } else {
                $error = "Error: " . $stmt->error;
            }

            $stmt->close();
        }
    }

    // Check if we're deleting a product
    if (isset($_POST['delete_product']) && isset($_POST['product_id'])) {
        $product_id = intval($_POST['product_id']);

        $sql = "DELETE FROM products WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $product_id);

        if ($stmt->execute()) {
            $success = "Product deleted successfully";
        } else {
            $error = "Error deleting product: " . $stmt->error;
        }

        $stmt->close();
    }
}

// Fetch all products for display
$sql = "SELECT * FROM products ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Manager - ALI Enterprise</title>
    <link rel="stylesheet" href="home.css">
    <link rel="stylesheet" href="product_manager.css">
</head>
<body>
    <!-- Header Section -->
    <header>
        <div class="logo">ALI Enterprise</div>
        <nav>
            <button id="mobile-menu-toggle" aria-label="Toggle Menu">â˜°</button>
            <ul id="nav-menu">
                <li><a href="home.php">Home</a></li>
                <li><a href="order.html">Orders</a></li>
                <li><a href="product.php">Products</a></li>
                <li><a href="track_order.html">Track Order</a></li>
                <li><a href="about_us.html">About Us</a></li>
                <li><a href="product_manager.php" aria-current="page">Product Manager</a></li>
            </ul>
        </nav>
    </header>

    <div class="admin-container">
        <h1>Product Manager</h1>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="admin-form">
            <h2>Add New Product</h2>
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="name">Product Name*</label>
                    <input type="text" id="name" name="name" value="<?php echo $name; ?>" required>
                </div>

                <div class="form-group">
                    <label for="price">Price (Rs)*</label>
                    <input type="number" id="price" name="price" step="0.01" value="<?php echo $price; ?>" required>
                </div>

                <div class="form-group">
                    <label for="stock_quantity">Stock Quantity*</label>
                    <input type="number" id="stock_quantity" name="stock_quantity" value="<?php echo $stock_quantity; ?>" required>
                </div>

                <div class="form-group">
                    <label>Product Images (Max 5)</label>
                    <div style="display: flex; gap: 10px;">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <div>
                                <label for="image_file_<?php echo $i; ?>">Image <?php echo $i; ?></label>
                                <input type="file" name="image_file_<?php echo $i; ?>" id="image_file_<?php echo $i; ?>" accept="image/jpeg, image/png, image/gif, image/webp">
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description"><?php echo $description; ?></textarea>
                </div>
                

                <div class="form-group">
                    <label>
                        <input type="checkbox" name="is_featured" <?php echo $is_featured ? 'checked' : ''; ?>>
                        Feature on homepage
                    </label>
                </div>
                <button type="submit" name="add_product" class="submit-button">Add Product</button>
            </form>
        </div>

        <h2>Existing Products</h2>

        <div class="product-list">
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <div class="product-item">
                        <div class="product-image-container">
                            <?php
                            $image_paths = explode(",", $row['image_path']);
                            foreach ($image_paths as $image_path) {
                                if (!empty($image_path)) {
                                    echo '<img src="' . htmlspecialchars($image_path) . '" alt="' . htmlspecialchars($row['name']) . '" class="product-image">';
                                }
                            }
                            ?>
                        </div>
                        <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                        <p>Rs.<?php echo number_format($row['price'], 2); ?></p>
                        <p>Stock: <?php echo $row['stock_quantity']; ?></p>
                        <p><?php echo htmlspecialchars($row['description']); ?></p>
                        <p>Featured: <?php echo $row['is_featured'] ? 'Yes' : 'No'; ?></p>

                        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" onsubmit="return confirm('Are you sure you want to delete this product?');">
                            <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                            <button type="submit" name="delete_product" class="delete-btn">Delete</button>
                        </form>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No products found.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer Section -->
    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <ul>
                    <h3>Contact Us</h3>
                    <address>
                        <p>101, MPCS Street Akkaraipattu 02</p>
                        <p>Hardware City, HC 15470</p>
                        <p>Email: info@alienterprise.com</p>
                        <p>Phone: (+94) 77838133</p>
                    </address>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Follow Us</h3>
                <div class="social-icons">
                    <a href="#" aria-label="Facebook"><img src="icons/facebook.svg" alt="Facebook"></a>
                    <a href="#" aria-label="Twitter"><img src="icons/twitter.svg" alt="Twitter"></a>
                    <a href="#" aria-label="Instagram"><img src="icons/instagram.svg" alt="Instagram"></a>
                </div>
            </div>
        </div>
        <div class="copyright">
            <p>&copy; 2025 ALI Enterprise. All Rights Reserved.</p>
        </div>
    </footer>
</body>
</html>
<?php
// Close connection
$conn->close();
?>