<?php
/**
 * General Configuration
 */

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Base URL configuration
define('BASE_URL', 'http://localhost/App-Control-Gastos/');
define('BASE_PATH', dirname(__DIR__) . '/');

// Email configuration (for password recovery)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com'); // Configure your email
define('SMTP_PASSWORD', 'your-app-password'); // Configure your app password
define('FROM_EMAIL', 'noreply@controlgastos.com');
define('FROM_NAME', 'Control de Gastos');

// Password reset token validity (in minutes)
define('TOKEN_VALIDITY', 5);

// Timezone
date_default_timezone_set('America/Mexico_City');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Autoload classes
spl_autoload_register(function ($class) {
    $paths = [
        BASE_PATH . 'models/',
        BASE_PATH . 'controllers/',
        BASE_PATH . 'config/'
    ];
    
    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Redirect to login if not authenticated
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . 'public/index.php?page=login');
        exit();
    }
}

/**
 * Redirect to dashboard if already authenticated
 */
function redirectIfAuthenticated() {
    if (isLoggedIn()) {
        header('Location: ' . BASE_URL . 'public/index.php?page=dashboard');
        exit();
    }
}

/**
 * Sanitize input
 */
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

/**
 * Format currency
 */
function formatCurrency($amount, $currency = 'MXN') {
    $symbols = [
        'MXN' => '$',
        'USD' => '$',
        'EUR' => '€'
    ];
    
    $symbol = $symbols[$currency] ?? '$';
    return $symbol . number_format($amount, 2);
}

/**
 * Get flash message
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

/**
 * Set flash message
 */
function setFlashMessage($message, $type = 'success') {
    $_SESSION['flash_message'] = [
        'message' => $message,
        'type' => $type
    ];
}

