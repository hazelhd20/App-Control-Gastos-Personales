<?php
/**
 * Authentication Controller
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

class AuthController {
    private $db;
    private $user;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->user = new User($this->db);
    }

    /**
     * Register new user
     */
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = [];

            // Validate inputs
            $full_name = sanitize($_POST['full_name'] ?? '');
            $email = sanitize($_POST['email'] ?? '');
            $phone = sanitize($_POST['phone'] ?? '');
            $occupation = sanitize($_POST['occupation'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';

            if (empty($full_name)) {
                $errors[] = "El nombre completo es obligatorio";
            }

            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "El correo electrónico no es válido";
            } elseif ($this->user->emailExists($email)) {
                $errors[] = "El correo electrónico ya está registrado";
            }

            if (empty($phone)) {
                $errors[] = "El teléfono es obligatorio";
            }

            if (empty($occupation)) {
                $errors[] = "La ocupación es obligatoria";
            }

            if (empty($password)) {
                $errors[] = "La contraseña es obligatoria";
            } elseif (strlen($password) < 8) {
                $errors[] = "La contraseña debe tener al menos 8 caracteres";
            } elseif (!preg_match('/[A-Z]/', $password)) {
                $errors[] = "La contraseña debe contener al menos una letra mayúscula";
            } elseif (!preg_match('/[0-9]/', $password)) {
                $errors[] = "La contraseña debe contener al menos un número";
            } elseif (!preg_match('/[^A-Za-z0-9]/', $password)) {
                $errors[] = "La contraseña debe contener al menos un carácter especial";
            }

            if ($password !== $confirm_password) {
                $errors[] = "Las contraseñas no coinciden";
            }

            if (empty($errors)) {
                $this->user->full_name = $full_name;
                $this->user->email = $email;
                $this->user->phone = $phone;
                $this->user->occupation = $occupation;
                $this->user->password = $password;

                if ($this->user->register()) {
                    // Auto login after registration
                    $_SESSION['user_id'] = $this->user->id;
                    $_SESSION['user_email'] = $email;
                    $_SESSION['user_name'] = $full_name;

                    setFlashMessage('Registro exitoso. Bienvenido a Control de Gastos!', 'success');
                    header('Location: ' . BASE_URL . 'public/index.php?page=initial-setup');
                    exit();
                } else {
                    $errors[] = "Error al registrar usuario. Intente nuevamente.";
                }
            }

            $_SESSION['register_errors'] = $errors;
            $_SESSION['register_data'] = $_POST;
            header('Location: ' . BASE_URL . 'public/index.php?page=register');
            exit();
        }
    }

    /**
     * Login user
     */
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = sanitize($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                setFlashMessage('Por favor ingrese correo y contraseña', 'error');
                header('Location: ' . BASE_URL . 'public/index.php?page=login');
                exit();
            }

            $user = $this->user->login($email, $password);

            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $user['full_name'];

                // Check if initial setup is complete
                $profile = new FinancialProfile($this->db);
                if (!$profile->isSetupComplete($user['id'])) {
                    header('Location: ' . BASE_URL . 'public/index.php?page=initial-setup');
                } else {
                    header('Location: ' . BASE_URL . 'public/index.php?page=dashboard');
                }
                exit();
            } else {
                setFlashMessage('Correo o contraseña incorrectos', 'error');
                header('Location: ' . BASE_URL . 'public/index.php?page=login');
                exit();
            }
        }
    }

    /**
     * Logout user
     */
    public function logout() {
        session_unset();
        session_destroy();
        header('Location: ' . BASE_URL . 'public/index.php?page=login');
        exit();
    }

    /**
     * Send password reset email
     */
    public function forgotPassword() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = sanitize($_POST['email'] ?? '');

            if (empty($email)) {
                setFlashMessage('Por favor ingrese su correo electrónico', 'error');
                header('Location: ' . BASE_URL . 'public/index.php?page=forgot-password');
                exit();
            }

            $token = $this->user->generateResetToken($email);

            if ($token) {
                // In a real application, send email with reset link
                $reset_link = BASE_URL . 'public/index.php?page=reset-password&token=' . $token;
                
                // For development, just show the link
                $_SESSION['reset_link'] = $reset_link;
                
                setFlashMessage('Se ha enviado un enlace de recuperación a su correo electrónico (válido por 5 minutos)', 'success');
                header('Location: ' . BASE_URL . 'public/index.php?page=forgot-password');
                exit();
            } else {
                setFlashMessage('No se encontró una cuenta con ese correo electrónico', 'error');
                header('Location: ' . BASE_URL . 'public/index.php?page=forgot-password');
                exit();
            }
        }
    }

    /**
     * Reset password
     */
    public function resetPassword() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = sanitize($_POST['token'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';

            $errors = [];

            if (empty($password)) {
                $errors[] = "La contraseña es obligatoria";
            } elseif (strlen($password) < 8) {
                $errors[] = "La contraseña debe tener al menos 8 caracteres";
            } elseif (!preg_match('/[A-Z]/', $password)) {
                $errors[] = "La contraseña debe contener al menos una letra mayúscula";
            } elseif (!preg_match('/[0-9]/', $password)) {
                $errors[] = "La contraseña debe contener al menos un número";
            } elseif (!preg_match('/[^A-Za-z0-9]/', $password)) {
                $errors[] = "La contraseña debe contener al menos un carácter especial";
            }

            if ($password !== $confirm_password) {
                $errors[] = "Las contraseñas no coinciden";
            }

            if (empty($errors)) {
                if ($this->user->resetPassword($token, $password)) {
                    setFlashMessage('Contraseña restablecida exitosamente. Ya puede iniciar sesión.', 'success');
                    header('Location: ' . BASE_URL . 'public/index.php?page=login');
                    exit();
                } else {
                    $errors[] = "El enlace de recuperación ha expirado o no es válido";
                }
            }

            $_SESSION['reset_errors'] = $errors;
            header('Location: ' . BASE_URL . 'public/index.php?page=reset-password&token=' . $token);
            exit();
        }
    }
}

