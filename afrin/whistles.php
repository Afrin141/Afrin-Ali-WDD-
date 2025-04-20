<?php
session_start(); // Start the session
include 'db_connect.php';
// Check if user ID is set in the session
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    die("User  ID is not set in the session.");
}

// Handle POST request for adding to whistles
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['product_id'])) {
        $product_id = $_POST['product_id'];

        // Validate user exists
        $user_check_stmt = $conn->prepare("SELECT id FROM users WHERE id = ?");
        $user_check_stmt->bind_param("i", $user_id);
        $user_check_stmt->execute();
        $user_check_result = $user_check_stmt->get_result();

        if ($user_check_result->num_rows === 0) {
            die("User  ID does not exist in the users table.");
        }

        // Validate product exists
        $stmt = $conn->prepare("SELECT id FROM products WHERE id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Check if product already in whistles
            $check_stmt = $conn->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
            $check_stmt->bind_param("ii", $user_id, $product_id);
            $check_stmt->execute();
            $whistle_result = $check_stmt->get_result();

            if ($whistle_result->num_rows > 0) {
                $response = ['success' => true, 'message' => 'Product already in your whistles'];
            } else {
                // Add new item to whistles
                $add_stmt = $conn->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
                $add_stmt->bind_param("ii", $user_id, $product_id);

                if ($add_stmt->execute()) {
                    $response = ['success' => true, 'message' => 'Product added to whistles'];
                } else {
                    $response = ['success' => false, 'message' => 'Failed to add product to whistles'];
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

    // Return the response as JSON
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
// Get all whistles for the current user
$stmt = $conn->prepare("SELECT * FROM whistles WHERE user_id = ?");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$whistle_result = $stmt->get_result();

$whistleItems = array();
while ($row = $whistle_result->fetch_assoc()) {
    $whistleItems[] = $row;
}

$stmt->close();

// Get product details for each whistle
$whistleProducts = array();
foreach ($whistleItems as $whistle) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $whistle['product_id']);
    $stmt->execute();
    $product_result = $stmt->get_result();
    
    if ($product_result->num_rows > 0) {
        $product = $product_result->fetch_assoc();
        $whistleProducts[] = $product;
    }
    
    $stmt->close();
}

// Redirect to home page if accessed directly
header('Location: home.php');
exit;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ALI Enterprise - My Whistles</title>
    <!-- Whistles Page Styles -->
<style>
    /* Styles */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        line-height: 1.6;
        color: #333;
        background-color: #f9f9f9;
        transition: background-color 0.3s ease;
    }

    /* Animation Variables */
    :root {
        --primary-color: #3498db;
        --secondary-color: #2c3e50;
        --accent-color: #e74c3c;
        --hover-color: #2980b9;
        --transition-speed: 0.3s;
    }
    /* Whistles Page Styles */
    .whistles-banner {
        background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('../images/whistles-bg.jpg');
        background-size: cover;
        background-position: center;
        color: white;
        text-align: center;
        padding: 4rem 2rem;
        animation: fadeIn 0.8s ease-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }

    .whistles-banner h1 {
        font-size: 2.5rem;
        margin-bottom: 1rem;
        text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.5);
    }

    .whistles-banner p {
        font-size: 1.1rem;
        max-width: 700px;
        margin: 0 auto;
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
    }

    .whistles-container {
        max-width: 1200px;
        margin: 3rem auto;
        padding: 0 2rem;
    }

    .whistles-empty {
        text-align: center;
        padding: 5rem 2rem;
        background-color: white;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        margin-top: 2rem;
        animation: fadeIn 0.5s ease-out;
    }

    .whistles-empty h2 {
        color: var(--secondary-color);
        margin-bottom: 1rem;
    }

    .whistles-empty p {
        margin-bottom: 2rem;
        color: #666;
    }

    .whistles-empty a {
        display: inline-block;
        padding: 0.8rem 2rem;
        background-color: var(--primary-color);
        color: white;
        text-decoration: none;
        border-radius: 30px;
        font-weight: bold;
        transition: all var(--transition-speed);
    }

    .whistles-empty a:hover {
        background-color: var(--hover-color);
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }

    .whistles-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 2rem;
    }

    .whistle-card {
        background-color: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        transition: all var(--transition-speed);
        opacity: 0;
        transform: translateY(20px);
        animation: fadeInItems 0.5s ease-out forwards;
    }

    .whistle-card:nth-child(1) { animation-delay: 0.1s; }
    .whistle-card:nth-child(2) { animation-delay: 0.2s; }
    .whistle-card:nth-child(3) { animation-delay: 0.3s; }
    .whistle-card:nth-child(4) { animation-delay: 0.4s; }
    .whistle-card:nth-child(5) { animation-delay: 0.5s; }
    .whistle-card:nth-child(6) { animation-delay: 0.6s; }

    @keyframes fadeInItems {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .whistle-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
    }

    .whistle-placeholder {
        height: 200px;
        background-color: #eee;
        display: flex;
        justify-content: center;
        align-items: center;
        color: #999;
        font-size: 1.2rem;
    }

    .whistle-info {
        padding: 1.5rem;
    }

    .whistle-info h3 {
        margin-bottom: 0.5rem;
        font-size: 1.2rem;
    }

    .whistle-actions {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.5rem;
        margin-top: 1rem;
    }

    .whistle-actions button {
        padding: 0.6rem;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-weight: bold;
        transition: all 0.3s ease;
    }

    .view-product {
        background-color: var(--primary-color);
        color: white;
    }

    .view-product:hover {
        background-color: var(--hover-color);
        transform: translateY(-2px);
    }

    .remove-whistle {
        background-color: #f0f0f0;
        color: #333;
    }

    .remove-whistle:hover {
        background-color: #e0e0e0;
        transform: translateY(-2px);
    }

    .add-to-cart {
        background-color: var(--accent-color);
        color: white;
        grid-column: 1 / 3;
    }

    .add-to-cart:hover {
        background-color: #c0392b;
        transform: translateY(-2px);
    }

    /* Clear Whistles Button */
    .clear-whistles {
        display: block;
        margin: 2rem auto;
        padding: 0.8rem 2rem;
        background-color: #f0f0f0;
        color: #333;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-weight: bold;
        transition: all 0.3s ease;
    }

    .clear-whistles:hover {
        background-color: #e0e0e0;
        transform: translateY(-2px);
    }

    /* Footer Animation */
    footer {
        background-color: var(--secondary-color);
        color: white;
        padding: 3rem 2rem 1rem;
    }

    .footer-content {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 2rem;
        margin-bottom: 2rem;
    }

    .footer-section h3 {
        position: relative;
        margin-bottom: 1.5rem;
        padding-bottom: 0.5rem;
    }

    .footer-section h3::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 50px;
        height: 2px;
        background-color: var(--primary-color);
    }

    .footer-section ul {
        list-style: none;
        padding: 0;
    }

    .footer-section ul li {
        margin-bottom: 0.5rem;
    }

    .footer-section a {
        color: white;
        text-decoration: none;
        transition: color var(--transition-speed);
    }

    .footer-section a:hover {
        color: var(--primary-color);
    }

    .footer-section address {
        font-style: normal;
    }

    .social-icons {
        display: flex;
        gap: 1rem;
        align-items: center;
    }

    .social-icons a {
        display: inline-block;
        transition: transform var(--transition-speed);
    }

    .social-icons a:hover {
        transform: translateY(-5px);
    }

    .social-icons img {
        width: 30px;
        height: 30px;
        filter: brightness(0) invert(1);
    }

    /* Copyright */
    .copyright {
        text-align: center;
        padding-top: 1rem;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
    }

    /* Notification Styles */
    .notification {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background-color: #4CAF50;
        color: white;
        padding: 10px 20px;
        border-radius: 4px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        opacity: 0;
        transform: translateY(20px);
        transition: opacity 0.3s ease, transform 0.3s ease;
        z-index: 1000;
    }

    .notification.visible {
        opacity: 1;
        transform: translateY(0);
    }

    /* Media Queries for Responsiveness */
    @media (max-width: 768px) {
        header {
            padding: 1rem;
        }

        .logo {
            font-size: 1.5rem;
        }

        nav {
            position: absolute;
            top: 100%;
            left:             0;
            width: 100%;
            background: linear-gradient(to bottom, var(--secondary-color), #34495e);
            padding: 1rem;
            display: none;
        }

        nav ul {
            flex-direction: column;
            align-items: center;
        }

        nav ul li {
            margin: 0.5rem 0;
        }

        nav ul li a {
            padding: 0.5rem;
        }

        nav.show {
            display: block;
        }

        #mobile-menu-toggle {
            display: block;
        }

        .search-container {
            width: 100%;
            margin-top: 0.5rem;
        }

        #search-input {
            width: 100%;
            border-radius: 4px;
        }

        #search-button {
            border-radius: 4px;
        }

        .user-actions {
            margin-top: 0.5rem;
        }

        .whistles-banner {
            padding: 2rem 1rem;
        }

        .whistles-banner h1 {
            font-size: 2rem;
        }

        .whistles-container {
            padding: 0 1rem;
        }

        .whistles-grid {
            grid-template-columns: 1fr;
        }

        .footer-content {
            grid-template-columns: 1fr;
        }

        .footer-section {
            margin-bottom: 2rem;
        }

        .copyright {
            padding: 1rem;
        }
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
                <li><a href="product.php">Products</a></li>
                <li><a href="about_us.html">About Us</a></li>
                <li><a href="product_manager.php">Product Manager</a></li>
                <li><a href="whistles.php" aria-current="page">My Whistles</a></li>
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
                <img src="icons/whistle-icon.svg" alt="">
                <span id="whistles-count" class="badge"><?php echo count($whistleItems); ?></span>
            </button>
        </div>
    </header>

    <!-- Main Content -->
    <main>
        <section class="whistles-banner">
            <h1>My Whistles</h1>
            <p>Check out the products you've whistled for later.</p>
        </section>

        <div class="whistles-container">
            <?php if (empty($whistleProducts)): ?>
            <div class="whistles-empty">
                <h2>Your Whistle List is Empty</h2>
                <p>Start adding products to your whistle list to keep track of items you're interested in.</p>
                <a href="product.php" class="cta-button">Explore Products</a>
            </div>
            <?php else: ?>
            <div class="whistles-grid">
                <?php foreach ($whistleProducts as $product): ?>
                <div class="whistle-card">
                    <div class="whistle-placeholder">
                    <?php
                    $image_paths = explode(",", $product['image_path']);
                    foreach ($image_paths as $image_path) {
                        if (!empty($image_path)) {
                            echo '<img src="' . htmlspecialchars($image_path) . '" 
                                    alt="' . htmlspecialchars($product['name']) . '" 
                                    style="width: 100%; height: 200px; object-fit: cover;">';
                        }
                    }
                    ?>
                    </div>
                    <div class="whistle-info">
                        <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                        <div class="whistle-actions">
                        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                                <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product['id']); ?>">
                                <button type="submit" class="remove-whistle" name="remove_whistle">Remove</button>
                            </form>
                            <button class="view-product" onclick="window.location.href='product.php?id=<?php echo htmlspecialchars($product['id']); ?>'">View Product</button>
                            <button class="add-to-cart" onclick="addToCart(<?php echo htmlspecialchars($product['id']); ?>)">Add to Cart</button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <button type="submit" class="clear-whistles" name="clear_whistles">Clear All Whistles</button>
            </form>
            <?php endif; ?>
        </div>
    </main>

    <!-- Footer Section -->
    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h3>Contact Us</h3>
                <address>
                    <p>101, MPCS Street Akkaraipattu 02</p>
                    <p>Hardware City, HC 15470</p>
                    <p>Email: info@alienterprise.com</p>
                    <p>Phone: (+94) 77838133</p>
                </address>
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
    document.addEventListener('DOMContentLoaded', function() {
        // Mobile Menu Toggle
        const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
        const navMenu = document.getElementById('nav-menu');

        if (mobileMenuToggle && navMenu) {
            mobileMenuToggle.addEventListener('click', function() {
                navMenu.classList.toggle('show');
            });
        }

        // Shopping Cart Functionality
        const cartButton = document.getElementById('cart-button');
        if (cartButton) {
            cartButton.addEventListener('click', function() {
                window.location.href = 'order.html'; // Redirect to the orders page
            });
        }

        // Search functionality
        const searchButton = document.getElementById('search-button');
        const searchInput = document.getElementById('search-input');

        if (searchButton && searchInput) {
            searchButton.addEventListener('click', function() {
                performSearch();
            });

            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    performSearch();
                }
            });
        }

        function performSearch() {
            const searchTerm = searchInput.value.trim().toLowerCase();
            if (searchTerm) {
                // Redirect to products page with search term
                window.location.href = `product.php?search=${encodeURIComponent(searchTerm)}`;
            }
        }

        // Update whistle count display in header
        function updateWhistleCountDisplay() {
            const whistleCountSpan = document.getElementById('whistles-count');
            if (whistleCountSpan) {
                // Get the current count from the server-side
                let currentCount = <?php echo count($whistleItems); ?>;
                whistleCountSpan.textContent = currentCount;
            }
        }

        // Call updateWhistleCountDisplay on page load
        updateWhistleCountDisplay();
    });

    function addToCart(productId) {
        // Implement AJAX call to add product to cart
        console.log(`Product ${productId} added to cart.`);
    }
    </script>
</body>
</html>
<?php
// Close connection
$conn->close();
?>v