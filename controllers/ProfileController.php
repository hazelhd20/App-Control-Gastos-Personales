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
     * Get financial limits based on currency
     * Returns array with min and max values for income, savings, debt, etc.
     */
    private function getFinancialLimits($currency) {
        $limits = [
            'MXN' => [
                'min_income' => 1000,        // Salario mínimo mensual aproximado
                'max_income' => 10000000,    // 10 millones MXN (muy alto pero posible)
                'min_amount' => 1,           // Mínimo para cualquier monto
                'max_amount' => 50000000,    // 50 millones MXN (para deudas grandes, ahorros importantes)
                'min_savings_goal' => 100,   // Mínimo para meta de ahorro
            ],
            'USD' => [
                'min_income' => 500,         // $500 USD mensual (mínimo razonable)
                'max_income' => 1000000,     // 1 millón USD mensual
                'min_amount' => 1,
                'max_amount' => 5000000,     // 5 millones USD
                'min_savings_goal' => 50,    // $50 USD mínimo
            ],
            'EUR' => [
                'min_income' => 500,         // €500 EUR mensual
                'max_income' => 1000000,     // 1 millón EUR mensual
                'min_amount' => 1,
                'max_amount' => 5000000,     // 5 millones EUR
                'min_savings_goal' => 50,    // €50 EUR mínimo
            ]
        ];

        return $limits[$currency] ?? $limits['MXN'];
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
            $debt_deadline = sanitize($_POST['debt_deadline'] ?? '');
            $monthly_payment = floatval($_POST['monthly_payment'] ?? 0);
            $debt_count = intval($_POST['debt_count'] ?? 0);
            $spending_limit_type = $_POST['spending_limit_type'] ?? 'auto';
            $spending_limit = floatval($_POST['spending_limit'] ?? 0);
            
            // Validations
            $errors = [];
            
            // Validate currency
            $valid_currencies = ['MXN', 'USD', 'EUR'];
            if (!in_array($currency, $valid_currencies)) {
                $errors[] = "La moneda seleccionada no es válida";
            }
            
            // Get financial limits based on currency
            $limits = $this->getFinancialLimits($currency);
            
            // Validate monthly income with realistic limits
            if ($monthly_income <= 0) {
                $errors[] = "El ingreso mensual debe ser mayor a 0";
            } elseif ($monthly_income < $limits['min_income']) {
                $errors[] = sprintf(
                    "El ingreso mensual es muy bajo. El mínimo recomendado es %s %s",
                    number_format($limits['min_income'], 2, '.', ','),
                    $currency
                );
            } elseif ($monthly_income > $limits['max_income']) {
                $errors[] = sprintf(
                    "El ingreso mensual es demasiado alto. El máximo permitido es %s %s",
                    number_format($limits['max_income'], 2, '.', ','),
                    $currency
                );
            }

            if (empty($start_date)) {
                $errors[] = "La fecha de inicio es obligatoria";
            } else {
                // Validate date format
                $date_parts = explode('-', $start_date);
                if (count($date_parts) !== 3 || !checkdate($date_parts[1], $date_parts[2], $date_parts[0])) {
                    $errors[] = "La fecha de inicio no es válida";
                } else {
                    // Validate that start date is not more than 1 week in the past
                    $start_datetime = new DateTime($start_date);
                    $today = new DateTime();
                    $today->setTime(23, 59, 59);
                    $oneWeekAgo = clone $today;
                    $oneWeekAgo->modify('-7 days');
                    $oneWeekAgo->setTime(0, 0, 0);
                    $start_datetime->setTime(0, 0, 0);
                    
                    if ($start_datetime < $oneWeekAgo) {
                        $errors[] = "La fecha de inicio no puede ser más antigua que una semana";
                    }
                    // Validate that start date is not in the future
                    if ($start_datetime > $today) {
                        $errors[] = "La fecha de inicio no puede ser futura";
                    }
                }
            }

            if (empty($payment_methods) || !is_array($payment_methods)) {
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
                    $debt_amount,
                    $debt_deadline,
                    $monthly_payment
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
                    // In initial setup, savings goal is required
                    if ($savings_goal <= 0) {
                        $errors[] = "Debe ingresar una meta de ahorro mayor a 0";
                    } elseif ($savings_goal < $limits['min_savings_goal']) {
                        $errors[] = sprintf(
                            "La meta de ahorro es muy baja. El mínimo recomendado es %s %s",
                            number_format($limits['min_savings_goal'], 2, '.', ','),
                            $currency
                        );
                    } elseif ($savings_goal > $limits['max_amount']) {
                        $errors[] = sprintf(
                            "La meta de ahorro es demasiado alta. El máximo permitido es %s %s",
                            number_format($limits['max_amount'], 2, '.', ','),
                            $currency
                        );
                    }
                    if (!empty($savings_deadline)) {
                        // Validate date format
                        $date_parts = explode('-', $savings_deadline);
                        if (count($date_parts) !== 3 || !checkdate($date_parts[1], $date_parts[2], $date_parts[0])) {
                            $errors[] = "La fecha límite de ahorro no es válida";
                        } else {
                            $deadline_date = new DateTime($savings_deadline);
                            $today = new DateTime();
                            $today->setTime(0, 0, 0);
                            $deadline_date->setTime(0, 0, 0);
                            if ($deadline_date <= $today) {
                                $errors[] = "La fecha límite de ahorro debe ser una fecha futura";
                            } else {
                                // Allow up to 30 years for long-term goals (like buying a house)
                                $max_deadline = clone $today;
                                $max_deadline->modify('+30 years');
                                if ($deadline_date > $max_deadline) {
                                    $errors[] = "La fecha límite no puede ser más de 30 años en el futuro";
                                }
                            }
                        }
                    }
                } elseif ($financial_goal === 'pagar_deudas') {
                    // In initial setup, debt_amount is required when selecting this goal
                    if ($debt_amount <= 0) {
                        $errors[] = "Debe ingresar un monto de deuda mayor a 0 cuando selecciona 'Pagar Deudas'";
                    } else {
                        if ($debt_amount < $limits['min_amount']) {
                            $errors[] = sprintf(
                                "El monto de la deuda debe ser mayor a %s %s",
                                number_format($limits['min_amount'], 2, '.', ','),
                                $currency
                            );
                        } elseif ($debt_amount > $limits['max_amount']) {
                            $errors[] = sprintf(
                                "El monto de la deuda es demasiado alto. El máximo permitido es %s %s",
                                number_format($limits['max_amount'], 2, '.', ','),
                                $currency
                            );
                        }
                        
                        // Validate debt-to-income ratio (annual)
                        $annual_income = $monthly_income * 12;
                        $debt_to_income_ratio = $debt_amount / $annual_income;
                        
                        // Warning threshold: if debt is more than 10x annual income, it's very high
                        if ($debt_to_income_ratio > 10) {
                            $errors[] = "La deuda es extremadamente alta comparada con tu ingreso anual. Por favor verifica los datos ingresados.";
                        }
                    }
                    
                    // More realistic debt count: typically people have 1-20 debts
                    if ($debt_count > 0 && ($debt_count < 1 || $debt_count > 50)) {
                        $errors[] = "El número de deudas debe estar entre 1 y 50";
                    }
                    
                    if ($monthly_payment > 0) {
                        if ($monthly_payment > $limits['max_amount']) {
                            $errors[] = sprintf(
                                "El pago mensual es demasiado alto. El máximo permitido es %s %s",
                                number_format($limits['max_amount'], 2, '.', ','),
                                $currency
                            );
                        }
                        // Validate that monthly payment doesn't exceed available income
                        if ($monthly_payment > $monthly_income * 0.95) {
                            $errors[] = "El pago mensual no puede exceder el 95% de tu ingreso mensual. Debes dejar algo para gastos básicos.";
                        }
                    }
                    
                    if (!empty($debt_deadline)) {
                        // Validate date format
                        $date_parts = explode('-', $debt_deadline);
                        if (count($date_parts) !== 3 || !checkdate($date_parts[1], $date_parts[2], $date_parts[0])) {
                            $errors[] = "La fecha objetivo para pagar deudas no es válida";
                        } else {
                            $deadline_date = new DateTime($debt_deadline);
                            $today = new DateTime();
                            $today->setTime(0, 0, 0);
                            $deadline_date->setTime(0, 0, 0);
                            if ($deadline_date <= $today) {
                                $errors[] = "La fecha objetivo para pagar deudas debe ser una fecha futura";
                            } else {
                                // Allow up to 15 years for debt payment (mortgages can be longer)
                                $max_deadline = clone $today;
                                $max_deadline->modify('+15 years');
                                if ($deadline_date > $max_deadline) {
                                    $errors[] = "La fecha objetivo no puede ser más de 15 años en el futuro para pagar deudas";
                                }
                            }
                        }
                    }
                } elseif ($financial_goal === 'controlar_gastos') {
                    // No additional fields required for 'controlar_gastos'
                    // The spending limit will be calculated automatically
                } elseif ($financial_goal === 'otro') {
                    // In initial setup, goal_description is required when selecting 'otro'
                    if (empty(trim($goal_description))) {
                        $errors[] = "Debe describir su objetivo financiero cuando selecciona 'Otro'";
                    } elseif (strlen(trim($goal_description)) < 10) {
                        $errors[] = "La descripción del objetivo debe tener al menos 10 caracteres";
                    } elseif (strlen($goal_description) > 500) {
                        $errors[] = "La descripción del objetivo es demasiado larga (máximo 500 caracteres)";
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
                    $savings_deadline,
                    $debt_deadline,
                    $monthly_payment
                );
            } else {
                // Validate manual spending limit
                if ($spending_limit <= 0) {
                    $errors[] = "El límite de gasto manual debe ser mayor a 0";
                } elseif ($spending_limit > $limits['max_amount']) {
                    $errors[] = sprintf(
                        "El límite de gasto es demasiado alto. El máximo permitido es %s %s",
                        number_format($limits['max_amount'], 2, '.', ','),
                        $currency
                    );
                } else {
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
                $this->profile->debt_deadline = !empty($debt_deadline) ? $debt_deadline : null;
                $this->profile->monthly_payment = $monthly_payment > 0 ? $monthly_payment : null;
                $this->profile->debt_count = $debt_count > 0 ? $debt_count : null;
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

            // Validate full name
            if (empty($full_name)) {
                $errors[] = "El nombre completo es obligatorio";
            } else {
                $full_name = trim($full_name);
                if (strlen($full_name) < 2) {
                    $errors[] = "El nombre completo debe tener al menos 2 caracteres";
                } elseif (strlen($full_name) > 255) {
                    $errors[] = "El nombre completo es demasiado largo (máximo 255 caracteres)";
                }
            }

            // Validate email
            if (empty($email)) {
                $errors[] = "El correo electrónico es obligatorio";
            } else {
                $email = trim($email);
                if (strlen($email) > 255) {
                    $errors[] = "El correo electrónico es demasiado largo (máximo 255 caracteres)";
                } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $errors[] = "El formato del correo electrónico no es válido";
                } else {
                    // Check if email changed
                    $current_user = $this->user->getById($user_id);
                    if ($email !== $current_user['email']) {
                        if ($this->user->emailExists($email)) {
                            $errors[] = "El correo electrónico ya está en uso";
                        }
                    }
                }
            }

            // Validate phone
            if (empty($phone)) {
                $errors[] = "El teléfono es obligatorio";
            } else {
                // Basic phone validation - remove non-digit characters for validation
                $phone_clean = preg_replace('/[^0-9+()-]/', '', $phone);
                $phone_digits = preg_replace('/[^0-9]/', '', $phone);
                if (strlen($phone_digits) < 7) {
                    $errors[] = "El teléfono debe tener al menos 7 dígitos";
                } elseif (strlen($phone_digits) > 15) {
                    $errors[] = "El teléfono no puede tener más de 15 dígitos";
                }
            }

            // Validate occupation
            if (empty($occupation)) {
                $errors[] = "La ocupación es obligatoria";
            } else {
                $occupation = trim($occupation);
                if (strlen($occupation) < 2) {
                    $errors[] = "La ocupación debe tener al menos 2 caracteres";
                } elseif (strlen($occupation) > 100) {
                    $errors[] = "La ocupación es demasiado larga (máximo 100 caracteres)";
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
            $debt_deadline = sanitize($_POST['debt_deadline'] ?? '');
            $monthly_payment = floatval($_POST['monthly_payment'] ?? 0);
            $debt_count = intval($_POST['debt_count'] ?? 0);
            $spending_limit = floatval($_POST['spending_limit'] ?? 0);

            // Validate currency
            $valid_currencies = ['MXN', 'USD', 'EUR'];
            if (!in_array($currency, $valid_currencies)) {
                $errors[] = "La moneda seleccionada no es válida";
            }

            // Get financial limits based on currency
            $limits = $this->getFinancialLimits($currency);
            
            // Validate monthly income with realistic limits
            if ($monthly_income <= 0) {
                $errors[] = "El ingreso mensual debe ser mayor a 0";
            } elseif ($monthly_income < $limits['min_income']) {
                $errors[] = sprintf(
                    "El ingreso mensual es muy bajo. El mínimo recomendado es %s %s",
                    number_format($limits['min_income'], 2, '.', ','),
                    $currency
                );
            } elseif ($monthly_income > $limits['max_income']) {
                $errors[] = sprintf(
                    "El ingreso mensual es demasiado alto. El máximo permitido es %s %s",
                    number_format($limits['max_income'], 2, '.', ','),
                    $currency
                );
            }

            // Validate spending limit
            if ($spending_limit <= 0) {
                $errors[] = "El límite de gasto debe ser mayor a 0";
            } elseif ($spending_limit > $limits['max_amount']) {
                $errors[] = sprintf(
                    "El límite de gasto es demasiado alto. El máximo permitido es %s %s",
                    number_format($limits['max_amount'], 2, '.', ','),
                    $currency
                );
            }

            // Validate payment methods
            if (empty($payment_methods) || !is_array($payment_methods)) {
                $errors[] = "Debe seleccionar al menos un medio de pago";
            }

            // Validate financial goal
            if (empty($financial_goal)) {
                $errors[] = "Debe seleccionar un objetivo financiero";
            } else {
                $valid_goals = ['ahorrar', 'pagar_deudas', 'controlar_gastos', 'otro'];
                if (!in_array($financial_goal, $valid_goals)) {
                    $errors[] = "El objetivo financiero seleccionado no es válido";
                }
            }

            // Validate financial goal feasibility
            if (!empty($financial_goal) && empty($errors)) {
                $goal_validation = $this->profile->validateGoalFeasibility(
                    $monthly_income,
                    $financial_goal,
                    $savings_goal,
                    $savings_deadline,
                    $debt_amount,
                    $debt_deadline,
                    $monthly_payment
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
                    // Get current profile to check if user has existing savings goal
                    $current_profile = $this->profile->getByUserId($user_id);
                    
                    // If savings_goal is not provided or is 0, check if user has existing one
                    if ($savings_goal <= 0) {
                        if ($current_profile && !empty($current_profile['savings_goal']) && $current_profile['savings_goal'] > 0) {
                            // Use existing savings goal from profile if not provided in form
                            $savings_goal = $current_profile['savings_goal'];
                        } else {
                            // If no existing savings goal and none provided, require it
                            $errors[] = "Debe ingresar una meta de ahorro mayor a 0 cuando selecciona 'Ahorrar'";
                        }
                    }
                    
                    // Validate savings_goal if it exists (either from form or profile)
                    if ($savings_goal > 0) {
                        if ($savings_goal < $limits['min_savings_goal']) {
                            $errors[] = sprintf(
                                "La meta de ahorro es muy baja. El mínimo recomendado es %s %s",
                                number_format($limits['min_savings_goal'], 2, '.', ','),
                                $currency
                            );
                        } elseif ($savings_goal > $limits['max_amount']) {
                            $errors[] = sprintf(
                                "La meta de ahorro es demasiado alta. El máximo permitido es %s %s",
                                number_format($limits['max_amount'], 2, '.', ','),
                                $currency
                            );
                        }
                        if (!empty($savings_deadline)) {
                            // Validate date format
                            $date_parts = explode('-', $savings_deadline);
                            if (count($date_parts) !== 3 || !checkdate($date_parts[1], $date_parts[2], $date_parts[0])) {
                                $errors[] = "La fecha límite de ahorro no es válida";
                            } else {
                                $deadline_date = new DateTime($savings_deadline);
                                $today = new DateTime();
                                $today->setTime(0, 0, 0);
                                $deadline_date->setTime(0, 0, 0);
                                if ($deadline_date <= $today) {
                                    $errors[] = "La fecha límite de ahorro debe ser una fecha futura";
                                } else {
                                    // Allow up to 30 years for long-term goals
                                    $max_deadline = clone $today;
                                    $max_deadline->modify('+30 years');
                                    if ($deadline_date > $max_deadline) {
                                        $errors[] = "La fecha límite no puede ser más de 30 años en el futuro";
                                    }
                                }
                            }
                        }
                    }
                } elseif ($financial_goal === 'pagar_deudas') {
                    // In update profile, if debt_amount is provided, validate it
                    // But it's optional when updating (user might want to clear debt info)
                    if ($debt_amount > 0) {
                        if ($debt_amount < $limits['min_amount']) {
                            $errors[] = sprintf(
                                "El monto de la deuda debe ser mayor a %s %s",
                                number_format($limits['min_amount'], 2, '.', ','),
                                $currency
                            );
                        } elseif ($debt_amount > $limits['max_amount']) {
                            $errors[] = sprintf(
                                "El monto de la deuda es demasiado alto. El máximo permitido es %s %s",
                                number_format($limits['max_amount'], 2, '.', ','),
                                $currency
                            );
                        }
                        
                        // Validate debt-to-income ratio (annual)
                        $annual_income = $monthly_income * 12;
                        $debt_to_income_ratio = $debt_amount / $annual_income;
                        
                        if ($debt_to_income_ratio > 10) {
                            $errors[] = "La deuda es extremadamente alta comparada con tu ingreso anual. Por favor verifica los datos ingresados.";
                        }
                    }
                    
                    // More realistic debt count: typically people have 1-20 debts
                    if ($debt_count > 0 && ($debt_count < 1 || $debt_count > 50)) {
                        $errors[] = "El número de deudas debe estar entre 1 y 50";
                    }
                    
                    if ($monthly_payment > 0) {
                        if ($monthly_payment > $limits['max_amount']) {
                            $errors[] = sprintf(
                                "El pago mensual es demasiado alto. El máximo permitido es %s %s",
                                number_format($limits['max_amount'], 2, '.', ','),
                                $currency
                            );
                        }
                        // Validate that monthly payment doesn't exceed available income
                        if ($monthly_payment > $monthly_income * 0.95) {
                            $errors[] = "El pago mensual no puede exceder el 95% de tu ingreso mensual. Debes dejar algo para gastos básicos.";
                        }
                    }
                    
                    if (!empty($debt_deadline)) {
                        // Validate date format
                        $date_parts = explode('-', $debt_deadline);
                        if (count($date_parts) !== 3 || !checkdate($date_parts[1], $date_parts[2], $date_parts[0])) {
                            $errors[] = "La fecha objetivo para pagar deudas no es válida";
                        } else {
                            $deadline_date = new DateTime($debt_deadline);
                            $today = new DateTime();
                            $today->setTime(0, 0, 0);
                            $deadline_date->setTime(0, 0, 0);
                            if ($deadline_date <= $today) {
                                $errors[] = "La fecha objetivo para pagar deudas debe ser una fecha futura";
                            } else {
                                // Allow up to 15 years for debt payment
                                $max_deadline = clone $today;
                                $max_deadline->modify('+15 years');
                                if ($deadline_date > $max_deadline) {
                                    $errors[] = "La fecha objetivo no puede ser más de 15 años en el futuro para pagar deudas";
                                }
                            }
                        }
                    }
                } elseif ($financial_goal === 'controlar_gastos') {
                    // No additional fields required for 'controlar_gastos'
                    // The spending limit will be calculated automatically
                } elseif ($financial_goal === 'otro') {
                    // In update profile, goal_description is required when selecting 'otro'
                    if (empty(trim($goal_description))) {
                        $errors[] = "Debe describir su objetivo financiero cuando selecciona 'Otro'";
                    } elseif (strlen(trim($goal_description)) < 10) {
                        $errors[] = "La descripción del objetivo debe tener al menos 10 caracteres";
                    } elseif (strlen($goal_description) > 500) {
                        $errors[] = "La descripción del objetivo es demasiado larga (máximo 500 caracteres)";
                    }
                }
            }

            // Validate spending limit
            if (empty($errors) && $spending_limit > 0) {
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

            // Update user info if no errors
            if (empty($errors)) {
                $this->user->id = $user_id;
                $this->user->full_name = trim($full_name);
                $this->user->phone = $phone;
                $this->user->occupation = trim($occupation);
                $this->user->email = trim($email);

                if ($this->user->update()) {
                    $_SESSION['user_name'] = trim($full_name);
                    $_SESSION['user_email'] = trim($email);
                } else {
                    $errors[] = "Error al actualizar la información personal. Intente nuevamente.";
                }
            }

            // Update financial profile if no errors
            if (empty($errors) && $monthly_income > 0 && $spending_limit > 0 && !empty($payment_methods)) {
                $this->profile->user_id = $user_id;
                $this->profile->monthly_income = $monthly_income;
                $this->profile->currency = $currency;
                $this->profile->payment_methods = json_encode($payment_methods);
                $this->profile->financial_goal = $financial_goal;
                $this->profile->goal_description = trim($goal_description);
                $this->profile->savings_goal = $savings_goal > 0 ? $savings_goal : null;
                $this->profile->savings_deadline = !empty($savings_deadline) ? $savings_deadline : null;
                $this->profile->debt_amount = $debt_amount > 0 ? $debt_amount : null;
                $this->profile->debt_deadline = !empty($debt_deadline) ? $debt_deadline : null;
                $this->profile->monthly_payment = $monthly_payment > 0 ? $monthly_payment : null;
                $this->profile->debt_count = $debt_count > 0 ? $debt_count : null;
                $this->profile->spending_limit = $spending_limit;

                if (!$this->profile->update()) {
                    $errors[] = "Error al actualizar el perfil financiero. Intente nuevamente.";
                }
            }

            if (empty($errors)) {
                setFlashMessage('Perfil actualizado exitosamente', 'success');
            } else {
                $_SESSION['profile_errors'] = $errors;
                $_SESSION['profile_data'] = $_POST; // Save form data for restoration
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

