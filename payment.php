<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// This will show you exactly where the output is coming from
ob_start();
// 1. Start session at the very top (before ANY output)
if (session_status() === PHP_SESSION_NONE) session_start();

// 2. Handle AJAX requests FIRST (before any includes that might output HTML)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // AJAX: Save phone and address
    if (isset($_POST['save_details'])) {
        require_once 'User.php';
        header('Content-Type: application/json');

        $phone = trim($_POST['phone'] ?? '');
        $addressLine1 = trim($_POST['address_line1'] ?? '');
        $addressLine2 = trim($_POST['address_line2'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $state = trim($_POST['state'] ?? '');
        $pincode = trim($_POST['pincode'] ?? '');

        if (!preg_match('/^[0-9]{10}$/', $phone)) {
            echo json_encode(['success' => false, 'message' => 'Valid 10-digit phone required.']);
            exit();
        }
        if (empty($addressLine1) || empty($city) || empty($state) || empty($pincode)) {
            echo json_encode(['success' => false, 'message' => 'Please fill all required fields.']);
            exit();
        }

        User::updatePhone($_SESSION['user_id'], $phone);
        User::updateAddress($_SESSION['user_id'], [
            'line1' => $addressLine1,
            'line2' => $addressLine2,
            'city' => $city,
            'state' => $state,
            'pincode' => $pincode
        ]);

        echo json_encode(['success' => true]);
        exit();
    }

    // AJAX: Process payment
    if (isset($_POST['ajax_payment'])) {
        require_once 'User.php';
        require_once 'Cart.php';
        require_once 'Order.php';
        
        header('Content-Type: application/json');

        $paymentId = $_POST['razorpay_payment_id'] ?? null;
        $phone = trim($_POST['phone'] ?? '');
        $deliveryAddress = $_POST['delivery_address'] ?? '';

        if (!$paymentId) {
            echo json_encode(['success' => false, 'message' => 'Missing payment ID']);
            exit();
        }

        try {
            $order = new Order($_SESSION['user_id']);
            $orderId = $order->createOrder('razorpay', $paymentId, $phone, $deliveryAddress);
            
            if ($orderId) {
                echo json_encode(['success' => true, 'order_id' => $orderId]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Order creation failed. Please contact support with payment ID: ' . $paymentId]);
            }
        } catch (Exception $e) {
            error_log("Order creation error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Order creation failed: ' . $e->getMessage()]);
        }
        exit();
    }
}

// 3. NOW load the required classes for regular page load
require_once 'User.php';
require_once 'Cart.php';
require_once 'Order.php';

// 4. Check if user is logged in
if (!User::isLoggedIn()) {
    header("Location: login.php");
    exit();
}

// 5. Cart & user details
$cart = new Cart($_SESSION['user_id']);
$cartItems = $cart->getItems();
$cartTotal = $cart->getTotal();

if (empty($cartItems) || $cartTotal <= 0) {
    header("Location: addtocart.php");
    exit();
}

$userDetails = User::getUserDetails($_SESSION['user_id']);

// 6. Razorpay payment variables
$amount_paise = intval(round($cartTotal * 100));
$user_name = htmlspecialchars($_SESSION['user_name'] ?? '');
$user_email = htmlspecialchars($_SESSION['user_email'] ?? '');

// 7. Include header AFTER all redirects and AJAX handling
include 'header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Checkout - ToyStore Kids</title>
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
     background:  linear-gradient(rgba(0, 0, 0, 0.04), rgba(0, 0, 0, 0.06)),url('https://thumbs.dreamstime.com/b/explore-detailed-texture-lifelike-toy-animals-adorable-closeup-images-perfect-animal-concept-backgrounds-320614078.jpg') no-repeat center center fixed;
    position: relative;
    background-size: cover;
    margin: 0;
    padding: 0;
   
}
}

body::before {
    content: '';
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.59);
    z-index: 0;
}
header {
    position: relative; /* or fixed if needed */
    z-index: 5; /* higher than .container and body::before */
}
.main-container {
    position: relative;
    z-index: 1;
    max-width: 900px;
    margin: 40px auto;
    padding: 20px;
}

.blur-wrapper {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border-radius: 20px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
    padding: 40px;
}

.page-title {
    text-align: center;
    margin-bottom: 40px;
    color: #fff;
}

.page-title h1 {
    font-size: 36px;
    font-weight: 700;
    margin-bottom: 10px;
    text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.6);
}

.page-title p {
    font-size: 16px;
    opacity: 0.9;
    text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.5);
}

.test-mode-badge {
    display: inline-block;
    background: rgba(255, 193, 7, 0.3);
    border: 1px solid rgba(255, 193, 7, 0.6);
    padding: 12px 20px;
    border-radius: 10px;
    color: #fff;
    font-weight: 600;
    margin: 20px 0;
    text-align: center;
    font-size: 14px;
}

.payment-methods-info {
    background: rgba(33, 150, 243, 0.2);
    border: 1px solid rgba(33, 150, 243, 0.4);
    padding: 15px;
    border-radius: 10px;
    color: #fff;
    margin: 15px 0;
    font-size: 13px;
    text-align: center;
}

.section {
    background: rgba(255, 255, 255, 0.08);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.15);
    border-radius: 15px;
    padding: 30px;
    margin-bottom: 25px;
}

.section-title {
    color: #fff;
    font-size: 20px;
    font-weight: 600;
    margin-bottom: 20px;
    text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.5);
}

.order-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.15);
    color: #fff;
}

.order-item:last-child {
    border-bottom: none;
}

.item-details {
    flex: 1;
}

.item-name {
    font-weight: 500;
    font-size: 16px;
    margin-bottom: 5px;
}

.item-qty {
    font-size: 14px;
    opacity: 0.8;
}

.item-price {
    font-weight: 600;
    font-size: 16px;
    white-space: nowrap;
    margin-left: 20px;
}

.order-total {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-top: 2px solid rgba(255, 255, 255, 0.3);
    margin-top: 20px;
    padding-top: 20px;
    font-size: 24px;
    font-weight: 700;
    color: #fff;
}

.saved-info {
    background: rgba(76, 175, 80, 0.2);
    border: 1px solid rgba(76, 175, 80, 0.5);
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 20px;
    color: #fff;
}

.saved-info p {
    margin-bottom: 8px;
    line-height: 1.6;
}

.saved-info p:last-child {
    margin-bottom: 0;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    color: #fff;
    font-weight: 600;
    margin-bottom: 8px;
    font-size: 14px;
}

.form-group label .required {
    color: #ff4444;
}

.form-group input {
    width: 100%;
    padding: 12px 15px;
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 10px;
    background: rgba(255, 255, 255, 0.1);
    color: #fff;
    font-size: 15px;
    font-family: 'Poppins', sans-serif;
    transition: all 0.3s;
}

.form-group input::placeholder {
    color: rgba(255, 255, 255, 0.5);
}

.form-group input:focus {
    outline: none;
    border-color: rgba(255, 255, 255, 0.6);
    background: rgba(255, 255, 255, 0.15);
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.btn {
    width: 100%;
    padding: 15px;
    border: none;
    border-radius: 12px;
    font-size: 16px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s;
    font-family: 'Poppins', sans-serif;
}

.btn-primary {
    background: linear-gradient(135deg, #09abd7ff, #0ebbcbff);
    color: #fff;
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

.btn-primary:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 8px 30px rgba(71, 176, 241, 0.6);
}

.btn-primary:disabled {
    background: rgba(150, 150, 150, 0.5);
    cursor: not-allowed;
    box-shadow: none;
}

.btn-secondary {
    background: rgba(16, 185, 129, 0.9);
    color: #fff;
    margin-bottom: 15px;
}

.btn-secondary:hover {
    background: rgba(16, 185, 129, 1);
    transform: translateY(-2px);
}

.btn-edit {
    background: rgba(15, 189, 192, 0.9);
    color: #fff;
    padding: 10px 20px;
    font-size: 14px;
    width: auto;
    margin-top: 10px;
}

.btn-edit:hover {
    background: rgba(14, 153, 208, 1);
}

.info-text {
    color: rgba(255, 255, 255, 0.8);
    font-size: 13px;
    text-align: center;
    margin-top: 12px;
}

.secure-badge {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    color: #4caf50;
    font-size: 13px;
    font-weight: 600;
    margin-top: 15px;
}

@media (max-width: 768px) {
    .main-container {
        padding: 15px;
        margin: 20px auto;
    }

    .blur-wrapper {
        padding: 25px 20px;
    }

    .page-title h1 {
        font-size: 28px;
    }

    .form-row {
        grid-template-columns: 1fr;
    }

    .order-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }

    .item-price {
        margin-left: 0;
    }
}
</style>
</head>
<body>


<div class="main-container">
    <div class="blur-wrapper">
        <div class="page-title">
            <h1>🛍️ Secure Checkout</h1>
            <p>Complete your purchase securely</p>
            
        </div>

        <!-- Order Summary -->
        <div class="section">
            <h2 class="section-title">Order Summary</h2>
            <?php foreach ($cartItems as $item): ?>
                <div class="order-item">
                    <div class="item-details">
                        <div class="item-name"><?= htmlspecialchars($item['name']) ?></div>
                        <div class="item-qty">Qty: <?= intval($item['quantity']) ?> × ₹<?= number_format($item['price'], 2) ?></div>
                    </div>
                    <div class="item-price">₹<?= number_format($item['subtotal'], 2) ?></div>
                </div>
            <?php endforeach; ?>
            <div class="order-total">
                <span>Total Amount</span>
                <span>₹<?= number_format($cartTotal, 2) ?></span>
            </div>
        </div>

        <!-- Delivery Details -->
        <div class="section">
            <h2 class="section-title">Delivery Details</h2>
            
            <?php if (!empty($userDetails['phone']) && !empty($userDetails['address_line1'])): ?>
                <div id="savedDetails">
                    <div class="saved-info">
                        <p><strong>Phone:</strong> <?= htmlspecialchars($userDetails['phone']) ?></p>
                        <p><strong>Address:</strong> <?= htmlspecialchars($userDetails['address_line1']) ?>
                        <?php if ($userDetails['address_line2']) echo ', ' . htmlspecialchars($userDetails['address_line2']); ?></p>
                        <p><?= htmlspecialchars($userDetails['city']) ?>, <?= htmlspecialchars($userDetails['state']) ?> - <?= htmlspecialchars($userDetails['pincode']) ?></p>
                    </div>
                    <button type="button" class="btn btn-edit" onclick="showEditForm()">Change Address</button>
                </div>
            <?php endif; ?>

            <form id="detailsForm" style="<?= (!empty($userDetails['phone']) && !empty($userDetails['address_line1'])) ? 'display:none' : '' ?>">
                <div class="form-group">
                    <label>Phone Number <span class="required">*</span></label>
                    <input type="tel" id="phone" name="phone" placeholder="10-digit mobile number" 
                           value="<?= htmlspecialchars($userDetails['phone'] ?? '') ?>" 
                           pattern="[0-9]{10}" maxlength="10" required>
                </div>
                
                <div class="form-group">
                    <label>Address Line 1 <span class="required">*</span></label>
                    <input type="text" id="address_line1" name="address_line1" 
                           placeholder="House No, Building, Street" 
                           value="<?= htmlspecialchars($userDetails['address_line1'] ?? '') ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Address Line 2</label>
                    <input type="text" id="address_line2" name="address_line2" 
                           placeholder="Landmark (Optional)" 
                           value="<?= htmlspecialchars($userDetails['address_line2'] ?? '') ?>">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>City <span class="required">*</span></label>
                        <input type="text" id="city" name="city" 
                               value="<?= htmlspecialchars($userDetails['city'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>State <span class="required">*</span></label>
                        <input type="text" id="state" name="state" 
                               value="<?= htmlspecialchars($userDetails['state'] ?? '') ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Pincode <span class="required">*</span></label>
                    <input type="text" id="pincode" name="pincode" placeholder="6 digits" 
                           value="<?= htmlspecialchars($userDetails['pincode'] ?? '') ?>" 
                           pattern="[0-9]{6}" maxlength="6" required>
                </div>
                
                <button type="submit" class="btn btn-secondary" id="saveBtn">Save Details</button>
            </form>
        </div>

        <!-- Payment -->
        <div class="section">
            <h2 class="section-title">Payment</h2>
            <button id="payBtn" class="btn btn-primary" 
                    <?= (empty($userDetails['phone']) || empty($userDetails['address_line1'])) ? 'disabled' : '' ?>>
                Pay ₹<?= number_format($cartTotal, 2) ?> Securely
            </button>
             <br><br>
            <a href="addtocart.php"><button id="payBtn" class="btn btn-primary" >Back</button></a>
            <div class="secure-badge">🔒 Secured by Razorpay • 256-bit Encryption</div>
            <p class="info-text">
                <?= (empty($userDetails['phone']) || empty($userDetails['address_line1'])) 
                    ? 'Please save your delivery details first' 
                    : 'All payment methods available: Card, UPI, NetBanking, Wallets' ?>
            </p>
           
             
        </div>
    </div>
</div>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
(() => {
    const form = document.getElementById('detailsForm');
    const payBtn = document.getElementById('payBtn');
    const savedDetails = document.getElementById('savedDetails');

    window.showEditForm = () => {
        if (savedDetails) savedDetails.style.display = 'none';
        form.style.display = 'block';
        payBtn.disabled = true;
    };

    // Save delivery details
    form.addEventListener('submit', e => {
        e.preventDefault();
        
        const phone = document.getElementById('phone').value.trim();
        if (!/^[0-9]{10}$/.test(phone)) {
            alert('Please enter a valid 10-digit phone number');
            return;
        }

        const formData = new URLSearchParams(new FormData(form));
        formData.append('save_details', '1');
        
        const saveBtn = document.getElementById('saveBtn');
        saveBtn.disabled = true;
        saveBtn.innerText = 'Saving...';

        fetch(location.pathname, {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                alert('Details saved successfully!');
                location.reload();
            } else {
                alert(data.message);
                saveBtn.disabled = false;
                saveBtn.innerText = 'Save Details';
            }
        })
        .catch(() => {
            alert('Error saving details. Please try again.');
            saveBtn.disabled = false;
            saveBtn.innerText = 'Save Details';
        });
    });

    // Razorpay payment
    payBtn.addEventListener('click', () => {
        payBtn.disabled = true;
        payBtn.innerText = 'Opening payment gateway...';

        const phone = document.getElementById('phone').value.trim();
        const addressParts = [
            document.getElementById('address_line1')?.value || '',
            document.getElementById('address_line2')?.value || '',
            document.getElementById('city')?.value || '',
            document.getElementById('state')?.value || '',
            document.getElementById('pincode')?.value || ''
        ].filter(Boolean);
        const deliveryAddress = addressParts.join(', ');

        const options = {
            key: 'rzp_test_RVGeQhiXbhlJS6', // Replace with your Razorpay key
            amount: <?= $amount_paise ?>,
            currency: 'INR',
            name: 'PlayTown',
            description: 'Order Payment',
            image: 'https://your-logo-url.com/logo.png', // Optional: Add your logo
            prefill: {
                name: '<?= $user_name ?>',
                email: '<?= $user_email ?>',
                contact: phone
            },
            theme: {
                color: '#0e9595ff'
            },
            // Enable all payment methods
            config: {
                display: {
                    blocks: {
                        banks: {
                            name: 'All payment methods',
                            instruments: [
                                {
                                    method: 'card'
                                },
                                {
                                    method: 'netbanking'
                                },
                                {
                                    method: 'upi'
                                },
                                {
                                    method: 'wallet'
                                }
                            ]
                        }
                    },
                    sequence: ['block.banks'],
                    preferences: {
                        show_default_blocks: true
                    }
                }
            },
            handler: response => {
                payBtn.innerText = 'Processing payment...';
                
                const paymentData = new URLSearchParams({
                    ajax_payment: '1',
                    razorpay_payment_id: response.razorpay_payment_id,
                    phone: phone,
                    delivery_address: deliveryAddress
                });

                fetch(location.pathname, {
                    method: 'POST',
                    body: paymentData
                })
                .then(r => r.json())
                .then(result => {
                    if (result.success) {
                        //alert('✅ Payment Successful!\n\nYour order has been placed successfully and will be delievred within 10 days.\nThank you for shopping with us!');
                        window.location.href = 'dashboard.php';
                    } else {
                        alert('❌ Error: ' + result.message + '\n\nPlease contact support if money was deducted.');
                        resetButton();
                    }
                })
                .catch(err => {
                    console.error('Order creation error:', err);
                    alert('⚠️ Payment succeeded but order processing failed.\n\nPlease contact support.');
                    resetButton();
                });
            },
            modal: {
                ondismiss: () => {
                    console.log('Payment cancelled by user');
                    resetButton();
                }
            }
        };

        const resetButton = () => {
            payBtn.disabled = false;
            payBtn.innerText = 'Pay ₹<?= number_format($cartTotal, 2) ?> Securely';
        };

        try {
            const rzp = new Razorpay(options);
            rzp.on('payment.failed', function (response) {
                console.error('Payment failed:', response.error);
                alert('❌ Payment Failed!\n\nReason: ' + response.error.description + '\n\nPlease try again.');
                resetButton();
            });
            rzp.open();
        } catch (error) {
            console.error('Razorpay initialization error:', error);
            alert('Error initializing payment gateway. Please refresh and try again.');
            resetButton();
        }
    });
})();
</script>
</body>
</html>