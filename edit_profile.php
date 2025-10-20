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

$userId = $_SESSION['user_id'];
$successMessage = '';
$errorMessage = '';

// Handle Profile Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $phone = trim($_POST['phone']);
    $address_line1 = trim($_POST['address_line1']);
    $address_line2 = trim($_POST['address_line2']);
    $city = trim($_POST['city']);
    $state = trim($_POST['state']);
    $pincode = trim($_POST['pincode']);
    
    // Validate phone number
    if (!empty($phone)) {
        if (!preg_match('/^[0-9]{10}$/', $phone)) {
            $errorMessage = "Phone number must be exactly 10 digits!";
        }
    }
    
    // Validate pincode
    if (!empty($pincode) && empty($errorMessage)) {
        if (!preg_match('/^[0-9]{6}$/', $pincode)) {
            $errorMessage = "Pincode must be exactly 6 digits!";
        }
    }
    
    // Validate city (only letters and spaces)
    if (!empty($city) && empty($errorMessage)) {
        if (!preg_match('/^[a-zA-Z\s]+$/', $city)) {
            $errorMessage = "City name should contain only letters and spaces!";
        }
    }
    
    // Validate state (only letters and spaces)
    if (!empty($state) && empty($errorMessage)) {
        if (!preg_match('/^[a-zA-Z\s]+$/', $state)) {
            $errorMessage = "State name should contain only letters and spaces!";
        }
    }
    
    // If no validation errors, update profile
    if (empty($errorMessage)) {
        $stmt = $db->prepare("UPDATE users SET phone = ?, address_line1 = ?, address_line2 = ?, city = ?, state = ?, pincode = ? WHERE id = ?");
        if ($stmt === false) {
            die("Error preparing statement: " . $db->error);
        }
        $stmt->bind_param("ssssssi", $phone, $address_line1, $address_line2, $city, $state, $pincode, $userId);
        
        if ($stmt->execute()) {
            $successMessage = "Profile updated successfully!";
            // Refresh user data
            $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $userData = $result->fetch_assoc();
        } else {
            $errorMessage = "Failed to update profile!";
        }
    }
}

// Handle Password Change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    
    // Validate inputs
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $errorMessage = "All password fields are required!";
    } elseif ($newPassword !== $confirmPassword) {
        $errorMessage = "New passwords do not match!";
    } elseif (strlen($newPassword) < 6) {
        $errorMessage = "New password must be at least 6 characters!";
    } else {
        // Verify current password
        $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        if (password_verify($currentPassword, $user['password'])) {
            // Update password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashedPassword, $userId);
            
            if ($stmt->execute()) {
                $successMessage = "Password changed successfully!";
            } else {
                $errorMessage = "Failed to change password!";
            }
        } else {
            $errorMessage = "Current password is incorrect!";
        }
    }
}

// Fetch current user data
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
if ($stmt === false) {
    die("Error preparing statement: " . $db->error);
}
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$userData = $result->fetch_assoc();

include 'header.php';
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Profile - ToyStore Kids</title>
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
    max-width: 900px;
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

.alert {
    padding: 15px 20px;
    border-radius: 10px;
    margin-bottom: 25px;
    font-size: 15px;
    font-weight: 500;
    text-align: center;
}

.alert-success {
    background: rgba(39, 174, 96, 0.9);
    color: #fff;
    box-shadow: 0 4px 15px rgba(39, 174, 96, 0.3);
}

.alert-error {
    background: rgba(231, 76, 60, 0.9);
    color: #fff;
    box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3);
}

.profile-sections {
    display: flex;
    flex-direction: column;
    gap: 30px;
}

.profile-section {
    background: rgba(255, 255, 255, 0.9);
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
    color: #2c3e50;
}

.section-title {
    font-size: 24px;
    font-weight: bold;
    color: #2c3e50;
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 2px solid #ecf0f1;
}

.form-group {
    margin-bottom: 20px;
}

.form-label {
    display: block;
    font-size: 14px;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.form-input {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #ecf0f1;
    border-radius: 8px;
    font-size: 16px;
    font-family: 'Poppins', sans-serif;
    transition: all 0.3s;
    box-sizing: border-box;
    background: #fff;
    color: #2c3e50;
}

.form-input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-input::placeholder {
    color: #bdc3c7;
}

.form-input:disabled {
    background: #e9ecef;
    color: #6c757d;
    cursor: not-allowed;
    border-color: #dee2e6;
    opacity: 1;
}

.disabled-note {
    font-size: 12px;
    color: #7f8c8d;
    margin-top: 5px;
    font-style: italic;
}

textarea.form-input {
    resize: vertical;
    min-height: 100px;
}

.btn-submit {
    width: 100%;
    padding: 14px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s;
    margin-top: 10px;
}

.btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
}

.btn-submit:active {
    transform: translateY(0);
}

.password-section {
    border-top: 2px dashed #ecf0f1;
    margin-top: 15px;
    padding-top: 25px;
}

.btn-change-password {
    background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
}

.btn-change-password:hover {
    box-shadow: 0 8px 20px rgba(243, 156, 18, 0.4);
}

.password-requirements {
    font-size: 13px;
    color: #7f8c8d;
    margin-top: 5px;
    font-style: italic;
}

@media (max-width: 768px) {
    .main-container {
        margin: 30px 15px;
        padding: 20px;
    }
    
    .page-title {
        font-size: 32px;
    }
    
    .profile-section {
        padding: 20px;
    }
}
</style>
</head>
<body>

<div class="main-container">
    <h1 class="page-title">👤 Edit Profile</h1>
    <p class="page-subtitle">Update your personal information and password</p>

    <?php if (!empty($successMessage)): ?>
        <div class="alert alert-success">✓ <?php echo htmlspecialchars($successMessage); ?></div>
    <?php endif; ?>

    <?php if (!empty($errorMessage)): ?>
        <div class="alert alert-error">✗ <?php echo htmlspecialchars($errorMessage); ?></div>
    <?php endif; ?>

    <div class="profile-sections">
        <!-- Profile Information Section -->
        <div class="profile-section">
            <h2 class="section-title">📝 Personal Information</h2>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" class="form-input" 
                           value="<?php echo htmlspecialchars($userData['name']); ?>" 
                           disabled readonly>
                    <div class="disabled-note">⚠️ Name cannot be changed</div>
                </div>

                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" class="form-input" 
                           value="<?php echo htmlspecialchars($userData['email']); ?>" 
                           disabled readonly>
                    <div class="disabled-note">⚠️ Email cannot be changed</div>
                </div>

                <div class="form-group">
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="phone" class="form-input" 
                           value="<?php echo htmlspecialchars($userData['phone'] ?? ''); ?>" 
                           placeholder="Enter 10 digit phone number" 
                           maxlength="10" 
                           pattern="[0-9]{10}"
                           title="Please enter exactly 10 digits">
                    <div class="password-requirements">Must be exactly 10 digits (e.g., 9876543210)</div>
                </div>

                <div class="form-group">
                    <label class="form-label">Address Line 1</label>
                    <input type="text" name="address_line1" class="form-input" 
                           value="<?php echo htmlspecialchars($userData['address_line1'] ?? ''); ?>" 
                           placeholder="House/Flat No, Building Name"
                           maxlength="255">
                </div>

                <div class="form-group">
                    <label class="form-label">Address Line 2</label>
                    <input type="text" name="address_line2" class="form-input" 
                           value="<?php echo htmlspecialchars($userData['address_line2'] ?? ''); ?>" 
                           placeholder="Street, Area, Locality"
                           maxlength="255">
                </div>

                <div class="form-group">
                    <label class="form-label">City</label>
                    <input type="text" name="city" class="form-input" 
                           value="<?php echo htmlspecialchars($userData['city'] ?? ''); ?>" 
                           placeholder="Enter your city" 
                           maxlength="100"
                           pattern="[a-zA-Z\s]+"
                           title="City name should contain only letters and spaces">
                    <div class="password-requirements">Only letters and spaces allowed</div>
                </div>

                <div class="form-group">
                    <label class="form-label">State</label>
                    <input type="text" name="state" class="form-input" 
                           value="<?php echo htmlspecialchars($userData['state'] ?? ''); ?>" 
                           placeholder="Enter your state" 
                           maxlength="100"
                           pattern="[a-zA-Z\s]+"
                           title="State name should contain only letters and spaces">
                    <div class="password-requirements">Only letters and spaces allowed</div>
                </div>

                <div class="form-group">
                    <label class="form-label">Pincode</label>
                    <input type="text" name="pincode" class="form-input" 
                           value="<?php echo htmlspecialchars($userData['pincode'] ?? ''); ?>" 
                           placeholder="Enter 6 digit pincode" 
                           maxlength="6"
                           pattern="[0-9]{6}"
                           title="Please enter exactly 6 digits">
                    <div class="password-requirements">Must be exactly 6 digits (e.g., 682001)</div>
                </div>

                <button type="submit" name="update_profile" class="btn-submit">
                    💾 Update Profile
                </button>
            </form>
        </div>

        <!-- Change Password Section -->
        <div class="profile-section">
            <h2 class="section-title">🔒 Change Password</h2>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label">Current Password *</label>
                    <input type="password" name="current_password" class="form-input" 
                           required placeholder="Enter current password">
                </div>

                <div class="form-group">
                    <label class="form-label">New Password *</label>
                    <input type="password" name="new_password" class="form-input" 
                           required placeholder="Enter new password" minlength="6">
                    <div class="password-requirements">
                        Password must be at least 6 characters long
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Confirm New Password *</label>
                    <input type="password" name="confirm_password" class="form-input" 
                           required placeholder="Re-enter new password" minlength="6">
                </div>

                <button type="submit" name="change_password" class="btn-submit btn-change-password">
                    🔑 Change Password
                </button>
            </form>
        </div>
    </div>
</div>

</body>
</html>