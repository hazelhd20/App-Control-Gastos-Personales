<?php
/**
 * Main Router
 */

require_once __DIR__ . '/../config/config.php';

$page = $_GET['page'] ?? 'login';
$action = $_GET['action'] ?? '';

// Handle controller actions
if (!empty($action)) {
    switch ($action) {
        case 'register':
            $controller = new AuthController();
            $controller->register();
            break;
        case 'login':
            $controller = new AuthController();
            $controller->login();
            break;
        case 'logout':
            $controller = new AuthController();
            $controller->logout();
            break;
        case 'forgot-password':
            $controller = new AuthController();
            $controller->forgotPassword();
            break;
        case 'reset-password':
            $controller = new AuthController();
            $controller->resetPassword();
            break;
        case 'initial-setup':
            $controller = new ProfileController();
            $controller->initialSetup();
            break;
        case 'update-profile':
            $controller = new ProfileController();
            $controller->update();
            break;
        case 'add-income':
            $controller = new ProfileController();
            $controller->addIncome();
            break;
        case 'add-transaction':
            $controller = new TransactionController();
            $controller->add();
            break;
        case 'delete-transaction':
            $controller = new TransactionController();
            $controller->delete();
            break;
        case 'export-transactions':
            $controller = new TransactionController();
            $controller->export();
            break;
        case 'get-dashboard-data':
            $controller = new ReportController();
            $controller->getDashboardData();
            break;
        case 'get-category-chart':
            $controller = new ReportController();
            $controller->getCategoryChartData();
            break;
        case 'get-monthly-comparison':
            $controller = new ReportController();
            $controller->getMonthlyComparison();
            break;
        case 'get-payment-method-data':
            $controller = new ReportController();
            $controller->getPaymentMethodData();
            break;
        default:
            header('HTTP/1.0 404 Not Found');
            echo "Action not found";
            exit();
    }
}

// Route to views
$allowed_pages = [
    'login', 'register', 'forgot-password', 'reset-password',
    'initial-setup', 'dashboard', 'profile', 'transactions', 
    'add-transaction', 'reports'
];

if (!in_array($page, $allowed_pages)) {
    $page = 'login';
}

// Public pages (no authentication required)
$public_pages = ['login', 'register', 'forgot-password', 'reset-password'];

if (!in_array($page, $public_pages)) {
    requireLogin();
    
    // Check if initial setup is complete
    if ($page !== 'initial-setup') {
        $database = new Database();
        $db = $database->getConnection();
        $profile = new FinancialProfile($db);
        
        if (!$profile->isSetupComplete($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'public/index.php?page=initial-setup');
            exit();
        }
    }
}

// Redirect authenticated users away from auth pages
if (in_array($page, $public_pages) && $page !== 'reset-password') {
    redirectIfAuthenticated();
}

// Load the view
$view_file = __DIR__ . '/../views/' . str_replace('-', '_', $page) . '.php';

if (file_exists($view_file)) {
    require_once $view_file;
} else {
    header('HTTP/1.0 404 Not Found');
    echo "Page not found";
}

