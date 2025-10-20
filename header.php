<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $isLoggedIn ? $_SESSION['user_name'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Georgia', serif;
            background: linear-gradient(135deg, #0cc0c3ff 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .header {
            background: #0991a6ff;
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }
        
        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 28px;
            font-weight: bold;
            color: #fff;
            text-decoration: none;
            display: flex;
            align-items: center;
        }
        
        .logo span {
            color: #ff6b6b;
            margin-left: 5px;
        }
        
        .nav-menu {
            display: flex;
            gap: 25px;
            align-items: center;
        }
        
        .nav-menu a {
            color: #ecf0f1;
            text-decoration: none;
            font-size: 16px;
            padding: 8px 16px;
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        
        .nav-menu a:hover {
            background: #34495e;
            color: #fff;
        }
        
        /* User Dropdown Styles */
        .user-dropdown {
            position: relative;
        }
        
        .user-name {
            color: #ff6b6b;
            font-weight: bold;
            padding: 8px 16px;
            background: #34495e;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .user-name:hover {
            background: #2c3e50;
        }
        
        .dropdown-arrow {
            font-size: 12px;
            transition: transform 0.3s ease;
        }
        
        .user-dropdown.active .dropdown-arrow {
            transform: rotate(180deg);
        }
        
        .dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            margin-top: 10px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            min-width: 200px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            z-index: 1000;
        }
        
        .user-dropdown.active .dropdown-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        .dropdown-menu a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 20px;
            color: #2c3e50;
            text-decoration: none;
            transition: all 0.3s ease;
            border-radius: 0;
        }
        
        .dropdown-menu a:first-child {
            border-radius: 8px 8px 0 0;
        }
        
        .dropdown-menu a:last-child {
            border-radius: 0 0 8px 8px;
        }
        
        .dropdown-menu a:hover {
            background: #f8f9fa;
            color: #0991a6ff;
            padding-left: 25px;
        }
        
        .dropdown-menu a span {
            font-size: 18px;
        }
        
        .logout-btn {
            background: #e74c3c;
            color: #fff !important;
        }
        
        .logout-btn:hover {
            background: #c0392b !important;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-container">
            <a href="dashboard.php" class="logo">🧸 Play<span>Town</span></a>
            <nav class="nav-menu">
                <?php if ($isLoggedIn): ?>
                    <a href="dashboard.php">Home</a>
                    <a href="addtocart.php">Cart</a>
                    
                    <div class="user-dropdown">
                        <div class="user-name">
                            👤 <?php echo htmlspecialchars($userName); ?>
                          
                        </div>
                        <div class="dropdown-menu">
                            <a href="orders.php">
                                <span>📦</span> My Orders
                            </a>
                            <a href="edit_profile.php">
                                <span>⚙️</span> Account 
                            </a>
                        </div>
                    </div>
                    
                    <a href="logout.php" class="logout-btn">Logout</a>
                <?php else: ?>
                    <a href="login.php">Login</a>
                    <a href="register.php">Register</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <script>
        // Toggle dropdown on click
        document.addEventListener('DOMContentLoaded', function() {
            const userDropdown = document.querySelector('.user-dropdown');
            const userName = document.querySelector('.user-name');
            
            if (userName) {
                userName.addEventListener('click', function(e) {
                    e.stopPropagation();
                    userDropdown.classList.toggle('active');
                });
                
                // Close dropdown when clicking outside
                document.addEventListener('click', function(e) {
                    if (!userDropdown.contains(e.target)) {
                        userDropdown.classList.remove('active');
                    }
                });
            }
        });
    </script>
</body>
</html>