<?php
require_once 'config.php';
require_once 'Product.php';

class Cart {
    protected $db;
    protected $userId;

    public function __construct($userId) {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->userId = $userId;
    }

    // Add item to cart
    public function addItem($productId, $quantity = 1) {
        $stmt = $this->db->prepare("SELECT id FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $this->userId, $productId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Update quantity
            $row = $result->fetch_assoc();
            $stmt = $this->db->prepare("UPDATE cart SET quantity = quantity + ? WHERE id = ?");
            $stmt->bind_param("ii", $quantity, $row['id']);
            return $stmt->execute();
        } else {
            // Insert new
            $stmt = $this->db->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
            $stmt->bind_param("iii", $this->userId, $productId, $quantity);
            return $stmt->execute();
        }
    }

    // Update quantity (increase or decrease)
    public function updateQuantity($cartId, $action) {
        try {
            // First get current quantity
            $sql = "SELECT quantity FROM cart WHERE id = ? AND user_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("ii", $cartId, $this->userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();
            
            if (!$row) {
                return false;
            }
            
            $currentQty = $row['quantity'];
            $newQty = $currentQty;
            
            if ($action === 'increase') {
                $newQty = $currentQty + 1;
            } elseif ($action === 'decrease' && $currentQty > 1) {
                $newQty = $currentQty - 1;
            }
            
            // Update quantity
            $updateSql = "UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?";
            $updateStmt = $this->db->prepare($updateSql);
            $updateStmt->bind_param("iii", $newQty, $cartId, $this->userId);
            $success = $updateStmt->execute();
            $updateStmt->close();
            
            return $success;
            
        } catch (Exception $e) {
            error_log("Error updating quantity: " . $e->getMessage());
            return false;
        }
    }

    // Clear all items from cart
    public function clearCart() {
        $stmt = $this->db->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->bind_param("i", $this->userId);
        return $stmt->execute();
    }

    // Remove item from cart
    public function removeItem($cartId) {
        $stmt = $this->db->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $cartId, $this->userId);
        return $stmt->execute();
    }

    // Get all cart items
    public function getItems() {
        $stmt = $this->db->prepare("
            SELECT c.id, c.product_id, c.quantity, p.name, p.price, p.image
            FROM cart c
            JOIN products p ON c.product_id = p.id
            WHERE c.user_id = ?
        ");
        $stmt->bind_param("i", $this->userId);
        $stmt->execute();
        $result = $stmt->get_result();

        $items = [];
        while ($row = $result->fetch_assoc()) {
            $row['subtotal'] = $row['price'] * $row['quantity'];
            $items[] = $row;
        }
        return $items;
    }

    // Check if product is already in cart
    public function isProductInCart($productId) {
        $stmt = $this->db->prepare("SELECT id FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $this->userId, $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }

    // Get total cart amount
    public function getTotal() {
        $items = $this->getItems();
        $total = 0;
        foreach ($items as $item) {
            $total += $item['subtotal'];
        }
        return $total;
    }
}
?>