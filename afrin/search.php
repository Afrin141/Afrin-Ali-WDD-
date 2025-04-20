<?php
    require_once 'db_connect.php';

// Get the search query from the URL
$search_query = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';

// SQL query to search for products by name
$sql = "SELECT * FROM products WHERE name LIKE '%" . $conn->real_escape_string($search_query) . "%'";
$result = $conn->query($sql);

// Prepare the results
$results = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $results[] = $row;
    }
}

$conn->close();

// Return the results as JSON
header('Content-Type: application/json');
echo json_encode($results);
?>
