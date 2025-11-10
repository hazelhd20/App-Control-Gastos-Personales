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

            // Validate name
            if (empty($name)) {
                $errors[] = "El nombre de la categoría es obligatorio";
            } else {
                $name = trim($name);
                if (strlen($name) < 2) {
                    $errors[] = "El nombre de la categoría debe tener al menos 2 caracteres";
                } elseif (strlen($name) > 100) {
                    $errors[] = "El nombre de la categoría es demasiado largo (máximo 100 caracteres)";
                } elseif (!preg_match('/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s\-_]+$/u', $name)) {
                    $errors[] = "El nombre de la categoría contiene caracteres no permitidos. Solo se permiten letras, números, espacios, guiones y guiones bajos";
                }
            }

            // Validate type
            $valid_types = ['expense', 'income'];
            if (!in_array($type, $valid_types)) {
                $errors[] = "El tipo de categoría no es válido";
                $type = 'expense'; // Default to expense if invalid
            }

            // Validate icon
            if (empty($icon)) {
                $errors[] = "Debe seleccionar un icono";
            } else {
                // Validate that icon is in the allowed list for this type
                $allowed_icons = Category::getIconsByType($type);
                if (!in_array($icon, $allowed_icons)) {
                    $errors[] = "El icono seleccionado no es válido para este tipo de categoría";
                }
            }

            // Validate color
            if (empty($color)) {
                $errors[] = "Debe seleccionar un color";
            } else {
                // Validate color format (hexadecimal)
                if (!preg_match('/^#[a-fA-F0-9]{6}$/', $color)) {
                    $errors[] = "El formato del color no es válido (debe ser hexadecimal, ej: #FF6B6B)";
                } else {
                    // Validate that color is in the allowed list
                    $allowed_colors = Category::getColors();
                    if (!in_array($color, $allowed_colors)) {
                        $errors[] = "El color seleccionado no está disponible";
                    }
                }
            }

            // Check if category already exists (only if name is valid)
            if (empty($errors) && !empty($name) && !empty($type)) {
                // Normalize name (trim and check again)
                $name = trim($name);
                if ($this->category->exists($name, $type, $user_id)) {
                    $errors[] = "Ya existe una categoría con el nombre '{$name}' para este tipo. Por favor elige otro nombre.";
                }
            }

            if (empty($errors)) {
                $this->category->user_id = $user_id;
                $this->category->name = trim($name);
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
                $_SESSION['category_data'] = [
                    'name' => $name,
                    'type' => $type,
                    'icon' => $icon,
                    'color' => $color
                ];
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

            // Validate ID
            if ($id <= 0) {
                $errors[] = "ID de categoría no válido";
            } else {
                // Verify that category exists and belongs to user
                $existing_category = $this->category->getById($id, $user_id);
                if (!$existing_category || $existing_category['user_id'] != $user_id) {
                    $errors[] = "La categoría no existe o no tienes permiso para editarla";
                } else {
                    $type = $existing_category['type'];
                }
            }

            // Validate name
            if (empty($name)) {
                $errors[] = "El nombre de la categoría es obligatorio";
            } else {
                $name = trim($name);
                if (strlen($name) < 2) {
                    $errors[] = "El nombre de la categoría debe tener al menos 2 caracteres";
                } elseif (strlen($name) > 100) {
                    $errors[] = "El nombre de la categoría es demasiado largo (máximo 100 caracteres)";
                } elseif (!preg_match('/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s\-_]+$/u', $name)) {
                    $errors[] = "El nombre de la categoría contiene caracteres no permitidos. Solo se permiten letras, números, espacios, guiones y guiones bajos";
                }
            }

            // Validate icon
            if (empty($icon)) {
                $errors[] = "Debe seleccionar un icono";
            } elseif (isset($type)) {
                // Validate that icon is in the allowed list for this type
                $allowed_icons = Category::getIconsByType($type);
                if (!in_array($icon, $allowed_icons)) {
                    $errors[] = "El icono seleccionado no es válido para este tipo de categoría";
                }
            }

            // Validate color
            if (empty($color)) {
                $errors[] = "Debe seleccionar un color";
            } else {
                // Validate color format (hexadecimal)
                if (!preg_match('/^#[a-fA-F0-9]{6}$/', $color)) {
                    $errors[] = "El formato del color no es válido (debe ser hexadecimal, ej: #FF6B6B)";
                } else {
                    // Validate that color is in the allowed list
                    $allowed_colors = Category::getColors();
                    if (!in_array($color, $allowed_colors)) {
                        $errors[] = "El color seleccionado no está disponible";
                    }
                }
            }

            // Check if category name already exists (only if name changed and is valid)
            if (empty($errors) && isset($existing_category)) {
                $name = trim($name);
                if ($existing_category['name'] !== $name) {
                    if ($this->category->exists($name, $type, $user_id)) {
                        $errors[] = "Ya existe una categoría con el nombre '{$name}' para este tipo. Por favor elige otro nombre.";
                    }
                }
            }

            if (empty($errors)) {
                $this->category->id = $id;
                $this->category->user_id = $user_id;
                $this->category->name = trim($name);
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

