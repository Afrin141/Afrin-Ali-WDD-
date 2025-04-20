<?php
// Enable error reporting for troubleshooting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session (add this line to manage user sessions)
session_start();

// Include database connection
require_once 'db_connect.php';

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Initialize variables
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'name_asc'; // Default sort
$search_query = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$category = isset($_GET['category']) ? mysqli_real_escape_string($conn, $_GET['category']) : '';

// Build SQL query
$sql = "SELECT * FROM products WHERE 1=1";

// Handle search filter if provided
if (!empty($search_query)) {
    $sql .= " AND (name LIKE '%" . $conn->real_escape_string($search_query) . "%' OR 
               description LIKE '%" . $conn->real_escape_string($search_query) . "%')";
}

// Fetch categories for the dropdown
$category_sql = "SELECT id, name FROM categories ORDER BY name";
$category_result = $conn->query($category_sql);
$categories = [];
if ($category_result && $category_result->num_rows > 0) {
    while ($row = $category_result->fetch_assoc()) {
        $categories[$row['id']] = $row['name'];
    }
}

// Handle category filter
if (!empty($category)) {
    $sql .= " AND category_id = " . (int)$category;
}

// Add sorting to SQL query
switch($sort_by) {
    case 'name_desc':
        $sql .= " ORDER BY name DESC";
        break;
    case 'price_asc':
        $sql .= " ORDER BY price ASC";
        break;
    case 'price_desc':
        $sql .= " ORDER BY price DESC";
        break;
    case 'newest':
        $sql .= " ORDER BY id DESC";
        break;
    default: 
        $sql .= " ORDER BY name ASC";
}

// Pagination
$items_per_page = 12;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

$count_sql = preg_replace('/SELECT \*/', 'SELECT COUNT(*)', $sql);
$count_sql = preg_replace('/ORDER BY.*$/', '', $count_sql);
$count_result = $conn->query($count_sql);
$total_items = ($count_result) ? $count_result->fetch_row()[0] : 0;
$total_pages = ceil($total_items / $items_per_page);

// Add limit for pagination
$sql .= " LIMIT $offset, $items_per_page";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ALI Enterprise - Hardware Products</title>
    <script src="home.js" defer></script>
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
            transition: all 0.3s ease;
        }

        .logo:hover {
            transform: translateY(-2px);
        }

        .logo-icon {
            font-size: 2.2rem;
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
            transition: all 0.3s ease;
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
            transition: all 0.3s ease;
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
            transition: all 0.3s ease;
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
            transition: all 0.3s ease;
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
            transition: all 0.3s ease;
            border-radius: 50px;
            background: #f8f9fa;
            z-index: 1000;
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
            transition: all 0.5s ease;
            gap: 0.75rem;
            position: relative;
        }

        .dropdown-content a:hover {
            background: rgba(52, 152, 219, 0.1);
            color: var(--primary-color);
            transition: all 0.5s ease;
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
            transition: all 0.5s ease;
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
            transition: all 0.3s ease;
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

        /* Hero Section Styles */
        .hero-section {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('Img/hardware-banner.jpg');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 4rem 2rem;
            text-align: center;
            margin-bottom: 3rem;
        }

        .hero-content {
            max-width: 800px;
            margin: 0 auto;
        }

        .hero-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }

        .hero-subtitle {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }

        .hero-features {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .hero-features .feature {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255, 255, 255, 0.1);
            padding: 0.5rem 1rem;
            border-radius: 50px;
        }

        .hero-features .feature i {
            color: var(--secondary-color);
        }

        .hero-cta {
            display: inline-block;
            background: var(--primary-color);
            color: white;
            padding: 1rem 2rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .hero-cta:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
        }

        /* Special Offers Section */
        .special-offers {
            padding: 3rem 2rem;
            background: #f8f9fa;
        }

        .offers-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .offer-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 3px 15px rgba(0, 0, 0, 0.1);
            position: relative;
            transition: all 0.3s ease;
        }

        .offer-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
        }

        .offer-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: var(--accent-color);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .offer-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .offer-content {
            padding: 1.5rem;
        }

        .offer-content h3 {
            margin-bottom: 0.5rem;
            color: var(--dark-gray);
        }

        .offer-content p {
            color: #666;
            margin-bottom: 1rem;
        }

        .offer-link {
            display: inline-block;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .offer-link:hover {
            color: var(--secondary-color);
        }

        /* Testimonials Section */
        .testimonials {
            padding: 3rem 2rem;
            background: white;
        }

        .testimonials-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .testimonial-card {
            background: #f8f9fa;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 3px 15px rgba(0, 0, 0, 0.1);
        }

        .testimonial-rating {
            color: #ffd700;
            margin-bottom: 1rem;
        }

        .testimonial-text {
            color: #666;
            font-style: italic;
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }

        .testimonial-author {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .testimonial-author img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
        }

        .testimonial-author h4 {
            margin: 0;
            color: var(--dark-gray);
        }

        .testimonial-author p {
            margin: 0;
            color: #666;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .hero-features {
                flex-direction: column;
                gap: 1rem;
            }

            .hero-title {
                font-size: 2rem;
            }

            .hero-subtitle {
                font-size: 1rem;
            }
        }

        /* Features Section */
        .features-section {
            padding: 3rem 2rem;
            background: #f8f9fa;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .feature-card {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 3px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
        }

        .feature-icon {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .feature-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--dark-gray);
        }

        .feature-description {
            color: #666;
            line-height: 1.6;
        }

        /* Section Title */
        .section-title {
            text-align: center;
            margin-bottom: 3rem;
        }

        .section-title h2 {
            font-size: 2rem;
            color: var(--dark-gray);
            margin-bottom: 1rem;
        }

        .section-title p {
            color: #666;
            max-width: 600px;
            margin: 0 auto;
        }

        /* Featured Products Section */
        main {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem 3rem;
        }

        .featured-products {
            margin-top: 2rem;
        }

        .featured-products h2 {
            font-size: 1.8rem;
            text-align: center;
            margin-bottom: 2rem;
            position: relative;
        }

        .featured-products h2::after {
            content: '';
            display: block;
            width: 50px;
            height: 3px;
            background-color: var(--primary-color);
            margin: 0.5rem auto 0;
        }

        /* Filters */
        .filters {
            background-color: var(--white);
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
        }

        .filter-row {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            align-items: center;
        }

        .filter-group {
            flex: 1;
            min-width: 200px;
        }

        .filter-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--dark-gray);
        }

        .filter-group select,
        .filter-group input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: var(--white);
            font-size: 0.9rem;
            transition: var(--transition);
        }

        .filter-group select:focus,
        .filter-group input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
            outline: none;
        }

        .filter-buttons {
            display: flex;
            gap: 0.75rem;
            align-items: center;
        }

        .filter-button {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
        }

        .apply-button {
            background-color: var(--primary-color);
            color: var(--white);
        }

        .apply-button:hover {
            background-color: var(--secondary-color);
        }

        .reset-button {
            background-color: var(--light-gray);
            color: var(--dark-gray);
        }

        .reset-button:hover {
            background-color: #e2e8f0;
        }

        /* Product Grid */
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 2rem;
        }

        .product-card {
            background-color: var(--white);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: var(--transition);
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .product-image {
            position: relative;
            overflow: hidden;
            aspect-ratio: 1 / 1;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .product-card:hover .product-image img {
            transform: scale(1.05);
        }

        .product-info {
            padding: 1.25rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .product-info h3 {
            font-size: 1.2rem;
            margin: 0 0 0.5rem;
            color: var(--dark-gray);
        }

        .stock-status {
            display: flex;
            align-items: center;
            margin-bottom: 0.75rem;
            font-size: 0.85rem;
        }

        .stock-indicator {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 0.5rem;
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

        .description {
            color: #666;
            font-size: 0.9rem;
            line-height: 1.5;
            margin-bottom: 1rem;
            flex-grow: 1;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .price {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--accent-color);
            margin: 0.5rem 0 1rem;
        }

        .product-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
            margin-top: auto;
        }

        .product-actions button,
        .product-actions a {
            padding: 0.75rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .add-to-cart {
            background-color: var(--primary-color);
            color: var(--white);
            grid-column: span 2;
        }

        .add-to-cart:hover {
            background-color: var(--secondary-color);
        }

        .add-to-whistles {
            background-color: var(--light-gray);
            color: var(--dark-gray);
        }

        .add-to-whistles:hover {
            background-color: #e2e8f0;
        }

        .view-details {
            background-color: var(--white);
            color: var(--primary-color);
            border: 1px solid var(--primary-color);
        }

        .view-details:hover {
            background-color: rgba(52, 152, 219, 0.1);
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 3rem;
            gap: 0.5rem;
        }

        .pagination a,
        .pagination span {
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
        }

        .pagination span.current {
            background-color: var(--primary-color);
            color: var(--white);
        }

        .page-info {
            margin-top: 1rem;
            text-align: center;
            color: #666;
            font-size: 0.9rem;
        }

        /* Empty Results */
        .empty-results {
            text-align: center;
            padding: 3rem 0;
        }

        .empty-results i {
            font-size: 3rem;
            color: #ccc;
            margin-bottom: 1rem;
        }

        .empty-results p {
            font-size: 1.1rem;
            color: #666;
            margin-bottom: 1.5rem;
        }

        /* Footer */
        footer {
            background-color: var(--dark-gray);
            color: var(--white);
            padding: 3rem 2rem 1rem;
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

        /* Category Filter Styles */
        .category-filter {
            background: #fff;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .category-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .category-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .category-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border-color: var(--primary-color);
        }

        .category-card.active {
            background: var(--primary-color);
            color: white;
        }

        .category-icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            color: var(--primary-color);
        }

        .category-card.active .category-icon {
            color: white;
        }

        .category-name {
            font-weight: 600;
            margin: 0;
        }

        /* Product Card Styles */
        .product-card {
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 3px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            position: relative;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .product-image {
            position: relative;
            padding-top: 75%;
            overflow: hidden;
        }

        .product-image img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .product-card:hover .product-image img {
            transform: scale(1.05);
        }

        .product-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: var(--primary-color);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .product-info {
            padding: 1.5rem;
        }

        .product-title {
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
            color: var(--dark-gray);
        }

        .product-price {
            font-size: 1.4rem;
            font-weight: 700;
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
            gap: 1rem;
            margin-top: 1rem;
        }

        .action-button {
            padding: 0.75rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .add-to-cart {
            background: var(--primary-color);
            color: white;
            grid-column: span 2;
        }

        .add-to-cart:hover {
            background: var(--secondary-color);
        }

        .view-details {
            background: #f8f9fa;
            color: var(--dark-gray);
        }

        .view-details:hover {
            background: #e9ecef;
        }

        /* Responsive Grid */
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 2rem;
            padding: 1rem;
        }

        @media (max-width: 768px) {
            .product-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                gap: 1.5rem;
            }
        }

        @media (max-width: 576px) {
            .product-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <!-- Updated Header Section -->
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

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-content">
            <h1 class="hero-title">Welcome to ALI Enterprise</h1>
            <p class="hero-subtitle">Your One-Stop Shop for Quality Hardware and Building Materials</p>
            <div class="hero-features">
                <div class="feature">
                    <i class="fas fa-truck"></i>
                    <span>Free Delivery on Orders Over Rs. 5000</span>
                </div>
                <div class="feature">
                    <i class="fas fa-shield-alt"></i>
                    <span>100% Genuine Products</span>
                </div>
                <div class="feature">
                    <i class="fas fa-undo"></i>
                    <span>Easy Returns</span>
                </div>
            </div>
            <a href="#featured-products" class="hero-cta">Explore Our Products</a>
        </div>
    </section>

    <!-- Special Offers Section -->
    <section class="special-offers">
        <div class="section-title">
            <h2>Special Offers</h2>
            <p>Don't miss out on these amazing deals</p>
        </div>
        <div class="offers-grid">
            <div class="offer-card">
                <div class="offer-badge">-20%</div>
                <img src="Img/tools-offer.jpg" alt="Tools Special Offer">
                <div class="offer-content">
                    <h3>Power Tools Sale</h3>
                    <p>Get 20% off on all power tools this week</p>
                    <a href="product.php?category=1" class="offer-link">Shop Now</a>
                </div>
            </div>
            <div class="offer-card">
                <div class="offer-badge">-15%</div>
                <img src="Img/paint-offer.jpg" alt="Paint Special Offer">
                <div class="offer-content">
                    <h3>Paint & Accessories</h3>
                    <p>15% discount on all paint products</p>
                    <a href="product.php?category=2" class="offer-link">Shop Now</a>
                </div>
            </div>
            <div class="offer-card">
                <div class="offer-badge">-10%</div>
                <img src="Img/electrical-offer.jpg" alt="Electrical Special Offer">
                <div class="offer-content">
                    <h3>Electrical Items</h3>
                    <p>10% off on all electrical products</p>
                    <a href="product.php?category=3" class="offer-link">Shop Now</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Customer Testimonials -->
    <section class="testimonials">
        <div class="section-title">
            <h2>What Our Customers Say</h2>
            <p>Real experiences from our valued customers</p>
        </div>
        <div class="testimonials-grid">
            <div class="testimonial-card">
                <div class="testimonial-rating">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
                <p class="testimonial-text">"Excellent quality products and fast delivery. The customer service is outstanding!"</p>
                <div class="testimonial-author">
                    <img src="Img/customer1.jpg" alt="Customer">
                    <div>
                        <h4>John Smith</h4>
                        <p>Regular Customer</p>
                    </div>
                </div>
            </div>
            <div class="testimonial-card">
                <div class="testimonial-rating">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
                <p class="testimonial-text">"Great prices and a wide selection of hardware items. Will definitely shop again!"</p>
                <div class="testimonial-author">
                    <img src="Img/customer2.jpg" alt="Customer">
                    <div>
                        <h4>Sarah Johnson</h4>
                        <p>Contractor</p>
                    </div>
                </div>
            </div>
            <div class="testimonial-card">
                <div class="testimonial-rating">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star-half-alt"></i>
                </div>
                <p class="testimonial-text">"The quality of their products is exceptional. Very satisfied with my purchase."</p>
                <div class="testimonial-author">
                    <img src="Img/customer3.jpg" alt="Customer">
                    <div>
                        <h4>Michael Brown</h4>
                        <p>Homeowner</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <main>
        <section class="featured-products">
            <h2>Featured Products</h2>

            <!-- Search and Filters Section -->
            <div class="filters">
                <form action="home.php" method="GET" id="filter-form">
                    <div class="filter-row">
                        <!-- Category Filter -->
                        <div class="filter-group">
                            <label for="category">Category</label>
                            <select name="category" id="category">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $id => $name): ?>
                                    <option value="<?= $id ?>" <?= ($category == $id) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($name) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Sort By -->
                        <div class="filter-group">
                            <label for="sort">Sort By</label>
                            <select name="sort" id="sort">
                                <option value="name_asc" <?= ($sort_by == 'name_asc') ? 'selected' : '' ?>>Name (A-Z)</option>
                                <option value="name_desc" <?= ($sort_by == 'name_desc') ? 'selected' : '' ?>>Name (Z-A)</option>
                                <option value="price_asc" <?= ($sort_by == 'price_asc') ? 'selected' : '' ?>>Price (Low to High)</option>
                                <option value="price_desc" <?= ($sort_by == 'price_desc') ? 'selected' : '' ?>>Price (High to Low)</option>
                                <option value="newest" <?= ($sort_by == 'newest') ? 'selected' : '' ?>>Newest First</option>
                            </select>
                        </div>
                        
                        <!-- Search Input -->
                        <div class="filter-group">
                            <label for="search">Search Products</label>
                            <input type="text" name="search" id="search" value="<?= htmlspecialchars($search_query) ?>" 
                                   placeholder="Type product name or description">
                        </div>
                        
                        <!-- Filter Buttons -->
                        <div class="filter-buttons">
                            <button type="submit" class="filter-button apply-button">
                                <i class="fas fa-filter"></i> Apply Filters
                            </button>
                            <button type="button" class="filter-button reset-button" onclick="resetFilters()">
                                <i class="fas fa-undo"></i> Reset
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Category Filter Section -->
            <div class="category-filter">
                <h2>Categories</h2>
                <div class="category-grid">
                    <div class="category-card <?= empty($category) ? 'active' : '' ?>" onclick="window.location.href='home.php'">
                        <i class="fas fa-th-large category-icon"></i>
                        <h3 class="category-name">All Products</h3>
                    </div>
                    <?php foreach ($categories as $id => $name): ?>
                        <div class="category-card <?= ($category == $id) ? 'active' : '' ?>" 
                             onclick="window.location.href='home.php?category=<?= $id ?>'">
                            <i class="fas fa-<?= getCategoryIcon($name) ?> category-icon"></i>
                            <h3 class="category-name"><?= htmlspecialchars($name) ?></h3>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Product Grid -->
            <div class="product-grid">
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <article class="product-card">
                            <?php if ($row['is_featured']): ?>
                                <div class="product-badge">Featured</div>
                            <?php endif; ?>
                            <div class="product-image">
                                <img src="./Img/<?= htmlspecialchars($row['image_url_1'] ?? 'default-product.jpg') ?>" 
                                     alt="<?= htmlspecialchars($row['name'] ?? '') ?>">
                            </div>
                            <div class="product-info">
                                <h3 class="product-title"><?= htmlspecialchars($row['name'] ?? '') ?></h3>
                                <div class="product-rating">
                                    <?php
                                    require_once 'star_rating.php';
                                    $avg_rating = getAverageRating($row['id'], $conn);
                                    echo displayStarRating($avg_rating);
                                    ?>
                                    <span class="rating-count">(<?= $avg_rating ?>)</span>
                                </div>
                                <p class="product-price">Rs. <?= number_format($row['price'] ?? 0, 2) ?></p>
                                <div class="product-actions">
                                    <button class="action-button add-to-cart" onclick="addToCart(<?= $row['id'] ?>)">
                                        <i class="fas fa-shopping-cart"></i> Add to Cart
                                    </button>
                                    <a href="view_product.php?id=<?= $row['id'] ?>" class="action-button view-details">
                                        <i class="fas fa-eye"></i> View Details
                                    </a>
                                </div>
                            </div>
                        </article>
                    <?php endwhile; ?>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search_query) ?>&category=<?= urlencode($category) ?>&sort=<?= urlencode($sort_by) ?>">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <?php if ($i == $page): ?>
                                <span class="current"><?= $i ?></span>
                            <?php else: ?>
                                <a href="?page=<?= $i ?>&search=<?= urlencode($search_query) ?>&category=<?= urlencode($category) ?>&sort=<?= urlencode($sort_by) ?>">
                                    <?= $i ?>
                                </a>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search_query) ?>&category=<?= urlencode($category) ?>&sort=<?= urlencode($sort_by) ?>">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="page-info">
                        Showing <?= $offset + 1 ?> - <?= min($offset + $items_per_page, $total_items) ?> of <?= $total_items ?> products
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="empty-results">
                    <i class="fas fa-search"></i>
                    <p>No products found matching your search criteria.</p>
                    <a href="home.php" class="cta-button">Clear Filters</a>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <!-- Footer Section -->
    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h2>Contact Us</h2>
                <address>
                    <p>101, MPCS Street Akkaraipattu 02</p>
                    <p>Hardware City, HC 15470</p>
                    <p>Email: info@alienterprise.com</p>
                    <p>Phone: (+94) 77838133</p>
                </address>
            </div>
            <div class="footer-section">
                <h2>Follow Us</h2>
                <div class="social-icons">
                    <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                    <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
        </div>
        <div class="copyright">
            <p>&copy; 2025 ALI Enterprise. All Rights Reserved.</p>
        </div>
    </footer>

    <script>
        // Mobile menu toggle
        document.getElementById('mobile-menu-toggle').addEventListener('click', function() {
            document.getElementById('nav-menu').classList.toggle('show');
        });

        // Reset filters
        function resetFilters() {
            window.location.href = 'home.php';
        }

        // Add to cart function
        function addToCart(productId) {
            // Implement cart functionality
            console.log('Adding product to cart:', productId);
        }

        // Add to whistles function
        function addToWhistles(productId) {
            // Implement whistles functionality
            console.log('Adding product to whistles:', productId);
        }
    </script>
</body>
</html>
<?php
// Close connection
$conn->close();
?>