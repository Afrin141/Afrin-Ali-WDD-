<?php
// Enable error reporting for troubleshooting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session
session_start();

// Include database connection
require_once 'db_connect.php';

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get product ID from URL
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Search functionality
$search_query = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$search_results = [];
if (!empty($search_query)) {
    $search_sql = "SELECT * FROM products WHERE name LIKE '%$search_query%' OR description LIKE '%$search_query%'";
    $search_result = $conn->query($search_sql);

    if ($search_result && $search_result->num_rows > 0) {
        while ($row = $search_result->fetch_assoc()) {
            $search_results[] = $row;
        }
    }
}

// If we have a valid product ID, fetch its details
$product = null;
if ($product_id > 0) {
    $sql = "SELECT * FROM products WHERE id = $product_id";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $product = $result->fetch_assoc();
    } else {
        // Product not found
        $error_message = "Product not found";
    }
}

// Fetch related products (random products as category isn't in your DB schema)
$related_products = [];
if ($product) {
    $related_sql = "SELECT * FROM products WHERE id != $product_id ORDER BY RAND() LIMIT 4";
    $related_result = $conn->query($related_sql);
    
    if ($related_result && $related_result->num_rows > 0) {
        while ($row = $related_result->fetch_assoc()) {
            $related_products[] = $row;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ($product) ? htmlspecialchars($product['name']) : 'Product Details'; ?> - ALI Enterprise</title>
    <link rel="stylesheet" href="HOME.css">
    <style>
        /* Additional styles for product view page */
        .product-view {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            margin-top: 2rem;
            margin-bottom: 2rem;
        }

        .product-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }

        .product-images {
            display: flex;
            flex-direction: column;
        }

        .main-image {
            width: 100%;
            height: 400px;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 1rem;
        }

        .main-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .image-thumbnails {
            display: flex;
            gap: 1rem;
            overflow-x: auto;
        }

        .thumbnail {
            width: 80px;
            height: 80px;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
            opacity: 0.7;
        }

        .thumbnail:hover, .thumbnail.active {
            opacity: 1;
            transform: scale(1.05);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        .thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .product-details h1 {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: var(--secondary-color);
        }

        .product-price {
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--accent-color);
            margin-bottom: 1rem;
        }

        .product-meta {
            display: flex;
            gap: 2rem;
            margin-bottom: 1.5rem;
        }

        .product-meta div {
            display: flex;
            flex-direction: column;
        }

        .meta-label {
            font-size: 0.9rem;
            color: #666;
        }

        .meta-value {
            font-weight: bold;
            color: #333;
            font-size: 1.1rem;
        }

        .product-description {
            margin-bottom: 2rem;
            line-height: 1.8;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            margin-bottom: 2rem;
            gap: 1rem;
        }

        .quantity-input {
            display: flex;
            align-items: center;
            border: 1px solid #ddd;
            border-radius: 4px;
            overflow: hidden;
        }

        .quantity-btn {
            background-color: #f0f0f0;
            border: none;
            padding: 0.5rem 1rem;
            cursor: pointer;
            font-size: 1.2rem;
            transition: all 0.3s ease;
        }

        .quantity-btn:hover {
            background-color: #e0e0e0;
        }

        #quantity {
            width: 50px;
            border: none;
            text-align: center;
            font-size: 1rem;
            padding: 0.5rem;
        }

        .action-buttons {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .add-to-cart, .buy-now, .add-to-whistles {
            padding: 1rem;
            border: none;
            border-radius: 4px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .add-to-cart {
            background-color: var(--primary-color);
            color: white;
            grid-column: 1;
        }

        .add-to-cart:hover {
            background-color: var(--hover-color);
            transform: translateY(-2px);
        }

        .buy-now {
            background-color: var(--accent-color);
            color: white;
            grid-column: 2;
        }

        .buy-now:hover {
            background-color: #c0392b;
            transform: translateY(-2px);
        }

        .add-to-whistles {
            background-color: #f0f0f0;
            color: #333;
            grid-column: 1 / 3;
        }

        .add-to-whistles:hover {
            background-color: #e0e0e0;
            transform: translateY(-2px);
        }

        .related-products {
            margin-top: 4rem;
        }

        .related-products h2 {
            text-align: center;
            margin-bottom: 2rem;
            position: relative;
            display: inline-block;
            left: 50%;
            transform: translateX(-50%);
        }

        .related-products h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 0;
            height: 3px;
            background-color: var(--primary-color);
            transition: width 0.8s ease;
            animation: expandLine 1s ease-out 0.5s forwards;
        }

        .related-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 2rem;
        }

        /* Profile dropdown styles */
        .profile-dropdown {
            position: relative;
            display: inline-block;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            background-color: #f9f9f9;
            min-width: 160px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1;
            border-radius: 4px;
        }

        .dropdown-content a {
            color: black;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            text-align: left;
        }

        .dropdown-content a:hover {
            background-color: #f1f1f1;
        }

        .profile-dropdown:hover .dropdown-content {
            display: block;
        }

        .profile-icon {
            cursor: pointer;
            padding: 5px;
            display: flex;
            align-items: center;
        }

        .profile-icon img {
            width: 24px;
            height: 24px;
            margin-right: 5px;
        }

        /* Notification styles */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #333;
            color: white;
            padding: 15px 20px;
            border-radius: 5px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            opacity: 0;
            transform: translateY(-20px);
            transition: opacity 0.3s, transform 0.3s;
            z-index: 1000;
        }

        .notification.visible {
            opacity: 1;
            transform: translateY(0);
        }

        @media (max-width: 768px) {
            .product-container {
                grid-template-columns: 1fr;
            }

            .main-image {
                height: 300px;
            }

            .action-buttons {
                grid-template-columns: 1fr;
            }

            .add-to-cart, .buy-now, .add-to-whistles {
                grid-column: 1;
            }

            .related-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            }
        }

        /* Product Gallery Styles */
        .product-gallery {
            position: relative;
            border-radius: 8px;
            overflow: hidden;
            background-color: white;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .main-image {
            width: 100%;
            height: 400px;
            overflow: hidden;
            position: relative;
        }

        .main-image img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            transition: transform 0.5s ease;
        }

        .thumbnail-container {
            display: flex;
            margin-top: 1rem;
            gap: 0.5rem;
            padding: 0.5rem;
            overflow-x: auto;
        }

        .thumbnail {
            width: 80px;
            height: 80px;
            border-radius: 4px;
            overflow: hidden;
            cursor: pointer;
            opacity: 0.7;
            transition: all 0.3s ease;
            flex-shrink: 0;
        }

        .thumbnail:hover {
            opacity: 1;
            transform: translateY(-3px);
        }

        .thumbnail.active {
            opacity: 1;
            box-shadow: 0 0 0 2px var(--primary-color);
        }

        .thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
    </style>
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
                <li><a href="product.php" aria-current="page">Products</a></li>
                <li><a href="track_order.html">Track Order</a></li>
                <li><a href="about_us.html">About Us</a></li>
                <li><a href="product_manager.php">Product Manager</a></li>
            </ul>
        </nav>

        <!-- Search Bar -->
        <div class="search-container">
            <form method="GET" action="view_product.php">
                <input id="search-input" type="search" name="search" placeholder="Search products..." aria-label="Search products" value="<?php echo htmlspecialchars($search_query); ?>">
                <button id="search-button" type="submit" aria-label="Search">🔍</button>
            </form>
        </div>

        <!-- Search Results Display -->
        <div id="search-results">
            <?php if (!empty($search_results)): ?>
                <?php foreach ($search_results as $prod): ?>
                    <div class="product-card">
                        <a href="view_product.php?id=<?php echo htmlspecialchars($prod['id']); ?>">
                            <img src="./Img/<?php echo htmlspecialchars($prod['image_url_1']); ?>" alt="<?php echo htmlspecialchars($prod['name']); ?>">
                            <h4><?php echo htmlspecialchars($prod['name']); ?></h4>
                            <p>Rs. <?php echo number_format($prod['price'], 2); ?></p>
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php elseif (!empty($search_query)): ?>
                <p>No products found matching your search.</p>
            <?php endif; ?>
        </div>

        <!-- Cart, Whistles, and Profile -->
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
            
            <!-- Profile Dropdown -->
            <div class="profile-dropdown">
                <div class="profile-icon">
                    <img src="icons/user-icon.svg" alt="Profile">
                    <span>Profile</span>
                </div>
                <div class="dropdown-content">
                    <a href="profile.php">My Profile</a>
                    <a href="my_orders.php">My Orders</a>
                    <a href="about_us.html">About Us</a>
                    <a href="logout.php">Logout</a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main>
        <?php if ($product): ?>
            <div class="product-view">
                <div class="product-container">
                    <!-- Product Images Section -->
                    <div class="product-detail-layout">
                        <div class="product-gallery">
                            <div class="main-image">
                                <img src="Img/<?php echo htmlspecialchars($product['image_url_1'] ?? ''); ?>" alt="<?php echo htmlspecialchars($product['name'] ?? ''); ?>" id="main-product-image">
                            </div>
                            <div class="thumbnail-container">
                                <?php
                                // Display all available product images as thumbnails
                                for ($i = 1; $i <= 5; $i++) {
                                    $image_url = $product['image_url_' . $i] ?? '';
                                    if (!empty($image_url)) {
                                        $active_class = ($i === 1) ? 'active' : '';
                                        ?>
                                        <div class="thumbnail <?php echo $active_class; ?>" onclick="changeMainImage('Img/<?php echo htmlspecialchars($image_url); ?>', this)">
                                            <img src="Img/<?php echo htmlspecialchars($image_url); ?>" alt="Product Image <?php echo $i; ?>">
                                        </div>
                                        <?php
                                    }
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Product Details Section -->
                    <div class="product-details">
                        <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                        <p class="product-price">Rs. <?php echo number_format($product['price'], 2); ?></p>
                        
                        <div class="product-meta">
                            <div>
                                <span class="meta-label">Stock</span>
                                <span class="meta-value" id="stock-quantity"><?php echo htmlspecialchars($product['stock_quantity']); ?></span>
                            </div>
                            <div>
                                <span class="meta-label">Featured</span>
                                <span class="meta-value"><?php echo ($product['is_featured']) ? 'Yes' : 'No'; ?></span>
                            </div>
                            <div>
                                <span class="meta-label">Added</span>
                                <span class="meta-value"><?php echo date('d M Y', strtotime($product['created_at'])); ?></span>
                            </div>
                        </div>
                        
                        <div class="product-description">
                            <h3>Description</h3>
                            <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                        </div>
                        
                        <!-- Quantity Controls -->
                        <div class="quantity-controls">
                            <label for="quantity">Quantity:</label>
                            <div class="quantity-input">
                                <button type="button" class="quantity-btn" id="decrease-quantity">-</button>
                                <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?php echo intval($product['stock_quantity']); ?>">
                                <button type="button" class="quantity-btn" id="increase-quantity">+</button>
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="action-buttons">
                            <button class="add-to-cart" 
                                    onclick="addToCart(<?php echo $product['id']; ?>)"
                                    data-product-id="<?php echo $product['id']; ?>"
                                    data-product="<?php echo htmlspecialchars($product['name']); ?>" 
                                    data-price="<?php echo $product['price']; ?>">
                                Add to Cart
                            </button>
                            <button class="buy-now" 
                                    data-product-id="<?php echo $product['id']; ?>"
                                    data-product="<?php echo htmlspecialchars($product['name']); ?>" 
                                    data-price="<?php echo $product['price']; ?>">
                                Buy Now
                            </button>
                            <button class="add-to-whistles"
                                    onclick="addToWhistles(<?php echo $product['id']; ?>)"
                                    data-product-id="<?php echo $product['id']; ?>"
                                    data-product="<?php echo htmlspecialchars($product['name']); ?>">
                                Add to Whistles
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Related Products Section -->
                <?php if (!empty($related_products)): ?>
                    <div class="related-products">
                        <h2>Related Products</h2>
                        <div class="related-grid">
                            <?php foreach ($related_products as $related): ?>
                                <article class="product-card">
                                    <div class="product-image">
                                        <?php 
                                        if (!empty($related['image_url_1'])) {
                                            echo '<img src="' . htmlspecialchars($related['image_url_1']) . '" 
                                                  alt="' . htmlspecialchars($related['name']) . '">';
                                        }
                                        ?>
                                    </div>
                                    <div class="product-info">
                                        <h3><?php echo htmlspecialchars($related['name']); ?></h3>
                                        <p class="product-price">Rs. <?php echo number_format($related['price'], 2); ?></p>
                                        <p class="stock-status">Stock: <?php echo htmlspecialchars($related['stock_quantity']); ?></p>
                                        <div class="product-actions">
                                            <a href="view_product.php?id=<?php echo $related['id']; ?>" class="view-details">View Details</a>
                                        </div>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="product-view">
                <div class="error-message">
                    <h2>Product Not Found</h2>
                    <p><?php echo isset($error_message) ? $error_message : 'The requested product could not be found.'; ?></p>
                    <a href="product.php" class="cta-button">Return to Products</a>
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

    <!-- JavaScript for image gallery and quantity control -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Image gallery functionality
            const mainImage = document.getElementById('main-product-image');
            const thumbnails = document.querySelectorAll('.thumbnail');
            
            thumbnails.forEach(thumb => {
                thumb.addEventListener('click', function() {
                    // Update main image
                    mainImage.src = this.getAttribute('data-image');
                    
                    // Update active thumbnail
                    thumbnails.forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                });
            });
            
            // Quantity control
            const quantityInput = document.getElementById('quantity');
            const decreaseBtn = document.getElementById('decrease-quantity');
            const increaseBtn = document.getElementById('increase-quantity');
            const stockQuantity = parseInt(document.getElementById('stock-quantity').textContent);
            
            decreaseBtn.addEventListener('click', function() {
                let currentVal = parseInt(quantityInput.value);
                if (currentVal > 1) {
                    quantityInput.value = currentVal - 1;
                }
            });
            
            increaseBtn.addEventListener('click', function() {
                let currentVal = parseInt(quantityInput.value);
                if (currentVal < stockQuantity) {
                    quantityInput.value = currentVal + 1;
                }
            });
            
            // Validate quantity input
            quantityInput.addEventListener('change', function() {
                let currentVal = parseInt(this.value);
                if (isNaN(currentVal) || currentVal < 1) {
                    this.value = 1;
                } else if (currentVal > stockQuantity) {
                    this.value = stockQuantity;
                }
            });
            
            // Button click animations
            const buttons = document.querySelectorAll('.action-buttons button');
            buttons.forEach(button => {
                button.addEventListener('click', function() {
                    this.classList.add('clicked');
                    setTimeout(() => {
                        this.classList.remove('clicked');
                    }, 400);
                });
            });
        });

        // Add to cart functionality
        function addToCart(productId) {
            const productName = document.querySelector('.add-to-cart[data-product-id="'+productId+'"]').getAttribute('data-product');
            const quantity = document.getElementById('quantity').value;
            
            // Here you'd typically use AJAX to add to cart without page refresh
            console.log(`Adding ${quantity} of ${productName} to cart`);
            
            // Show notification
            showNotification(`${productName} added to cart!`);
            
            // Update cart count
            updateCartCount(parseInt(quantity));
        }
        
        // Add to whistles functionality
        function addToWhistles(productId) {
            const productName = document.querySelector('.add-to-whistles[data-product-id="'+productId+'"]').getAttribute('data-product');
            
            // Here you'd typically use AJAX to add to whistles without page refresh
            console.log(`Adding ${productName} to whistles`);
            
            // Show notification
            showNotification(`${productName} added to whistles!`);
            
            // Update whistles count
            updateWhistlesCount(1);
        }
        
        // Buy now functionality
        document.querySelector('.buy-now').addEventListener('click', function() {
            const productId = this.getAttribute('data-product-id');
            const quantity = document.getElementById('quantity').value;
            
            // Redirect to checkout page with product info
            window.location.href = `checkout.php?product_id=${productId}&quantity=${quantity}`;
        });
        
        // Helper function to show notification
        function showNotification(message) {
            // Create notification element if it doesn't exist
            let notification = document.querySelector('.notification');
            if (!notification) {
                notification = document.createElement('div');
                notification.className = 'notification';
                document.body.appendChild(notification);
            }
            
            // Set message and show notification
            notification.textContent = message;
            notification.classList.add('visible');
            
            // Hide notification after delay
            setTimeout(() => {
                notification.classList.remove('visible');
            }, 3000);
        }
        
        // Helper function to update cart count
        function updateCartCount(increment) {
            const cartCount = document.getElementById('cart-count');
            if (cartCount) {
                let count = parseInt(cartCount.textContent) || 0;
                cartCount.textContent = count + increment;
                cartCount.classList.add('active');
            }
        }
        
        // Helper function to update whistles count
        function updateWhistlesCount(increment) {
            const whistlesCount = document.getElementById('whistles-count');
            if (whistlesCount) {
                let count = parseInt(whistlesCount.textContent) || 0;
                whistlesCount.textContent = count + increment;
                whistlesCount.classList.add('active');
            }
        }

        function changeMainImage(imageUrl, clickedThumbnail) {
            // Update main image
            document.getElementById('main-product-image').src = imageUrl;
            
            // Update active thumbnail
            document.querySelectorAll('.thumbnail').forEach(thumb => {
                thumb.classList.remove('active');
            });
            clickedThumbnail.classList.add('active');
        }
    </script>
</body>

</html>
<?php
// Close connection
$conn->close();
?>