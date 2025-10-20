<?php
require_once 'User.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'All fields are required!';
    } else {
        $user = new User();
        if ($user->login($email, $password)) {
            header("Location: dashboard.php");
            exit();
        } else {
            $error = 'Invalid email or password!';
        }
    }
}

include 'header.php';
?>

<style>
body {
    background: url('https://thumbs.dreamstime.com/b/explore-detailed-texture-lifelike-toy-animals-adorable-closeup-images-perfect-animal-concept-backgrounds-320614078.jpg') no-repeat center center fixed;
    background-size: cover;
    font-family: 'Poppins', sans-serif;
    margin: 0;
    padding: 0;
}

/* Login container only (blur + glass effect) */
.container {
    max-width: 450px;
    margin: 80px auto;
    padding: 40px;
    background: rgba(255, 255, 255, 0.2);  /* translucent background */
    border-radius: 15px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.3);
    backdrop-filter: blur(15px);  /* main blur effect */
    -webkit-backdrop-filter: blur(15px);
    color: #fff;
}

h2 {
    text-align: center;
    color: #fff;
    margin-bottom: 30px;
    font-size: 32px;
}

.form-group {
    margin-bottom: 20px;
}

label {
    display: block;
    margin-bottom: 8px;
    color: #f5f5f5;
    font-weight: bold;
    font-size: 14px;
}

input[type="email"],
input[type="password"] {
    width: 100%;
    padding: 12px;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    background: rgba(255, 255, 255, 0.3);
    color: #fff;
    outline: none;
    transition: background 0.3s ease;
}

input[type="email"]::placeholder,
input[type="password"]::placeholder {
    color: rgba(255, 255, 255, 0.7);
}

input[type="email"]:focus,
input[type="password"]:focus {
    background: rgba(255, 255, 255, 0.5);
}

.btn {
    width: 100%;
    padding: 14px;
    background: linear-gradient(135deg, #0e7882ff 0%, #1e9fa4ff 100%);
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 18px;
    font-weight: bold;
    cursor: pointer;
    transition: transform 0.2s ease, box-shadow 0.3s ease;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 20px rgba(10, 161, 161, 0.4);
}

.alert {
    padding: 12px;
    margin-bottom: 20px;
    border-radius: 8px;
    font-size: 14px;
}

.alert-error {
    background: rgba(255, 0, 0, 0.2);
    color: #ffdddd;
    border: 1px solid rgba(255, 0, 0, 0.3);
}

.register-link {
    text-align: center;
    margin-top: 20px;
    color: #eee;
}

.register-link a {
    color: #66d2ea;
    text-decoration: none;
    font-weight: bold;
}

.register-link a:hover {
    text-decoration: underline;
}
</style>

<div class="container">
    <h2>Login Here</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <form method="POST" action="">
        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" required placeholder="Enter your email">
        </div>
        
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required placeholder="Enter your password">
        </div>
        
        <button type="submit" class="btn">Login</button>
    </form>
    
    <div class="register-link">
        Don't have an account? <a href="register.php">Register here</a>
    </div>
</div>

</body>
</html>
