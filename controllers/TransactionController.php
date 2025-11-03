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

            // Validations
            if ($amount <= 0) {
                $errors[] = "El monto debe ser mayor a 0";
            }

            if (empty($category)) {
                $errors[] = "La categoría es obligatoria";
            }
            
            if ($type === 'expense' && empty($payment_method)) {
                $errors[] = "El medio de pago es obligatorio para gastos";
            }

            if (empty($errors)) {
                $this->transaction->user_id = $user_id;
                $this->transaction->type = $type;
                $this->transaction->amount = $amount;
                $this->transaction->category = $category;
                $this->transaction->payment_method = $payment_method;
                $this->transaction->description = $description;
                $this->transaction->transaction_date = $transaction_date;

                if ($this->transaction->create()) {
                    // Check spending limit if it's an expense
                    if ($type === 'expense') {
                        $this->checkSpendingLimit($user_id);
                    }

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

            if ($this->transaction->delete($transaction_id, $user_id)) {
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

