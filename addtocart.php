<?php
// Start session if not already
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'User.php';
require_once 'Cart.php';

// Redirect if not logged in
if (!User::isLoggedIn()) {
    header("Location: login.php");
    exit();
}

// Create Cart object
$cart = new Cart($_SESSION['user_id']);

// Handle AJAX update
if (isset($_POST['update_quantity'])) {
    header('Content-Type: application/json');

    $cartId = isset($_POST['cart_id']) ? intval($_POST['cart_id']) : 0;
    $action = $_POST['action'] ?? '';

    if ($cartId > 0 && in_array($action, ['increase','decrease'])) {
        $success = $cart->updateQuantity($cartId, $action);
        echo json_encode(['success' => $success]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    }
    exit();
}

// Handle remove from cart
$message = '';
$messageType = '';
if (isset($_GET['remove'])) {
    $cartId = intval($_GET['remove']);
    if ($cart->removeItem($cartId)) {
        $message = 'Item removed from cart!';
        $messageType = 'success';
        header("Location: addtocart.php");
        exit();
    }
}

// Get cart items and totals
$cartItems = $cart->getItems();
$cartTotal = $cart->getTotal();
$itemCount = count($cartItems);

// Include header
include 'header.php';
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - ToyStore Kids</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
                background: url('https://thumbs.dreamstime.com/b/explore-detailed-texture-lifelike-toy-animals-adorable-closeup-images-perfect-animal-concept-backgrounds-320614078.jpg') no-repeat center center fixed;
    position: relative;
    background-size: cover;
    margin: 0;
    padding: 0;
        }
body::before {
    content: '';
    position: fixed;
    inset: 0;
    
    z-index: 0;
}


        .container {
            position: relative;
            z-index: 1;
            max-width: 1100px;
            margin: 40px auto;
            padding: 20px;
        }
header {
    position: relative; /* or fixed if needed */
    z-index: 5; /* higher than .container and body::before */
}

        .blur-container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
            padding: 35px;
        }

        .top {
            text-align: center;
            margin-bottom: 35px;
        }

        .top h1 {
            color: #fff;
            font-size: 36px;
            font-weight: 700;
            text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.6);
            margin-bottom: 10px;
        }

        .top p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 16px;
            text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.5);
        }

        .cart-badge {
            display: inline-block;
            background: linear-gradient(135deg, #2fc6ddff 0%, #0bb5b2ff 100%);
            color: #fff;
            padding: 8px 18px;
            border-radius: 50px;
            font-weight: 600;
            margin-top: 12px;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        .alert {
            background: rgba(76, 175, 80, 0.3);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(76, 175, 80, 0.6);
            color: #fff;
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            text-align: center;
            font-weight: 500;
            animation: slideDown 0.4s ease;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .empty-cart {
            text-align: center;
            padding: 60px 20px;
            color: #fff;
        }

        .empty-cart-icon {
            font-size: 80px;
            margin-bottom: 20px;
            opacity: 0.8;
        }

        .empty-cart h2 {
            font-size: 28px;
            margin-bottom: 15px;
            text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.5);
        }

        .empty-cart p {
            font-size: 16px;
            opacity: 0.9;
            margin-bottom: 25px;
            text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.5);
        }

        .cart-items {
            margin-bottom: 25px;
        }

        .cart-item {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 15px;
            display: grid;
            grid-template-columns: 100px 1fr auto;
            gap: 20px;
            align-items: center;
            transition: all 0.3s ease;
        }

        .cart-item:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
        }

        .item-image {
            width: 100px;
            height: 100px;
            border-radius: 12px;
            object-fit: cover;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }

        .item-details {
            color: #fff;
        }

        .item-name {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 8px;
            text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.5);
        }

        .item-price {
            font-size: 15px;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 12px;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 10px;
        }

        .qty-btn {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: #fff;
            width: 35px;
            height: 35px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 18px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }

        .qty-btn:hover:not(:disabled) {
            background: rgba(255, 255, 255, 0.3);
            transform: scale(1.1);
        }

        .qty-btn:disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }

        .qty-display {
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.3);
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 600;
            min-width: 50px;
            text-align: center;
        }

        .item-actions {
            text-align: right;
        }

        .item-subtotal {
            font-size: 24px;
            font-weight: 700;
            color: #fff;
            margin-bottom: 15px;
            text-shadow: 2px 2px 6px rgba(0, 0, 0, 0.5);
        }

        .btn-remove {
            background: rgba(231, 76, 60, 0.8);
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }

        .btn-remove:hover {
            background: rgba(231, 76, 60, 1);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(231, 76, 60, 0.4);
        }

        .cart-summary {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 15px;
            padding: 30px;
            margin-top: 25px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            color: #fff;
            font-size: 16px;
        }

        .summary-total {
            display: flex;
            justify-content: space-between;
            padding-top: 20px;
            border-top: 2px solid rgba(255, 255, 255, 0.3);
            margin-top: 15px;
            color: #fff;
            font-size: 28px;
            font-weight: 700;
            text-shadow: 2px 2px 6px rgba(0, 0, 0, 0.5);
        }

        .btn-checkout {
            width: 100%;
            background: linear-gradient(135deg, #3db9ebff 0%, #1a95b0ff 100%);
            color: #fff;
            border: none;
            padding: 18px;
            border-radius: 12px;
            font-size: 18px;
            font-weight: 700;
            cursor: pointer;
            margin-top: 20px;
            transition: all 0.3s ease;
            text-decoration: none;
            display: block;
            text-align: center;
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-checkout:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.6);
        }

        .btn-continue {
            width: 100%;
            background: rgba(255, 255, 255, 0.15);
            color: #fff;
            border: 2px solid rgba(255, 255, 255, 0.3);
            padding: 14px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 15px;
            transition: all 0.3s ease;
            text-decoration: none;
            display: block;
            text-align: center;
        }

        .btn-continue:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .container {
                margin: 20px auto;
                padding: 15px;
            }

            .blur-container {
                padding: 25px;
            }

            .top h1 {
                font-size: 28px;
            }

            .cart-item {
                grid-template-columns: 80px 1fr;
                gap: 15px;
            }

            .item-image {
                width: 80px;
                height: 80px;
            }

            .item-actions {
                grid-column: 1 / -1;
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-top: 10px;
            }

            .item-subtotal {
                font-size: 20px;
                margin-bottom: 0;
            }
        }
    </style>
</head>
<body>


<div class="container">
    <div class="blur-container">
        <div class="top">
            <h1>🛒 Shopping Cart</h1>
            <p>Review your items and proceed to checkout</p>
            <?php if ($itemCount > 0): ?>
                <span class="cart-badge"><?= $itemCount ?> Item<?= $itemCount > 1 ? 's' : '' ?> in Cart</span>
            <?php endif; ?>
        </div>

        <?php if ($message): ?>
            <div class="alert">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <?php if (empty($cartItems)): ?>
            <!-- Empty Cart -->
            <div class="empty-cart">
                <div class="empty-cart-icon">🛍️</div>
                <h2>Your Cart is Empty</h2>
                <p>Looks like you haven't added any toys yet!</p>
                <a href="dashboard.php" class="btn-checkout">Start Shopping</a>
            </div>
        <?php else: ?>
            <!-- Cart Items -->
            <div class="cart-items">
                <?php foreach ($cartItems as $item): ?>
                    <div class="cart-item" data-cart-id="<?= $item['id'] ?>">
                        <img src="<?= htmlspecialchars($item['image']) ?>" 
                             alt="<?= htmlspecialchars($item['name']) ?>" 
                             class="item-image"
                             onerror="this.src='https://via.placeholder.com/100x100?text=No+Image'">
                        
                        <div class="item-details">
                            <div class="item-name"><?= htmlspecialchars($item['name']) ?></div>
                            <div class="item-price">₹<?= number_format($item['price'], 2) ?> each</div>
                            
                            <div class="quantity-controls">
                                <button class="qty-btn qty-decrease" data-cart-id="<?= $item['id'] ?>" <?= $item['quantity'] <= 1 ? 'disabled' : '' ?>>−</button>
                                <span class="qty-display"><?= intval($item['quantity']) ?></span>
                                <button class="qty-btn qty-increase" data-cart-id="<?= $item['id'] ?>">+</button>
                            </div>
                        </div>
                        
                        <div class="item-actions">
                            <div class="item-subtotal">₹<?= number_format($item['subtotal'], 2) ?></div>
                            <a href="?remove=<?= $item['id'] ?>" 
                               class="btn-remove" 
                               onclick="return confirm('Remove this item from cart?')">
                               🗑️ Remove
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Cart Summary -->
            <div class="cart-summary">
                <div class="summary-row">
                    <span>Subtotal (<?= $itemCount ?> item<?= $itemCount > 1 ? 's' : '' ?>)</span>
                    <span id="subtotal-amount">₹<?= number_format($cartTotal, 2) ?></span>
                </div>
                <div class="summary-row">
                    <span>Shipping</span>
                    <span>FREE</span>
                </div>
                <div class="summary-total">
                    <span>Total Amount</span>
                    <span id="total-amount">₹<?= number_format($cartTotal, 2) ?></span>
                </div>

                <a href="payment.php" class="btn-checkout">
                    Proceed to Checkout →
                </a>
                <a href="dashboard.php" class="btn-continue">
                    ← Continue Shopping
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    function updateQuantity(cartId, action) {
        // Use form-urlencoded to ensure PHP parses $_POST correctly
        const formData = new URLSearchParams();
        formData.append('update_quantity', '1');
        formData.append('cart_id', cartId);
        formData.append('action', action);

        fetch('addtocart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: formData.toString()
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload(); // reload cart to show updated quantities
            } else {
                alert(data.message || 'Failed to update quantity');
            }
        })
        .catch(() => {
            alert('Error updating quantity');
        });
    }

    document.querySelectorAll('.qty-increase').forEach(btn => {
        btn.addEventListener('click', function() {
            updateQuantity(this.dataset.cartId, 'increase');
        });
    });

    document.querySelectorAll('.qty-decrease').forEach(btn => {
        btn.addEventListener('click', function() {
            updateQuantity(this.dataset.cartId, 'decrease');
        });
    });
});

</script>
</body>
</html>