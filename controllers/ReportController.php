<?php
/**
 * Report Controller
 */

require_once __DIR__ . '/../config/config.php';

class ReportController {
    private $db;
    private $transaction;
    private $profile;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->transaction = new Transaction($this->db);
        $this->profile = new FinancialProfile($this->db);
    }

    /**
     * Get dashboard data
     */
    public function getDashboardData() {
        requireLogin();

        $user_id = $_SESSION['user_id'];
        $year = intval($_GET['year'] ?? date('Y'));
        $month = intval($_GET['month'] ?? date('m'));

        $data = [
            'summary' => $this->transaction->getMonthlySummary($user_id, $year, $month),
            'by_category' => $this->transaction->getExpensesByCategory($user_id, $year, $month),
            'recent_transactions' => $this->transaction->getRecent($user_id, 10),
            'profile' => $this->profile->getByUserId($user_id)
        ];

        // Calculate balance
        $data['balance'] = ($data['profile']['monthly_income'] ?? 0) - 
                          ($data['summary']['total_expenses'] ?? 0);

        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }

    /**
     * Get chart data for expenses by category
     */
    public function getCategoryChartData() {
        requireLogin();

        $user_id = $_SESSION['user_id'];
        $year = intval($_GET['year'] ?? date('Y'));
        $month = intval($_GET['month'] ?? date('m'));

        $expenses = $this->transaction->getExpensesByCategory($user_id, $year, $month);

        $labels = [];
        $data = [];
        $colors = [
            '#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6',
            '#EC4899', '#14B8A6', '#F97316', '#6366F1', '#84CC16'
        ];

        foreach ($expenses as $index => $expense) {
            $labels[] = $expense['category'];
            $data[] = floatval($expense['total']);
        }

        $chartData = [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Gastos por Categoría',
                    'data' => $data,
                    'backgroundColor' => array_slice($colors, 0, count($data))
                ]
            ]
        ];

        header('Content-Type: application/json');
        echo json_encode($chartData);
        exit();
    }

    /**
     * Get monthly comparison data
     */
    public function getMonthlyComparison() {
        requireLogin();

        $user_id = $_SESSION['user_id'];
        $year = intval($_GET['year'] ?? date('Y'));

        $months = [];
        $income_data = [];
        $expense_data = [];

        for ($i = 1; $i <= 12; $i++) {
            $summary = $this->transaction->getMonthlySummary($user_id, $year, $i);
            
            $months[] = date('M', mktime(0, 0, 0, $i, 1));
            $income_data[] = floatval($summary['total_income'] ?? 0);
            $expense_data[] = floatval($summary['total_expenses'] ?? 0);
        }

        $chartData = [
            'labels' => $months,
            'datasets' => [
                [
                    'label' => 'Ingresos',
                    'data' => $income_data,
                    'borderColor' => '#10B981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'tension' => 0.4
                ],
                [
                    'label' => 'Gastos',
                    'data' => $expense_data,
                    'borderColor' => '#EF4444',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                    'tension' => 0.4
                ]
            ]
        ];

        header('Content-Type: application/json');
        echo json_encode($chartData);
        exit();
    }

    /**
     * Get payment method distribution
     */
    public function getPaymentMethodData() {
        requireLogin();

        $user_id = $_SESSION['user_id'];
        $year = intval($_GET['year'] ?? date('Y'));
        $month = intval($_GET['month'] ?? date('m'));

        $summary = $this->transaction->getMonthlySummary($user_id, $year, $month);

        $chartData = [
            'labels' => ['Efectivo', 'Tarjeta'],
            'datasets' => [
                [
                    'label' => 'Gastos por Método de Pago',
                    'data' => [
                        floatval($summary['cash_expenses'] ?? 0),
                        floatval($summary['card_expenses'] ?? 0)
                    ],
                    'backgroundColor' => ['#3B82F6', '#10B981']
                ]
            ]
        ];

        header('Content-Type: application/json');
        echo json_encode($chartData);
        exit();
    }
}

