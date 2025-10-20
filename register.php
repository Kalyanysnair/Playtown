<?php
require_once 'User.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    
    // Validation
    if (empty($name) || empty($email) || empty($password)) {
        $error = 'All fields are required!';
    } elseif (strlen($name) < 3) {
        $error = 'Name must be at least 3 characters long!';
    } elseif (!preg_match('/^[a-zA-Z\s]+$/', $name)) {
        $error = 'Name can only contain letters and spaces!';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match!';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters!';
    } else {
        $user = new User();
        if ($user->register($name, $email, $password)) {
            $success = 'Registration successful! Please login.';
        } else {
            $error = 'Email already exists!';
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

/* Glass effect container */
.container {
    max-width: 450px;
    margin: 80px auto;
    padding: 40px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 15px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
    backdrop-filter: blur(15px);
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

input[type="text"],
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
    transition: background 0.3s ease, border 0.3s ease;
    box-sizing: border-box;
}

input[type="text"]::placeholder,
input[type="email"]::placeholder,
input[type="password"]::placeholder {
    color: rgba(255, 255, 255, 0.7);
}

input[type="text"]:focus,
input[type="email"]:focus,
input[type="password"]:focus {
    background: rgba(255, 255, 255, 0.5);
}

input.invalid {
    border: 2px solid rgba(255, 0, 0, 0.6);
    background: rgba(255, 0, 0, 0.1);
}

input.valid {
    border: 2px solid rgba(0, 255, 0, 0.6);
    background: rgba(0, 255, 0, 0.1);
}

.input-hint {
    font-size: 12px;
    color: rgba(255, 255, 255, 0.7);
    margin-top: 5px;
    display: block;
}

.error-hint {
    color: #ff6b6b;
    font-size: 12px;
    margin-top: 5px;
    display: none;
}

.btn {
    width: 100%;
    padding: 14px;
    background: linear-gradient(135deg, #66c5eaff 0%, #17adb0ff 100%);
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 18px;
    font-weight: bold;
    cursor: pointer;
    transition: transform 0.2s ease, box-shadow 0.3s ease;
    box-sizing: border-box;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
}

.btn:disabled {
    background: rgba(150, 150, 150, 0.5);
    cursor: not-allowed;
    transform: none;
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

.alert-success {
    background: rgba(0, 255, 0, 0.2);
    color: #ddffdd;
    border: 1px solid rgba(0, 255, 0, 0.3);
}

.login-link {
    text-align: center;
    margin-top: 20px;
    color: #eee;
}

.login-link a {
    color: #66d2ea;
    text-decoration: none;
    font-weight: bold;
}

.login-link a:hover {
    text-decoration: underline;
}
</style>

<div class="container">
    <h2>📝 Register</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <form method="POST" action="" id="registerForm">
        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="name" id="name" required 
                   placeholder="Enter your full name (letters only)"
                   pattern="[a-zA-Z\s]+" 
                   minlength="3">
            <span class="input-hint">At least 3 characters, letters only</span>
            <span class="error-hint" id="nameError"></span>
        </div>
        
        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" id="email" required 
                   placeholder="Enter your email">
        </div>
        
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" id="password" required 
                   placeholder="Enter password (min 6 characters)"
                   minlength="6">
            <span class="input-hint">Minimum 6 characters</span>
        </div>
        
        <div class="form-group">
            <label>Confirm Password</label>
            <input type="password" name="confirm_password" id="confirmPassword" required 
                   placeholder="Confirm your password">
            <span class="error-hint" id="passwordError"></span>
        </div>
        
        <button type="submit" class="btn">Register</button>
    </form>
    
    <div class="login-link">
        Already have an account? <a href="login.php">Login here</a>
    </div>
</div>

<script>
// Real-time validation
const nameInput = document.getElementById('name');
const passwordInput = document.getElementById('password');
const confirmPasswordInput = document.getElementById('confirmPassword');
const form = document.getElementById('registerForm');

// Name validation
nameInput.addEventListener('input', function() {
    const value = this.value.trim();
    const nameError = document.getElementById('nameError');
    const isValid = /^[a-zA-Z\s]+$/.test(value) && value.length >= 3;
    
    if (value.length === 0) {
        this.classList.remove('valid', 'invalid');
        nameError.style.display = 'none';
    } else if (!isValid) {
        this.classList.add('invalid');
        this.classList.remove('valid');
        if (value.length < 3) {
            nameError.textContent = 'Name must be at least 3 characters';
        } else {
            nameError.textContent = 'Only letters and spaces allowed';
        }
        nameError.style.display = 'block';
    } else {
        this.classList.add('valid');
        this.classList.remove('invalid');
        nameError.style.display = 'none';
    }
});

// Password match validation
confirmPasswordInput.addEventListener('input', function() {
    const passwordError = document.getElementById('passwordError');
    if (this.value.length > 0) {
        if (this.value !== passwordInput.value) {
            this.classList.add('invalid');
            this.classList.remove('valid');
            passwordError.textContent = 'Passwords do not match';
            passwordError.style.display = 'block';
        } else {
            this.classList.add('valid');
            this.classList.remove('invalid');
            passwordError.style.display = 'none';
        }
    } else {
        this.classList.remove('valid', 'invalid');
        passwordError.style.display = 'none';
    }
});

// Form submission validation
form.addEventListener('submit', function(e) {
    const name = nameInput.value.trim();
    const password = passwordInput.value;
    const confirmPassword = confirmPasswordInput.value;
    
    // Name validation
    if (name.length < 3) {
        e.preventDefault();
        alert('Name must be at least 3 characters long!');
        nameInput.focus();
        return false;
    }
    
    if (!/^[a-zA-Z\s]+$/.test(name)) {
        e.preventDefault();
        alert('Name can only contain letters and spaces!');
        nameInput.focus();
        return false;
    }
    
    // Password match validation
    if (password !== confirmPassword) {
        e.preventDefault();
        alert('Passwords do not match!');
        confirmPasswordInput.focus();
        return false;
    }
    
    // Password length validation
    if (password.length < 6) {
        e.preventDefault();
        alert('Password must be at least 6 characters!');
        passwordInput.focus();
        return false;
    }
});
</script>

</body>
</html>