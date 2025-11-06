<?php
$page_title = 'Reportes - Control de Gastos';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

$database = new Database();
$db = $database->getConnection();

$transaction_model = new Transaction($db);
$profile_model = new FinancialProfile($db);

$user_id = $_SESSION['user_id'];
$profile = $profile_model->getByUserId($user_id);

// Get current month data
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$month = isset($_GET['month']) ? intval($_GET['month']) : date('m');

$summary = $transaction_model->getMonthlySummary($user_id, $year, $month);
$categories = $transaction_model->getExpensesByCategory($user_id, $year, $month);
?>

<div class="min-h-screen bg-gray-50 py-6 sm:py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">
                <i class="fas fa-chart-bar mr-2 sm:mr-3 text-blue-600"></i>Reportes y Análisis
            </h1>
            <p class="text-sm sm:text-base text-gray-600 mt-2">Visualiza tus finanzas con gráficos detallados</p>
        </div>

        <!-- Period Selector -->
        <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6 mb-6">
            <form method="GET" action="" class="flex flex-wrap items-end gap-3 sm:gap-4">
                <input type="hidden" name="page" value="reports">
                
                <div>
                    <label for="year" class="block text-sm font-medium text-gray-700 mb-1">Año</label>
                    <select id="year" name="year" 
                            onchange="this.form.submit()"
                            class="block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <?php for ($y = date('Y'); $y >= date('Y') - 5; $y--): ?>
                            <option value="<?php echo $y; ?>" <?php echo $y === $year ? 'selected' : ''; ?>>
                                <?php echo $y; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div>
                    <label for="month" class="block text-sm font-medium text-gray-700 mb-1">Mes</label>
                    <select id="month" name="month" 
                            onchange="this.form.submit()"
                            class="block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <?php
                        $months = [
                            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
                        ];
                        foreach ($months as $num => $name):
                        ?>
                            <option value="<?php echo $num; ?>" <?php echo $num === $month ? 'selected' : ''; ?>>
                                <?php echo $name; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        </div>

        <!-- Summary Statistics -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6">
            <div class="bg-gradient-to-br from-blue-500 to-blue-700 rounded-xl shadow-lg p-4 sm:p-6 text-white">
                <p class="text-xs sm:text-sm opacity-90 mb-2">Ingresos del Mes</p>
                <p class="text-2xl sm:text-3xl font-bold">
                    <?php echo formatCurrency($summary['total_income'] ?? 0, $profile['currency']); ?>
                </p>
            </div>

            <div class="bg-gradient-to-br from-red-500 to-red-700 rounded-xl shadow-lg p-4 sm:p-6 text-white">
                <p class="text-xs sm:text-sm opacity-90 mb-2">Gastos del Mes</p>
                <p class="text-2xl sm:text-3xl font-bold">
                    <?php echo formatCurrency($summary['total_expenses'] ?? 0, $profile['currency']); ?>
                </p>
            </div>

            <div class="bg-gradient-to-br from-green-500 to-green-700 rounded-xl shadow-lg p-4 sm:p-6 text-white">
                <p class="text-xs sm:text-sm opacity-90 mb-2">Balance</p>
                <p class="text-2xl sm:text-3xl font-bold">
                    <?php 
                    $balance = ($summary['total_income'] ?? 0) - ($summary['total_expenses'] ?? 0);
                    echo formatCurrency($balance, $profile['currency']); 
                    ?>
                </p>
            </div>

            <div class="bg-gradient-to-br from-purple-500 to-purple-700 rounded-xl shadow-lg p-4 sm:p-6 text-white">
                <p class="text-xs sm:text-sm opacity-90 mb-2">Promedio Diario</p>
                <p class="text-2xl sm:text-3xl font-bold">
                    <?php 
                    $days_in_month = date('t', strtotime("$year-$month-01"));
                    $daily_avg = $days_in_month > 0 ? ($summary['total_expenses'] ?? 0) / $days_in_month : 0;
                    echo formatCurrency($daily_avg, $profile['currency']); 
                    ?>
                </p>
            </div>
        </div>

        <!-- Charts Row 1 -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 mb-6">
            <!-- Monthly Comparison Chart -->
            <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6">
                <h3 class="text-lg sm:text-xl font-bold text-gray-900 mb-4">
                    <i class="fas fa-chart-line mr-2 text-blue-600"></i>Comparación Mensual
                </h3>
                <canvas id="monthlyComparisonChart"></canvas>
            </div>

            <!-- Expenses by Category -->
            <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6">
                <h3 class="text-lg sm:text-xl font-bold text-gray-900 mb-4">
                    <i class="fas fa-chart-pie mr-2 text-blue-600"></i>Gastos por Categoría
                </h3>
                <?php if (!empty($categories)): ?>
                    <canvas id="categoryPieChart"></canvas>
                <?php else: ?>
                    <div class="text-center py-12 text-gray-500">
                        <i class="fas fa-chart-pie text-6xl mb-4 opacity-50"></i>
                        <p>No hay gastos en este período</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Charts Row 2 -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 mb-6">
            <!-- Payment Methods Distribution -->
            <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6">
                <h3 class="text-lg sm:text-xl font-bold text-gray-900 mb-4">
                    <i class="fas fa-credit-card mr-2 text-blue-600"></i>Distribución por Método de Pago
                </h3>
                <?php if (($summary['total_expenses'] ?? 0) > 0): ?>
                    <canvas id="paymentMethodChart"></canvas>
                    <div class="mt-4 grid grid-cols-2 gap-3 sm:gap-4">
                        <div class="text-center p-3 bg-blue-50 rounded-lg">
                            <p class="text-xs sm:text-sm text-gray-600">Efectivo</p>
                            <p class="text-lg sm:text-xl font-bold text-blue-600">
                                <?php echo formatCurrency($summary['cash_expenses'] ?? 0, $profile['currency']); ?>
                            </p>
                        </div>
                        <div class="text-center p-3 bg-green-50 rounded-lg">
                            <p class="text-xs sm:text-sm text-gray-600">Tarjeta</p>
                            <p class="text-lg sm:text-xl font-bold text-green-600">
                                <?php echo formatCurrency($summary['card_expenses'] ?? 0, $profile['currency']); ?>
                            </p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="text-center py-12 text-gray-500">
                        <i class="fas fa-credit-card text-6xl mb-4 opacity-50"></i>
                        <p>No hay gastos en este período</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Category Breakdown -->
            <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6">
                <h3 class="text-lg sm:text-xl font-bold text-gray-900 mb-4">
                    <i class="fas fa-list mr-2 text-blue-600"></i>Desglose por Categoría
                </h3>
                <?php if (!empty($categories)): ?>
                    <div class="space-y-3 max-h-96 overflow-y-auto pr-2">
                        <?php 
                        $total = array_sum(array_column($categories, 'total'));
                        foreach ($categories as $cat): 
                            $percentage = $total > 0 ? ($cat['total'] / $total) * 100 : 0;
                        ?>
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-sm font-medium text-gray-700">
                                        <?php echo htmlspecialchars($cat['category']); ?>
                                    </span>
                                    <span class="text-sm font-bold text-gray-900">
                                        <?php echo formatCurrency($cat['total'], $profile['currency']); ?>
                                    </span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-600 h-2 rounded-full" 
                                         style="width: <?php echo round($percentage); ?>%"></div>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">
                                    <?php echo round($percentage, 1); ?>% del total
                                </p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-12 text-gray-500">
                        <i class="fas fa-list text-6xl mb-4 opacity-50"></i>
                        <p>No hay datos para mostrar</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Goal Progress (if applicable) -->
        <?php if ($profile['financial_goal'] === 'ahorrar' && $profile['savings_goal']): ?>
            <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6 mb-6">
                <h3 class="text-lg sm:text-xl font-bold text-gray-900 mb-4">
                    <i class="fas fa-bullseye mr-2 text-blue-600"></i>Progreso de Meta de Ahorro
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6">
                    <div class="text-center">
                        <p class="text-xs sm:text-sm text-gray-600 mb-2">Meta de Ahorro</p>
                        <p class="text-2xl sm:text-3xl font-bold text-blue-600">
                            <?php echo formatCurrency($profile['savings_goal'], $profile['currency']); ?>
                        </p>
                    </div>
                    <div class="text-center">
                        <p class="text-xs sm:text-sm text-gray-600 mb-2">Ahorro Actual</p>
                        <p class="text-2xl sm:text-3xl font-bold text-green-600">
                            <?php echo formatCurrency($balance, $profile['currency']); ?>
                        </p>
                    </div>
                    <div class="text-center">
                        <p class="text-xs sm:text-sm text-gray-600 mb-2">Progreso</p>
                        <p class="text-2xl sm:text-3xl font-bold text-purple-600">
                            <?php 
                            $progress = $profile['savings_goal'] > 0 ? ($balance / $profile['savings_goal']) * 100 : 0;
                            echo round($progress) . '%'; 
                            ?>
                        </p>
                    </div>
                </div>
                <div class="mt-6 w-full bg-gray-200 rounded-full h-4">
                    <div class="bg-gradient-to-r from-blue-500 to-green-500 h-4 rounded-full transition-all duration-500" 
                         style="width: <?php echo min(100, round($progress)); ?>%"></div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($profile['financial_goal'] === 'pagar_deudas' && $profile['debt_amount']): ?>
            <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6">
                <h3 class="text-lg sm:text-xl font-bold text-gray-900 mb-4">
                    <i class="fas fa-hand-holding-usd mr-2 text-blue-600"></i>Progreso de Pago de Deudas
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6">
                    <div class="text-center">
                        <p class="text-xs sm:text-sm text-gray-600 mb-2">Deuda Total</p>
                        <p class="text-2xl sm:text-3xl font-bold text-red-600">
                            <?php echo formatCurrency($profile['debt_amount'], $profile['currency']); ?>
                        </p>
                    </div>
                    <div class="text-center">
                        <p class="text-xs sm:text-sm text-gray-600 mb-2">Ahorro para Pago</p>
                        <p class="text-2xl sm:text-3xl font-bold text-blue-600">
                            <?php echo formatCurrency(max(0, $balance), $profile['currency']); ?>
                        </p>
                    </div>
                    <div class="text-center">
                        <p class="text-xs sm:text-sm text-gray-600 mb-2">Progreso</p>
                        <p class="text-2xl sm:text-3xl font-bold text-green-600">
                            <?php 
                            $debt_progress = $profile['debt_amount'] > 0 ? (max(0, $balance) / $profile['debt_amount']) * 100 : 0;
                            echo round($debt_progress) . '%'; 
                            ?>
                        </p>
                    </div>
                </div>
                <div class="mt-6 w-full bg-gray-200 rounded-full h-4">
                    <div class="bg-gradient-to-r from-red-500 to-green-500 h-4 rounded-full transition-all duration-500" 
                         style="width: <?php echo min(100, round($debt_progress)); ?>%"></div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Monthly Comparison Chart
fetch('<?php echo BASE_URL; ?>public/index.php?action=get-monthly-comparison&year=<?php echo $year; ?>')
    .then(response => response.json())
    .then(data => {
        const ctx = document.getElementById('monthlyComparisonChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: data,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    });

// Category Pie Chart
<?php if (!empty($categories)): 
    $category_labels = array_column($categories, 'category');
    $category_data = array_column($categories, 'total');
    $category_colors = [];
    $default_colors = ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899', '#14B8A6', '#F97316', '#6366F1', '#84CC16'];
    
    foreach ($categories as $index => $cat) {
        $category_colors[] = $cat['category_color'] ?? $default_colors[$index % count($default_colors)];
    }
?>
const categoryCtx = document.getElementById('categoryPieChart').getContext('2d');
new Chart(categoryCtx, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode($category_labels); ?>,
        datasets: [{
            data: <?php echo json_encode($category_data); ?>,
            backgroundColor: <?php echo json_encode($category_colors); ?>
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
<?php endif; ?>

// Payment Method Chart
<?php if (($summary['total_expenses'] ?? 0) > 0): ?>
const paymentCtx = document.getElementById('paymentMethodChart').getContext('2d');
new Chart(paymentCtx, {
    type: 'bar',
    data: {
        labels: ['Efectivo', 'Tarjeta'],
        datasets: [{
            label: 'Gastos',
            data: [
                <?php echo $summary['cash_expenses'] ?? 0; ?>,
                <?php echo $summary['card_expenses'] ?? 0; ?>
            ],
            backgroundColor: ['#3B82F6', '#10B981']
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
<?php endif; ?>
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

