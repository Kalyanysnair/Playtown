<?php
require_once 'config.php';

class Product {
    private $db;

    // Product properties
    private $id;
    private $name;
    private $category;
    private $description;
    private $specifications;
    private $price;
    private $image;
    private $additional_images;
    private $size;
    private $material;
    private $created_at;

    // Constructor: initialize DB
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    // Set product properties from associative array
    private function setProperties($data) {
        $this->id = $data['id'] ?? null;
        $this->name = $data['name'] ?? '';
        $this->category = $data['category'] ?? '';
        $this->description = $data['description'] ?? '';
        $this->specifications = $data['specifications'] ?? '';
        $this->price = $data['price'] ?? 0;
        $this->image = $data['image'] ?? '';
        $this->additional_images = $data['additional_images'] ?? '';
        $this->size = $data['size'] ?? '';
        $this->material = $data['material'] ?? '';
        $this->created_at = $data['created_at'] ?? '';
    }

    // Get all products
    public function getAllProducts() {
        $query = "SELECT * FROM products ORDER BY id DESC";
        $result = $this->db->query($query);

        $products = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $products[] = $row;
            }
        }
        return $products;
    }

    // Get a single product by ID
    public function getProductById($id) {
        $stmt = $this->db->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows === 1) {
            $data = $result->fetch_assoc();
            $this->setProperties($data);
            return $data;
        }
        return null;
    }

    // Search products by name
    public function searchProducts($keyword) {
        $likeKeyword = "%$keyword%";
        $stmt = $this->db->prepare("SELECT * FROM products WHERE name LIKE ? ORDER BY id DESC");
        $stmt->bind_param("s", $likeKeyword);
        $stmt->execute();
        $result = $stmt->get_result();

        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        return $products;
    }

    // Getter methods for product properties
    public function getId() { return $this->id; }
    public function getName() { return $this->name; }
    public function getCategory() { return $this->category; }
    public function getDescription() { return $this->description; }
    public function getSpecifications() { return $this->specifications; }
    public function getPrice() { return $this->price; }
    public function getImage() { return $this->image; }
    public function getAdditionalImages() { return $this->additional_images; }
    public function getSize() { return $this->size; }
    public function getMaterial() { return $this->material; }
    public function getCreatedAt() { return $this->created_at; }
}
?>
