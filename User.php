<?php
require_once 'config.php';

class User {
    protected $db;
    protected $id;
    protected $name;
    protected $email;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    // Encapsulation: Getters and Setters
    public function getId() {
        return $this->id;
    }
    
    public function getName() {
        return $this->name;
    }
    
    public function getEmail() {
        return $this->email;
    }
    
    public function setId($id) {
        $this->id = $id;
    }
    
    public function setName($name) {
        $this->name = $name;
    }
    
    public function setEmail($email) {
        $this->email = $email;
    }
    
    // Register new user
    public function register($name, $email, $password) {
        // Check if email already exists
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return false; // Email already exists
        }
        
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert new user
        $stmt = $this->db->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $hashedPassword);
        
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
    
    // Login user
    public function login($email, $password) {
        $stmt = $this->db->prepare("SELECT id, name, email, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password'])) {
                $this->id = $user['id'];
                $this->name = $user['name'];
                $this->email = $user['email'];
                
                // Set session variables
                $_SESSION['user_id'] = $this->id;
                $_SESSION['user_name'] = $this->name;
                $_SESSION['user_email'] = $this->email;
                
                return true;
            }
        }
        return false;
    }
    
    // Update user phone
    public static function updatePhone($userId, $phone) {
        $database = new Database();
        $db = $database->getConnection();
        
        $stmt = $db->prepare("UPDATE users SET phone = ? WHERE id = ?");
        $stmt->bind_param("si", $phone, $userId);
        return $stmt->execute();
    }

    // Update user address
    public static function updateAddress($userId, $address) {
        $database = new Database();
        $db = $database->getConnection();
        
        $stmt = $db->prepare("UPDATE users SET address_line1 = ?, address_line2 = ?, city = ?, state = ?, pincode = ? WHERE id = ?");
        $stmt->bind_param("sssssi", 
            $address['line1'], 
            $address['line2'], 
            $address['city'], 
            $address['state'], 
            $address['pincode'], 
            $userId
        );
        return $stmt->execute();
    }

    // Get user details
    public static function getUserDetails($userId) {
        $database = new Database();
        $db = $database->getConnection();
        
        $stmt = $db->prepare("SELECT phone, address_line1, address_line2, city, state, pincode FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    // Check if user is logged in
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    // Logout user
    public static function logout() {
        session_destroy();
        header("Location: login.php");
        exit();
    }
}
// NO CLOSING ?> 