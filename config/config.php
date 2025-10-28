<?php
/**
 * Auto Configuration
 */

// Session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Composer autoloader
if (file_exists(dirname(__DIR__) . '/vendor/autoload.php')) {
    require_once dirname(__DIR__) . '/vendor/autoload.php';
}

// Auto-detect base URL
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$scriptName = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$baseUrl = $protocol . '://' . $host . rtrim(str_replace('/public', '', $scriptName), '/') . '/';

// Assets URL for static files (css, js, images)
// If we're in the public directory, assets are relative to current directory
// Otherwise, they're in the public subdirectory
$assetsUrl = $protocol . '://' . $host . rtrim($scriptName, '/') . '/';

define('BASE_URL', $baseUrl);
define('ASSETS_URL', $assetsUrl);
define('BASE_PATH', dirname(__DIR__) . '/');

// Email config
define('SMTP_HOST', 'mail.hazelhd.com');
define('SMTP_PORT', 465);
define('SMTP_USERNAME', 'no-reply@hazelhd.com');
define('SMTP_PASSWORD', 'esDECczn*HkOZe-Y');
define('FROM_EMAIL', 'no-reply@hazelhd.com');
define('FROM_NAME', 'Control de Gastos');
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

// Helper functions
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . 'public/index.php?page=login');
        exit();
    }
}

function redirectIfAuthenticated() {
    if (isLoggedIn()) {
        header('Location: ' . BASE_URL . 'public/index.php?page=dashboard');
        exit();
    }
}

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function formatCurrency($amount, $currency = 'MXN') {
    $symbols = ['MXN' => '$', 'USD' => '$', 'EUR' => '€'];
    $symbol = $symbols[$currency] ?? '$';
    return $symbol . number_format($amount, 2);
}

function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

function setFlashMessage($message, $type = 'success') {
    $_SESSION['flash_message'] = [
        'message' => $message,
        'type' => $type
    ];
}
