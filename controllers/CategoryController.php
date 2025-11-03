<?php
/**
 * Category Controller
 */

require_once __DIR__ . '/../config/config.php';

class CategoryController {
    private $db;
    private $category;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->category = new Category($this->db);
    }

    /**
     * Show categories management page
     */
    public function manageCategories() {
        requireLogin();

        $user_id = $_SESSION['user_id'];
        $custom_categories = $this->category->getCustomCategoriesByUser($user_id);

        $page_title = 'Gestionar Categorías - Control de Gastos';
        require_once __DIR__ . '/../includes/header.php';
        require_once __DIR__ . '/../includes/navbar.php';

        include __DIR__ . '/../views/manage_categories.php';
    }

    /**
     * Create new category
     */
    public function createCategory() {
        requireLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user_id = $_SESSION['user_id'];
            
            $name = sanitize($_POST['name'] ?? '');
            $type = sanitize($_POST['type'] ?? 'expense');
            $icon = sanitize($_POST['icon'] ?? '');
            $color = sanitize($_POST['color'] ?? '#FF6B6B');

            $errors = [];

            if (empty($name)) {
                $errors[] = "El nombre de la categoría es obligatorio";
            }

            if ($this->category->exists($name, $type, $user_id)) {
                $errors[] = "Ya existe una categoría con ese nombre para este tipo";
            }

            if (empty($errors)) {
                $this->category->user_id = $user_id;
                $this->category->name = $name;
                $this->category->type = $type;
                $this->category->icon = $icon;
                $this->category->color = $color;

                if ($this->category->create()) {
                    setFlashMessage('Categoría creada exitosamente', 'success');
                } else {
                    setFlashMessage('Error al crear la categoría', 'error');
                }
            } else {
                $_SESSION['category_errors'] = $errors;
            }

            // Return JSON if AJAX request
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                if (empty($errors)) {
                    echo json_encode(['success' => true, 'message' => 'Categoría creada exitosamente']);
                } else {
                    echo json_encode(['success' => false, 'errors' => $errors]);
                }
                exit();
            }
        }

        header('Location: ' . BASE_URL . 'public/index.php?page=manage-categories');
        exit();
    }

    /**
     * Update category
     */
    public function updateCategory() {
        requireLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user_id = $_SESSION['user_id'];
            
            $id = intval($_POST['id'] ?? 0);
            $name = sanitize($_POST['name'] ?? '');
            $icon = sanitize($_POST['icon'] ?? '');
            $color = sanitize($_POST['color'] ?? '#FF6B6B');

            $errors = [];

            if (empty($name)) {
                $errors[] = "El nombre de la categoría es obligatorio";
            }

            if (empty($errors)) {
                $this->category->id = $id;
                $this->category->user_id = $user_id;
                $this->category->name = $name;
                $this->category->icon = $icon;
                $this->category->color = $color;

                if ($this->category->update()) {
                    setFlashMessage('Categoría actualizada exitosamente', 'success');
                } else {
                    setFlashMessage('Error al actualizar la categoría', 'error');
                }
            }

            // Return JSON if AJAX request
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                if (empty($errors)) {
                    echo json_encode(['success' => true, 'message' => 'Categoría actualizada exitosamente']);
                } else {
                    echo json_encode(['success' => false, 'errors' => $errors]);
                }
                exit();
            }
        }

        header('Location: ' . BASE_URL . 'public/index.php?page=manage-categories');
        exit();
    }

    /**
     * Delete category
     */
    public function deleteCategory() {
        requireLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user_id = $_SESSION['user_id'];
            $id = intval($_POST['id'] ?? 0);

            if ($this->category->delete($id, $user_id)) {
                setFlashMessage('Categoría eliminada exitosamente', 'success');
            } else {
                setFlashMessage('Error al eliminar la categoría', 'error');
            }

            // Return JSON if AJAX request
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                echo json_encode(['success' => true, 'message' => 'Categoría eliminada exitosamente']);
                exit();
            }
        }

        header('Location: ' . BASE_URL . 'public/index.php?page=manage-categories');
        exit();
    }

    /**
     * Get categories by type (AJAX)
     */
    public function getCategoriesByType() {
        requireLogin();

        $user_id = $_SESSION['user_id'];
        $type = sanitize($_GET['type'] ?? 'expense');

        $categories = $this->category->getCategoriesByUser($user_id, $type);

        header('Content-Type: application/json');
        echo json_encode($categories);
        exit();
    }
}

