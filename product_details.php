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

include 'header.php';

$productObj = new Product();
$message = '';
$product = null;

// Get product ID from URL
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $product = $productObj->getProductById($id);

    if (!$product) {
        die("Product not found.");
    }
}

// Handle Add to Cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $cart = new Cart($_SESSION['user_id']);
    $message = $cart->addItem($product['id'], 1)
        ? 'Product added to cart successfully!'
        : 'Failed to add product to cart!';
}

// Parse additional images
$additionalImages = [];
if (!empty($product['additional_images'])) {
    $additionalImages = array_filter(explode(',', $product['additional_images']));
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo htmlspecialchars($product['name'] ?? 'Product Details'); ?></title>
<style>
body {
    background: linear-gradient(rgba(0, 0, 0, 0.06), rgba(0, 0, 0, 0.3)),
                url('https://thumbs.dreamstime.com/b/explore-detailed-texture-lifelike-toy-animals-adorable-closeup-images-perfect-animal-concept-backgrounds-320614078.jpg') no-repeat center center fixed;
    background-size: cover;
    font-family: 'Poppins', sans-serif;
    margin:0;
    padding:0;
    color:#fff;
}

.main-container {
    max-width:900px;
    margin:60px auto;
    padding:30px;
    background:rgba(255,255,255,0.12);
    backdrop-filter:blur(10px);
    border-radius:18px;
    box-shadow:0 10px 40px rgba(0,0,0,0.4);
    color:#fff;
}

.product-gallery {
    margin-bottom:25px;
}

.product-image {
    width:100%;
    max-height:450px;
    object-fit:cover;
    border-radius:12px;
    margin-bottom:15px;
    box-shadow:0 5px 20px rgba(0,0,0,0.3);
}

.additional-images {
    display:grid;
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    gap:10px;
    margin-top:15px;
}

.additional-images img {
    width:100%;
    height:120px;
    object-fit:cover;
    border-radius:8px;
    cursor:pointer;
    transition:transform 0.2s, box-shadow 0.2s;
    border:2px solid rgba(255,255,255,0.3);
}

.additional-images img:hover {
    transform:scale(1.05);
    box-shadow:0 5px 15px rgba(0,0,0,0.5);
}

.product-header {
    border-bottom:2px solid rgba(255,255,255,0.2);
    padding-bottom:15px;
    margin-bottom:20px;
}

.product-name { 
    font-size:32px; 
    font-weight:bold; 
    margin-bottom:8px; 
    color:#fff;
}

.product-category {
    display:inline-block;
    background:rgba(19, 135, 164, 0.8);
    color:#fff;
    padding:6px 15px;
    border-radius:20px;
    font-size:14px;
    font-weight:600;
    margin-bottom:15px;
}

.product-section {
    background:rgba(255,255,255,0.08);
    padding:20px;
    border-radius:12px;
    margin-bottom:20px;
    border:1px solid rgba(255,255,255,0.15);
}

.section-title {
    font-size:18px;
    font-weight:bold;
    color:#fff;
    margin-bottom:12px;
    display:flex;
    align-items:center;
    gap:8px;
}

.product-description { 
    font-size:16px; 
    line-height:1.7; 
    color:#f0f0f0;
}

.product-price { 
    font-size:32px; 
    font-weight:bold; 
    color:#27ae60; 
    margin-bottom:10px;
}

.product-meta {
    display:grid;
    grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));
    gap:15px;
    margin-top:15px;
}

.meta-item {
    background:rgba(255,255,255,0.1);
    padding:12px;
    border-radius:8px;
    border-left:3px solid #0e8296ff;
}

.meta-label {
    font-size:12px;
    color:#bbb;
    text-transform:uppercase;
    margin-bottom:5px;
}

.meta-value {
    font-size:16px;
    font-weight:600;
    color:#fff;
}

.specifications-list {
    list-style:none;
    padding:0;
    margin:0;
}

.specifications-list li {
    padding:10px 0;
    border-bottom:1px solid rgba(255,255,255,0.1);
    color:#f0f0f0;
}

.specifications-list li:last-child {
    border-bottom:none;
}

.btn {
    padding:14px 28px;
    border:none;
    border-radius:10px;
    font-size:16px;
    font-weight:bold;
    cursor:pointer;
    margin-right:15px;
    transition:all 0.3s;
    text-decoration:none;
    display:inline-block;
}
.btnn{
 background: linear-gradient(135deg,#667eea 0%,#764ba2 100%); 
    color:#fff;
    box-shadow:0 5px 15px rgba(47, 139, 169, 0.4);

}
.btn-add-cart { 
    background: linear-gradient(135deg,#667eea 0%,#764ba2 100%); 
    color:#fff;
    box-shadow:0 5px 15px rgba(47, 139, 169, 0.4);
}

.btn-add-cart:hover { 
    transform: translateY(-2px); 
    box-shadow:0 8px 20px rgba(44, 186, 230, 0.6);
}

.btn-back { 
    background:rgba(149, 165, 166, 0.8); 
    color:#fff; 
    margin-bottom:20px;
}

.btn-back:hover { 
    background:rgba(149, 165, 166, 1);
    transform: translateY(-2px); 
}

.alert { 
    background: rgba(212,237,218,0.9); 
    color:#155724; 
    padding:15px; 
    border-radius:10px; 
    margin-bottom:20px; 
    border:1px solid #c3e6cb; 
    text-align:center;
    font-weight:600;
}

.action-buttons {
    margin-top:25px;
    padding-top:20px;
    border-top:2px solid rgba(255,255,255,0.2);
}

@media (max-width: 768px) {
    .main-container {
        margin:20px;
        padding:20px;
    }
    
    .product-name {
        font-size:24px;
    }
    
    .product-meta {
        grid-template-columns:1fr;
    }
    
    .btn {
        width:100%;
        margin-right:0;
        margin-bottom:10px;
    }
}
</style>
</head>
<body>

<div class="main-container">

    <a href="dashboard.php" class="btn btn-back">← Back to Dashboard</a>

    <?php if ($message): ?>
        <div class="alert"><?php echo $message; ?></div>
    <?php endif; ?>

    <?php if ($product): ?>
        
        <!-- Product Gallery -->
        <div class="product-gallery">
            <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                 class="product-image" id="mainImage">
            
            <?php if (!empty($additionalImages)): ?>
                <div class="additional-images">
                    <?php foreach ($additionalImages as $img): ?>
                        <img src="<?php echo htmlspecialchars(trim($img)); ?>" 
                             alt="Additional view" 
                             onclick="changeMainImage('<?php echo htmlspecialchars(trim($img)); ?>')">
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Product Header -->
        <div class="product-header">
            <?php if (!empty($product['category'])): ?>
                <span class="product-category">📦 <?php echo htmlspecialchars($product['category']); ?></span>
            <?php endif; ?>
            
            <h1 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h1>
            <p class="product-price">₹<?php echo number_format($product['price'], 2); ?></p>
        </div>

        <!-- Description -->
        <?php if (!empty($product['description'])): ?>
            <div class="product-section">
                <div class="section-title">📝 Description</div>
                <p class="product-description"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
            </div>
        <?php endif; ?>

        <!-- Product Meta (Size, Material, Created At) -->
        <div class="product-meta">
            <?php if (!empty($product['size'])): ?>
                <div class="meta-item">
                    <div class="meta-label">Size</div>
                    <div class="meta-value">📏 <?php echo htmlspecialchars($product['size']); ?></div>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($product['material'])): ?>
                <div class="meta-item">
                    <div class="meta-label">Material</div>
                    <div class="meta-value">🧱 <?php echo htmlspecialchars($product['material']); ?></div>
                </div>
            <?php endif; ?>
            
            <div class="meta-item">
                <div class="meta-label">Added On</div>
                <div class="meta-value">📅 <?php echo date('M d, Y', strtotime($product['created_at'])); ?></div>
            </div>
        </div>

        <!-- Specifications -->
        <?php if (!empty($product['specifications'])): ?>
            <div class="product-section">
                <div class="section-title">⚙️ Specifications</div>
                <ul class="specifications-list">
                    <?php 
                    $specs = explode(';', $product['specifications']);
                    foreach ($specs as $spec): 
                        $spec = trim($spec);
                        if (!empty($spec)):
                    ?>
                        <li>✓ <?php echo htmlspecialchars($spec); ?></li>
                    <?php 
                        endif;
                    endforeach; 
                    ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <form method="POST" action="" style="display:inline;">
                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                <button type="submit" name="add_to_cart" class="btn btn-add-cart">🛒 Add to Cart</button>
            </form>
        </div>

    <?php endif; ?>

</div>

<script>
function changeMainImage(src) {
    document.getElementById('mainImage').src = src;
}
</script>

</body>
</html>