<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Amazon Basics 8 oz Hammer, Turquoise - Ali Enterprise</title>
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

        /* Header Animations */
        header {
            background: linear-gradient(to right, var(--secondary-color), #34495e);
            padding: 1rem 2rem;
            position: sticky;
            top: 0;
            z-index: 100;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            animation: headerFadeIn 0.8s ease-out;
        }

        @keyframes headerFadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logo {
            font-size: 1.8rem;
            font-weight: bold;
            color: white;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
            transition: all var(--transition-speed);
        }

        .logo:hover {
            transform: scale(1.05);
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }

        /* Navigation Animation */
        nav ul {
            display: flex;
            list-style: none;
        }

        nav ul li {
            margin: 0 0.5rem;
            opacity: 0;
            animation: fadeInNavItems 0.5s ease-out forwards;
        }

        nav ul li:nth-child(1) {
            animation-delay: 0.1s;
        }

        nav ul li:nth-child(2) {
            animation-delay: 0.2s;
        }

        nav ul li:nth-child(3) {
            animation-delay: 0.3s;
        }

        nav ul li:nth-child(4) {
            animation-delay: 0.4s;
        }

        nav ul li:nth-child(5) {
            animation-delay: 0.5s;
        }

        @keyframes fadeInNavItems {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        nav ul li a {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            transition: all var(--transition-speed);
            position: relative;
        }

        nav ul li a::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 50%;
            background-color: white;
            transition: all var(--transition-speed);
        }

        nav ul li a:hover::after {
            width: 80%;
            left: 10%;
        }

        nav ul li a:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        nav ul li a[aria-current="page"] {
            background-color: rgba(255, 255, 255, 0.2);
        }

        nav ul li a[aria-current="page"]::after {
            width: 80%;
            left: 10%;
        }

        /* All the rest of your CSS goes here */

        /* Additional Product Page Specific Styles */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .product-container {
            display: flex;
            flex-wrap: wrap;
            margin-top: 20px;
        }

        .product-images {
            flex: 1;
            min-width: 300px;
            margin-right: 20px;
        }

        .main-image {
            max-width: 100%;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 10px;
        }

        .thumbnail-images {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .thumbnail {
            width: 60px;
            height: 60px;
            border: 1px solid #ddd;
            border-radius: 4px;
            cursor: pointer;
            object-fit: cover;
        }

        .thumbnail:hover {
            border-color: var(--primary-color);
        }

        .product-details {
            flex: 2;
            min-width: 300px;
        }

        .product-title {
            font-size: 1.8rem;
            margin-bottom: 10px;
            color: #333;
        }

        .product-brand {
            color: var(--primary-color);
            margin-bottom: 15px;
        }

        .product-rating {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .rating-stars {
            color: #FFA41C;
            margin-right: 5px;
        }

        .product-price {
            font-size: 1.8rem;
            font-weight: bold;
            color: #B12704;
            margin: 15px 0;
        }

        .shipping-info {
            margin-bottom: 15px;
        }

        .stock-status {
            color: #007600;
            font-weight: bold;
            margin: 15px 0;
        }

        .product-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
            max-width: 400px;
        }

        .quantity-select {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 10px;
        }

        .add-to-cart-btn {
            background-color: #FFD814;
            border: none;
            padding: 10px 20px;
            border-radius: 20px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s;
        }

        .add-to-cart-btn:hover {
            background-color: #F7CA00;
        }

        .buy-now-btn {
            background-color: #FFA41C;
            border: none;
            padding: 10px 20px;
            border-radius: 20px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s;
        }

        .buy-now-btn:hover {
            background-color: #FF8F00;
        }

        .product-description {
            margin-top: 30px;
        }

        .product-details-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .product-details-table tr {
            border-bottom: 1px solid #eee;
        }

        .product-details-table th {
            text-align: left;
            padding: 10px;
            width: 150px;
            background-color: #f5f5f5;
        }

        .product-details-table td {
            padding: 10px;
        }

        .color-options {
            display: flex;
            gap: 10px;
            margin: 15px 0;
        }

        .color-option {
            border: 1px solid #ddd;
            padding: 5px;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .color-option.active {
            border: 2px solid var(--primary-color);
        }

        .color-option:hover {
            border-color: #aaa;
        }

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

        @media (max-width: 768px) {
            .product-container {
                flex-direction: column;
            }

            .product-images {
                margin-right: 0;
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Header Section -->
    <header>
        <a href="index.php" class="logo">Ali Enterprise</a>
        <nav>
            <ul>
                <li><a href="index.php" aria-current="page">Home</a></li>
                <li><a href="products.php">Products</a></li>
                <li><a href="categories.php">Categories</a></li>
                <li><a href="about.php">About</a></li>
                <li><a href="contact.php">Contact</a></li>
            </ul>
        </nav>
        <div class="search-container">
            <input type="text" id="search-input" placeholder="Search...">
            <button id="search-button">
                <i class="fa fa-search"></i>
            </button>
        </div>
        <div class="user-actions">
            <button class="icon-button" id="cart-button">
                <img src="images/cart-icon.png" alt="Cart">
                <span class="badge" id="cart-badge">0</span>
            </button>
            <button class="icon-button" id="wishlist-button">
                <img src="images/heart-icon.png" alt="Wishlist">
                <span class="badge" id="wishlist-badge">0</span>
            </button>
        </div>
        <button id="mobile-menu-toggle">
            <i class="fa fa-bars"></i>
        </button>
    </header>

    <!-- Main Content -->
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li><a href="index.php">Home</a></li>
                <li><a href="categories.php?category=tools">Tools & Home Improvement</a></li>
                <li>Amazon Basics 8 oz Hammer, Turquoise</li>
            </ol>
        </nav>

        <?php
        // Database connection
        $servername = "localhost";
        $username = "root"; // 
        $password = ""; //
        $dbname = "ali_enterprise";

        // Create connection
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Get product ID from URL
        $product_id = isset($_GET['id']) ? intval($_GET['id']) : 1; // Default to product ID 1 if not specified

        // Query to get product details
        $sql = "SELECT * FROM products WHERE id = $product_id";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            // Output data of each row
            $row = $result->fetch_assoc();
            // For demonstration, we'll use hardcoded values to match the image example
            $product_name = "Amazon Basics 8 oz Hammer, Turquoise";
            $product_price = 7.69;
            $product_rating = 4.8;
            $product_reviews = 2462;
            $product_brand = "Amazon Basics";
            $product_color = "Turquoise";
            $product_weight = "0.37 Kilograms";
            $product_size = "8 oz";
            $product_material = "Alloy Steel";
            $product_in_stock = true;
            $product_shipping = 75.84;
        ?>

        <div class="product-container">
            <div class="product-images">
                <img src="images/hammer-turquoise.jpg" alt="Amazon Basics 8 oz Hammer, Turquoise" class="main-image" id="main-product-image">
                <div class="thumbnail-images">
                    <img src="images/hammer-turquoise.jpg" alt="Hammer front view" class="thumbnail" onclick="changeImage(this.src)">
                    <img src="images/hammer-turquoise-side.jpg" alt="Hammer side view" class="thumbnail" onclick="changeImage(this.src)">
                    <img src="images/hammer-turquoise-grip.jpg" alt="Hammer grip detail" class="thumbnail" onclick="changeImage(this.src)">
                    <img src="images/hammer-turquoise-head.jpg" alt="Hammer head detail" class="thumbnail" onclick="changeImage(this.src)">
                </div>
            </div>

            <div class="product-details">
                <h1 class="product-title"><?php echo $product_name; ?></h1>
                <p class="product-brand">Visit the <?php echo $product_brand; ?> Store</p>
                
                <div class="product-rating">
                    <div class="rating-stars">★★★★★</div>
                    <span><?php echo $product_rating; ?> (<?php echo $product_reviews; ?>)</span>
                </div>
                
                <div class="product-price">
                    <span class="currency">$</span><?php echo $product_price; ?>
                </div>
                
                <div class="shipping-info">
                    $<?php echo $product_shipping; ?> Shipping & Import Charges to Sri Lanka
                    <a href="#" id="details-link">Details</a>
                </div>

                <div>
                    <strong>Delivery Wednesday, April 16.</strong>
                    <div>Order within 19 hrs 59 mins</div>
                </div>
                
                <div class="stock-status">
                    In Stock
                </div>
                
                <div>
                    <label for="quantity">Quantity:</label>
                    <select id="quantity" class="quantity-select">
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                    </select>
                </div>

                <div>
                    <p>Color: <?php echo $product_color; ?></p>
                    <div class="color-options">
                        <div class="color-option active">
                            <img src="images/hammer-turquoise-thumb.jpg" alt="Turquoise" width="40" height="40">
                            <div>$7.69</div>
                        </div>
                        <div class="color-option">
                            <img src="images/hammer-pink-thumb.jpg" alt="Pink" width="40" height="40">
                            <div>$8.54</div>
                        </div>
                    </div>
                </div>
                
                <div class="product-actions">
                    <button class="add-to-cart-btn" onclick="addToCart()">Add to Cart</button>
                    <button class="buy-now-btn" onclick="buyNow()">Buy Now</button>
                </div>

                <table class="product-details-table">
                    <tr>
                        <th>Brand</th>
                        <td><?php echo $product_brand; ?></td>
                    </tr>
                    <tr>
                        <th>Size</th>
                        <td><?php echo $product_size; ?></td>
                    </tr>
                    <tr>
                        <th>Head Material</th>
                        <td><?php echo $product_material; ?></td>
                    </tr>
                    <tr>
                        <th>Handle Material</th>
                        <td><?php echo $product_material; ?></td>
                    </tr>
                    <tr>
                        <th>Color</th>
                        <td><?php echo $product_color; ?></td>
                    </tr>
                    <tr>
                        <th>Item Weight</th>
                        <td><?php echo $product_weight; ?></td>
                    </tr>
                </table>

                <div class="product-description">
                    <h2>About this item</h2>
                    <ul>
                        <li>8-ounce hammer with fiberglass handle and rubber grip; great for light-duty projects and repairs around the house</li>
                        <li>Steel head with polished finish; flat striking face on one side and curved claw on the other</li>
                        <li>Ergonomic rubber grip absorbs shock and provides a secure hold</li>
                        <li>Fiberglass core helps dampen vibrations</li>
                        <li>Measures approximately 10.6 by 4.2 by 1.1 inches</li>
                    </ul>
                </div>
            </div>
        </div>

        <?php
        } else {
            echo "<p>Product not found</p>";
        }
        $conn->close();
        ?>
    </div>

    <!-- Footer -->
    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h3>About Us</h3>
                <p>Ali Enterprise offers quality tools and home improvement products at competitive prices.</p>
            </div>
            <div class="footer-section">
                <h3>Contact Us</h3>
                <address>
                    123 Main Street<br>
                    Colombo, Sri Lanka<br>
                    Phone: (123) 456-7890<br>
                    Email: info@alienterprise.com
                </address>
            </div>
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="products.php">Products</a></li>
                    <li><a href="faq.php">FAQ</a></li>
                    <li><a href="privacy.php">Privacy Policy</a></li>
                    <li><a href="terms.php">Terms of Service</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Follow Us</h3>
                <div class="social-icons">
                    <a href="#"><img src="images/facebook-icon.png" alt="Facebook"></a>
                    <a href="#"><img src="images/twitter-icon.png" alt="Twitter"></a>
                    <a href="#"><img src="images/instagram-icon.png" alt="Instagram"></a>
                    <a href="#"><img src="images/youtube-icon.png" alt="YouTube"></a>
                </div>
            </div>
        </div>
        <div class="copyright">
            <p>&copy; 2025 Ali Enterprise. All Rights Reserved.</p>
        </div>
    </footer>

    <!-- Notification -->
    <div class="notification" id="notification">Item added to cart</div>

    <script>
        function changeImage(src) {
            document.getElementById('main-product-image').src = src;
        }

        function addToCart() {
            // Add to cart functionality would go here
            // For now, we'll just show a notification
            const notification = document.getElementById('notification');
            notification.textContent = 'Item added to cart';
            notification.classList.add('visible');
            
            // Update cart badge
            const cartBadge = document.getElementById('cart-badge');
            const currentCount = parseInt(cartBadge.textContent);
            cartBadge.textContent = currentCount + 1;
            cartBadge.classList.add('active');
            
            // Hide notification after 3 seconds
            setTimeout(() => {
                notification.classList.remove('visible');
            }, 3000);
        }

        function buyNow() {
            // Buy now functionality would go here
            window.location.href = 'checkout.php';
        }

        // Mobile menu toggle
        document.getElementById('mobile-menu-toggle').addEventListener('click', function() {
            document.querySelector('nav').classList.toggle('show');
        });
    </script>
</body>
</html>