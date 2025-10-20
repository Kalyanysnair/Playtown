<?php
require_once 'User.php';
require_once 'config.php';

// Start session if not already
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if not logged in
if (!User::isLoggedIn()) {
    header("Location: login.php");
    exit();
}

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Fetch all orders for the logged-in user
$userId = $_SESSION['user_id'];
$stmt = $db->prepare("
    SELECT 
        o.id as order_id,
        o.total_amount,
        o.status,
        o.order_date,
        o.delivery_address,
        o.phone
    FROM orders o
    WHERE o.user_id = ?
    ORDER BY o.order_date DESC
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$orders = $result->fetch_all(MYSQLI_ASSOC);

include 'header.php';
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Order History - ToyStore Kids</title>
<style>
body {
    background: url('https://thumbs.dreamstime.com/b/explore-detailed-texture-lifelike-toy-animals-adorable-closeup-images-perfect-animal-concept-backgrounds-320614078.jpg') no-repeat center center fixed;
    background-size: cover;
    font-family: 'Poppins', sans-serif;
    margin: 0;
    padding: 0;
    color: #fff;
}

.main-container {
    max-width: 1200px;
    margin: 60px auto;
    padding: 30px;
    background: rgba(255, 255, 255, 0.12);
    backdrop-filter: blur(10px);
    border-radius: 18px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.4);
    min-height: 500px;
}

.page-title {
    text-align: center;
    font-size: 40px;
    margin-bottom: 10px;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
}

.page-subtitle {
    text-align: center;
    font-size: 18px;
    margin-bottom: 40px;
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
}

.orders-container {
    display: flex;
    flex-direction: column;
    gap: 25px;
}

.order-card {
    background: rgba(255, 255, 255, 0.9);
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    color: #2c3e50;
}

.order-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 2px solid #ecf0f1;
    padding-bottom: 15px;
    margin-bottom: 15px;
    flex-wrap: wrap;
    gap: 10px;
}

.order-id {
    font-size: 20px;
    font-weight: bold;
    color: #2c3e50;
}

.order-date {
    font-size: 14px;
    color: #7f8c8d;
}

.order-status {
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: bold;
    text-transform: uppercase;
}

.status-pending {
    background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
    color: #fff;
}

.status-completed {
    background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
    color: #fff;
}

.status-delivered {
    background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
    color: #fff;
}

.status-cancelled {
    background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
    color: #fff;
}

.status-processing {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
    color: #fff;
}

.status-shipped {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
    color: #fff;
}

.order-body {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 15px;
}

.order-info-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.info-label {
    font-size: 13px;
    color: #7f8c8d;
    font-weight: 600;
    text-transform: uppercase;
}

.info-value {
    font-size: 16px;
    color: #2c3e50;
    font-weight: 500;
}

.order-total {
    font-size: 24px;
    font-weight: bold;
    color: #27ae60;
}

.order-footer {
    display: flex;
    justify-content: flex-end;
    gap: 15px;
    padding-top: 15px;
    border-top: 2px solid #ecf0f1;
}

.btn-view-details {
    padding: 10px 25px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: bold;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.3s;
    display: inline-block;
}

.btn-view-details:hover {
    transform: scale(1.05);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
}

.empty-orders {
    text-align: center;
    padding: 80px 20px;
    background: rgba(255, 255, 255, 0.85);
    border-radius: 15px;
    color: #7f8c8d;
}

.empty-orders h2 {
    font-size: 32px;
    margin-bottom: 15px;
    color: #2c3e50;
}

.empty-orders p {
    font-size: 18px;
    margin-bottom: 25px;
}

.btn-shop-now {
    padding: 15px 40px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    transition: all 0.3s;
}

.btn-shop-now:hover {
    transform: scale(1.05);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
}

@media (max-width: 768px) {
    .order-body {
        grid-template-columns: 1fr;
    }
    
    .order-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .page-title {
        font-size: 32px;
    }
}
</style>
</head>
<body>

<div class="main-container">
    <h1 class="page-title">📦 Order History</h1>
    <p class="page-subtitle">Track and view all your previous orders</p>

    <div class="orders-container">
        <?php if (empty($orders)): ?>
            <div class="empty-orders">
                <h2>🛍️ No Orders Yet</h2>
                <p>You haven't placed any orders yet. Start shopping now!</p>
                <a href="dashboard.php" class="btn-shop-now">🎪 Start Shopping</a>
            </div>
        <?php else: ?>
            <?php foreach ($orders as $order): ?>
                <?php
                // Format date - check if order_date exists and is valid
                if (!empty($order['order_date'])) {
                    $orderDate = date('F j, Y g:i A', strtotime($order['order_date']));
                } else {
                    $orderDate = 'Date not available';
                }
                
                // Determine status class
                $statusClass = 'status-pending';
                $status = strtolower($order['status'] ?? 'pending');
                
                switch($status) {
                    case 'completed':
                    case 'delivered':
                        $statusClass = 'status-delivered';
                        break;
                    case 'cancelled':
                        $statusClass = 'status-cancelled';
                        break;
                    case 'processing':
                    case 'shipped':
                        $statusClass = 'status-processing';
                        break;
                    default:
                        $statusClass = 'status-pending';
                }
                
                // Get delivery address
                $deliveryAddress = $order['delivery_address'] ?? 'Address not provided';
                $phone = $order['phone'] ?? 'N/A';
                ?>
                
                <div class="order-card">
                    <div class="order-header">
                        <div>
                            <div class="order-id">Order #<?php echo str_pad($order['order_id'], 6, '0', STR_PAD_LEFT); ?></div>
                            <div class="order-date"><?php echo $orderDate; ?></div>
                        </div>
                        <div class="order-status <?php echo $statusClass; ?>">
                            <?php echo htmlspecialchars(ucfirst($order['status'] ?? 'pending')); ?>
                        </div>
                    </div>

                    <div class="order-body">
                        <div class="order-info-group">
                            <span class="info-label">Delivery Address</span>
                            <span class="info-value"><?php echo htmlspecialchars($deliveryAddress); ?></span>
                        </div>
                        
                        <div class="order-info-group">
                            <span class="info-label">Contact Number</span>
                            <span class="info-value"><?php echo htmlspecialchars($phone); ?></span>
                        </div>
                        
                        <div class="order-info-group">
                            <span class="info-label">Order Total</span>
                            <span class="order-total">₹<?php echo number_format($order['total_amount'], 2); ?></span>
                        </div>
                    </div>

                    <!-- <div class="order-footer">
                        <a href="order_details.php?id=<?php echo $order['order_id']; ?>" class="btn-view-details">
                            📋 View Details
                        </a>
                    </div> -->
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

</body>
</html>