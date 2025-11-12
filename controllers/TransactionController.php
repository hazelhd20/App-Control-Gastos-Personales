<?php
/**
 * Transaction Controller
 */

require_once __DIR__ . '/../config/config.php';

class TransactionController {
    private $db;
    private $transaction;
    private $alert;
    private $profile;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->transaction = new Transaction($this->db);
        $this->alert = new Alert($this->db);
        $this->profile = new FinancialProfile($this->db);
        $this->goal_progress_helper = new GoalProgressHelper($this->db);
    }
    
    private $goal_progress_helper;

    /**
     * Get transaction limits based on currency
     * Returns array with min and max values for transactions
     */
    private function getTransactionLimits($currency) {
        $limits = [
            'MXN' => [
                'min_amount' => 0.01,        // Mínimo para cualquier transacción
                'max_single_transaction' => 10000000,  // 10 millones MXN (para compras grandes como auto, casa, etc.)
                'warning_expense_ratio' => 2.0,  // Advertir si gasto > 2x ingreso mensual
                'warning_income_ratio' => 5.0,   // Advertir si ingreso > 5x ingreso mensual (puede ser bono)
            ],
            'USD' => [
                'min_amount' => 0.01,
                'max_single_transaction' => 1000000,   // 1 millón USD
                'warning_expense_ratio' => 2.0,
                'warning_income_ratio' => 5.0,
            ],
            'EUR' => [
                'min_amount' => 0.01,
                'max_single_transaction' => 1000000,   // 1 millón EUR
                'warning_expense_ratio' => 2.0,
                'warning_income_ratio' => 5.0,
            ]
        ];

        return $limits[$currency] ?? $limits['MXN'];
    }

    /**
     * Add new transaction
     */
    public function addTransaction() {
        requireLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = [];
            $user_id = $_SESSION['user_id'];

            $type = sanitize($_POST['type'] ?? 'expense');
            $amount = floatval($_POST['amount'] ?? 0);
            $category = sanitize($_POST['category'] ?? '');
            $payment_method = sanitize($_POST['payment_method'] ?? '');
            $description = sanitize($_POST['description'] ?? '');
            $transaction_date = sanitize($_POST['transaction_date'] ?? date('Y-m-d'));

            // Get user's profile to access currency and monthly income
            $user_profile = $this->profile->getByUserId($user_id);
            $currency = $user_profile['currency'] ?? 'MXN';
            $monthly_income = $user_profile['monthly_income'] ?? 0;

            // Validations
            // Validate transaction type
            $valid_types = ['expense', 'income'];
            if (!in_array($type, $valid_types)) {
                $errors[] = "El tipo de transacción no es válido";
                $type = 'expense'; // Default to expense if invalid
            }

            // Get transaction limits based on currency
            $limits = $this->getTransactionLimits($currency);

            // Validate amount
            $amount_input = $_POST['amount'] ?? '';
            
            if (empty($amount_input)) {
                $errors[] = "El monto es obligatorio";
            } elseif (!is_numeric($amount_input)) {
                $errors[] = "El monto debe ser un número válido";
            } else {
                // Validate decimal places (max 2) - check original input string
                if (strpos($amount_input, '.') !== false) {
                    $parts = explode('.', $amount_input);
                    if (isset($parts[1]) && strlen(rtrim($parts[1], '0')) > 2) {
                        $errors[] = "El monto no puede tener más de 2 decimales";
                    }
                }
                
                // Validate amount range
                if ($amount <= 0) {
                    $errors[] = "El monto debe ser mayor a 0";
                } elseif ($amount < $limits['min_amount']) {
                    $errors[] = sprintf(
                        "El monto es muy bajo. El mínimo permitido es %s %s",
                        number_format($limits['min_amount'], 2, '.', ','),
                        $currency
                    );
                } elseif ($amount > $limits['max_single_transaction']) {
                    $errors[] = sprintf(
                        "El monto es demasiado alto. El máximo permitido para una transacción es %s %s. Si necesitas registrar un monto mayor, considera dividirlo en múltiples transacciones.",
                        number_format($limits['max_single_transaction'], 2, '.', ','),
                        $currency
                    );
                } else {
                    // Round to 2 decimal places for consistency
                    $amount = round($amount, 2);
                    
                    // Note: We don't block transactions based on income comparison
                    // Large transactions (like buying a car, receiving a bonus) are legitimate
                    // The server-side validation ensures the amount is within acceptable limits
                    // Additional contextual validation could be added here if needed
                }
            }

            // Validate category
            if (empty($category)) {
                $errors[] = "La categoría es obligatoria";
            } elseif (strlen($category) > 100) {
                $errors[] = "El nombre de la categoría es demasiado largo (máximo 100 caracteres)";
            } else {
                // Validate that category exists for this user and type
                $categories = $this->transaction->getCategories($user_id, $type);
                $category_names = array_column($categories, 'name');
                if (!in_array($category, $category_names)) {
                    $errors[] = "La categoría seleccionada no es válida para este tipo de transacción";
                }
            }
            
            // Validate payment method (only for expenses)
            if ($type === 'expense') {
                if (empty($payment_method)) {
                    $errors[] = "El medio de pago es obligatorio para gastos";
                } else {
                    // Validate that payment method is in user's profile
                    $profile = $this->profile->getByUserId($user_id);
                    if ($profile && !empty($profile['payment_methods'])) {
                        $valid_methods = $profile['payment_methods'];
                        if (!in_array($payment_method, $valid_methods)) {
                            $errors[] = "El medio de pago seleccionado no está disponible en tu perfil";
                        }
                    } else {
                        // If no payment methods in profile, only allow common ones
                        $valid_methods = ['efectivo', 'tarjeta'];
                        if (!in_array($payment_method, $valid_methods)) {
                            $errors[] = "El medio de pago no es válido";
                        }
                    }
                }
            } else {
                // For income, payment_method should be empty or null
                $payment_method = null;
            }

            // Validate transaction date
            if (empty($transaction_date)) {
                $errors[] = "La fecha de la transacción es obligatoria";
            } else {
                // Validate date format
                $date_parts = explode('-', $transaction_date);
                if (count($date_parts) !== 3 || !checkdate($date_parts[1], $date_parts[2], $date_parts[0])) {
                    $errors[] = "La fecha de la transacción no es válida";
                } else {
                    $transaction_datetime = new DateTime($transaction_date);
                    $today = new DateTime();
                    $today->setTime(23, 59, 59); // End of today
                    
                    // For expenses, date cannot be in the future
                    // For income, allow today and past dates (income can be registered when received)
                    if ($type === 'expense') {
                        if ($transaction_datetime > $today) {
                            $errors[] = "La fecha del gasto no puede ser futura";
                        }
                    } else {
                        // For income, allow up to 1 day in the future (in case user registers today's income in advance)
                        $tomorrow = clone $today;
                        $tomorrow->modify('+1 day');
                        $tomorrow->setTime(23, 59, 59);
                        if ($transaction_datetime > $tomorrow) {
                            $errors[] = "La fecha del ingreso no puede ser más de 1 día en el futuro";
                        }
                    }
                    
                    // Validate that date is not too old (more than 3 years for expenses, 5 years for income)
                    $max_years_back = ($type === 'expense') ? 3 : 5;
                    $max_date = new DateTime();
                    $max_date->modify("-{$max_years_back} years");
                    if ($transaction_datetime < $max_date) {
                        $errors[] = sprintf(
                            "La fecha de la transacción no puede ser de hace más de %d %s",
                            $max_years_back,
                            $max_years_back === 1 ? 'año' : 'años'
                        );
                    }
                    
                    // Validate that transaction date is not before profile start date
                    if ($user_profile && !empty($user_profile['start_date'])) {
                        $start_date = new DateTime($user_profile['start_date']);
                        $start_date->setTime(0, 0, 0);
                        $transaction_datetime->setTime(0, 0, 0);
                        
                        if ($transaction_datetime < $start_date) {
                            $formatted_start_date = date('d/m/Y', strtotime($user_profile['start_date']));
                            $errors[] = sprintf(
                                "La fecha de la transacción no puede ser anterior a la fecha de inicio de tu perfil (%s)",
                                $formatted_start_date
                            );
                        }
                    }
                }
            }

            // Validate description (optional but if provided, validate length)
            if (!empty($description)) {
                $description = trim($description);
                if (strlen($description) > 500) {
                    $errors[] = "La descripción es demasiado larga (máximo 500 caracteres)";
                }
            }

            if (empty($errors)) {
                $this->transaction->user_id = $user_id;
                $this->transaction->type = $type;
                $this->transaction->amount = $amount;
                $this->transaction->category = $category;
                $this->transaction->payment_method = $payment_method;
                $this->transaction->description = !empty($description) ? $description : null;
                $this->transaction->transaction_date = $transaction_date;

                if ($this->transaction->create()) {
                    // Check spending limit if it's an expense
                    if ($type === 'expense') {
                        $this->checkSpendingLimit($user_id);
                    }

                    // Actualizar progreso mensual del objetivo financiero
                    $transaction_year = date('Y', strtotime($transaction_date));
                    $transaction_month = date('m', strtotime($transaction_date));
                    $this->goal_progress_helper->updateMonthProgress($user_id, $transaction_year, $transaction_month);

                    setFlashMessage(
                        $type === 'expense' ? 'Gasto registrado exitosamente' : 'Ingreso registrado exitosamente',
                        'success'
                    );
                    
                    // Return JSON if AJAX request
                    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                        echo json_encode(['success' => true]);
                        exit();
                    }
                    
                    header('Location: ' . BASE_URL . 'public/index.php?page=transactions');
                    exit();
                } else {
                    $errors[] = "Error al registrar transacción";
                }
            }

            $_SESSION['transaction_errors'] = $errors;
            $_SESSION['transaction_data'] = $_POST;
            
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                echo json_encode(['success' => false, 'errors' => $errors]);
                exit();
            }
            
            header('Location: ' . BASE_URL . 'public/index.php?page=add-transaction');
            exit();
        }
    }

    /**
     * Check spending limit and create alerts
     */
    private function checkSpendingLimit($user_id) {
        $year = date('Y');
        $month = date('m');

        $summary = $this->transaction->getMonthlySummary($user_id, $year, $month);
        $spending_limit = $this->profile->getSpendingLimit($user_id);

        if ($spending_limit > 0) {
            $total_expenses = $summary['total_expenses'];
            $percentage = ($total_expenses / $spending_limit) * 100;

            // Alert if exceeded limit
            if ($total_expenses > $spending_limit) {
                $this->alert->user_id = $user_id;
                $this->alert->type = 'limit_exceeded';
                $this->alert->message = "¡Alerta! Has excedido tu límite de gasto mensual. Gasto actual: " . 
                                       formatCurrency($total_expenses) . " de " . 
                                       formatCurrency($spending_limit);
                $this->alert->create();
            }
            // Warning if 80% or more
            elseif ($percentage >= 80) {
                $this->alert->user_id = $user_id;
                $this->alert->type = 'limit_warning';
                $this->alert->message = "Advertencia: Has alcanzado el " . round($percentage) . 
                                       "% de tu límite mensual de gasto.";
                $this->alert->create();
            }
        }
    }

    /**
     * Delete transaction
     */
    public function deleteTransaction() {
        requireLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user_id = $_SESSION['user_id'];
            $transaction_id = intval($_POST['transaction_id'] ?? 0);

            // Obtener la transacción antes de eliminarla para saber qué mes actualizar
            // Usamos una consulta directa para obtener solo la fecha de la transacción
            $query = "SELECT transaction_date FROM transactions WHERE id = :id AND user_id = :user_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $transaction_id);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            $transaction_to_delete = $stmt->fetch();

            if ($this->transaction->delete($transaction_id, $user_id)) {
                // Actualizar progreso mensual del objetivo financiero
                if ($transaction_to_delete && isset($transaction_to_delete['transaction_date'])) {
                    $transaction_year = date('Y', strtotime($transaction_to_delete['transaction_date']));
                    $transaction_month = date('m', strtotime($transaction_to_delete['transaction_date']));
                    $this->goal_progress_helper->updateMonthProgress($user_id, $transaction_year, $transaction_month);
                }

                setFlashMessage('Transacción eliminada exitosamente', 'success');
            } else {
                setFlashMessage('Error al eliminar transacción', 'error');
            }

            header('Location: ' . BASE_URL . 'public/index.php?page=transactions');
            exit();
        }
    }

    /**
     * Get transactions (for AJAX)
     */
    public function getTransactions() {
        requireLogin();

        $user_id = $_SESSION['user_id'];
        $year = intval($_GET['year'] ?? date('Y'));
        $month = intval($_GET['month'] ?? date('m'));

        $transactions = $this->transaction->getByMonth($user_id, $year, $month);

        header('Content-Type: application/json');
        echo json_encode($transactions);
        exit();
    }

    /**
     * Export transactions to CSV
     */
    public function exportTransactions() {
        requireLogin();

        $user_id = $_SESSION['user_id'];
        $year = intval($_GET['year'] ?? date('Y'));
        $month = intval($_GET['month'] ?? date('m'));

        $transactions = $this->transaction->getByMonth($user_id, $year, $month);

        // Set headers for CSV download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="transacciones_' . $year . '_' . $month . '.csv"');

        $output = fopen('php://output', 'w');

        // UTF-8 BOM for Excel compatibility
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // Headers
        fputcsv($output, ['Fecha', 'Tipo', 'Categoría', 'Monto', 'Método de Pago', 'Descripción']);

        // Data
        foreach ($transactions as $transaction) {
            fputcsv($output, [
                $transaction['transaction_date'],
                $transaction['type'] === 'expense' ? 'Gasto' : 'Ingreso',
                $transaction['category'],
                $transaction['amount'],
                $transaction['payment_method'] ?? 'N/A',
                $transaction['description'] ?? ''
            ]);
        }

        fclose($output);
        exit();
    }
}

