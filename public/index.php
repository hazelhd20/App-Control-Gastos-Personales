<?php
/**
 * Auto Router - Automatically routes to controllers and views
 */

require_once __DIR__ . '/../config/config.php';

$router = new Router();

$page = $_GET['page'] ?? 'login';
$action = $_GET['action'] ?? '';

// Handle action
if (!empty($action)) {
    if (!$router->handleAction($action)) {
        header('HTTP/1.0 404 Not Found');
        echo "Action not found";
        exit();
    }
}

// Load view
$router->loadView($page);
