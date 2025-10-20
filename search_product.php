<?php
require_once 'User.php';
require_once 'Product.php';

if (!User::isLoggedIn()) {
    echo json_encode([]);
    exit();
}

$keyword = $_GET['q'] ?? '';
$productObj = new Product();

// Escape special characters for security
$keyword = $productObj->db->real_escape_string($keyword);

if (!empty($keyword)) {
    // Search products where the name contains the typed letters
    $query = "SELECT * FROM products 
              WHERE name LIKE '%$keyword%' 
              AND stock > 0 
              ORDER BY id DESC";
    $result = $productObj->db->query($query);

    $products = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }
} else {
    // If search box is empty, show all products
    $products = $productObj->getAllProducts();
}

// Return JSON for AJAX
echo json_encode($products);
