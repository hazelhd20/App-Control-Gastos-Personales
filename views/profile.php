<?php
$page_title = 'Mi Perfil - Control de Gastos';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

$database = new Database();
$db = $database->getConnection();

$user_model = new User($db);
$profile_model = new FinancialProfile($db);
$transaction_model = new Transaction($db);
$goal_progress_helper = new GoalProgressHelper($db);

$user_id = $_SESSION['user_id'];
$user = $user_model->getById($user_id);
$profile = $profile_model->getByUserId($user_id);

// Get current month statistics
$year = date('Y');
$month = date('m');
$summary = $transaction_model->getMonthlySummary($user_id, $year, $month);
$total_expenses = $summary['total_expenses'] ?? 0;
$total_income = $summary['total_income'] ?? 0;
$balance = $profile['monthly_income'] + $total_income - $total_expenses; // Monthly balance for display
$spending_percentage = $profile['spending_limit'] > 0 ? ($total_expenses / $profile['spending_limit']) * 100 : 0;

// Calcular progreso acumulado basado en meses planificados (se separa automáticamente cada mes)
$accumulated_progress = $goal_progress_helper->getAccumulatedProgress($user_id);
$total_savings_balance = $accumulated_progress;

// Calculate progress for goals
$savings_progress = 0;
if ($profile['financial_goal'] === 'ahorrar' && $profile['savings_goal'] > 0) {
    $savings_progress = min(100, max(0, ($accumulated_progress / $profile['savings_goal']) * 100));
}

// Calculate days since start
$start_date = new DateTime($profile['start_date']);
$today = new DateTime();
$days_active = $start_date->diff($today)->days;

// Safe check for email_verified
$email_verified = isset($user['email_verified']) ? (bool)$user['email_verified'] : false;

$flash = getFlashMessage();
$errors = $_SESSION['profile_errors'] ?? [];
$profile_data = $_SESSION['profile_data'] ?? [];
unset($_SESSION['profile_errors'], $_SESSION['profile_data']);

$password_errors = $_SESSION['password_errors'] ?? [];
unset($_SESSION['password_errors']);
?>

<div class="min-h-screen bg-gray-50 py-6 sm:py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6 sm:mb-8">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">
                <i class="fas fa-user mr-2 sm:mr-3 text-blue-600"></i>Mi Perfil
            </h1>
            <p class="text-sm sm:text-base text-gray-600 mt-2">Administra tu información personal y configuración financiera</p>
        </div>

        <?php if ($flash): ?>
            <div class="mb-4 sm:mb-6 alert-auto-hide <?php echo $flash['type'] === 'error' ? 'alert-danger' : 'alert-success'; ?>">
                <i class="fas <?php echo $flash['type'] === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle'; ?>"></i>
                <p class="text-sm"><?php echo htmlspecialchars($flash['message']); ?></p>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="mb-4 sm:mb-6 alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <div class="flex-1">
                    <p class="font-semibold mb-2">Por favor corrige los siguientes errores:</p>
                    <ul class="list-disc list-inside space-y-1 text-sm">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
            <!-- Sidebar - Summary Cards -->
            <div class="lg:col-span-1 space-y-4 sm:space-y-6">
                <!-- User Card -->
                <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center">
                            <i class="fas fa-user text-blue-600 text-xl"></i>
                        </div>
                        <div class="ml-4 flex-1">
                            <h3 class="font-semibold text-gray-900"><?php echo htmlspecialchars($user['full_name']); ?></h3>
                            <p class="text-sm text-gray-500"><?php echo htmlspecialchars($user['email']); ?></p>
                        </div>
                    </div>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Ocupación</span>
                            <span class="font-medium text-gray-900"><?php echo htmlspecialchars($user['occupation']); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Miembro desde</span>
                            <span class="font-medium text-gray-900"><?php echo date('M Y', strtotime($profile['start_date'])); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Financial Summary -->
                <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6">
                    <h3 class="font-semibold text-gray-900 mb-4">Resumen Financiero</h3>
                    <div class="space-y-4">
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Ingreso Mensual</p>
                            <p class="text-lg font-bold text-gray-900">
                                <?php echo formatCurrency($profile['monthly_income'], $profile['currency']); ?>
                            </p>
                        </div>
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <p class="text-sm text-gray-600">Límite de Gasto</p>
                                <p class="text-sm font-semibold text-gray-900">
                                    <?php echo formatCurrency($profile['spending_limit'], $profile['currency']); ?>
                                </p>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-<?php echo $spending_percentage > 90 ? 'red' : ($spending_percentage > 70 ? 'yellow' : 'green'); ?>-500 h-2 rounded-full transition-all" 
                                     style="width: <?php echo min(100, $spending_percentage); ?>%"></div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">
                                <?php echo number_format($spending_percentage, 1); ?>% usado (<?php echo formatCurrency($total_expenses, $profile['currency']); ?>)
                            </p>
                        </div>
                        <div class="pt-3 border-t">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Balance</span>
                                <span class="text-sm font-bold <?php echo $balance >= 0 ? 'text-green-600' : 'text-red-600'; ?>">
                                    <?php echo formatCurrency($balance, $profile['currency']); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Goal Card -->
                <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6">
                    <h3 class="font-semibold text-gray-900 mb-4">Objetivo</h3>
                    <?php
                    $goals = [
                        'ahorrar' => ['icon' => 'fa-piggy-bank', 'name' => 'Ahorrar', 'color' => 'text-pink-600'],
                        'pagar_deudas' => ['icon' => 'fa-hand-holding-usd', 'name' => 'Pagar Deudas', 'color' => 'text-red-600'],
                        'controlar_gastos' => ['icon' => 'fa-chart-line', 'name' => 'Controlar Gastos', 'color' => 'text-green-600'],
                        'otro' => ['icon' => 'fa-edit', 'name' => 'Otro', 'color' => 'text-purple-600']
                    ];
                    $current_goal = $goals[$profile['financial_goal']] ?? ['icon' => 'fa-question', 'name' => 'No definido', 'color' => 'text-gray-600'];
                    ?>
                    <div class="flex items-center mb-3">
                        <i class="fas <?php echo $current_goal['icon']; ?> <?php echo $current_goal['color']; ?> text-xl mr-3"></i>
                        <span class="font-medium text-gray-900"><?php echo $current_goal['name']; ?></span>
                    </div>
                    
                    <?php if ($profile['financial_goal'] === 'ahorrar' && $profile['savings_goal']): ?>
                        <div class="mt-3">
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-gray-600">Meta</span>
                                <span class="font-semibold"><?php echo formatCurrency($profile['savings_goal'], $profile['currency']); ?></span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-pink-500 h-2 rounded-full" style="width: <?php echo min(100, $savings_progress); ?>%"></div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1"><?php echo number_format($savings_progress, 1); ?>% completado</p>
                        </div>
                    <?php elseif ($profile['financial_goal'] === 'pagar_deudas' && $profile['debt_amount']): ?>
                        <div class="mt-3 space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Deuda Total</span>
                                <span class="font-semibold text-red-600"><?php echo formatCurrency($profile['debt_amount'], $profile['currency']); ?></span>
                            </div>
                            <?php if (isset($profile['monthly_payment']) && $profile['monthly_payment'] > 0): ?>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Pago Mensual</span>
                                    <span class="font-semibold"><?php echo formatCurrency($profile['monthly_payment'], $profile['currency']); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Main Content - Forms -->
            <div class="lg:col-span-2 space-y-4 sm:space-y-6">
                <!-- Profile Form -->
                <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6">
                    <h2 class="text-lg sm:text-xl font-semibold text-gray-900 mb-4 sm:mb-6">
                        <i class="fas fa-user-edit mr-2 text-blue-600"></i>Información Personal
                    </h2>

                    <form action="<?php echo BASE_URL; ?>public/index.php?action=update-profile" method="POST" class="space-y-6" data-validate="true">
                        <!-- Personal Information -->
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900 mb-4">Datos Personales</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="full_name" class="block text-sm font-medium text-gray-700 mb-1">
                                        Nombre Completo *
                                    </label>
                                    <input id="full_name" name="full_name" type="text" required 
                                           maxlength="255"
                                           minlength="2"
                                           value="<?php echo htmlspecialchars($profile_data['full_name'] ?? $user['full_name']); ?>"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <p class="mt-1 text-xs text-gray-500">Mínimo 2 caracteres, máximo 255.</p>
                                </div>
                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                                        Correo Electrónico *
                                    </label>
                                    <input id="email" name="email" type="email" required 
                                           maxlength="255"
                                           value="<?php echo htmlspecialchars($profile_data['email'] ?? $user['email']); ?>"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <?php if (!$email_verified): ?>
                                        <p class="mt-1 text-xs text-amber-600">
                                            <i class="fas fa-exclamation-circle mr-1"></i>Email no verificado
                                        </p>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">
                                        Teléfono *
                                    </label>
                                    <input id="phone" name="phone" type="tel" required 
                                           data-pattern="[0-9+()\s\-]{7,20}"
                                           data-min-length="7"
                                           data-max-length="20"
                                           value="<?php echo htmlspecialchars($profile_data['phone'] ?? $user['phone']); ?>"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <p class="mt-1 text-xs text-gray-500">Mínimo 7 dígitos, máximo 15 dígitos.</p>
                                </div>
                                <div>
                                    <label for="occupation" class="block text-sm font-medium text-gray-700 mb-1">
                                        Ocupación *
                                    </label>
                                    <input id="occupation" name="occupation" type="text" required 
                                           maxlength="100"
                                           minlength="2"
                                           value="<?php echo htmlspecialchars($profile_data['occupation'] ?? $user['occupation']); ?>"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <p class="mt-1 text-xs text-gray-500">Mínimo 2 caracteres, máximo 100.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Financial Information -->
                        <div class="border-t pt-6">
                            <h3 class="text-sm font-semibold text-gray-900 mb-4">Información Financiera</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="monthly_income" class="block text-sm font-medium text-gray-700 mb-1">
                                        Ingreso Mensual *
                                    </label>
                                    <div class="relative">
                                        <input id="monthly_income" name="monthly_income" type="number" step="0.01" required 
                                               min="0.01"
                                               max="999999999.99"
                                               value="<?php echo htmlspecialchars($profile_data['monthly_income'] ?? $profile['monthly_income']); ?>"
                                               class="w-full px-4 py-2 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                               oninput="updateCalculations()">
                                        <span class="absolute right-3 top-2.5 text-gray-500 text-sm"><?php echo $profile_data['currency'] ?? $profile['currency']; ?></span>
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500">Ingresa tu ingreso mensual. Los límites se ajustan según la moneda seleccionada.</p>
                                </div>
                                <div>
                                    <label for="currency" class="block text-sm font-medium text-gray-700 mb-1">
                                        Moneda *
                                    </label>
                                    <select id="currency" name="currency" required 
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="MXN" <?php echo ($profile_data['currency'] ?? $profile['currency']) === 'MXN' ? 'selected' : ''; ?>>MXN</option>
                                        <option value="USD" <?php echo ($profile_data['currency'] ?? $profile['currency']) === 'USD' ? 'selected' : ''; ?>>USD</option>
                                        <option value="EUR" <?php echo ($profile_data['currency'] ?? $profile['currency']) === 'EUR' ? 'selected' : ''; ?>>EUR</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="spending_limit" class="block text-sm font-medium text-gray-700 mb-1">
                                        Límite Mensual de Gasto *
                                    </label>
                                    <div class="relative">
                                        <input id="spending_limit" name="spending_limit" type="number" step="0.01" required 
                                               min="0.01"
                                               max="999999999.99"
                                               value="<?php echo htmlspecialchars($profile_data['spending_limit'] ?? $profile['spending_limit']); ?>"
                                               class="w-full px-4 py-2 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                               oninput="updateCalculations()">
                                        <span class="absolute right-3 top-2.5 text-gray-500 text-sm"><?php echo $profile_data['currency'] ?? $profile['currency']; ?></span>
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500" id="spending_limit_info">El límite de gasto se ajusta según tu ingreso y objetivos.</p>
                                </div>
                                <div>
                                    <label for="financial_goal" class="block text-sm font-medium text-gray-700 mb-1">
                                        Objetivo Financiero *
                                    </label>
                                    <select id="financial_goal" name="financial_goal" required 
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            onchange="toggleGoalFields(); validateGoalFieldsProfile()">
                                        <option value="ahorrar" <?php echo ($profile_data['financial_goal'] ?? $profile['financial_goal']) === 'ahorrar' ? 'selected' : ''; ?>>Ahorrar</option>
                                        <option value="pagar_deudas" <?php echo ($profile_data['financial_goal'] ?? $profile['financial_goal']) === 'pagar_deudas' ? 'selected' : ''; ?>>Pagar Deudas</option>
                                        <option value="controlar_gastos" <?php echo ($profile_data['financial_goal'] ?? $profile['financial_goal']) === 'controlar_gastos' ? 'selected' : ''; ?>>Controlar Gastos</option>
                                        <option value="otro" <?php echo ($profile_data['financial_goal'] ?? $profile['financial_goal']) === 'otro' ? 'selected' : ''; ?>>Otro</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Savings Goal Fields -->
                            <div id="savings-fields" class="mt-4 p-4 bg-pink-50 border border-pink-200 rounded-lg transition-all duration-300 <?php echo ($profile_data['financial_goal'] ?? $profile['financial_goal']) !== 'ahorrar' ? 'hidden' : ''; ?>">
                                <h4 class="text-sm font-semibold text-gray-900 mb-3">
                                    <i class="fas fa-piggy-bank text-pink-600 mr-2"></i>Meta de Ahorro
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="savings_goal" class="block text-sm font-medium text-gray-700 mb-1">
                                            Meta de Ahorro
                                        </label>
                                        <div class="relative">
                                            <input id="savings_goal" name="savings_goal" type="number" step="0.01" 
                                                   min="0.01"
                                                   max="999999999.99"
                                                   value="<?php echo htmlspecialchars($profile_data['savings_goal'] ?? $profile['savings_goal'] ?? ''); ?>"
                                                   class="w-full px-4 py-2 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
                                            <span class="absolute right-3 top-2.5 text-gray-500 text-sm"><?php echo $profile_data['currency'] ?? $profile['currency']; ?></span>
                                        </div>
                                        <p class="mt-1 text-xs text-gray-500">Opcional. Meta de ahorro deseada.</p>
                                    </div>
                                    <div>
                                        <label for="savings_deadline" class="block text-sm font-medium text-gray-700 mb-1">
                                            Fecha Objetivo
                                        </label>
                                        <input id="savings_deadline" name="savings_deadline" type="date" 
                                               min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>"
                                               value="<?php echo htmlspecialchars($profile_data['savings_deadline'] ?? $profile['savings_deadline'] ?? ''); ?>"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
                                        <p class="mt-1 text-xs text-gray-500">Debe ser una fecha futura.</p>
                                    </div>
                                </div>
                                <div id="savings_info_profile" class="hidden mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                    <div class="flex items-start">
                                        <i class="fas fa-info-circle text-blue-600 mt-1 mr-2"></i>
                                        <div class="flex-1">
                                            <p class="text-sm font-medium text-blue-900 mb-1">Información de tu meta de ahorro:</p>
                                            <ul id="savings_info_list_profile" class="text-xs text-blue-800 space-y-1"></ul>
                                        </div>
                                    </div>
                                </div>
                                <div id="savings_warning_profile" class="hidden mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                                    <div class="flex items-start">
                                        <i class="fas fa-exclamation-triangle text-yellow-600 mt-1 mr-2"></i>
                                        <div class="flex-1">
                                            <p class="text-sm font-medium text-yellow-900 mb-1">Advertencia:</p>
                                            <p id="savings_warning_text_profile" class="text-xs text-yellow-800"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Debt Fields -->
                            <div id="debt-fields" class="mt-4 p-4 bg-red-50 border border-red-200 rounded-lg transition-all duration-300 <?php echo ($profile_data['financial_goal'] ?? $profile['financial_goal']) !== 'pagar_deudas' ? 'hidden' : ''; ?>">
                                <h4 class="text-sm font-semibold text-gray-900 mb-3">
                                    <i class="fas fa-hand-holding-usd text-red-600 mr-2"></i>Información de Deudas
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="debt_amount" class="block text-sm font-medium text-gray-700 mb-1">
                                            Monto Total de Deuda
                                        </label>
                                        <div class="relative">
                                            <input id="debt_amount" name="debt_amount" type="number" step="0.01" 
                                                   min="0.01"
                                                   max="999999999.99"
                                                   value="<?php echo htmlspecialchars($profile_data['debt_amount'] ?? $profile['debt_amount'] ?? ''); ?>"
                                                   class="w-full px-4 py-2 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                            <span class="absolute right-3 top-2.5 text-gray-500 text-sm"><?php echo $profile_data['currency'] ?? $profile['currency']; ?></span>
                                        </div>
                                        <p class="mt-1 text-xs text-gray-500">Opcional. Monto total de deudas.</p>
                                    </div>
                                    <div>
                                        <label for="debt_count" class="block text-sm font-medium text-gray-700 mb-1">
                                            Número de Deudas
                                        </label>
                                        <input id="debt_count" name="debt_count" type="number" step="1" min="1" max="50"
                                               value="<?php echo htmlspecialchars($profile_data['debt_count'] ?? $profile['debt_count'] ?? ''); ?>"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                        <p class="mt-1 text-xs text-gray-500">Opcional. Entre 1 y 50 deudas.</p>
                                    </div>
                                    <div>
                                        <label for="debt_deadline" class="block text-sm font-medium text-gray-700 mb-1">
                                            Fecha Objetivo para Pagar
                                        </label>
                                        <input id="debt_deadline" name="debt_deadline" type="date" 
                                               min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>"
                                               value="<?php echo htmlspecialchars($profile_data['debt_deadline'] ?? $profile['debt_deadline'] ?? ''); ?>"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                        <p class="mt-1 text-xs text-gray-500">Debe ser una fecha futura.</p>
                                    </div>
                                    <div>
                                        <label for="monthly_payment" class="block text-sm font-medium text-gray-700 mb-1">
                                            Pago Mensual
                                        </label>
                                        <div class="relative">
                                            <input id="monthly_payment" name="monthly_payment" type="number" step="0.01" 
                                                   min="0.01"
                                                   max="999999999.99"
                                                   value="<?php echo htmlspecialchars($profile_data['monthly_payment'] ?? $profile['monthly_payment'] ?? ''); ?>"
                                                   class="w-full px-4 py-2 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                            <span class="absolute right-3 top-2.5 text-gray-500 text-sm"><?php echo $profile_data['currency'] ?? $profile['currency']; ?></span>
                                        </div>
                                        <p class="mt-1 text-xs text-gray-500">Opcional. Pago mensual que realizas para las deudas.</p>
                                    </div>
                                </div>
                                <div id="debt_info_profile" class="hidden mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                    <div class="flex items-start">
                                        <i class="fas fa-info-circle text-blue-600 mt-1 mr-2"></i>
                                        <div class="flex-1">
                                            <p class="text-sm font-medium text-blue-900 mb-1">Información sobre tu deuda:</p>
                                            <ul id="debt_info_list_profile" class="text-xs text-blue-800 space-y-1"></ul>
                                        </div>
                                    </div>
                                </div>
                                <div id="debt_warning_profile" class="hidden mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                                    <div class="flex items-start">
                                        <i class="fas fa-exclamation-triangle text-yellow-600 mt-1 mr-2"></i>
                                        <div class="flex-1">
                                            <p class="text-sm font-medium text-yellow-900 mb-1">Advertencia:</p>
                                            <p id="debt_warning_text_profile" class="text-xs text-yellow-800"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Other Goal Field -->
                            <div id="other-goal-fields" class="mt-4 p-4 bg-purple-50 border border-purple-200 rounded-lg transition-all duration-300 <?php echo ($profile_data['financial_goal'] ?? $profile['financial_goal']) !== 'otro' ? 'hidden' : ''; ?>">
                                <h4 class="text-sm font-semibold text-gray-900 mb-3">
                                    <i class="fas fa-edit text-purple-600 mr-2"></i>Descripción del Objetivo
                                </h4>
                                <textarea id="goal_description" name="goal_description" rows="3" 
                                          maxlength="500"
                                          minlength="10"
                                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                          placeholder="Describe tu objetivo financiero..."><?php echo htmlspecialchars($profile_data['goal_description'] ?? $profile['goal_description'] ?? ''); ?></textarea>
                                <p class="mt-1 text-xs text-gray-500">Mínimo 10 caracteres, máximo 500.</p>
                            </div>

                            <!-- Payment Methods -->
                            <div class="mt-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Medios de Pago *
                                </label>
                                <div class="flex gap-4">
                                    <?php
                                    $saved_payment_methods = !empty($profile_data['payment_methods']) ? $profile_data['payment_methods'] : ($profile['payment_methods'] ?? []);
                                    if (is_string($saved_payment_methods)) {
                                        $saved_payment_methods = json_decode($saved_payment_methods, true) ?? [];
                                    }
                                    ?>
                                    <label class="flex items-center p-3 border-2 rounded-lg cursor-pointer transition <?php echo in_array('efectivo', $saved_payment_methods) ? 'border-green-500 bg-green-50' : 'border-gray-300 hover:border-gray-400'; ?>">
                                        <input type="checkbox" name="payment_methods[]" value="efectivo" 
                                               <?php echo in_array('efectivo', $saved_payment_methods) ? 'checked' : ''; ?>
                                               class="w-4 h-4 text-green-600 rounded focus:ring-green-500">
                                        <span class="ml-2 font-medium text-sm">Efectivo</span>
                                    </label>
                                    <label class="flex items-center p-3 border-2 rounded-lg cursor-pointer transition <?php echo in_array('tarjeta', $saved_payment_methods) ? 'border-blue-500 bg-blue-50' : 'border-gray-300 hover:border-gray-400'; ?>">
                                        <input type="checkbox" name="payment_methods[]" value="tarjeta" 
                                               <?php echo in_array('tarjeta', $saved_payment_methods) ? 'checked' : ''; ?>
                                               class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500">
                                        <span class="ml-2 font-medium text-sm">Tarjeta</span>
                                    </label>
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Debe seleccionar al menos un medio de pago.</p>
                            </div>
                        </div>

                        <div class="flex justify-end pt-4 border-t">
                            <button type="submit" class="btn-primary px-6 py-2.5 rounded-lg font-medium">
                                <i class="fas fa-save mr-2"></i>Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Change Password Section -->
                <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6">
                    <h2 class="text-lg sm:text-xl font-semibold text-gray-900 mb-4 sm:mb-6">
                        <i class="fas fa-key mr-2 text-blue-600"></i>Cambiar Contraseña
                    </h2>

                    <?php if (!empty($password_errors)): ?>
                        <div class="mb-4 sm:mb-6 alert-danger">
                            <i class="fas fa-exclamation-circle"></i>
                            <div class="flex-1">
                                <p class="font-semibold mb-2">Por favor corrige los siguientes errores:</p>
                                <ul class="list-disc list-inside space-y-1 text-sm">
                                    <?php foreach ($password_errors as $error): ?>
                                        <li><?php echo htmlspecialchars($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    <?php endif; ?>

                    <form action="<?php echo BASE_URL; ?>public/index.php?action=change-password" method="POST" class="space-y-4" data-validate="true" data-validate-on-input="true">
                        <div>
                            <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">
                                Contraseña Actual *
                            </label>
                            <div class="relative">
                                <input id="current_password" name="current_password" type="password" required 
                                       class="w-full px-4 py-2 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <span class="absolute right-3 top-2.5 toggle-password cursor-pointer text-gray-400 hover:text-gray-600">
                                    <i class="fas fa-eye"></i>
                                </span>
                            </div>
                        </div>

                        <div>
                            <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1" data-label="Nueva Contraseña">
                                Nueva Contraseña *
                            </label>
                            <div class="relative">
                                <input id="new_password" name="new_password" type="password" required 
                                       data-validate-password="true"
                                       data-min-length="8"
                                       class="w-full px-4 py-2 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <span class="absolute right-3 top-2.5 toggle-password cursor-pointer text-gray-400 hover:text-gray-600">
                                    <i class="fas fa-eye"></i>
                                </span>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">
                                Mínimo 8 caracteres, debe incluir mayúsculas, números y caracteres especiales
                            </p>
                        </div>

                        <div>
                            <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1" data-label="Confirmar Nueva Contraseña">
                                Confirmar Nueva Contraseña *
                            </label>
                            <div class="relative">
                                <input id="confirm_password" name="confirm_password" type="password" required 
                                       data-confirm-password="new_password"
                                       class="w-full px-4 py-2 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <span class="absolute right-3 top-2.5 toggle-password cursor-pointer text-gray-400 hover:text-gray-600">
                                    <i class="fas fa-eye"></i>
                                </span>
                            </div>
                        </div>

                        <div class="flex justify-end pt-4 border-t">
                            <button type="submit" class="btn-primary px-6 py-2.5 rounded-lg font-medium">
                                <i class="fas fa-key mr-2"></i>Actualizar Contraseña
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Store initial goal to detect changes
let initialGoal = null;

function hasDataInFields(goalType) {
    if (goalType === 'ahorrar') {
        const savingsGoal = document.getElementById('savings_goal').value;
        const savingsDeadline = document.getElementById('savings_deadline').value;
        return savingsGoal || savingsDeadline;
    } else if (goalType === 'pagar_deudas') {
        const debtAmount = document.getElementById('debt_amount').value;
        const debtDeadline = document.getElementById('debt_deadline').value;
        const monthlyPayment = document.getElementById('monthly_payment').value;
        const debtCount = document.getElementById('debt_count').value;
        return debtAmount || debtDeadline || monthlyPayment || debtCount;
    } else if (goalType === 'otro') {
        const goalDescription = document.getElementById('goal_description').value;
        return goalDescription && goalDescription.trim().length > 0;
    }
    return false;
}

function clearGoalFields(goalType) {
    if (goalType === 'ahorrar') {
        document.getElementById('savings_goal').value = '';
        document.getElementById('savings_deadline').value = '';
    } else if (goalType === 'pagar_deudas') {
        document.getElementById('debt_amount').value = '';
        document.getElementById('debt_deadline').value = '';
        document.getElementById('monthly_payment').value = '';
        document.getElementById('debt_count').value = '';
    } else if (goalType === 'otro') {
        document.getElementById('goal_description').value = '';
    }
}

function showGoalChangeWarning(oldGoal, newGoal, callback) {
    const hasOldData = hasDataInFields(oldGoal);
    
    if (hasOldData) {
        const goalNames = {
            'ahorrar': 'Ahorrar',
            'pagar_deudas': 'Pagar Deudas',
            'controlar_gastos': 'Controlar Gastos',
            'otro': 'Otro'
        };
        
        if (confirm(`⚠️ Advertencia: Tienes datos guardados para el objetivo "${goalNames[oldGoal]}".\n\nAl cambiar a "${goalNames[newGoal]}", estos datos se ocultarán pero no se eliminarán.\n\n¿Deseas continuar?`)) {
            callback();
        } else {
            // Revert to old goal
            document.getElementById('financial_goal').value = oldGoal;
            return false;
        }
    } else {
        callback();
    }
    return true;
}

function updateSpendingLimitForGoal(goal) {
    const monthlyIncome = parseFloat(document.getElementById('monthly_income').value) || 0;
    const spendingLimitInput = document.getElementById('spending_limit');
    const infoText = document.getElementById('spending_limit_info');
    const currency = document.getElementById('currency').value;
    
    if (monthlyIncome <= 0) {
        return;
    }
    
    let recommendedLimit = 0;
    let message = '';
    let messageClass = 'mt-1 text-xs text-gray-500';
    
    switch(goal) {
        case 'ahorrar':
            // Para ahorrar, recomendar 70% del ingreso (dejar 30% para ahorro)
            recommendedLimit = monthlyIncome * 0.70;
            message = `💡 Límite recomendado para ahorrar: ${recommendedLimit.toFixed(2)} ${currency} (70% del ingreso, dejando 30% para ahorro)`;
            messageClass = 'mt-1 text-xs text-blue-600';
            break;
        case 'pagar_deudas':
            // Para pagar deudas, recomendar 60% del ingreso (dejar 40% para pagos)
            recommendedLimit = monthlyIncome * 0.60;
            message = `💡 Límite recomendado para pagar deudas: ${recommendedLimit.toFixed(2)} ${currency} (60% del ingreso, dejando 40% para pagos)`;
            messageClass = 'mt-1 text-xs text-blue-600';
            break;
        case 'controlar_gastos':
            // Para controlar gastos, recomendar 80% del ingreso
            recommendedLimit = monthlyIncome * 0.80;
            message = `💡 Límite recomendado para controlar gastos: ${recommendedLimit.toFixed(2)} ${currency} (80% del ingreso)`;
            messageClass = 'mt-1 text-xs text-blue-600';
            break;
        case 'otro':
            // Para otros objetivos, recomendar 75% del ingreso
            recommendedLimit = monthlyIncome * 0.75;
            message = `💡 Límite recomendado: ${recommendedLimit.toFixed(2)} ${currency} (75% del ingreso)`;
            messageClass = 'mt-1 text-xs text-blue-600';
            break;
    }
    
    // Only suggest if current limit is very different (more than 20% difference)
    const currentLimit = parseFloat(spendingLimitInput.value) || 0;
    const difference = Math.abs(currentLimit - recommendedLimit);
    const percentageDiff = (difference / monthlyIncome) * 100;
    
    if (percentageDiff > 20) {
        infoText.textContent = message;
        infoText.className = messageClass;
        
        // Show suggestion button
        if (!document.getElementById('apply-limit-suggestion')) {
            const suggestionBtn = document.createElement('button');
            suggestionBtn.id = 'apply-limit-suggestion';
            suggestionBtn.type = 'button';
            suggestionBtn.className = 'mt-2 text-xs text-blue-600 hover:text-blue-800 underline';
            suggestionBtn.textContent = 'Aplicar límite sugerido';
            suggestionBtn.onclick = function() {
                spendingLimitInput.value = recommendedLimit.toFixed(2);
                updateCalculations();
                this.remove();
            };
            infoText.parentElement.appendChild(suggestionBtn);
        }
    }
}

function toggleGoalFields() {
    const goal = document.getElementById('financial_goal').value;
    const savingsFields = document.getElementById('savings-fields');
    const debtFields = document.getElementById('debt-fields');
    const otherFields = document.getElementById('other-goal-fields');
    
    // Check if goal actually changed
    if (goal === initialGoal) {
        return;
    }
    
    // Show warning if there's data in the old goal fields
    const oldGoal = initialGoal;
    const goalChanged = showGoalChangeWarning(oldGoal, goal, function() {
        // Hide all fields with fade out
        [savingsFields, debtFields, otherFields].forEach(field => {
            if (!field.classList.contains('hidden')) {
                field.style.opacity = '0';
                field.style.transform = 'translateY(-10px)';
                setTimeout(() => {
                    field.classList.add('hidden');
                    field.style.opacity = '';
                    field.style.transform = '';
                }, 200);
            }
        });
        
        // Show relevant fields with fade in
        setTimeout(() => {
            let targetField = null;
            if (goal === 'ahorrar') {
                targetField = savingsFields;
            } else if (goal === 'pagar_deudas') {
                targetField = debtFields;
            } else if (goal === 'otro') {
                targetField = otherFields;
            }
            
            if (targetField) {
                targetField.classList.remove('hidden');
                targetField.style.opacity = '0';
                targetField.style.transform = 'translateY(-10px)';
                setTimeout(() => {
                    targetField.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                    targetField.style.opacity = '1';
                    targetField.style.transform = 'translateY(0)';
                }, 10);
            }
            
            // Update spending limit suggestion
            updateSpendingLimitForGoal(goal);
            
            // Update initial goal
            initialGoal = goal;
        }, 200);
    });
    
    if (!goalChanged) {
        return;
    }
}

function updateCalculations() {
    const monthlyIncome = parseFloat(document.getElementById('monthly_income').value) || 0;
    const spendingLimit = parseFloat(document.getElementById('spending_limit').value) || 0;
    const currency = document.getElementById('currency').value;
    const infoText = document.getElementById('spending_limit_info');
    
    // Remove suggestion button if exists
    const suggestionBtn = document.getElementById('apply-limit-suggestion');
    if (suggestionBtn) {
        suggestionBtn.remove();
    }
    
    if (monthlyIncome > 0 && spendingLimit > 0) {
        const percentage = (spendingLimit / monthlyIncome) * 100;
        const available = monthlyIncome - spendingLimit;
        
        if (percentage > 90) {
            infoText.textContent = `⚠️ Límite muy alto (${percentage.toFixed(1)}% del ingreso)`;
            infoText.className = 'mt-1 text-xs text-red-600';
        } else if (percentage > 70) {
            infoText.textContent = `⚠️ Límite alto (${percentage.toFixed(1)}% del ingreso)`;
            infoText.className = 'mt-1 text-xs text-amber-600';
        } else {
            infoText.textContent = `✓ ${available.toFixed(2)} ${currency} disponibles después del límite`;
            infoText.className = 'mt-1 text-xs text-green-600';
        }
    } else {
        infoText.textContent = 'El límite de gasto se ajusta según tu ingreso y objetivos.';
        infoText.className = 'mt-1 text-xs text-gray-500';
    }
}

// Helper function to format currency
function formatCurrencyProfile(amount, currency = 'MXN') {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: currency
    }).format(amount);
}

// Helper function to calculate months between dates
function calculateMonthsBetweenProfile(startDate, endDate) {
    const start = new Date(startDate);
    const end = new Date(endDate);
    
    if (start >= end) {
        return 1;
    }
    
    let years = end.getFullYear() - start.getFullYear();
    let months = end.getMonth() - start.getMonth();
    let days = end.getDate() - start.getDate();
    
    if (days < 0) {
        months--;
        const prevMonth = new Date(end.getFullYear(), end.getMonth(), 0);
        days += prevMonth.getDate();
    }
    
    if (months < 0) {
        years--;
        months += 12;
    }
    
    let totalMonths = (years * 12) + months;
    
    if (totalMonths === 0) {
        if (days > 0) {
            totalMonths = 1;
        }
    } else {
        if (days >= 15) {
            totalMonths += 1;
        }
    }
    
    return Math.max(1, totalMonths);
}

// Validate savings goal in real-time
function validateSavingsGoalProfile() {
    const savingsGoal = parseFloat(document.getElementById('savings_goal').value) || 0;
    const savingsDeadline = document.getElementById('savings_deadline').value;
    const monthlyIncome = parseFloat(document.getElementById('monthly_income').value) || 0;
    const currency = document.getElementById('currency').value;
    
    const infoBox = document.getElementById('savings_info_profile');
    const warningBox = document.getElementById('savings_warning_profile');
    const infoList = document.getElementById('savings_info_list_profile');
    const warningText = document.getElementById('savings_warning_text_profile');
    
    // Hide both initially
    infoBox.classList.add('hidden');
    warningBox.classList.add('hidden');
    infoList.innerHTML = '';
    
    if (savingsGoal <= 0 || monthlyIncome <= 0) {
        return;
    }
    
    const info = [];
    let hasWarning = false;
    let warningMessage = '';
    
    if (savingsDeadline) {
        const deadline = new Date(savingsDeadline);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        deadline.setHours(0, 0, 0, 0);
        
        if (deadline <= today) {
            warningMessage = 'La fecha límite debe ser una fecha futura';
            hasWarning = true;
        } else {
            const months = calculateMonthsBetweenProfile(today, deadline);
            const requiredMonthly = savingsGoal / months;
            const percentage = (requiredMonthly / monthlyIncome) * 100;
            
            info.push(`Tiempo disponible: ${months} mes${months > 1 ? 'es' : ''}`);
            info.push(`Ahorro mensual necesario: ${formatCurrencyProfile(requiredMonthly, currency)}`);
            info.push(`Porcentaje del ingreso: ${percentage.toFixed(1)}%`);
            
            if (requiredMonthly > monthlyIncome * 0.50) {
                warningMessage = `Para alcanzar tu meta en ${months} meses, necesitarías ahorrar ${formatCurrencyProfile(requiredMonthly, currency)} mensualmente (${percentage.toFixed(1)}% de tu ingreso). Esto puede ser difícil de mantener.`;
                hasWarning = true;
            } else if (requiredMonthly > monthlyIncome * 0.30) {
                warningMessage = `Necesitarás ahorrar ${formatCurrencyProfile(requiredMonthly, currency)} mensualmente (${percentage.toFixed(1)}% de tu ingreso). Asegúrate de que esto sea sostenible.`;
                hasWarning = true;
            }
            
            if (months > 120) {
                warningMessage = (warningMessage ? warningMessage + ' ' : '') + 'La fecha límite es muy lejana. Considera establecer una meta más cercana para mantener la motivación.';
                hasWarning = true;
            }
        }
    } else {
        const recommendedSavings = monthlyIncome * 0.25;
        info.push(`Ahorro mensual recomendado: ${formatCurrencyProfile(recommendedSavings, currency)} (25% del ingreso)`);
        info.push(`Tiempo estimado para alcanzar la meta: ${Math.ceil(savingsGoal / recommendedSavings)} meses`);
    }
    
    if (info.length > 0) {
        infoList.innerHTML = info.map(item => `<li>• ${item}</li>`).join('');
        infoBox.classList.remove('hidden');
    }
    
    if (hasWarning) {
        warningText.textContent = warningMessage;
        warningBox.classList.remove('hidden');
    }
}

// Validate debt goal in real-time
function validateDebtGoalProfile() {
    const debtAmount = parseFloat(document.getElementById('debt_amount').value) || 0;
    const debtDeadline = document.getElementById('debt_deadline').value;
    const monthlyPayment = parseFloat(document.getElementById('monthly_payment').value) || 0;
    const debtCount = parseInt(document.getElementById('debt_count').value) || 0;
    const monthlyIncome = parseFloat(document.getElementById('monthly_income').value) || 0;
    const currency = document.getElementById('currency').value;
    
    const infoBox = document.getElementById('debt_info_profile');
    const warningBox = document.getElementById('debt_warning_profile');
    const infoList = document.getElementById('debt_info_list_profile');
    const warningText = document.getElementById('debt_warning_text_profile');
    
    // Hide both initially
    infoBox.classList.add('hidden');
    warningBox.classList.add('hidden');
    infoList.innerHTML = '';
    
    if (debtAmount <= 0 || monthlyIncome <= 0) {
        return;
    }
    
    const info = [];
    let hasWarning = false;
    let warningMessage = '';
    
    const annualIncome = monthlyIncome * 12;
    const debtRatio = debtAmount / annualIncome;
    
    info.push(`Ratio deuda/ingreso anual: ${(debtRatio * 100).toFixed(1)}%`);
    
    if (debtCount > 0) {
        const avgDebtPerLoan = debtAmount / debtCount;
        info.push(`Número de deudas: ${debtCount}`);
        info.push(`Deuda promedio por préstamo: ${formatCurrencyProfile(avgDebtPerLoan, currency)}`);
    }
    
    if (debtRatio > 5) {
        warningMessage = 'Tu deuda es muy alta comparada con tu ingreso anual. Considera buscar asesoría financiera profesional.';
        hasWarning = true;
    }
    
    if (debtDeadline) {
        const deadline = new Date(debtDeadline);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        deadline.setHours(0, 0, 0, 0);
        
        if (deadline <= today) {
            warningMessage = 'La fecha objetivo debe ser una fecha futura';
            hasWarning = true;
        } else {
            const months = calculateMonthsBetweenProfile(today, deadline);
            const requiredMonthlyPayment = debtAmount / months;
            const paymentPercentage = (requiredMonthlyPayment / monthlyIncome) * 100;
            
            info.push(`Fecha objetivo: ${new Date(debtDeadline).toLocaleDateString('es-MX')}`);
            info.push(`Tiempo disponible: ${months} mes${months > 1 ? 'es' : ''}`);
            info.push(`Pago mensual necesario: ${formatCurrencyProfile(requiredMonthlyPayment, currency)}`);
            info.push(`Porcentaje del ingreso: ${paymentPercentage.toFixed(1)}%`);
            
            if (requiredMonthlyPayment > monthlyIncome * 0.50) {
                warningMessage = (warningMessage ? warningMessage + ' ' : '') + 
                    `Para pagar tu deuda en ${months} meses, necesitarías pagar ${formatCurrencyProfile(requiredMonthlyPayment, currency)} mensualmente (${paymentPercentage.toFixed(1)}% de tu ingreso). Esto puede ser difícil de mantener.`;
                hasWarning = true;
            } else if (requiredMonthlyPayment > monthlyIncome * 0.30) {
                warningMessage = (warningMessage ? warningMessage + ' ' : '') + 
                    `Necesitarás pagar ${formatCurrencyProfile(requiredMonthlyPayment, currency)} mensualmente (${paymentPercentage.toFixed(1)}% de tu ingreso). Asegúrate de que esto sea sostenible.`;
                hasWarning = true;
            }
            
            if (monthlyPayment > 0) {
                const monthsToPay = Math.ceil(debtAmount / monthlyPayment);
                if (monthsToPay > months) {
                    warningMessage = (warningMessage ? warningMessage + ' ' : '') + 
                        `Con un pago mensual de ${formatCurrencyProfile(monthlyPayment, currency)}, tardarías ${monthsToPay} meses en pagar, que es más que tu fecha objetivo (${months} meses). Considera aumentar el pago mensual.`;
                    hasWarning = true;
                } else if (monthsToPay <= months) {
                    info.push(`Con tu pago mensual de ${formatCurrencyProfile(monthlyPayment, currency)}, pagarás la deuda en aproximadamente ${monthsToPay} mes${monthsToPay > 1 ? 'es' : ''}`);
                }
            }
        }
    } else if (monthlyPayment > 0) {
        const monthsToPay = Math.ceil(debtAmount / monthlyPayment);
        const paymentPercentage = (monthlyPayment / monthlyIncome) * 100;
        
        info.push(`Pago mensual: ${formatCurrencyProfile(monthlyPayment, currency)}`);
        info.push(`Porcentaje del ingreso: ${paymentPercentage.toFixed(1)}%`);
        info.push(`Tiempo estimado para pagar: ${monthsToPay} mes${monthsToPay > 1 ? 'es' : ''} (${(monthsToPay / 12).toFixed(1)} años)`);
        
        if (monthlyPayment > monthlyIncome * 0.50) {
            warningMessage = (warningMessage ? warningMessage + ' ' : '') + 
                `El pago mensual de ${formatCurrencyProfile(monthlyPayment, currency)} representa ${paymentPercentage.toFixed(1)}% de tu ingreso. Asegúrate de que esto sea sostenible.`;
            hasWarning = true;
        }
        
        if (monthsToPay > 120) {
            warningMessage = (warningMessage ? warningMessage + ' ' : '') + 
                `Con este pago mensual, tardarías aproximadamente ${monthsToPay} meses (${(monthsToPay / 12).toFixed(1)} años) en pagar tu deuda. Considera aumentar el pago mensual.`;
            hasWarning = true;
        }
    }
    
    if (info.length > 0) {
        infoList.innerHTML = info.map(item => `<li>• ${item}</li>`).join('');
        infoBox.classList.remove('hidden');
    }
    
    if (hasWarning) {
        warningText.textContent = warningMessage;
        warningBox.classList.remove('hidden');
    }
}

// Validate other goal in real-time
function validateOtherGoalProfile() {
    const description = document.getElementById('goal_description').value.trim();
    const goalDescriptionField = document.getElementById('goal_description');
    
    // Remove previous validation styling
    goalDescriptionField.classList.remove('border-red-500', 'border-yellow-500', 'border-green-500');
    
    if (description.length === 0) {
        goalDescriptionField.classList.add('border-red-500');
        return false;
    } else if (description.length < 10) {
        goalDescriptionField.classList.add('border-yellow-500');
        return false;
    } else {
        goalDescriptionField.classList.add('border-green-500');
        return true;
    }
}

// Validate goal fields based on current goal
function validateGoalFieldsProfile() {
    const goal = document.getElementById('financial_goal').value;
    
    // Hide all info/warning boxes first
    document.getElementById('savings_info_profile').classList.add('hidden');
    document.getElementById('savings_warning_profile').classList.add('hidden');
    document.getElementById('debt_info_profile').classList.add('hidden');
    document.getElementById('debt_warning_profile').classList.add('hidden');
    
    // Validate based on goal
    setTimeout(() => {
        if (goal === 'ahorrar') {
            validateSavingsGoalProfile();
        } else if (goal === 'pagar_deudas') {
            validateDebtGoalProfile();
        } else if (goal === 'otro') {
            validateOtherGoalProfile();
        }
    }, 300); // Wait for fields to be shown
}

// Debounce function to limit function calls
function debounceProfile(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

document.addEventListener('DOMContentLoaded', function() {
    // El toggle-password se maneja en main.js, no es necesario duplicarlo aquí
    
    // Initialize goal fields based on current selection (without animation on load)
    const goal = document.getElementById('financial_goal').value;
    initialGoal = goal; // Set initial goal
    
    const savingsFields = document.getElementById('savings-fields');
    const debtFields = document.getElementById('debt-fields');
    const otherFields = document.getElementById('other-goal-fields');
    
    savingsFields.classList.add('hidden');
    debtFields.classList.add('hidden');
    otherFields.classList.add('hidden');
    
    if (goal === 'ahorrar') {
        savingsFields.classList.remove('hidden');
    } else if (goal === 'pagar_deudas') {
        debtFields.classList.remove('hidden');
    } else if (goal === 'otro') {
        otherFields.classList.remove('hidden');
    }
    
    // Update spending limit suggestion for current goal
    updateSpendingLimitForGoal(goal);
    
    // Update calculations after a brief delay to ensure all fields are loaded
    setTimeout(function() {
        updateCalculations();
        // Run initial validations
        validateGoalFieldsProfile();
    }, 300);
    
    // Listen for monthly income changes to update suggestions and validations
    document.getElementById('monthly_income').addEventListener('input', debounceProfile(function() {
        const currentGoal = document.getElementById('financial_goal').value;
        updateSpendingLimitForGoal(currentGoal);
        updateCalculations();
        // Re-validate goal fields when income changes
        if (currentGoal === 'ahorrar') {
            validateSavingsGoalProfile();
        } else if (currentGoal === 'pagar_deudas') {
            validateDebtGoalProfile();
        }
    }, 300));
    
    // Listen for currency changes to update validations
    document.getElementById('currency').addEventListener('change', function() {
        const currentGoal = document.getElementById('financial_goal').value;
        if (currentGoal === 'ahorrar') {
            validateSavingsGoalProfile();
        } else if (currentGoal === 'pagar_deudas') {
            validateDebtGoalProfile();
        }
    });
    
    // Listen for savings goal field changes
    const savingsGoalInput = document.getElementById('savings_goal');
    if (savingsGoalInput) {
        savingsGoalInput.addEventListener('input', debounceProfile(validateSavingsGoalProfile, 300));
    }
    
    const savingsDeadlineInput = document.getElementById('savings_deadline');
    if (savingsDeadlineInput) {
        savingsDeadlineInput.addEventListener('change', validateSavingsGoalProfile);
    }
    
    // Listen for debt field changes
    const debtAmountInput = document.getElementById('debt_amount');
    if (debtAmountInput) {
        debtAmountInput.addEventListener('input', debounceProfile(validateDebtGoalProfile, 300));
    }
    
    const debtDeadlineInput = document.getElementById('debt_deadline');
    if (debtDeadlineInput) {
        debtDeadlineInput.addEventListener('change', validateDebtGoalProfile);
    }
    
    const monthlyPaymentInput = document.getElementById('monthly_payment');
    if (monthlyPaymentInput) {
        monthlyPaymentInput.addEventListener('input', debounceProfile(validateDebtGoalProfile, 300));
    }
    
    const debtCountInput = document.getElementById('debt_count');
    if (debtCountInput) {
        debtCountInput.addEventListener('input', debounceProfile(validateDebtGoalProfile, 300));
    }
    
    // Listen for other goal description changes
    const goalDescriptionInput = document.getElementById('goal_description');
    if (goalDescriptionInput) {
        goalDescriptionInput.addEventListener('input', debounceProfile(validateOtherGoalProfile, 300));
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
