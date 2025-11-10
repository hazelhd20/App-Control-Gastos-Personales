<?php
$page_title = 'Dashboard - Control de Gastos';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

$database = new Database();
$db = $database->getConnection();

$transaction_model = new Transaction($db);
$profile_model = new FinancialProfile($db);
$alert_model = new Alert($db);
$goal_progress_helper = new GoalProgressHelper($db);

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
$balance = $total_income - $total_expenses; // Monthly balance for display
$spending_percentage = $profile['spending_limit'] > 0 ? ($total_expenses / $profile['spending_limit']) * 100 : 0;

// Calculate total accumulated savings for savings goal progress (acumulado total)
$total_savings_balance = $transaction_model->getTotalSavingsBalance($user_id);

// Obtener progreso mensual del objetivo financiero
$monthly_progress = $goal_progress_helper->getCurrentMonthProgress($user_id);

$flash = getFlashMessage();
?>

<div class="min-h-screen bg-gray-50 pb-12">
    <?php if ($flash): ?>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
            <div class="alert-auto-hide <?php echo $flash['type'] === 'error' ? 'alert-danger' : 'alert-success'; ?>">
                <i class="fas <?php echo $flash['type'] === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle'; ?>"></i>
                <p class="text-sm"><?php echo htmlspecialchars($flash['message']); ?></p>
            </div>
        </div>
    <?php endif; ?>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
        <!-- Header -->
        <div class="mb-6 sm:mb-8">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">
                ¡Bienvenido, <?php echo htmlspecialchars($_SESSION['user_name']); ?>! 👋
            </h1>
            <p class="text-sm sm:text-base text-gray-600 mt-2">
                Resumen financiero de <?php echo date('F Y'); ?>
            </p>
        </div>

        <!-- Alerts -->
        <?php if (!empty($alerts)): ?>
            <div class="mb-6 space-y-3">
                <?php foreach ($alerts as $alert): ?>
                    <div class="<?php echo $alert['type'] === 'limit_exceeded' ? 'alert-danger' : 'alert-warning'; ?>">
                        <div class="flex items-start">
                            <i class="fas fa-exclamation-triangle text-xl mr-3"></i>
                            <p class="flex-1"><?php echo htmlspecialchars($alert['message']); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6 sm:mb-8">
            <!-- Total Income -->
            <div class="bg-gradient-to-br from-green-500 to-green-700 rounded-xl shadow-lg p-5 sm:p-6 text-white card-hover">
                <div class="flex items-center justify-between mb-3">
                    <i class="fas fa-arrow-down text-2xl sm:text-3xl"></i>
                    <span class="text-xs sm:text-sm opacity-90">Ingresos</span>
                </div>
                <p class="text-2xl sm:text-3xl font-bold">
                    <?php echo formatCurrency($total_income, $profile['currency']); ?>
                </p>
                <p class="text-xs sm:text-sm opacity-90 mt-2">Este mes</p>
            </div>

            <!-- Total Expenses -->
            <div class="bg-gradient-to-br from-red-500 to-red-700 rounded-xl shadow-lg p-5 sm:p-6 text-white card-hover">
                <div class="flex items-center justify-between mb-3">
                    <i class="fas fa-arrow-up text-2xl sm:text-3xl"></i>
                    <span class="text-xs sm:text-sm opacity-90">Gastos</span>
                </div>
                <p class="text-2xl sm:text-3xl font-bold">
                    <?php echo formatCurrency($total_expenses, $profile['currency']); ?>
                </p>
                <p class="text-xs sm:text-sm opacity-90 mt-2">Este mes</p>
            </div>

            <!-- Balance -->
            <div class="bg-gradient-to-br from-blue-500 to-blue-700 rounded-xl shadow-lg p-5 sm:p-6 text-white card-hover">
                <div class="flex items-center justify-between mb-3">
                    <i class="fas fa-wallet text-2xl sm:text-3xl"></i>
                    <span class="text-xs sm:text-sm opacity-90">Saldo</span>
                </div>
                <p class="text-2xl sm:text-3xl font-bold">
                    <?php echo formatCurrency($balance, $profile['currency']); ?>
                </p>
                <p class="text-xs sm:text-sm opacity-90 mt-2">Disponible</p>
            </div>

            <!-- Spending Limit -->
            <div class="bg-gradient-to-br from-purple-500 to-purple-700 rounded-xl shadow-lg p-5 sm:p-6 text-white card-hover">
                <div class="flex items-center justify-between mb-3">
                    <i class="fas fa-chart-line text-2xl sm:text-3xl"></i>
                    <span class="text-xs sm:text-sm opacity-90">Límite</span>
                </div>
                <p class="text-2xl sm:text-3xl font-bold">
                    <?php echo round($spending_percentage); ?>%
                </p>
                <p class="text-xs sm:text-sm opacity-90 mt-2">
                    <?php echo formatCurrency($profile['spending_limit'], $profile['currency']); ?> total
                </p>
            </div>
        </div>

        <!-- Charts and Recent Transactions -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 mb-6 sm:mb-8">
            <!-- Expenses by Category Chart -->
            <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6">
                <h3 class="text-lg sm:text-xl font-bold text-gray-900 mb-4">
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
            <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6">
                <h3 class="text-lg sm:text-xl font-bold text-gray-900 mb-4">
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
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
            <!-- Recent Transactions -->
            <div class="lg:col-span-2 bg-white rounded-xl shadow-lg p-4 sm:p-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-0 mb-4 sm:mb-6">
                    <h3 class="text-lg sm:text-xl font-bold text-gray-900">
                        <i class="fas fa-history mr-2 text-blue-600"></i>Transacciones Recientes
                    </h3>
                    <a href="<?php echo BASE_URL; ?>public/index.php?page=transactions" 
                       class="text-blue-600 hover:text-blue-700 text-sm font-medium inline-flex items-center">
                        Ver todas <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>

                <?php if (!empty($recent)): ?>
                    <div class="space-y-3">
                        <?php foreach ($recent as $trans): 
                            $category_icon = $trans['category_icon'] ?? 'fa-tag';
                            $category_color = $trans['category_color'] ?? ($trans['type'] === 'expense' ? '#EF4444' : '#10B981');
                        ?>
                            <div class="flex items-center justify-between p-3 sm:p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                                <div class="flex items-center flex-1 min-w-0">
                                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-full flex items-center justify-center flex-shrink-0" style="background-color: <?php echo htmlspecialchars($category_color); ?>20; border: 2px solid <?php echo htmlspecialchars($category_color); ?>40;">
                                        <i class="fas <?php echo htmlspecialchars($category_icon); ?> text-sm sm:text-base" style="color: <?php echo htmlspecialchars($category_color); ?>;"></i>
                                    </div>
                                    <div class="ml-3 sm:ml-4 min-w-0 flex-1">
                                        <p class="font-medium text-gray-900 truncate text-sm sm:text-base"><?php echo htmlspecialchars($trans['category']); ?></p>
                                        <p class="text-xs sm:text-sm text-gray-600"><?php echo date('d/m/Y', strtotime($trans['transaction_date'])); ?></p>
                                    </div>
                                </div>
                                <p class="font-bold text-sm sm:text-base ml-2 flex-shrink-0 <?php echo $trans['type'] === 'expense' ? 'text-red-600' : 'text-green-600'; ?>">
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
            <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6">
                <h3 class="text-lg sm:text-xl font-bold text-gray-900 mb-4 sm:mb-6">
                    <i class="fas fa-bolt mr-2 text-blue-600"></i>Acciones Rápidas
                </h3>
                <div class="space-y-2 sm:space-y-3">
                    <a href="<?php echo BASE_URL; ?>public/index.php?page=add-transaction" 
                       class="block w-full btn-primary py-2.5 sm:py-3 px-4 rounded-lg text-center font-semibold text-sm sm:text-base">
                        <i class="fas fa-plus-circle mr-2"></i>Registrar Gasto
                    </a>
                    <a href="<?php echo BASE_URL; ?>public/index.php?page=transactions" 
                       class="block w-full bg-gray-100 text-gray-700 hover:bg-gray-200 py-2.5 sm:py-3 px-4 rounded-lg text-center font-semibold transition text-sm sm:text-base">
                        <i class="fas fa-list mr-2"></i>Ver Transacciones
                    </a>
                    <a href="<?php echo BASE_URL; ?>public/index.php?page=reports" 
                       class="block w-full bg-gray-100 text-gray-700 hover:bg-gray-200 py-2.5 sm:py-3 px-4 rounded-lg text-center font-semibold transition text-sm sm:text-base">
                        <i class="fas fa-chart-bar mr-2"></i>Ver Reportes
                    </a>
                    <a href="<?php echo BASE_URL; ?>public/index.php?page=profile" 
                       class="block w-full bg-gray-100 text-gray-700 hover:bg-gray-200 py-2.5 sm:py-3 px-4 rounded-lg text-center font-semibold transition text-sm sm:text-base">
                        <i class="fas fa-user-cog mr-2"></i>Configuración
                    </a>
                </div>

                <!-- Monthly Goal Progress -->
                <?php if ($monthly_progress): ?>
                    <?php if ($monthly_progress['goal_type'] === 'savings'): ?>
                        <div class="mt-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                            <p class="text-sm font-medium text-blue-900 mb-3">
                                <i class="fas fa-bullseye mr-2"></i>Progreso Mensual de Ahorro
                            </p>
                            <div class="space-y-2">
                                <div class="flex justify-between items-center">
                                    <span class="text-xs text-blue-700">Planificado este mes:</span>
                                    <span class="text-sm font-semibold text-blue-900">
                                        <?php echo formatCurrency($monthly_progress['planned_amount'], $profile['currency']); ?>
                                    </span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-xs text-blue-700">Ahorrado este mes:</span>
                                    <span class="text-sm font-semibold <?php echo $monthly_progress['total_progress'] >= $monthly_progress['planned_amount'] ? 'text-green-600' : 'text-blue-900'; ?>">
                                        <?php echo formatCurrency($monthly_progress['total_progress'], $profile['currency']); ?>
                                    </span>
                                </div>
                                <?php if ($monthly_progress['adjustments'] != 0): ?>
                                    <div class="flex justify-between items-center text-xs text-orange-600">
                                        <span>Ajustes:</span>
                                        <span><?php echo formatCurrency($monthly_progress['adjustments'], $profile['currency']); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="mt-3 w-full bg-blue-200 rounded-full h-3">
                                <div class="bg-blue-600 h-3 rounded-full transition-all duration-300" 
                                     style="width: <?php echo min(100, max(0, $monthly_progress['completion_percentage'])); ?>%"></div>
                            </div>
                            <div class="flex justify-between items-center mt-2">
                                <p class="text-xs font-medium text-blue-600">
                                    <?php echo round($monthly_progress['completion_percentage'], 1); ?>% del objetivo mensual
                                </p>
                                <?php if ($monthly_progress['remaining'] > 0): ?>
                                    <p class="text-xs text-blue-700">
                                        Faltan: <?php echo formatCurrency($monthly_progress['remaining'], $profile['currency']); ?>
                                    </p>
                                <?php elseif ($monthly_progress['surplus'] > 0): ?>
                                    <p class="text-xs text-green-600 font-semibold">
                                        +<?php echo formatCurrency($monthly_progress['surplus'], $profile['currency']); ?> extra
                                    </p>
                                <?php else: ?>
                                    <p class="text-xs text-green-600 font-semibold">✓ Objetivo cumplido</p>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Progreso total acumulado -->
                            <div class="mt-4 pt-4 border-t border-blue-200">
                                <p class="text-xs text-blue-700 mb-1">Progreso total hacia la meta:</p>
                                <p class="text-lg font-bold text-blue-600">
                                    <?php echo formatCurrency(max(0, $total_savings_balance), $profile['currency']); ?>
                                    <span class="text-xs font-normal text-blue-700">
                                        / <?php echo formatCurrency($profile['savings_goal'], $profile['currency']); ?>
                                    </span>
                                </p>
                                <?php 
                                $total_progress_percentage = $profile['savings_goal'] > 0 ? (max(0, $total_savings_balance) / $profile['savings_goal']) * 100 : 0;
                                ?>
                                <div class="mt-2 w-full bg-blue-100 rounded-full h-2">
                                    <div class="bg-blue-500 h-2 rounded-full" 
                                         style="width: <?php echo min(100, max(0, $total_progress_percentage)); ?>%"></div>
                                </div>
                                <p class="text-xs text-blue-600 mt-1">
                                    <?php echo round(min(100, max(0, $total_progress_percentage)), 1); ?>% de la meta total
                                </p>
                            </div>
                        </div>
                    <?php elseif ($monthly_progress['goal_type'] === 'debt_payment'): ?>
                        <div class="mt-6 p-4 bg-red-50 rounded-lg border border-red-200">
                            <p class="text-sm font-medium text-red-900 mb-3">
                                <i class="fas fa-hand-holding-usd mr-2"></i>Progreso Mensual de Pago de Deudas
                            </p>
                            <div class="space-y-2">
                                <div class="flex justify-between items-center">
                                    <span class="text-xs text-red-700">Planificado este mes:</span>
                                    <span class="text-sm font-semibold text-red-900">
                                        <?php echo formatCurrency($monthly_progress['planned_amount'], $profile['currency']); ?>
                                    </span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-xs text-red-700">Ahorrado este mes:</span>
                                    <span class="text-sm font-semibold <?php echo $monthly_progress['total_progress'] >= $monthly_progress['planned_amount'] ? 'text-green-600' : 'text-red-900'; ?>">
                                        <?php echo formatCurrency($monthly_progress['total_progress'], $profile['currency']); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="mt-3 w-full bg-red-200 rounded-full h-3">
                                <div class="bg-green-600 h-3 rounded-full transition-all duration-300" 
                                     style="width: <?php echo min(100, max(0, $monthly_progress['completion_percentage'])); ?>%"></div>
                            </div>
                            <div class="flex justify-between items-center mt-2">
                                <p class="text-xs font-medium text-red-600">
                                    <?php echo round($monthly_progress['completion_percentage'], 1); ?>% del objetivo mensual
                                </p>
                                <?php if ($monthly_progress['remaining'] > 0): ?>
                                    <p class="text-xs text-red-700">
                                        Faltan: <?php echo formatCurrency($monthly_progress['remaining'], $profile['currency']); ?>
                                    </p>
                                <?php elseif ($monthly_progress['surplus'] > 0): ?>
                                    <p class="text-xs text-green-600 font-semibold">
                                        +<?php echo formatCurrency($monthly_progress['surplus'], $profile['currency']); ?> extra
                                    </p>
                                <?php else: ?>
                                    <p class="text-xs text-green-600 font-semibold">✓ Objetivo cumplido</p>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Progreso total acumulado -->
                            <div class="mt-4 pt-4 border-t border-red-200">
                                <p class="text-xs text-red-700 mb-1">Deuda total:</p>
                                <p class="text-lg font-bold text-red-600">
                                    <?php echo formatCurrency($profile['debt_amount'], $profile['currency']); ?>
                                </p>
                                <p class="text-xs text-blue-600 mt-1">
                                    Disponible para pagar: <?php echo formatCurrency(max(0, $total_savings_balance), $profile['currency']); ?>
                                </p>
                                <?php 
                                $debt_progress = $profile['debt_amount'] > 0 ? (max(0, $total_savings_balance) / $profile['debt_amount']) * 100 : 0;
                                $debt_remaining = max(0, $profile['debt_amount'] - max(0, $total_savings_balance));
                                ?>
                                <div class="mt-2 w-full bg-red-100 rounded-full h-2">
                                    <div class="bg-green-500 h-2 rounded-full" 
                                         style="width: <?php echo min(100, max(0, $debt_progress)); ?>%"></div>
                                </div>
                                <p class="text-xs text-red-600 mt-1">
                                    <?php if ($debt_progress >= 100): ?>
                                        ✓ Tienes suficiente para pagar la deuda
                                    <?php else: ?>
                                        Falta: <?php echo formatCurrency($debt_remaining, $profile['currency']); ?> (<?php echo round(100 - min(100, max(0, $debt_progress)), 1); ?>%)
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    <?php elseif ($monthly_progress['goal_type'] === 'spending_control'): ?>
                        <div class="mt-6 p-4 bg-purple-50 rounded-lg border border-purple-200">
                            <p class="text-sm font-medium text-purple-900 mb-3">
                                <i class="fas fa-chart-line mr-2"></i>Control de Gastos Mensual
                            </p>
                            <div class="space-y-2">
                                <div class="flex justify-between items-center">
                                    <span class="text-xs text-purple-700">Límite de gasto:</span>
                                    <span class="text-sm font-semibold text-purple-900">
                                        <?php echo formatCurrency($monthly_progress['planned_amount'], $profile['currency']); ?>
                                    </span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-xs text-purple-700">Gastado este mes:</span>
                                    <span class="text-sm font-semibold <?php echo $monthly_progress['total_progress'] <= $monthly_progress['planned_amount'] ? 'text-green-600' : 'text-red-600'; ?>">
                                        <?php echo formatCurrency($monthly_progress['total_progress'], $profile['currency']); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="mt-3 w-full bg-purple-200 rounded-full h-3">
                                <div class="<?php echo $monthly_progress['total_progress'] <= $monthly_progress['planned_amount'] ? 'bg-green-600' : 'bg-red-600'; ?> h-3 rounded-full transition-all duration-300" 
                                     style="width: <?php echo min(100, max(0, ($monthly_progress['total_progress'] / $monthly_progress['planned_amount']) * 100)); ?>%"></div>
                            </div>
                            <div class="flex justify-between items-center mt-2">
                                <p class="text-xs font-medium text-purple-600">
                                    <?php echo round(($monthly_progress['total_progress'] / $monthly_progress['planned_amount']) * 100, 1); ?>% del límite
                                </p>
                                <?php if ($monthly_progress['total_progress'] > $monthly_progress['planned_amount']): ?>
                                    <p class="text-xs text-red-600 font-semibold">
                                        Excedido: <?php echo formatCurrency($monthly_progress['total_progress'] - $monthly_progress['planned_amount'], $profile['currency']); ?>
                                    </p>
                                <?php else: ?>
                                    <p class="text-xs text-green-600 font-semibold">
                                        Disponible: <?php echo formatCurrency($monthly_progress['planned_amount'] - $monthly_progress['total_progress'], $profile['currency']); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php elseif ($profile['financial_goal'] === 'ahorrar' && $profile['savings_goal']): ?>
                    <!-- Fallback: mostrar progreso acumulado si no hay progreso mensual registrado -->
                    <div class="mt-6 p-4 bg-blue-50 rounded-lg">
                        <p class="text-sm font-medium text-blue-900 mb-2">
                            <i class="fas fa-bullseye mr-2"></i>Meta de Ahorro
                        </p>
                        <p class="text-2xl font-bold text-blue-600">
                            <?php echo formatCurrency(max(0, $total_savings_balance), $profile['currency']); ?>
                        </p>
                        <p class="text-xs text-blue-700 mt-1">
                            de <?php echo formatCurrency($profile['savings_goal'], $profile['currency']); ?>
                        </p>
                        <div class="mt-2 w-full bg-blue-200 rounded-full h-2">
                            <?php 
                            $savings_progress = $profile['savings_goal'] > 0 ? (max(0, $total_savings_balance) / $profile['savings_goal']) * 100 : 0;
                            ?>
                            <div class="bg-blue-600 h-2 rounded-full" 
                                 style="width: <?php echo min(100, max(0, $savings_progress)); ?>%"></div>
                        </div>
                        <p class="text-xs text-blue-600 mt-1">
                            <?php echo round(min(100, max(0, $savings_progress)), 1); ?>% completado
                        </p>
                    </div>
                <?php elseif ($profile['financial_goal'] === 'pagar_deudas' && $profile['debt_amount']): ?>
                    <!-- Fallback: mostrar progreso acumulado si no hay progreso mensual registrado -->
                    <div class="mt-6 p-4 bg-red-50 rounded-lg">
                        <p class="text-sm font-medium text-red-900 mb-2">
                            <i class="fas fa-hand-holding-usd mr-2"></i>Pago de Deudas
                        </p>
                        <p class="text-lg font-bold text-red-600">
                            Deuda: <?php echo formatCurrency($profile['debt_amount'], $profile['currency']); ?>
                        </p>
                        <p class="text-2xl font-bold text-blue-600 mt-2">
                            Disponible: <?php echo formatCurrency(max(0, $total_savings_balance), $profile['currency']); ?>
                        </p>
                        <?php 
                        $debt_progress = $profile['debt_amount'] > 0 ? (max(0, $total_savings_balance) / $profile['debt_amount']) * 100 : 0;
                        $debt_remaining = max(0, $profile['debt_amount'] - max(0, $total_savings_balance));
                        ?>
                        <div class="mt-2 w-full bg-red-200 rounded-full h-2">
                            <div class="bg-green-600 h-2 rounded-full transition-all" 
                                 style="width: <?php echo min(100, max(0, $debt_progress)); ?>%"></div>
                        </div>
                        <p class="text-xs text-red-600 mt-1">
                            <?php if ($debt_progress >= 100): ?>
                                ✓ Tienes suficiente para pagar la deuda
                            <?php else: ?>
                                Falta: <?php echo formatCurrency($debt_remaining, $profile['currency']); ?> (<?php echo round(100 - min(100, max(0, $debt_progress)), 1); ?>%)
                            <?php endif; ?>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Category Chart
<?php if (!empty($categories)): 
    $category_labels = array_column($categories, 'category');
    $category_data = array_column($categories, 'total');
    $category_colors = [];
    $default_colors = ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899', '#14B8A6', '#F97316', '#6366F1', '#84CC16'];
    
    foreach ($categories as $index => $cat) {
        $category_colors[] = $cat['category_color'] ?? $default_colors[$index % count($default_colors)];
    }
?>
const categoryCtx = document.getElementById('categoryChart').getContext('2d');
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

