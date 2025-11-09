<?php
/**
 * Profile Controller
 */

require_once __DIR__ . '/../config/config.php';

class ProfileController {
    private $db;
    private $profile;
    private $user;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->profile = new FinancialProfile($this->db);
        $this->user = new User($this->db);
    }

    /**
     * Initial financial setup
     */
    public function initialSetup() {
        requireLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = [];
            $user_id = $_SESSION['user_id'];

            // Check if setup already complete
            if ($this->profile->isSetupComplete($user_id)) {
                header('Location: ' . BASE_URL . 'public/index.php?page=dashboard');
                exit();
            }

            // Validate inputs
            $monthly_income = floatval($_POST['monthly_income'] ?? 0);
            $currency = sanitize($_POST['currency'] ?? 'MXN');
            $start_date = sanitize($_POST['start_date'] ?? '');
            $payment_methods = $_POST['payment_methods'] ?? [];
            $financial_goal = sanitize($_POST['financial_goal'] ?? '');
            $goal_description = sanitize($_POST['goal_description'] ?? '');
            $savings_goal = floatval($_POST['savings_goal'] ?? 0);
            $savings_deadline = sanitize($_POST['savings_deadline'] ?? '');
            $debt_amount = floatval($_POST['debt_amount'] ?? 0);
            $spending_limit_type = $_POST['spending_limit_type'] ?? 'auto';
            $spending_limit = floatval($_POST['spending_limit'] ?? 0);

            // Validations
            if ($monthly_income <= 0) {
                $errors[] = "El ingreso mensual debe ser mayor a 0";
            }

            if (empty($start_date)) {
                $errors[] = "La fecha de inicio es obligatoria";
            }

            if (empty($payment_methods)) {
                $errors[] = "Debe seleccionar al menos un medio de pago";
            }

            if (empty($financial_goal)) {
                $errors[] = "Debe seleccionar un objetivo financiero";
            }

            // Validate financial goal feasibility
            if (!empty($financial_goal)) {
                $goal_validation = $this->profile->validateGoalFeasibility(
                    $monthly_income,
                    $financial_goal,
                    $savings_goal,
                    $savings_deadline,
                    $debt_amount
                );

                if (!$goal_validation['valid']) {
                    $errors[] = $goal_validation['message'];
                }

                // Add warnings as errors (user must acknowledge)
                if (!empty($goal_validation['warnings'])) {
                    foreach ($goal_validation['warnings'] as $warning) {
                        $errors[] = $warning;
                    }
                }

                // Specific validations per goal type
                if ($financial_goal === 'ahorrar') {
                    if ($savings_goal <= 0) {
                        $errors[] = "Debe ingresar una meta de ahorro mayor a 0";
                    }
                    if (!empty($savings_deadline)) {
                        $deadline_date = new DateTime($savings_deadline);
                        $today = new DateTime();
                        if ($deadline_date <= $today) {
                            $errors[] = "La fecha límite de ahorro debe ser una fecha futura";
                        }
                    }
                } elseif ($financial_goal === 'pagar_deudas') {
                    if ($debt_amount <= 0) {
                        $errors[] = "Debe ingresar el monto de la deuda mayor a 0";
                    }
                } elseif ($financial_goal === 'otro') {
                    if (empty(trim($goal_description))) {
                        $errors[] = "Debe describir su objetivo financiero cuando selecciona 'Otro'";
                    }
                }
            }

            // Calculate spending limit if auto
            if ($spending_limit_type === 'auto') {
                $spending_limit = $this->profile->calculateSpendingLimit(
                    $monthly_income,
                    $financial_goal,
                    $savings_goal,
                    $debt_amount,
                    $savings_deadline
                );
            } else {
                // Validate manual spending limit
                $limit_validation = $this->profile->validateSpendingLimit(
                    $spending_limit,
                    $monthly_income,
                    $financial_goal,
                    $savings_goal,
                    $debt_amount
                );

                if (!$limit_validation['valid']) {
                    $errors[] = $limit_validation['message'];
                }

                // Add warnings as errors (user must acknowledge)
                if (!empty($limit_validation['warnings'])) {
                    foreach ($limit_validation['warnings'] as $warning) {
                        $errors[] = $warning;
                    }
                }
            }

            if (empty($errors)) {
                $this->profile->user_id = $user_id;
                $this->profile->monthly_income = $monthly_income;
                $this->profile->currency = $currency;
                $this->profile->start_date = $start_date;
                $this->profile->payment_methods = json_encode($payment_methods);
                $this->profile->financial_goal = $financial_goal;
                $this->profile->goal_description = $goal_description;
                $this->profile->savings_goal = $savings_goal > 0 ? $savings_goal : null;
                $this->profile->savings_deadline = !empty($savings_deadline) ? $savings_deadline : null;
                $this->profile->debt_amount = $debt_amount > 0 ? $debt_amount : null;
                $this->profile->spending_limit = $spending_limit;

                if ($this->profile->create()) {
                    setFlashMessage('Perfil financiero configurado exitosamente!', 'success');
                    header('Location: ' . BASE_URL . 'public/index.php?page=dashboard');
                    exit();
                } else {
                    $errors[] = "Error al guardar el perfil. Intente nuevamente.";
                }
            }

            $_SESSION['setup_errors'] = $errors;
            $_SESSION['setup_data'] = $_POST;
            header('Location: ' . BASE_URL . 'public/index.php?page=initial-setup');
            exit();
        }
    }

    /**
     * Update profile
     */
    public function updateProfile() {
        requireLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = [];
            $user_id = $_SESSION['user_id'];

            // Update user info
            $full_name = sanitize($_POST['full_name'] ?? '');
            $phone = sanitize($_POST['phone'] ?? '');
            $occupation = sanitize($_POST['occupation'] ?? '');
            $email = sanitize($_POST['email'] ?? '');

            if (!empty($full_name) && !empty($phone) && !empty($occupation)) {
                $this->user->id = $user_id;
                $this->user->full_name = $full_name;
                $this->user->phone = $phone;
                $this->user->occupation = $occupation;

                // Check if email changed
                $current_user = $this->user->getById($user_id);
                if ($email !== $current_user['email']) {
                    if ($this->user->emailExists($email)) {
                        $errors[] = "El correo electrónico ya está en uso";
                    } else {
                        $this->user->email = $email;
                    }
                }

                if (empty($errors) && $this->user->update()) {
                    $_SESSION['user_name'] = $full_name;
                    if (!empty($this->user->email)) {
                        $_SESSION['user_email'] = $email;
                    }
                }
            }

            // Update financial profile
            $monthly_income = floatval($_POST['monthly_income'] ?? 0);
            $currency = sanitize($_POST['currency'] ?? 'MXN');
            $payment_methods = $_POST['payment_methods'] ?? [];
            $financial_goal = sanitize($_POST['financial_goal'] ?? '');
            $goal_description = sanitize($_POST['goal_description'] ?? '');
            $savings_goal = floatval($_POST['savings_goal'] ?? 0);
            $savings_deadline = sanitize($_POST['savings_deadline'] ?? '');
            $debt_amount = floatval($_POST['debt_amount'] ?? 0);
            $spending_limit = floatval($_POST['spending_limit'] ?? 0);

            if ($monthly_income > 0 && $spending_limit > 0 && !empty($payment_methods)) {
                $this->profile->user_id = $user_id;
                $this->profile->monthly_income = $monthly_income;
                $this->profile->currency = $currency;
                $this->profile->payment_methods = json_encode($payment_methods);
                $this->profile->financial_goal = $financial_goal;
                $this->profile->goal_description = $goal_description;
                $this->profile->savings_goal = $savings_goal > 0 ? $savings_goal : null;
                $this->profile->savings_deadline = !empty($savings_deadline) ? $savings_deadline : null;
                $this->profile->debt_amount = $debt_amount > 0 ? $debt_amount : null;
                $this->profile->spending_limit = $spending_limit;

                $this->profile->update();
            }

            if (empty($errors)) {
                setFlashMessage('Perfil actualizado exitosamente', 'success');
            } else {
                $_SESSION['profile_errors'] = $errors;
            }

            header('Location: ' . BASE_URL . 'public/index.php?page=profile');
            exit();
        }
    }

    /**
     * Add additional income
     */
    public function addIncome() {
        requireLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user_id = $_SESSION['user_id'];
            $additional_income = floatval($_POST['additional_income'] ?? 0);

            if ($additional_income > 0) {
                $this->profile->updateIncome($user_id, $additional_income);
                setFlashMessage('Ingreso adicional registrado exitosamente', 'success');
            }

            header('Location: ' . BASE_URL . 'public/index.php?page=profile');
            exit();
        }
    }

    /**
     * Change password
     */
    public function changePassword() {
        requireLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user_id = $_SESSION['user_id'];
            $current_password = $_POST['current_password'] ?? '';
            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';

            $errors = [];

            if (empty($current_password)) {
                $errors[] = "Debe ingresar su contraseña actual";
            }

            if (empty($new_password)) {
                $errors[] = "La contraseña nueva es obligatoria";
            } elseif (strlen($new_password) < 8) {
                $errors[] = "La contraseña debe tener al menos 8 caracteres";
            } elseif (!preg_match('/[A-Z]/', $new_password)) {
                $errors[] = "La contraseña debe contener al menos una letra mayúscula";
            } elseif (!preg_match('/[0-9]/', $new_password)) {
                $errors[] = "La contraseña debe contener al menos un número";
            } elseif (!preg_match('/[^A-Za-z0-9]/', $new_password)) {
                $errors[] = "La contraseña debe contener al menos un carácter especial";
            }

            if ($new_password !== $confirm_password) {
                $errors[] = "Las contraseñas no coinciden";
            }

            if (empty($errors)) {
                $result = $this->user->updatePassword($user_id, $new_password, $current_password);

                if ($result === true) {
                    setFlashMessage('Contraseña actualizada exitosamente', 'success');
                } elseif ($result === 'wrong_current_password') {
                    $errors[] = "La contraseña actual es incorrecta";
                } elseif ($result === 'same_password') {
                    $errors[] = "La nueva contraseña debe ser diferente a la contraseña actual";
                } else {
                    $errors[] = "Error al actualizar la contraseña. Intente nuevamente.";
                }
            }

            if (!empty($errors)) {
                $_SESSION['password_errors'] = $errors;
            }

            header('Location: ' . BASE_URL . 'public/index.php?page=profile');
            exit();
        }
    }
}

