<?php
$page_title = 'Dashboard - Control de Gastos';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

$database = new Database();
$db = $database->getConnection();

$transaction_model = new Transaction($db);
$profile_model = new FinancialProfile($db);
$alert_model = new Alert($db);

$user_id = $_SESSION['user_id'];
$year = date('Y');
$month = date('m');

$profile = $profile_model->getByUserId($user_id);
$summary = $transaction_model->getMonthlySummary($user_id, $year, $month);
$categories = $transaction_model->getExpensesByCategory($user_id, $year, $month);
$recent = $transaction_model->getRecent($user_id, 5);
$alerts = $alert_model->getUnreadByUserId($user_id);

$total_income = $profile['monthly_income'] + ($summary['total_income'] ?? 0);
$total_expenses = $summary['total_expenses'] ?? 0;
$balance = $total_income - $total_expenses;
$spending_percentage = $profile['spending_limit'] > 0 ? ($total_expenses / $profile['spending_limit']) * 100 : 0;

$flash = getFlashMessage();
?>

<div class="min-h-screen bg-gray-50 pb-12">
    <?php if ($flash): ?>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
            <div class="p-4 rounded-lg alert-auto-hide <?php echo $flash['type'] === 'error' ? 'alert-danger' : 'alert-success'; ?>">
                <p class="text-sm"><?php echo htmlspecialchars($flash['message']); ?></p>
            </div>
        </div>
    <?php endif; ?>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">
                ¡Bienvenido, <?php echo htmlspecialchars($_SESSION['user_name']); ?>! 👋
            </h1>
            <p class="text-gray-600 mt-2">
                Resumen financiero de <?php echo date('F Y'); ?>
            </p>
        </div>

        <!-- Alerts -->
        <?php if (!empty($alerts)): ?>
            <div class="mb-6 space-y-3">
                <?php foreach ($alerts as $alert): ?>
                    <div class="p-4 rounded-lg <?php echo $alert['type'] === 'limit_exceeded' ? 'alert-danger' : 'alert-warning'; ?>">
                        <div class="flex items-start">
                            <i class="fas fa-exclamation-triangle text-xl mr-3"></i>
                            <p class="flex-1"><?php echo htmlspecialchars($alert['message']); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Income -->
            <div class="bg-gradient-to-br from-green-500 to-green-700 rounded-xl shadow-lg p-6 text-white card-hover">
                <div class="flex items-center justify-between mb-2">
                    <i class="fas fa-arrow-down text-3xl"></i>
                    <span class="text-sm opacity-90">Ingresos</span>
                </div>
                <p class="text-3xl font-bold">
                    <?php echo formatCurrency($total_income, $profile['currency']); ?>
                </p>
                <p class="text-sm opacity-90 mt-2">Este mes</p>
            </div>

            <!-- Total Expenses -->
            <div class="bg-gradient-to-br from-red-500 to-red-700 rounded-xl shadow-lg p-6 text-white card-hover">
                <div class="flex items-center justify-between mb-2">
                    <i class="fas fa-arrow-up text-3xl"></i>
                    <span class="text-sm opacity-90">Gastos</span>
                </div>
                <p class="text-3xl font-bold">
                    <?php echo formatCurrency($total_expenses, $profile['currency']); ?>
                </p>
                <p class="text-sm opacity-90 mt-2">Este mes</p>
            </div>

            <!-- Balance -->
            <div class="bg-gradient-to-br from-blue-500 to-blue-700 rounded-xl shadow-lg p-6 text-white card-hover">
                <div class="flex items-center justify-between mb-2">
                    <i class="fas fa-wallet text-3xl"></i>
                    <span class="text-sm opacity-90">Saldo</span>
                </div>
                <p class="text-3xl font-bold">
                    <?php echo formatCurrency($balance, $profile['currency']); ?>
                </p>
                <p class="text-sm opacity-90 mt-2">Disponible</p>
            </div>

            <!-- Spending Limit -->
            <div class="bg-gradient-to-br from-purple-500 to-purple-700 rounded-xl shadow-lg p-6 text-white card-hover">
                <div class="flex items-center justify-between mb-2">
                    <i class="fas fa-chart-line text-3xl"></i>
                    <span class="text-sm opacity-90">Límite</span>
                </div>
                <p class="text-3xl font-bold">
                    <?php echo round($spending_percentage); ?>%
                </p>
                <p class="text-sm opacity-90 mt-2">
                    <?php echo formatCurrency($profile['spending_limit'], $profile['currency']); ?> total
                </p>
            </div>
        </div>

        <!-- Charts and Recent Transactions -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Expenses by Category Chart -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-4">
                    <i class="fas fa-chart-pie mr-2 text-blue-600"></i>Gastos por Categoría
                </h3>
                <?php if (!empty($categories)): ?>
                    <canvas id="categoryChart"></canvas>
                <?php else: ?>
                    <div class="text-center py-12 text-gray-500">
                        <i class="fas fa-chart-pie text-6xl mb-4 opacity-50"></i>
                        <p>No hay gastos registrados este mes</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Payment Method Distribution -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-4">
                    <i class="fas fa-credit-card mr-2 text-blue-600"></i>Métodos de Pago
                </h3>
                <?php if ($total_expenses > 0): ?>
                    <canvas id="paymentChart"></canvas>
                <?php else: ?>
                    <div class="text-center py-12 text-gray-500">
                        <i class="fas fa-credit-card text-6xl mb-4 opacity-50"></i>
                        <p>No hay gastos registrados este mes</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Transactions and Quick Actions -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Recent Transactions -->
            <div class="lg:col-span-2 bg-white rounded-xl shadow-lg p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-bold text-gray-900">
                        <i class="fas fa-history mr-2 text-blue-600"></i>Transacciones Recientes
                    </h3>
                    <a href="<?php echo BASE_URL; ?>public/index.php?page=transactions" 
                       class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                        Ver todas <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>

                <?php if (!empty($recent)): ?>
                    <div class="space-y-3">
                        <?php foreach ($recent as $trans): ?>
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                                <div class="flex items-center flex-1">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center <?php echo $trans['type'] === 'expense' ? 'bg-red-100 text-red-600' : 'bg-green-100 text-green-600'; ?>">
                                        <i class="fas <?php echo $trans['type'] === 'expense' ? 'fa-minus' : 'fa-plus'; ?>"></i>
                                    </div>
                                    <div class="ml-4">
                                        <p class="font-medium text-gray-900"><?php echo htmlspecialchars($trans['category']); ?></p>
                                        <p class="text-sm text-gray-600"><?php echo date('d/m/Y', strtotime($trans['transaction_date'])); ?></p>
                                    </div>
                                </div>
                                <p class="font-bold <?php echo $trans['type'] === 'expense' ? 'text-red-600' : 'text-green-600'; ?>">
                                    <?php echo $trans['type'] === 'expense' ? '-' : '+'; ?>
                                    <?php echo formatCurrency($trans['amount'], $profile['currency']); ?>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-12 text-gray-500">
                        <i class="fas fa-receipt text-6xl mb-4 opacity-50"></i>
                        <p>No hay transacciones registradas</p>
                        <a href="<?php echo BASE_URL; ?>public/index.php?page=add-transaction" 
                           class="mt-4 inline-block btn-primary py-2 px-4 rounded-lg">
                            Registrar primera transacción
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-6">
                    <i class="fas fa-bolt mr-2 text-blue-600"></i>Acciones Rápidas
                </h3>
                <div class="space-y-3">
                    <a href="<?php echo BASE_URL; ?>public/index.php?page=add-transaction" 
                       class="block w-full btn-primary py-3 px-4 rounded-lg text-center font-semibold">
                        <i class="fas fa-plus-circle mr-2"></i>Registrar Gasto
                    </a>
                    <a href="<?php echo BASE_URL; ?>public/index.php?page=transactions" 
                       class="block w-full bg-gray-100 text-gray-700 hover:bg-gray-200 py-3 px-4 rounded-lg text-center font-semibold transition">
                        <i class="fas fa-list mr-2"></i>Ver Transacciones
                    </a>
                    <a href="<?php echo BASE_URL; ?>public/index.php?page=reports" 
                       class="block w-full bg-gray-100 text-gray-700 hover:bg-gray-200 py-3 px-4 rounded-lg text-center font-semibold transition">
                        <i class="fas fa-chart-bar mr-2"></i>Ver Reportes
                    </a>
                    <a href="<?php echo BASE_URL; ?>public/index.php?page=profile" 
                       class="block w-full bg-gray-100 text-gray-700 hover:bg-gray-200 py-3 px-4 rounded-lg text-center font-semibold transition">
                        <i class="fas fa-user-cog mr-2"></i>Configuración
                    </a>
                </div>

                <!-- Goal Progress -->
                <?php if ($profile['financial_goal'] === 'ahorrar' && $profile['savings_goal']): ?>
                    <div class="mt-6 p-4 bg-blue-50 rounded-lg">
                        <p class="text-sm font-medium text-blue-900 mb-2">
                            <i class="fas fa-bullseye mr-2"></i>Meta de Ahorro
                        </p>
                        <p class="text-2xl font-bold text-blue-600">
                            <?php echo formatCurrency($balance, $profile['currency']); ?>
                        </p>
                        <p class="text-xs text-blue-700 mt-1">
                            de <?php echo formatCurrency($profile['savings_goal'], $profile['currency']); ?>
                        </p>
                        <div class="mt-2 w-full bg-blue-200 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full" 
                                 style="width: <?php echo min(100, ($balance / $profile['savings_goal']) * 100); ?>%"></div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Category Chart
<?php if (!empty($categories)): ?>
const categoryCtx = document.getElementById('categoryChart').getContext('2d');
new Chart(categoryCtx, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode(array_column($categories, 'category')); ?>,
        datasets: [{
            data: <?php echo json_encode(array_column($categories, 'total')); ?>,
            backgroundColor: [
                '#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6',
                '#EC4899', '#14B8A6', '#F97316', '#6366F1', '#84CC16'
            ]
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
<?php if ($total_expenses > 0): ?>
const paymentCtx = document.getElementById('paymentChart').getContext('2d');
new Chart(paymentCtx, {
    type: 'pie',
    data: {
        labels: ['Efectivo', 'Tarjeta'],
        datasets: [{
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
                position: 'bottom'
            }
        }
    }
});
<?php endif; ?>
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

