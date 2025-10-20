<?php
require_once 'User.php';
require_once 'Product.php';
require_once 'Cart.php';

// Start session if not already
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if not logged in
if (!User::isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$productObj = new Product();
$cart = new Cart($_SESSION['user_id']);

// Handle AJAX Add to Cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_add_to_cart'])) {
    header('Content-Type: application/json');
    $productId = $_POST['product_id'];
    $success = $cart->addItem($productId, 1);
    echo json_encode(['success' => $success]);
    exit();
}

// Handle AJAX check cart status
if (isset($_GET['ajax_check_cart'])) {
    header('Content-Type: application/json');
    $productId = $_GET['product_id'];
    $isInCart = $cart->isProductInCart($productId);
    echo json_encode(['in_cart' => $isInCart]);
    exit();
}

// Handle AJAX search
if (isset($_GET['ajax_search'])) {
    $query = $_GET['q'] ?? '';
    $query = trim($query);

    if ($query === '') {
        $products = $productObj->getAllProducts();
    } else {
        $products = $productObj->searchProducts($query);
    }

    // Add cart status to each product
    foreach ($products as &$product) {
        $product['in_cart'] = $cart->isProductInCart($product['id']);
    }

    header('Content-Type: application/json');
    echo json_encode($products);
    exit();
}

// Initial load: show all products
$products = $productObj->getAllProducts();

// Add cart status to each product
foreach ($products as &$product) {
    $product['in_cart'] = $cart->isProductInCart($product['id']);
}

include 'header.php';
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard - ToyStore Kids</title>
<style>
body {
    background: url('https://thumbs.dreamstime.com/b/explore-detailed-texture-lifelike-toy-animals-adorable-closeup-images-perfect-animal-concept-backgrounds-320614078.jpg') no-repeat center center fixed;
    background-size: cover;
    font-family: 'Poppins', sans-serif;
    margin:0;
    padding:0;
    color:#fff;
}

.main-container {
    max-width:1200px;
    margin:60px auto;
    padding:30px;
    background:rgba(255,255,255,0.12);
    backdrop-filter:blur(10px);
    border-radius:18px;
    box-shadow:0 10px 40px rgba(0,0,0,0.4);
}

.page-title { text-align:center; font-size:40px; margin-bottom:10px; }
.page-subtitle { text-align:center; font-size:18px; margin-bottom:30px; }

.search-bar { text-align:center; margin-bottom:30px; }
.search-input {
    width:70%;
    padding:12px 20px;
    border-radius:25px;
    border:none;
    font-size:16px;
    outline:none;
    background: rgba(255,255,255,0.8);
    color:#333;
}

.products-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:25px; }

.product-card {
    background: rgba(255,255,255,0.9);
    border-radius:15px;
    overflow:hidden;
    box-shadow:0 6px 20px rgba(0,0,0,0.2);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    color:#2c3e50;
}

.product-card:hover { transform:translateY(-5px); box-shadow:0 10px 30px rgba(0,0,0,0.3); }

.product-image { width:100%; height:220px; object-fit:cover; cursor:pointer; }
.product-info { padding:20px; }
.product-name { font-size:20px; font-weight:bold; margin-bottom:8px; }
.product-description { font-size:14px; color:#7f8c8d; margin-bottom:12px; line-height:1.5; }
.product-price { font-size:22px; font-weight:bold; color:#27ae60; margin-top:8px; }

.btn-add-cart { 
    width:100%; 
    padding:12px; 
    background:linear-gradient(135deg,#667eea 0%,#764ba2 100%); 
    color:#fff; 
    border:none; 
    border-radius:8px; 
    font-size:16px; 
    font-weight:bold; 
    cursor:pointer; 
    margin-top:12px; 
    transition:all 0.3s;
    text-decoration: none;
    display: block;
    text-align: center;
}

.btn-add-cart:hover { transform:scale(1.05); }

.btn-add-cart:disabled {
    cursor: not-allowed;
    opacity: 0.7;
}

.btn-go-to-cart {
    background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%) !important;
}

.btn-go-to-cart:hover {
    background: linear-gradient(135deg, #e67e22 0%, #d35400 100%) !important;
    transform: scale(1.05);
}

.btn-added {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%) !important;
    transform: scale(1.05);
}

.btn-added:hover {
    transform: scale(1.05);
}

.no-products { 
    text-align:center; 
    padding:60px 20px; 
    background: rgba(255,255,255,0.85); 
    border-radius:12px; 
    color:#7f8c8d; 
    font-size:18px;
}
</style>
</head>
<body>

<div class="main-container">
    <h1 class="page-title">🎪 Play Town - ToyStore Kids!</h1>
    <p class="page-subtitle">Discover amazing toys for your little ones</p>

    <div class="search-bar">
        <input type="text" id="searchInput" class="search-input" placeholder="Search for toys...">
    </div>

    <div id="productsContainer" class="products-grid">
        <?php foreach ($products as $prod): ?>
            <div class="product-card">
                <a href="product_details.php?id=<?php echo $prod['id']; ?>">
                    <img src="<?php echo htmlspecialchars($prod['image']); ?>" class="product-image">
                </a>
                <div class="product-info">
                    <h3 class="product-name"><?php echo htmlspecialchars($prod['name']); ?></h3>
                    <p class="product-description"><?php echo htmlspecialchars($prod['description']); ?></p>
                    <p class="product-price">₹<?php echo number_format($prod['price'], 2); ?></p>
                    <?php if ($prod['in_cart']): ?>
                        <a href="addtocart.php" class="btn-add-cart btn-go-to-cart">
                            🛍️ Go to Cart
                        </a>
                    <?php else: ?>
                        <button class="btn-add-cart" onclick="addToCart(<?php echo $prod['id']; ?>, this)">
                            🛒 Add to Cart
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
const searchInput = document.getElementById('searchInput');
const productsContainer = document.getElementById('productsContainer');

// Add to Cart function
function addToCart(productId, button) {
    // Disable button during request
    button.disabled = true;
    const originalText = button.innerHTML;
    button.innerHTML = '⏳ Adding...';

    const formData = new FormData();
    formData.append('ajax_add_to_cart', '1');
    formData.append('product_id', productId);

    fetch('dashboard.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Change button to green "Added"
            button.innerHTML = '✓ Added to Cart';
            button.classList.add('btn-added');
            
            // After 1 second, change to "Go to Cart"
            setTimeout(() => {
                // Replace button with link
                const link = document.createElement('a');
                link.href = 'addtocart.php';
                link.className = 'btn-add-cart btn-go-to-cart';
                link.innerHTML = '🛍️ Go to Cart';
                button.parentNode.replaceChild(link, button);
            }, 1000);
        } else {
            alert('Failed to add product to cart!');
            button.innerHTML = originalText;
            button.disabled = false;
        }
    })
    .catch(err => {
        console.error('Error:', err);
        alert('An error occurred. Please try again.');
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

// Search functionality
searchInput.addEventListener('input', function() {
    const query = searchInput.value.trim();
    fetch('dashboard.php?ajax_search=1&q=' + encodeURIComponent(query))
        .then(res => res.json())
        .then(data => {
            productsContainer.innerHTML = '';
            if (data.length === 0) {
                productsContainer.innerHTML = '<div class="no-products"><h2>No products found</h2></div>';
                return;
            }
            data.forEach(prod => {
                const card = document.createElement('div');
                card.classList.add('product-card');
                
                let buttonHtml = '';
                if (prod.in_cart) {
                    buttonHtml = `<a href="addtocart.php" class="btn-add-cart btn-go-to-cart">🛍️ Go to Cart</a>`;
                } else {
                    buttonHtml = `<button class="btn-add-cart" onclick="addToCart(${prod.id}, this)">🛒 Add to Cart</button>`;
                }
                
                card.innerHTML = `
                    <a href="product_details.php?id=${prod.id}">
                        <img src="${prod.image}" class="product-image" alt="${prod.name}">
                    </a>
                    <div class="product-info">
                        <h3 class="product-name">${prod.name}</h3>
                        <p class="product-description">${prod.description}</p>
                        <p class="product-price">₹${parseFloat(prod.price).toFixed(2)}</p>
                        ${buttonHtml}
                    </div>
                `;
                productsContainer.appendChild(card);
            });
        });
});
</script>

</body>
</html>