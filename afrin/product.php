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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];
    // Validate and add the product to the wishlist
    // (Include your existing logic for adding to the database)
    
    // Return a JSON response
    echo json_encode(['success' => true, 'message' => 'Product added to your wishlist']);
    exit;
}
// Initialize variables for filtering and pagination
$search_query = isset($_GET['search']) ? $_GET['search'] : '';
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'name_asc';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$items_per_page = 12;
$offset = ($page - 1) * $items_per_page;

// Build the SQL query with filter
$sql = "SELECT * FROM products WHERE 1=1";
$count_sql = "SELECT COUNT(*) as total FROM products WHERE 1=1";

// Add search filter if provided
if (!empty($search_query)) {
    $sql .= " AND (name LIKE '%" . $conn->real_escape_string($search_query) . "%' OR 
               description LIKE '%" . $conn->real_escape_string($search_query) . "%')";
    $count_sql .= " AND (name LIKE '%" . $conn->real_escape_string($search_query) . "%' OR 
                   description LIKE '%" . $conn->real_escape_string($search_query) . "%')";
}

// Add sorting
switch ($sort_by) {
    case 'price_asc':
        $sql .= " ORDER BY price ASC";
        break;
    case 'price_desc':
        $sql .= " ORDER BY price DESC";
        break;
    case 'name_desc':
        $sql .= " ORDER BY name DESC";
        break;
    case 'newest':
        $sql .= " ORDER BY id DESC";
        break;
    default:
        $sql .= " ORDER BY name ASC";
}

// Get total number of products for pagination
$count_result = $conn->query($count_sql);
$total_products = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_products / $items_per_page);

// Add pagination
$sql .= " LIMIT $items_per_page OFFSET $offset";

// Get products
$result = $conn->query($sql);

// Debug
if (!$result) {
    echo "<!-- Error in SQL query: " . $conn->error . " -->";
}

$productCount = ($result) ? $result->num_rows : 0;
echo "<!-- Found $productCount products -->";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ALI Enterprise - All Products</title>
    <link rel="stylesheet" href="HOME.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --light-gray: #f5f7fa;
            --dark-gray: #34495e;
            --text-color: #2c3e50;
            --white: #ffffff;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--text-color);
            background-color: #f9fafb;
            margin: 0;
            padding: 0;
        }

        /* Updated Header Styles */
        header {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
            padding: 0.8rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .logo {
            font-size: 2rem;
            font-weight: 800;
            color: var(--primary-color);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: var(--transition);
        }

        .logo:hover {
            transform: translateY(-2px);
        }

        .logo-icon {
            font-size: 2.2rem;
            color: var(--secondary-color);
        }

        .logo-text {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            letter-spacing: -0.5px;
        }

        /* Updated Navigation */
        nav ul {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
            gap: 1.5rem;
            align-items: center;
        }

        nav a {
            color: var(--dark-gray);
            text-decoration: none;
            font-weight: 600;
            font-size: 1rem;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: var(--transition);
            position: relative;
        }

        nav a:hover {
            color: var(--primary-color);
            background: rgba(52, 152, 219, 0.1);
        }

        nav a[aria-current="page"] {
            color: var(--primary-color);
            background: rgba(52, 152, 219, 0.1);
        }

        /* Updated Search Container */
        .search-container {
            position: relative;
            max-width: 400px;
            width: 100%;
            margin: 0 1rem;
        }

        .search-container input {
            width: 100%;
            padding: 0.8rem 3rem 0.8rem 1.5rem;
            border: 2px solid #e9ecef;
            border-radius: 50px;
            font-size: 0.95rem;
            outline: none;
            transition: var(--transition);
            background: #f8f9fa;
        }

        .search-container input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
            background: white;
        }

        .search-container button {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1.2rem;
            color: var(--dark-gray);
            transition: var(--transition);
        }

        .search-container button:hover {
            color: var(--primary-color);
        }

        /* Updated User Actions */
        .user-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .icon-button {
            background: none;
            border: none;
            cursor: pointer;
            position: relative;
            padding: 0.6rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--dark-gray);
            transition: var(--transition);
            border-radius: 50%;
        }

        .icon-button:hover {
            background: rgba(52, 152, 219, 0.1);
            color: var(--primary-color);
            transform: translateY(-2px);
        }

        .badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--accent-color);
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        /* Updated Profile Dropdown */
        .profile-dropdown {
            position: relative;
        }

        .profile-icon {
            cursor: pointer;
            padding: 0.6rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: var(--transition);
            border-radius: 50px;
            background: #f8f9fa;
        }

        .profile-icon:hover {
            background: rgba(52, 152, 219, 0.1);
            color: var(--primary-color);
        }

        .dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            top: 100%;
            min-width: 220px;
            background: white;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            overflow: hidden;
            z-index: 100;
            margin-top: 0.5rem;
        }

        .dropdown-content a {
            display: flex;
            align-items: center;
            padding: 0.8rem 1.2rem;
            color: var(--dark-gray);
            text-decoration: none;
            transition: var (--transition);
            gap: 0.75rem;
            position: relative;
        }

        .dropdown-content a:hover {
            background: rgba(52, 152, 219, 0.1);
            color: var(--primary-color);
        }

        .dropdown-content a i {
            width: 20px;
            text-align: center;
        }

        .dropdown-content .badge {
            position: absolute;
            right: 1.2rem;
            background: var(--accent-color);
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        .profile-dropdown:hover .dropdown-content {
            display: block;
        }

        /* Mobile Menu Toggle */
        #mobile-menu-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--dark-gray);
            padding: 0.5rem;
            border-radius: 8px;
            transition: var(--transition);
        }

        #mobile-menu-toggle:hover {
            background: rgba(52, 152, 219, 0.1);
            color: var(--primary-color);
        }

        @media (max-width: 992px) {
            header {
                padding: 0.8rem 1.5rem;
            }

            .logo {
                font-size: 1.8rem;
            }

            .search-container {
                max-width: 300px;
            }
        }

        @media (max-width: 768px) {
            header {
                padding: 0.8rem 1rem;
            }

            .logo {
                font-size: 1.6rem;
            }

            #mobile-menu-toggle {
                display: block;
            }

            nav ul {
                display: none;
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: white;
                flex-direction: column;
                padding: 1rem;
                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            }

            nav ul.show {
                display: flex;
            }

            nav a {
                width: 100%;
                text-align: center;
                padding: 0.8rem;
            }

            .search-container {
                order: 3;
                max-width: 100%;
                margin: 1rem 0;
            }
        }

        /* Main Content Styles */
        main {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .product-listing {
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow);
            padding: 2rem;
        }

        .product-listing h1 {
            text-align: center;
            color: var(--dark-gray);
            font-size: 2rem;
            font-weight: 700;
        }

        /* Updated Filters Section */
        .filters {
            background-color: var(--white);
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
            align-items: center;
        }

        .filter-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .filters label {
            font-weight: 600;
            color: var(--dark-gray);
            white-space: nowrap;
        }

        .filters select, .filters input {
            padding: 0.75rem 1rem;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            outline: none;
            transition: var(--transition);
            background-color: var(--white);
            font-size: 0.95rem;
            min-width: 200px;
        }

        .filters select:focus, .filters input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(44, 62, 80, 0.1);
        }

        .filter-button {
            background-color: var(--primary-color);
            color: var(--white);
            border: none;
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            cursor: pointer;
            transition: var(--transition);
            font-weight: 600;
            align-items: center;
            gap: 0.5rem;
        }

        .filter-button:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
        }

        .reset-filters {
            background-color: var(--light-gray);
            color: var(--dark-gray);
            border: 2px solid #e9ecef;
        }

        .reset-filters:hover {
            background-color: #e2e8f0;
        }

        /* Updated Product Grid */
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 2rem;
            padding: 1rem;
        }

        .product-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 3px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .product-image {
            position: relative;
            width: 100%;
            height: 250px; /* Fixed height for image container */
            overflow: hidden;
            background-color: #f8f9fa;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .product-card:hover .product-image img {
            transform: scale(1.05);
        }

        .product-info {
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            flex: 1;
        }

        .product-info h2 {
            font-size: 1.2rem;
            margin: 0;
            color: var(--dark-gray);
            line-height: 1.4;
        }

        .stock-status {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            margin: 0.25rem 0;
        }

        .description {
            color: #666;
            font-size: 0.95rem;
            line-height: 1.5;
            margin: 0.5rem 0;
            flex: 1;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .price {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--accent-color);
            margin: 0.5rem 0;
        }

        .product-rating {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 0.5rem 0;
        }

        .star-rating {
            color: #ffd700;
        }

        .rating-count {
            color: #666;
            font-size: 0.9rem;
        }

        .product-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
            margin-top: auto;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 0 0 12px 12px;
        }

        .product-actions button {
            padding: 0.75rem 1rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            height: 100%;
            min-height: 40px;
            font-size: 0.95rem;
        }

        .add-to-cart {
            background: var(--primary-color);
            color: white;
            grid-column: span 2;
        }

        .add-to-cart:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
        }

        .add-to-whistles {
            background: var(--light-gray);
            color: var(--dark-gray);
            border: 1px solid #e2e8f0;
        }

        .add-to-whistles:hover {
            background: #e2e8f0;
            transform: translateY(-2px);
        }

        /* Updated Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 3rem 0;
            gap: 0.5rem;
        }

        .pagination a, .pagination span {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
        }

        .pagination a {
            background-color: var(--white);
            color: var(--dark-gray);
            box-shadow: var(--shadow);
        }

        .pagination a:hover {
            background-color: var(--light-gray);
            transform: translateY(-2px);
        }

        .pagination span.current {
            background-color: var(--primary-color);
            color: var(--white);
        }

        /* Category Badge */
        .category-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: var(--primary-color);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            opacity: 0.9;
            z-index: 1;
        }

        /* Stock Status */
        .stock-status {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 0.5rem 0;
            font-size: 0.9rem;
        }

        .stock-indicator {
            width: 10px;
            height: 10px;
            border-radius: 50%;
        }

        .in-stock {
            background-color: #2ecc71;
        }

        .low-stock {
            background-color: #f39c12;
        }

        .out-of-stock {
            background-color: #e74c3c;
        }

        /* Products Header */
        .products-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding: 0 1rem;
        }

        .product-count {
            font-size: 1.1rem;
            color: var(--dark-gray);
            font-weight: 500;
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            main {
                padding: 1.5rem;
            }
            
            .product-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                gap: 1.5rem;
            }
        }

        @media (max-width: 768px) {
            main {
                padding: 1rem;
            }
            
            .product-listing {
                padding: 1.5rem;
            }
            
            .product-grid {
                grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
                gap: 1rem;
            }
            
            .product-image {
                height: 200px;
            }

            .product-info {
                padding: 1.25rem;
            }

            .product-actions {
                padding: 0.75rem;
                gap: 0.5rem;
            }

            .product-actions button {
                padding: 0.5rem 0.75rem;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 576px) {
            .product-grid {
                grid-template-columns: 1fr;
            }
            
            .filter-row {
                flex-direction: column;
            }
            
            .filter-group {
                width: 100%;
            }

            .product-actions {
                grid-template-columns: 1fr;
                gap: 0.5rem;
            }

            .add-to-cart {
                grid-column: 1;
            }
        }

        /* Footer Styles */
        footer {
            background-color: var(--dark-gray);
            color: var(--white);
            padding: 3rem 2rem 1rem;
            margin-top: 3rem;
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
        }

        .footer-section h2 {
            font-size: 1.3rem;
            margin-bottom: 1.5rem;
            position: relative;
            padding-bottom: 0.5rem;
        }

        .footer-section h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 2px;
            background-color: var(--primary-color);
        }

        .footer-section address {
            font-style: normal;
            line-height: 1.8;
        }

        .social-icons {
            display: flex;
            gap: 1rem;
        }

        .social-icons a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            transition: var(--transition);
        }

        .social-icons a:hover {
            background-color: var(--primary-color);
            transform: translateY(-3px);
        }

        .copyright {
            text-align: center;
            padding-top: 2rem;
            margin-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 0.9rem;
            opacity: 0.7;
        }
    </style>
</head>

<body>
    <!-- Header Section -->
 <header>
        <div class="logo">
            <i class="fas fa-tools logo-icon"></i>
            <span class="logo-text">ALI Enterprise</span>
        </div>
        
        <button id="mobile-menu-toggle" aria-label="Toggle Menu">
            <i class="fas fa-bars"></i>
        </button>
        
        <nav>
            <ul id="nav-menu">
                <li><a href="home.php" aria-current="page">Home</a></li>
                <li><a href="product.php">Products</a></li>
                <li><a href="order.html">Orders</a></li>
                <li><a href="track_order.html">Track Order</a></li>
                <li><a href="about_us.html">About Us</a></li>
            </ul>
        </nav>

        <div class="search-container">
            <form method="GET" action="home.php">
                <input id="search-input" type="search" name="search" placeholder="Search products..." 
                       aria-label="Search products" value="<?php echo htmlspecialchars($search_query); ?>">
                <button id="search-button" type="submit" aria-label="Search">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>

        <div class="user-actions">
            <div class="profile-dropdown">
                <div class="profile-icon">
                    <i class="fas fa-user-circle"></i>
                    <span>Profile</span>
                </div>
                <div class="dropdown-content">
                    <a href="profile.php"><i class="fas fa-user"></i> My Profile</a>
                    <a href="cart.php"><i class="fas fa-shopping-cart"></i> My Cart <span id="cart-count" class="badge">0</span></a>
                    <a href="whistles.php"><i class="fas fa-bell"></i> My Wishlist <span id="whistles-count" class="badge">0</span></a>
                    <a href="my_orders.php"><i class="fas fa-box"></i> My Orders</a>
                    <a href="about_us.html"><i class="fas fa-info-circle"></i> About Us</a>
                    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main>
        <section class="product-listing">
            <h1 style="text-align: center;">All Products</h1>
            
            <!-- Filters Section -->
            <div class="filters">
                <form action="product.php" method="GET" id="filter-form">
                    <div style="display: flex; flex-wrap: wrap; gap: 1rem; align-items: center;">
                        <!-- Sort By -->
                        <div>
                            <label for="sort">Sort By:</label>
                            <select name="sort" id="sort">
                                <option value="name_asc" <?= ($sort_by == 'name_asc') ? 'selected' : '' ?>>Name (A-Z)</option>
                                <option value="name_desc" <?= ($sort_by == 'name_desc') ? 'selected' : '' ?>>Name (Z-A)</option>
                                <option value="price_asc" <?= ($sort_by == 'price_asc') ? 'selected' : '' ?>>Price (Low to High)</option>
                                <option value="price_desc" <?= ($sort_by == 'price_desc') ? 'selected' : '' ?>>Price (High to Low)</option>
                                <option value="newest" <?= ($sort_by == 'newest') ? 'selected' : '' ?>>Newest First</option>
                            </select>
                        </div>
                        
                        <!-- Search Input -->
                        <div>
                            <label for="search">Search:</label>
                            <input type="text" name="search" id="search" value="<?= htmlspecialchars($search_query) ?>" placeholder="Search products...">
                        </div>
                        
                        <!-- Filter Buttons -->
                        <div>
                            <button type="submit" class="filter-button">Apply Filters</button>
                            <button type="button" class="filter-button reset-filters" onclick="resetFilters()">Reset</button>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Products Header -->
            <div class="products-header">
                <div class="product-count">
                    Showing <?= min($productCount, $items_per_page) ?> of <?= $total_products ?> products
                </div>
            </div>
            
            <!-- Products Grid -->
            <div class="product-grid">
                <?php 
                if ($result && $result->num_rows > 0) {
                    $counter = 0;
                    while($row = $result->fetch_assoc()) {
                        $counter++;
                        $stock_class = 'in-stock';
                        $stock_text = 'In Stock';
                        
                        if ($row['stock_quantity'] <= 0) {
                            $stock_class = 'out-of-stock';
                            $stock_text = 'Out of Stock';
                        } elseif ($row['stock_quantity'] <= 5) {
                            $stock_class = 'low-stock';
                            $stock_text = 'Low Stock: ' . $row['stock_quantity'];
                        }
                ?>
                <article class="product-card">
                    <div class="product-image">
                        <img src="<?= htmlspecialchars($row['image_path']) ?>" 
                             alt="<?= htmlspecialchars($row['name']) ?>"
                             loading="lazy">
                    </div>
                    <div class="product-info">
                        <h2><?= htmlspecialchars($row['name']) ?></h2>
                        <div class="stock-status">
                            <span class="stock-indicator <?= $stock_class ?>"></span>
                            <?= $stock_text ?>
                        </div>
                        <p class="description"><?= htmlspecialchars($row['description']) ?></p>
                        <p class="price">Price: Rs.<?= number_format($row['price'], 2) ?></p>
                    </div>
                    <div class="product-actions">
                        <button class="add-to-cart" onclick="addToCart(<?= $row['id'] ?>)">
                            <i class="fas fa-shopping-cart"></i> Add to Cart
                        </button>
                        <button class="add-to-whistles" onclick="addToWhistles(<?= $row['id'] ?>)">
                            <i class="fas fa-heart"></i> Wishlist
                        </button>
                        <button class="order-now" onclick="orderNow(<?= $row['id'] ?>)">
                            <a href="view_product.php?id=<?= $row['id'] ?>" class="ordernow">order Now</a>
                            <i class="fa fa-shopping-bag"></i> Order Now 
                        </button>
                    </div>
                </article>
                <?php 
                    }
                } else {
                    echo "<p>No products found.</p>";
                }
                ?>
            </div>
            
            <!-- Pagination -->
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="product.php?page=<?= $page - 1 ?>&search=<?= urlencode($search_query) ?>&sort=<?= $sort_by ?>">Previous</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <?php if ($i == $page): ?>
                        <span class="current"><?= $i ?></span>
                    <?php else: ?>
                        <a href="product.php?page=<?= $i ?>&search=<?= urlencode($search_query) ?>&sort=<?= $sort_by ?>"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <a href="product.php?page=<?= $page + 1 ?>&search=<?= urlencode($search_query) ?>&sort=<?= $sort_by ?>">Next</a>
                <?php endif; ?>
            </div>
        </section>
    </main>
    
    <!-- Footer Section -->
    <footer>
        <div class="footer-content">
            <div class="footer-logo">ALI Enterprise</div>
            <div class="footer-links">
                <a href="terms.html">Terms of Service</a>
                <a href="privacy.html">Privacy Policy</a>
                <a href="contact.html">Contact Us</a>
            </div>
            <p class="footer-copyright">&copy; 2025 ALI Enterprise. All rights reserved.</p>
        </div>
    </footer>

    <!-- Notification Area -->
    <div id="notification" class="notification"></div>

    <!-- JavaScript (keep at the end of the body) -->
    <script>
        function showNotification(message, duration = 3000) {
            const notification = document.getElementById('notification');
            notification.textContent = message;
            notification.classList.add('visible');

            setTimeout(() => {
                notification.classList.remove('visible');
            }, duration);
        }

        function addToWhistles(productId) {
            if (!productId) {
                console.error('No product ID provided');
                showNotification('Failed to add to whistles: No product ID provided.');
                return; // Exit the function if productId is null
            }

            const formData = new FormData();
            formData.append('product_id', productId);

            fetch('whistles.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(`Product ID ${productId} added to whistles.`);
                } else {
                    showNotification(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('An error occurred while adding to the wishlist.');
            });
        }

        function resetFilters() {
            document.getElementById('search').value = '';
            document.getElementById('sort').value = 'name_asc';
            document.getElementById('filter-form').submit();
        }

        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
            const navMenu = document.getElementById('nav-menu');
            const searchButton = document.getElementById('search-button');
            const searchInput = document.getElementById('search-input');
            const cartButton = document.getElementById('cart-button');
            const cartCount = document.getElementById('cart-count');

            // Mobile Menu Toggle
            mobileMenuToggle.addEventListener('click', function() {
                navMenu.classList.toggle('active');
            });

            // Search Functionality
            searchButton.addEventListener('click', function() {
                document.getElementById('search').value = searchInput.value;
                document.getElementById('filter-form').submit();
            });

            // Cart Button Functionality
            cartButton.addEventListener('click', function() {
                alert('View Cart');
            });

            // Update Cart Count (Example)
            function updateCartCount(count) {
                cartCount.textContent = count;
            }

            // Example: Initial cart count
            updateCartCount(5);
        });
    </script>
</body>

</html>
<?php
// Close the database connection
$conn->close();
?>