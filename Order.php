<?php
// Order.php - Class definition (MySQLi version)
require_once 'config.php';

class Order {
    private $userId;
    private $conn;

    public function __construct($userId) {
        $this->userId = $userId;
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    /**
     * Create a new order
     * @param string $paymentMethod
     * @param string $paymentId
     * @param string $phone
     * @param string $deliveryAddress
     * @return int|false Order ID on success, false on failure
     */
    public function createOrder($paymentMethod, $paymentId, $phone, $deliveryAddress) {
        try {
            // Get cart items
            require_once 'Cart.php';
            $cart = new Cart($this->userId);
            $cartItems = $cart->getItems();
            $cartTotal = $cart->getTotal();

            if (empty($cartItems)) {
                throw new Exception("Cart is empty");
            }

            // Start transaction
            $this->conn->begin_transaction();

            // Insert order
            $sql = "INSERT INTO orders (
                user_id, 
                total_amount, 
                payment_method, 
                payment_id, 
                phone, 
                delivery_address, 
                status
            ) VALUES (?, ?, ?, ?, ?, ?, 'pending')";
            
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $this->conn->error);
            }

            $stmt->bind_param(
                "idssss",
                $this->userId,
                $cartTotal,
                $paymentMethod,
                $paymentId,
                $phone,
                $deliveryAddress
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }

            $orderId = $this->conn->insert_id;
            $stmt->close();

            // Insert order items
            $sqlItem = "INSERT INTO order_items (
                order_id, 
                product_id, 
                product_name, 
                quantity, 
                price, 
                subtotal
            ) VALUES (?, ?, ?, ?, ?, ?)";
            
            $stmtItem = $this->conn->prepare($sqlItem);
            if (!$stmtItem) {
                throw new Exception("Prepare item failed: " . $this->conn->error);
            }

            foreach ($cartItems as $item) {
                $productId = isset($item['product_id']) ? $item['product_id'] : 0;
                
                $stmtItem->bind_param(
                    "iisidd",
                    $orderId,
                    $productId,
                    $item['name'],
                    $item['quantity'],
                    $item['price'],
                    $item['subtotal']
                );
                
                if (!$stmtItem->execute()) {
                    throw new Exception("Execute item failed: " . $stmtItem->error);
                }
            }
            
            $stmtItem->close();

            // Clear the cart
            $cart->clearCart();

            // Commit transaction
            $this->conn->commit();

            return $orderId;

        } catch (Exception $e) {
            // Rollback on error
            $this->conn->rollback();
            error_log("Order creation error: " . $e->getMessage());
            throw $e; // Re-throw to get error message
        }
    }

    /**
     * Get all orders for this user
     * @return array Array of orders
     */
    public function getUserOrders() {
        try {
            $sql = "SELECT 
                o.id as order_id,
                o.total_amount,
                o.payment_method,
                o.payment_id,
                o.phone,
                o.delivery_address as shipping_address,
                o.status,
                o.order_date as created_at
            FROM orders o
            WHERE o.user_id = ?
            ORDER BY o.order_date DESC";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $this->userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $orders = [];

            while ($order = $result->fetch_assoc()) {
                // Get items for this order
                $itemsSql = "SELECT 
                    product_id,
                    product_name as name,
                    quantity,
                    price,
                    subtotal
                FROM order_items
                WHERE order_id = ?";
                
                $itemsStmt = $this->conn->prepare($itemsSql);
                $itemsStmt->bind_param("i", $order['order_id']);
                $itemsStmt->execute();
                $itemsResult = $itemsStmt->get_result();
                
                $items = [];
                while ($item = $itemsResult->fetch_assoc()) {
                    $items[] = $item;
                }
                $itemsStmt->close();
                
                $order['items'] = json_encode($items);
                $orders[] = $order;
            }
            
            $stmt->close();
            return $orders;

        } catch (Exception $e) {
            error_log("Error fetching orders: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get order statistics for this user
     * @return array Order statistics
     */
    public function getOrderStats() {
        try {
            $sql = "SELECT 
                COUNT(*) as total_orders,
                COALESCE(SUM(total_amount), 0) as total_spent,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_orders
            FROM orders
            WHERE user_id = ?";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $this->userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $stats = $result->fetch_assoc();
            $stmt->close();
            
            return $stats;

        } catch (Exception $e) {
            error_log("Error fetching order stats: " . $e->getMessage());
            return [
                'total_orders' => 0,
                'total_spent' => 0,
                'pending_orders' => 0,
                'completed_orders' => 0
            ];
        }
    }

    /**
     * Get a specific order by ID
     * @param int $orderId
     * @return array|null Order details or null
     */
    public function getOrderById($orderId) {
        try {
            $sql = "SELECT 
                id as order_id,
                user_id,
                total_amount,
                payment_method,
                payment_id,
                phone,
                delivery_address as shipping_address,
                status,
                order_date as created_at
            FROM orders 
            WHERE id = ? AND user_id = ?";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ii", $orderId, $this->userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $order = $result->fetch_assoc();
            $stmt->close();

            if ($order) {
                // Get items
                $itemsSql = "SELECT 
                    product_id,
                    product_name as name,
                    quantity,
                    price,
                    subtotal
                FROM order_items
                WHERE order_id = ?";
                
                $itemsStmt = $this->conn->prepare($itemsSql);
                $itemsStmt->bind_param("i", $orderId);
                $itemsStmt->execute();
                $itemsResult = $itemsStmt->get_result();
                
                $items = [];
                while ($item = $itemsResult->fetch_assoc()) {
                    $items[] = $item;
                }
                $itemsStmt->close();
                
                $order['items'] = $items;
            }

            return $order;

        } catch (Exception $e) {
            error_log("Error fetching order: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Cancel an order
     * @param int $orderId
     * @return bool Success status
     */
    public function cancelOrder($orderId) {
        try {
            $sql = "UPDATE orders 
                SET status = 'cancelled' 
                WHERE id = ? AND user_id = ? AND status = 'pending'";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ii", $orderId, $this->userId);
            $stmt->execute();
            $affected = $stmt->affected_rows;
            $stmt->close();
            
            return $affected > 0;

        } catch (Exception $e) {
            error_log("Error cancelling order: " . $e->getMessage());
            return false;
        }
    }
}
?>