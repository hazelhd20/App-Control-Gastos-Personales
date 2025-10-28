<?php
/**
 * Auto Router - Automatically routes to controllers and views
 */

class Router {
    private $controllersPath;
    private $viewsPath;
    private $publicPages = ['login', 'register', 'forgot-password', 'reset-password', 'verify-email'];
    
    public function __construct() {
        $this->controllersPath = BASE_PATH . 'controllers/';
        $this->viewsPath = BASE_PATH . 'views/';
    }
    
    /**
     * Auto-detect and execute controller action
     */
    public function handleAction($action) {
        if (empty($action)) return false;
        
        // Auto-discover controllers
        $controllers = $this->getControllers();
        
        // Try to find matching controller and method
        foreach ($controllers as $controllerClass) {
            $controller = new $controllerClass();
            $methods = get_class_methods($controller);
            
            // Convert action-name to methodName or method_name
            $possibleMethods = [
                $this->actionToMethod($action),
                str_replace('-', '_', $action),
                lcfirst(str_replace('-', '', ucwords($action, '-')))
            ];
            
            foreach ($possibleMethods as $method) {
                if (in_array($method, $methods)) {
                    $controller->$method();
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Get all available controllers
     */
    private function getControllers() {
        $controllers = [];
        foreach (scandir($this->controllersPath) as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                $controllers[] = pathinfo($file, PATHINFO_FILENAME);
            }
        }
        return $controllers;
    }
    
    /**
     * Convert action-name to methodName
     */
    private function actionToMethod($action) {
        $parts = explode('-', $action);
        $method = array_shift($parts);
        foreach ($parts as $part) {
            $method .= ucfirst($part);
        }
        return $method;
    }
    
    /**
     * Get all available views
     */
    public function getViews() {
        $views = [];
        foreach (scandir($this->viewsPath) as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                $viewName = str_replace('_', '-', pathinfo($file, PATHINFO_FILENAME));
                $views[$viewName] = $file;
            }
        }
        return $views;
    }
    
    /**
     * Load view
     */
    public function loadView($page) {
        $views = $this->getViews();
        
        if (!isset($views[$page])) {
            $page = 'login';
        }
        
        // Auth checks
        if (!in_array($page, $this->publicPages)) {
            requireLogin();
            
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
        
        if (in_array($page, $this->publicPages) && $page !== 'reset-password' && $page !== 'verify-email') {
            redirectIfAuthenticated();
        }
        
        // Email verification with token
        if ($page === 'verify-email' && isset($_GET['token'])) {
            $controller = new AuthController();
            $controller->verifyEmail();
        }
        
        // Load view
        $view_file = $this->viewsPath . $views[$page];
        if (file_exists($view_file)) {
            require_once $view_file;
        } else {
            header('HTTP/1.0 404 Not Found');
            echo "Page not found";
        }
    }
}

