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
$product = null;
$error = '';

// Check if product ID is provided              
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $product_id = intval($_GET['id']);
    
    // Fetch product details
    $sql = "SELECT * FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
    } else {
        $error = "Product not found";
    }
    
    $stmt->close();
} else {
    $error = "No product specified";
}

// Handle ratings submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_rating'])) {
    $rating = intval($_POST['rating']);
    $review = trim($_POST['review']);
    $product_id = intval($_POST['product_id']);
    
    // Validate rating
    if ($rating >= 1 && $rating <= 5) {
        $sql = "INSERT INTO product_ratings (product_id, rating, review, created_at) 
                VALUES (?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iis", $product_id, $rating, $review);
        
        if ($stmt->execute()) {
            // Redirect to avoid form resubmission
            header("Location: product_details.php?id=$product_id&rating_success=1");
            exit();
        } else {
            $error = "Error saving rating: " . $stmt->error;
        }
        
        $stmt->close();
    } else {
        $error = "Invalid rating value";
    }
}

// Fetch product ratings
$ratings = [];
$average_rating = 0;
$total_ratings = 0;

if ($product) {
    $sql = "SELECT * FROM product_ratings WHERE product_id = ? ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product['id']);
    $stmt->execute();
    $ratings_result = $stmt->get_result();
    
    if ($ratings_result->num_rows > 0) {
        $rating_sum = 0;
        while ($row = $ratings_result->fetch_assoc()) {
            $ratings[] = $row;
            $rating_sum += $row['rating'];
        }
        $total_ratings = count($ratings);
        $average_rating = $total_ratings > 0 ? round($rating_sum / $total_ratings, 1) : 0;
    }
    
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product ? htmlspecialchars($product['name']) : 'Product Details'; ?> - ALI Enterprise</title>
    <link rel="stylesheet" href="HOME.css">
    <link rel="stylesheet" href="product_details.css">
</head>

<body>
    <!-- Header Section -->
    <header>
        <div class="logo">ALI Enterprise</div>
        <nav>
            <button id="mobile-menu-toggle" aria-label="Toggle Menu">☰</button>
            <ul id="nav-menu">
                <li><a href="home.php">Home</a></li>
                <li><a href="order.html">Orders</a></li>
                <li><a href="product.php">Products</a></li>
                <li><a href="track_order.html">Track Order</a></li>
                <li><a href="about_us.html">About Us</a></li>
                <li><a href="product_manager.php">Product Manager</a></li>
            </ul>
        </nav>

        <!-- Search Bar -->
        <div class="search-container">
            <input id="search-input" type="search" placeholder="Search products..." aria-label="Search products">
            <button id="search-button" aria-label="Search">🔍</button>
        </div>

        <!-- Cart and Whistles -->
        <div class="user-actions">
            <button id="cart-button" class="icon-button" aria-label="Shopping Cart">
                <img src="icons/cart-icon.svg" alt="">
                <span id="cart-count" class="badge">0</span>
            </button>
            <button id="whistles-button" class="icon-button" aria-label="Whistles">
                <a href="whistles.php" class="icon-button">
                    <img src="../images/whistle.svg" alt="Whistles">
                    <span id="whistles-count" class="badge">0</span>
                </a>
            </button>
        </div>
    </header>

    <!-- Main Content -->
    <main class="product-details-container">
        <?php if ($error): ?>
            <div class="error-message">
                <p><?php echo $error; ?></p>
                <a href="home.php" class="button">Return to Home</a>
            </div>
        <?php elseif ($product): ?>
            <div class="product-details">
                <div class="product-gallery">
                    <?php
                    $image_paths = explode(",", $product['image_path']);
                    if (!empty($image_paths[0])):
                    ?>
                        <div class="main-image">
                            <img id="main-product-image" src="<?php echo htmlspecialchars($image_paths[0]); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        </div>
                        
                        <?php if (count(array_filter($image_paths)) > 1): ?>
                            <div class="thumbnail-gallery">
                                <?php foreach ($image_paths as $index => $path): ?>
                                    <?php if (!empty($path)): ?>
                                    <img src="<?php echo htmlspecialchars($path); ?>" 
                                         alt="Thumbnail <?php echo $index + 1; ?>"
                                         onclick="changeMainImage('<?php echo htmlspecialchars($path); ?>')" 
                                         class="thumbnail <?php echo $index === 0 ? 'active' : ''; ?>">
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="main-image">
                            <div class="no-image">No Image Available</div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="product-info">
                    <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                    
                    <div class="product-rating">
                        <div class="stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <span class="star <?php echo $i <= $average_rating ? 'filled' : ''; ?>">★</span>
                            <?php endfor; ?>
                        </div>
                        <span class="rating-count"><?php echo $total_ratings; ?> ratings</span>
                    </div>
                    
                    <div class="product-price">
                        <span>Rs.<?php echo number_format($product['price'], 2); ?></span>
                    </div>
                    
                    <div class="stock-status <?php echo $product['stock_quantity'] > 0 ? 'in-stock' : 'out-of-stock'; ?>">
                        <?php if ($product['stock_quantity'] > 0): ?>
                            <span>In Stock: <?php echo $product['stock_quantity']; ?> units available</span>
                        <?php else: ?>
                            <span>Out of Stock</span>
                        <?php endif; ?>
                    </div>

                    <div class="product-description">
                        <h2>Product Description</h2>
                        <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                    </div>
                    
                    <?php if ($product['stock_quantity'] > 0): ?>
                    <div class="purchase-options">
                        <div class="quantity-selector">
                            <label for="quantity">Quantity:</label>
                            <div class="quantity-controls">
                                <button type="button" class="quantity-btn minus" onclick="decrementQuantity()">-</button>
                                <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?php echo $product['stock_quantity']; ?>">
                                <button type="button" class="quantity-btn plus" onclick="incrementQuantity()">+</button>
                            </div>
                        </div>
                        
                        <button class="add-to-cart-btn" onclick="addToCart(<?php echo $product['id']; ?>, '<?php echo addslashes($product['name']); ?>', <?php echo $product['price']; ?>)">
                            Add to Cart
                        </button>
                        
                        <button class="buy-now-btn" onclick="buyNow(<?php echo $product['id']; ?>)">
                            Buy Now
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Customer Ratings and Reviews -->
            <div class="customer-reviews">
                <h2>Customer Reviews</h2>
                
                <?php if (isset($_GET['rating_success'])): ?>
                <div class="success-message">
                    <p>Thank you for your review!</p>
                </div>
                <?php endif; ?>
                
                <div class="rating-summary">
                    <div class="average-rating">
                        <span class="big-rating"><?php echo $average_rating; ?></span>
                        <div class="stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <span class="star <?php echo $i <= $average_rating ? 'filled' : ''; ?>">★</span>
                            <?php endfor; ?>
                        </div>
                        <span><?php echo $total_ratings; ?> reviews</span>
                    </div>
                </div>
                
                <!-- Review Form -->
                <div class="write-review">
                    <h3>Write a Review</h3>
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?id=" . $product['id']); ?>">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        
                        <div class="rating-input">
                            <label>Your Rating:</label>
                            <div class="star-rating">
                                <?php for ($i = 5; $i >= 1; $i--): ?>
                                <input type="radio" id="star<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>" <?php echo $i === 5 ? 'checked' : ''; ?>>
                                <label for="star<?php echo $i; ?>">★</label>
                                <?php endfor; ?>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="review">Your Review:</label>
                            <textarea id="review" name="review" placeholder="Share your experience with this product..."></textarea>
                        </div>
                        
                        <button type="submit" name="submit_rating" class="submit-review">Submit Review</button>
                    </form>
                </div>
                
                <!-- Reviews List -->
                <div class="reviews-list">
                    <?php if (empty($ratings)): ?>
                        <p class="no-reviews">No reviews yet. Be the first to review this product!</p>
                    <?php else: ?>
                        <?php foreach ($ratings as $review): ?>
                            <div class="review-item">
                                <div class="review-header">
                                    <div class="review-stars">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <span class="star <?php echo $i <= $review['rating'] ? 'filled' : ''; ?>">★</span>
                                        <?php endfor; ?>
                                    </div>
                                    <span class="review-date"><?php echo date('F j, Y', strtotime($review['created_at'])); ?></span>
                                </div>
                                <p class="review-text"><?php echo nl2br(htmlspecialchars($review['review'])); ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </main>

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

    <script>
        // Function to change the main image when a thumbnail is clicked
        function changeMainImage(imagePath) {
            document.getElementById('main-product-image').src = imagePath;
            
            // Update active thumbnail state
            const thumbnails = document.querySelectorAll('.thumbnail');
            thumbnails.forEach(thumb => {
                if (thumb.src === imagePath) {
                    thumb.classList.add('active');
                } else {
                    thumb.classList.remove('active');
                }
            });
        }
        
        // Quantity adjustment functions
        function incrementQuantity() {
            const quantityInput = document.getElementById('quantity');
            const maxQuantity = parseInt(quantityInput.getAttribute('max'));
            let currentQuantity = parseInt(quantityInput.value);
            
            if (currentQuantity < maxQuantity) {
                quantityInput.value = currentQuantity + 1;
            }
        }
        
        function decrementQuantity() {
            const quantityInput = document.getElementById('quantity');
            let currentQuantity = parseInt(quantityInput.value);
            
            if (currentQuantity > 1) {
                quantityInput.value = currentQuantity - 1;
            }
        }
        
        // Cart functions
        function addToCart(productId, productName, productPrice) {
            const quantity = parseInt(document.getElementById('quantity').value);
            
            // Get existing cart or initialize empty cart
            let cart = JSON.parse(localStorage.getItem('cart')) || [];
            
            // Check if product already in cart
            const existingProductIndex = cart.findIndex(item => item.id === productId);
            
            if (existingProductIndex !== -1) {
                // Update quantity
                cart[existingProductIndex].quantity += quantity;
            } else {
                // Add new product
                cart.push({
                    id: productId,
                    name: productName,
                    price: productPrice,
                    quantity: quantity
                });
            }
            
            // Save updated cart
            localStorage.setItem('cart', JSON.stringify(cart));
            
            // Update cart count in header
            updateCartCount();
            
            // Show notification
            showNotification(`Added ${quantity} ${productName} to cart`);
        }
        
        function buyNow(productId) {
            // Add to cart first
            const quantity = parseInt(document.getElementById('quantity').value);
            const productName = document.querySelector('.product-info h1').textContent;
            const productPrice = parseFloat(document.querySelector('.product-price span').textContent.replace('Rs.', '').replace(',', ''));
            
            addToCart(productId, productName, productPrice);
            
            // Redirect to checkout
            window.location.href = 'checkout.php';
        }
        
        // Update cart count display
        function updateCartCount() {
            const cart = JSON.parse(localStorage.getItem('cart')) || [];
            const totalItems = cart.reduce((total, item) => total + item.quantity, 0);
            
            const cartCountElement = document.getElementById('cart-count');
            cartCountElement.textContent = totalItems;
            
            if (totalItems > 0) {
                cartCountElement.classList.add('active');
            } else {
                cartCountElement.classList.remove('active');
            }
        }
        
        // Show notification
        function showNotification(message) {
            // Create notification element if it doesn't exist
            let notification = document.querySelector('.notification');
            
            if (!notification) {
                notification = document.createElement('div');
                notification.className = 'notification';
                document.body.appendChild(notification);
            }
            
            // Set message and show
            notification.textContent = message;
            notification.classList.add('visible');
            
            // Hide after delay
            setTimeout(() => {
                notification.classList.remove('visible');
            }, 3000);
        }
        
        // Initialize cart count on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateCartCount();
            
            // Mobile menu toggle
            const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
            const navMenu = document.getElementById('nav-menu');
            
            if (mobileMenuToggle) {
                mobileMenuToggle.addEventListener('click', function() {
                    navMenu.classList.toggle('show');
                });
            }
        });
    </script>
</body>
</html>
<?php
// Close connection
$conn->close();
?>