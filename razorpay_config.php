<?php
// razorpay_config.php - Centralized Razorpay configuration
// Keep this file secure and never commit credentials to version control

define('RAZORPAY_KEY_ID', 'rzp_test_RVGeQhiXbhlJS6');
define('RAZORPAY_KEY_SECRET', 'jVK5Bd2FDS2In38yTTsdkfB6');

// Environment: 'test' or 'live'
define('RAZORPAY_ENV', 'test');

// Currency
define('RAZORPAY_CURRENCY', 'INR');

// API Base URL
define('RAZORPAY_API_URL', 'https://api.razorpay.com/v1/');
?>