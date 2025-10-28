<?php
/**
 * Authentication Controller
 */

require_once __DIR__ . '/../config/config.php';

class AuthController {
    private $db;
    private $user;
    private $emailService;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->user = new User($this->db);
        
        // Only initialize email service if PHPMailer is available
        if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            $this->emailService = new EmailService();
        }
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
                    // Send verification email
                    if ($this->emailService) {
                        $this->emailService->sendVerificationEmail(
                            $email, 
                            $full_name, 
                            $this->user->verification_token
                        );
                    }
                    
                    // Store email in session for verification page
                    $_SESSION['pending_verification_email'] = $email;
                    
                    setFlashMessage('Registro exitoso. Por favor verifica tu correo electrónico para activar tu cuenta.', 'success');
                    header('Location: ' . BASE_URL . 'public/index.php?page=verify-email');
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
                // Check if email verification is required
                if (isset($user['error']) && $user['error'] === 'email_not_verified') {
                    $_SESSION['pending_verification_email'] = $user['email'];
                    setFlashMessage('Debes verificar tu correo electrónico antes de iniciar sesión.', 'warning');
                    header('Location: ' . BASE_URL . 'public/index.php?page=verify-email');
                    exit();
                }
                
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
                // Get user info
                $user = $this->user->getUserByEmail($email);
                
                // Send password reset email
                if ($this->emailService && $user) {
                    $this->emailService->sendPasswordResetEmail(
                        $email, 
                        $user['full_name'], 
                        $token
                    );
                }
                
                setFlashMessage('Se ha enviado un enlace de recuperación a tu correo electrónico (válido por 5 minutos)', 'success');
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
                $result = $this->user->resetPassword($token, $password);
                
                if ($result === true) {
                    setFlashMessage('Contraseña restablecida exitosamente. Ya puede iniciar sesión.', 'success');
                    header('Location: ' . BASE_URL . 'public/index.php?page=login');
                    exit();
                } elseif ($result === 'same_password') {
                    $errors[] = "La nueva contraseña debe ser diferente a la contraseña anterior";
                } else {
                    $errors[] = "El enlace de recuperación ha expirado o no es válido";
                }
            }

            $_SESSION['reset_errors'] = $errors;
            header('Location: ' . BASE_URL . 'public/index.php?page=reset-password&token=' . $token);
            exit();
        }
    }

    /**
     * Verify email address
     */
    public function verifyEmail() {
        if (isset($_GET['token'])) {
            $token = sanitize($_GET['token']);
            
            $user = $this->user->verifyEmail($token);
            
            if ($user) {
                // Auto login after verification
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $user['full_name'];
                
                unset($_SESSION['pending_verification_email']);
                
                setFlashMessage('¡Correo verificado exitosamente! Bienvenido a Control de Gastos.', 'success');
                header('Location: ' . BASE_URL . 'public/index.php?page=initial-setup');
                exit();
            } else {
                setFlashMessage('El enlace de verificación ha expirado o no es válido.', 'error');
                header('Location: ' . BASE_URL . 'public/index.php?page=verify-email');
                exit();
            }
        }
    }

    /**
     * Resend verification email
     */
    public function resendVerification() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = sanitize($_POST['email'] ?? '');
            
            if (empty($email)) {
                setFlashMessage('Por favor ingrese su correo electrónico', 'error');
                header('Location: ' . BASE_URL . 'public/index.php?page=verify-email');
                exit();
            }
            
            $token = $this->user->resendVerificationToken($email);
            
            if ($token) {
                $user = $this->user->getUserByEmail($email);
                
                // Send verification email
                if ($this->emailService && $user) {
                    $this->emailService->sendVerificationEmail(
                        $email, 
                        $user['full_name'], 
                        $token
                    );
                }
                
                setFlashMessage('Se ha reenviado el correo de verificación.', 'success');
            } else {
                setFlashMessage('No se pudo reenviar el correo. Verifica que tu cuenta no esté ya verificada.', 'error');
            }
            
            header('Location: ' . BASE_URL . 'public/index.php?page=verify-email');
            exit();
        }
    }
}

