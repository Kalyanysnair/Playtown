<?php
// verify_payment.php - Verify Razorpay payment signature
require_once 'config.php';

class RazorpayVerification {
    private $keyId = 'rzp_test_RVGeQhiXbhlJS6';
    private $keySecret = 'jVK5Bd2FDS2In38yTTsdkfB6';
    
    /**
     * Verify payment signature from Razorpay
     * 
     * @param string $orderId Razorpay order ID (if created)
     * @param string $paymentId Razorpay payment ID
     * @param string $signature Razorpay signature
     * @return bool True if signature is valid
     */
    public function verifyPaymentSignature($orderId, $paymentId, $signature) {
        $generatedSignature = hash_hmac('sha256', $orderId . '|' . $paymentId, $this->keySecret);
        return hash_equals($generatedSignature, $signature);
    }
    
    /**
     * Verify payment ID only (for simple checkout without order creation)
     * 
     * @param string $paymentId Razorpay payment ID
     * @return array Payment details or false
     */
    public function fetchPaymentDetails($paymentId) {
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, "https://api.razorpay.com/v1/payments/" . $paymentId);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $this->keyId . ":" . $this->keySecret);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200 && $response) {
            $paymentData = json_decode($response, true);
            
            // Check if payment is captured/authorized
            if (isset($paymentData['status']) && 
                in_array($paymentData['status'], ['captured', 'authorized'])) {
                return $paymentData;
            }
        }
        
        return false;
    }
    
    /**
     * Create Razorpay order for more secure checkout
     * 
     * @param float $amount Amount in INR
     * @param string $receiptId Unique receipt ID
     * @return array|false Order data or false
     */
    public function createOrder($amount, $receiptId) {
        $amountPaise = intval(round($amount * 100));
        
        $data = json_encode([
            'amount' => $amountPaise,
            'currency' => 'INR',
            'receipt' => $receiptId,
            'notes' => [
                'source' => 'ToyStore Kids'
            ]
        ]);
        
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, "https://api.razorpay.com/v1/orders");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_USERPWD, $this->keyId . ":" . $this->keySecret);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200 && $response) {
            return json_decode($response, true);
        }
        
        return false;
    }
}
?>