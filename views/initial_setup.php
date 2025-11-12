<?php
$page_title = 'Configuración Inicial - Control de Gastos';
require_once __DIR__ . '/../includes/header.php';

$errors = $_SESSION['setup_errors'] ?? [];
$old_data = $_SESSION['setup_data'] ?? [];
unset($_SESSION['setup_errors'], $_SESSION['setup_data']);
?>

<div class="min-h-screen blue-gradient py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-2xl shadow-2xl p-8">
            <div class="text-center mb-8">
                <i class="fas fa-cog text-6xl text-blue-600 mb-4"></i>
                <h2 class="text-3xl font-extrabold text-gray-900">
                    Configuración Inicial
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    Configura tu perfil financiero para comenzar
                </p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="mb-6 alert-danger">
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

            <form action="<?php echo BASE_URL; ?>public/index.php?action=initial-setup" method="POST" class="space-y-8" data-validate="false" data-no-validate="true">
                <!-- Income and Currency -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="monthly_income" class="block text-sm font-medium text-gray-700">
                            <i class="fas fa-dollar-sign mr-2 text-green-600"></i>Ingreso Mensual *
                        </label>
                        <input id="monthly_income" name="monthly_income" type="number" step="0.01" min="0" required 
                               value="<?php echo htmlspecialchars($old_data['monthly_income'] ?? ''); ?>"
                               class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="15000.00"
                               oninput="updateAutoLimit();">
                    </div>

                    <div>
                        <label for="currency" class="block text-sm font-medium text-gray-700">
                            <i class="fas fa-money-bill mr-2 text-green-600"></i>Moneda *
                        </label>
                        <select id="currency" name="currency" required 
                                class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="MXN" <?php echo ($old_data['currency'] ?? 'MXN') === 'MXN' ? 'selected' : ''; ?>>MXN - Peso Mexicano</option>
                            <option value="USD" <?php echo ($old_data['currency'] ?? '') === 'USD' ? 'selected' : ''; ?>>USD - Dólar Estadounidense</option>
                            <option value="EUR" <?php echo ($old_data['currency'] ?? '') === 'EUR' ? 'selected' : ''; ?>>EUR - Euro</option>
                        </select>
                    </div>
                </div>

                <!-- Start Date -->
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700">
                        <i class="fas fa-calendar mr-2 text-blue-600"></i>Fecha de Inicio del Control *
                    </label>
                    <input id="start_date" name="start_date" type="date" required 
                           value="<?php echo htmlspecialchars($old_data['start_date'] ?? date('Y-m-d')); ?>"
                           class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <!-- Payment Methods -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        <i class="fas fa-credit-card mr-2 text-blue-600"></i>Medios de Pago *
                    </label>
                    <p class="text-xs text-gray-600 mb-4">Selecciona uno o más medios de pago que utilizas habitualmente</p>
                    
                    <!-- Payment Method Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4" id="payment_methods_container">
                        <label class="payment-card cursor-pointer p-4 border-2 rounded-lg transition-all duration-300 hover:shadow-md relative <?php echo in_array('efectivo', $old_data['payment_methods'] ?? []) ? 'border-green-500 bg-green-50' : 'border-gray-300 hover:border-green-300'; ?>">
                            <input type="checkbox" name="payment_methods[]" value="efectivo" 
                                   <?php echo in_array('efectivo', $old_data['payment_methods'] ?? []) ? 'checked' : ''; ?>
                                   class="payment-checkbox absolute opacity-0 cursor-pointer" style="top: 0; left: 0; width: 100%; height: 100%; z-index: 1;">
                            <div class="flex items-start relative pointer-events-none" style="z-index: 0;">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center">
                                        <i class="fas fa-money-bill-wave text-2xl text-green-600"></i>
                                    </div>
                                </div>
                                <div class="ml-4 flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900">Efectivo</h3>
                                    <p class="text-sm text-gray-600 mt-1">Pagos en moneda física, billetes y monedas</p>
                                </div>
                                <div class="flex-shrink-0 ml-2">
                                    <div class="payment-checkmark <?php echo in_array('efectivo', $old_data['payment_methods'] ?? []) ? '' : 'hidden'; ?> w-6 h-6 rounded-full bg-green-500 flex items-center justify-center">
                                        <i class="fas fa-check text-white text-xs"></i>
                                    </div>
                                </div>
                            </div>
                        </label>
                        
                        <label class="payment-card cursor-pointer p-4 border-2 rounded-lg transition-all duration-300 hover:shadow-md relative <?php echo in_array('tarjeta', $old_data['payment_methods'] ?? []) ? 'border-blue-500 bg-blue-50' : 'border-gray-300 hover:border-blue-300'; ?>">
                            <input type="checkbox" name="payment_methods[]" value="tarjeta" 
                                   <?php echo in_array('tarjeta', $old_data['payment_methods'] ?? []) ? 'checked' : ''; ?>
                                   class="payment-checkbox absolute opacity-0 cursor-pointer" style="top: 0; left: 0; width: 100%; height: 100%; z-index: 1;">
                            <div class="flex items-start relative pointer-events-none" style="z-index: 0;">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center">
                                        <i class="fas fa-credit-card text-2xl text-blue-600"></i>
                                    </div>
                                </div>
                                <div class="ml-4 flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900">Tarjeta</h3>
                                    <p class="text-sm text-gray-600 mt-1">Tarjetas de débito, crédito o prepago</p>
                                </div>
                                <div class="flex-shrink-0 ml-2">
                                    <div class="payment-checkmark <?php echo in_array('tarjeta', $old_data['payment_methods'] ?? []) ? '' : 'hidden'; ?> w-6 h-6 rounded-full bg-blue-500 flex items-center justify-center">
                                        <i class="fas fa-check text-white text-xs"></i>
                                    </div>
                                </div>
                            </div>
                        </label>
                    </div>
                    
                    <!-- Validation message -->
                    <div id="payment_methods_error" class="hidden mt-2 p-3 bg-red-50 border border-red-200 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-circle text-red-600 mr-2"></i>
                            <p class="text-sm text-red-800">Debes seleccionar al menos un medio de pago</p>
                        </div>
                    </div>
                </div>

                <!-- Financial Goal -->
                <div>
                    <label for="financial_goal" class="block text-sm font-medium text-gray-700 mb-3">
                        <i class="fas fa-bullseye mr-2 text-blue-600"></i>Objetivo Financiero Principal *
                    </label>
                    
                    <!-- Goal Selection Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4" id="goal_cards_container">
                        <label class="goal-card cursor-pointer p-4 border-2 rounded-lg transition-all duration-300 hover:shadow-md <?php echo ($old_data['financial_goal'] ?? '') === 'ahorrar' ? 'border-blue-500 bg-blue-50' : 'border-gray-300 hover:border-blue-300'; ?>">
                            <input type="radio" name="financial_goal" value="ahorrar" 
                                   <?php echo ($old_data['financial_goal'] ?? '') === 'ahorrar' ? 'checked' : ''; ?>
                                   class="hidden goal-radio"
                                   onchange="selectGoalCard('ahorrar'); toggleGoalFields();">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 rounded-full bg-pink-100 flex items-center justify-center">
                                        <i class="fas fa-piggy-bank text-2xl text-pink-600"></i>
                                    </div>
                                </div>
                                <div class="ml-4 flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900">Ahorrar</h3>
                                    <p class="text-sm text-gray-600 mt-1">Establece metas de ahorro y alcanza tus objetivos financieros</p>
                                </div>
                            </div>
                        </label>
                        
                        <label class="goal-card cursor-pointer p-4 border-2 rounded-lg transition-all duration-300 hover:shadow-md <?php echo ($old_data['financial_goal'] ?? '') === 'pagar_deudas' ? 'border-blue-500 bg-blue-50' : 'border-gray-300 hover:border-blue-300'; ?>">
                            <input type="radio" name="financial_goal" value="pagar_deudas" 
                                   <?php echo ($old_data['financial_goal'] ?? '') === 'pagar_deudas' ? 'checked' : ''; ?>
                                   class="hidden goal-radio"
                                   onchange="selectGoalCard('pagar_deudas'); toggleGoalFields();">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center">
                                        <i class="fas fa-hand-holding-usd text-2xl text-red-600"></i>
                                    </div>
                                </div>
                                <div class="ml-4 flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900">Pagar Deudas</h3>
                                    <p class="text-sm text-gray-600 mt-1">Gestiona y reduce tus deudas de manera efectiva</p>
                                </div>
                            </div>
                        </label>
                        
                        <label class="goal-card cursor-pointer p-4 border-2 rounded-lg transition-all duration-300 hover:shadow-md <?php echo ($old_data['financial_goal'] ?? '') === 'controlar_gastos' ? 'border-blue-500 bg-blue-50' : 'border-gray-300 hover:border-blue-300'; ?>">
                            <input type="radio" name="financial_goal" value="controlar_gastos" 
                                   <?php echo ($old_data['financial_goal'] ?? '') === 'controlar_gastos' ? 'checked' : ''; ?>
                                   class="hidden goal-radio"
                                   onchange="selectGoalCard('controlar_gastos'); toggleGoalFields();">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center">
                                        <i class="fas fa-chart-line text-2xl text-green-600"></i>
                                    </div>
                                </div>
                                <div class="ml-4 flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900">Controlar Gastos</h3>
                                    <p class="text-sm text-gray-600 mt-1">Mantén un control estricto sobre tus gastos mensuales</p>
                                </div>
                            </div>
                        </label>
                        
                        <label class="goal-card cursor-pointer p-4 border-2 rounded-lg transition-all duration-300 hover:shadow-md <?php echo ($old_data['financial_goal'] ?? '') === 'otro' ? 'border-blue-500 bg-blue-50' : 'border-gray-300 hover:border-blue-300'; ?>">
                            <input type="radio" name="financial_goal" value="otro" 
                                   <?php echo ($old_data['financial_goal'] ?? '') === 'otro' ? 'checked' : ''; ?>
                                   class="hidden goal-radio"
                                   onchange="selectGoalCard('otro'); toggleGoalFields();">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 rounded-full bg-purple-100 flex items-center justify-center">
                                        <i class="fas fa-edit text-2xl text-purple-600"></i>
                                    </div>
                                </div>
                                <div class="ml-4 flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900">Otro</h3>
                                    <p class="text-sm text-gray-600 mt-1">Define tu propio objetivo financiero personalizado</p>
                                </div>
                            </div>
                        </label>
                    </div>
                    
                    <!-- Hidden select for form validation -->
                    <select id="financial_goal_select" name="financial_goal" required 
                            class="hidden"
                            onchange="toggleGoalFields()">
                        <option value="">Selecciona un objetivo</option>
                        <option value="ahorrar" <?php echo ($old_data['financial_goal'] ?? '') === 'ahorrar' ? 'selected' : ''; ?>>Ahorrar</option>
                        <option value="pagar_deudas" <?php echo ($old_data['financial_goal'] ?? '') === 'pagar_deudas' ? 'selected' : ''; ?>>Pagar Deudas</option>
                        <option value="controlar_gastos" <?php echo ($old_data['financial_goal'] ?? '') === 'controlar_gastos' ? 'selected' : ''; ?>>Controlar Gastos</option>
                        <option value="otro" <?php echo ($old_data['financial_goal'] ?? '') === 'otro' ? 'selected' : ''; ?>>Otro</option>
                    </select>
                </div>

                <!-- Savings Goal Fields -->
                <div id="savings_fields" class="hidden goal-section transition-all duration-500 ease-in-out">
                    <div class="mb-4 p-4 bg-pink-50 border-l-4 border-pink-500 rounded">
                        <div class="flex items-center">
                            <i class="fas fa-piggy-bank text-pink-600 mr-2"></i>
                            <p class="text-sm font-medium text-pink-900">Configuración de Meta de Ahorro</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                        <div>
                            <label for="savings_goal" class="block text-sm font-medium text-gray-700">
                                <i class="fas fa-piggy-bank mr-2 text-pink-600"></i>Meta de Ahorro *
                            </label>
                            <input id="savings_goal" name="savings_goal" type="number" step="0.01" min="0" 
                                   value="<?php echo htmlspecialchars($old_data['savings_goal'] ?? ''); ?>"
                                   class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="10000.00"
                                   oninput="calculateRecommendedDeadline(); validateSavingsGoal();">
                            <p class="mt-1 text-xs text-gray-500">Ingresa el monto total que deseas ahorrar</p>
                        </div>
                        <div>
                            <label for="savings_deadline" class="block text-sm font-medium text-gray-700">
                                <i class="fas fa-calendar-check mr-2 text-pink-600"></i>Fecha Objetivo
                            </label>
                            <input id="savings_deadline" name="savings_deadline" type="date" 
                                   value="<?php echo htmlspecialchars($old_data['savings_deadline'] ?? ''); ?>"
                                   class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>"
                                   onchange="validateSavingsGoal()"
                                   onfocus="markDeadlineAsManuallySet()">
                            <p class="mt-1 text-xs text-gray-500">
                                <span id="deadline_recommendation_text" class="hidden text-blue-600 font-medium">
                                    <i class="fas fa-lightbulb mr-1"></i>Fecha recomendada establecida automáticamente
                                </span>
                                <span id="deadline_manual_text">Fecha límite para alcanzar tu meta (opcional)</span>
                            </p>
                        </div>
                    </div>
                    <div id="savings_info" class="hidden p-4 bg-blue-50 border border-blue-200 rounded-lg mb-4">
                        <div class="flex items-start">
                            <i class="fas fa-info-circle text-blue-600 mt-1 mr-2"></i>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-blue-900 mb-1">Información de tu meta de ahorro:</p>
                                <ul id="savings_info_list" class="text-xs text-blue-800 space-y-1"></ul>
                            </div>
                        </div>
                    </div>
                    <div id="savings_warning" class="hidden p-4 bg-yellow-50 border border-yellow-200 rounded-lg mb-4">
                        <div class="flex items-start">
                            <i class="fas fa-exclamation-triangle text-yellow-600 mt-1 mr-2"></i>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-yellow-900 mb-1">Advertencia:</p>
                                <p id="savings_warning_text" class="text-xs text-yellow-800"></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Debt Fields -->
                <div id="debt_fields" class="hidden goal-section transition-all duration-500 ease-in-out">
                    <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 rounded">
                        <div class="flex items-center">
                            <i class="fas fa-hand-holding-usd text-red-600 mr-2"></i>
                            <p class="text-sm font-medium text-red-900">Configuración de Pago de Deudas</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                        <div>
                            <label for="debt_amount" class="block text-sm font-medium text-gray-700">
                                <i class="fas fa-hand-holding-usd mr-2 text-red-600"></i>Monto Total de Deuda *
                            </label>
                            <input id="debt_amount" name="debt_amount" type="number" step="0.01" min="0" 
                                   value="<?php echo htmlspecialchars($old_data['debt_amount'] ?? ''); ?>"
                                   class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="50000.00"
                                   oninput="validateDebtGoal(); calculateRecommendedMonthlyPayment();">
                            <p class="mt-1 text-xs text-gray-500">Ingresa el monto total de todas tus deudas</p>
                        </div>
                        <div>
                            <label for="debt_count" class="block text-sm font-medium text-gray-700">
                                <i class="fas fa-list-ol mr-2 text-red-600"></i>Número de Deudas
                            </label>
                            <input id="debt_count" name="debt_count" type="number" step="1" min="1" 
                                   value="<?php echo htmlspecialchars($old_data['debt_count'] ?? ''); ?>"
                                   class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="3">
                            <p class="mt-1 text-xs text-gray-500">Cantidad de deudas que tienes (opcional)</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                        <div>
                            <label for="debt_deadline" class="block text-sm font-medium text-gray-700">
                                <i class="fas fa-calendar-check mr-2 text-red-600"></i>Fecha Objetivo para Pagar
                            </label>
                            <input id="debt_deadline" name="debt_deadline" type="date" 
                                   value="<?php echo htmlspecialchars($old_data['debt_deadline'] ?? ''); ?>"
                                   class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   min="<?php echo date('Y-m-d', strtotime('+1 month')); ?>"
                                   onchange="validateDebtGoal(); calculateRecommendedMonthlyPayment();">
                            <p class="mt-1 text-xs text-gray-500">
                                <span id="debt_deadline_recommendation_text" class="hidden text-blue-600 font-medium">
                                    <i class="fas fa-lightbulb mr-1"></i>Fecha recomendada establecida automáticamente
                                </span>
                                <span id="debt_deadline_manual_text">Fecha objetivo para pagar todas las deudas (opcional)</span>
                            </p>
                        </div>
                        <div>
                            <label for="monthly_payment" class="block text-sm font-medium text-gray-700">
                                <i class="fas fa-calendar-alt mr-2 text-red-600"></i>Pago Mensual
                            </label>
                            <input id="monthly_payment" name="monthly_payment" type="number" step="0.01" min="0" 
                                   value="<?php echo htmlspecialchars($old_data['monthly_payment'] ?? ''); ?>"
                                   class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="2500.00"
                                   oninput="validateDebtGoal(); calculateDebtDeadlineFromPayment();"
                                   onfocus="markMonthlyPaymentAsManuallySet()">
                            <p class="mt-1 text-xs text-gray-500">
                                <span id="monthly_payment_recommendation_text" class="hidden text-blue-600 font-medium">
                                    <i class="fas fa-lightbulb mr-1"></i>Pago mensual recomendado calculado automáticamente
                                </span>
                                <span id="monthly_payment_manual_text">Pago mensual que planeas realizar (opcional)</span>
                            </p>
                        </div>
                    </div>
                    <div id="debt_info" class="hidden p-4 bg-blue-50 border border-blue-200 rounded-lg mb-4">
                        <div class="flex items-start">
                            <i class="fas fa-info-circle text-blue-600 mt-1 mr-2"></i>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-blue-900 mb-1">Información sobre tu deuda:</p>
                                <ul id="debt_info_list" class="text-xs text-blue-800 space-y-1"></ul>
                            </div>
                        </div>
                    </div>
                    <div id="debt_warning" class="hidden p-4 bg-yellow-50 border border-yellow-200 rounded-lg mb-4">
                        <div class="flex items-start">
                            <i class="fas fa-exclamation-triangle text-yellow-600 mt-1 mr-2"></i>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-yellow-900 mb-1">Advertencia:</p>
                                <p id="debt_warning_text" class="text-xs text-yellow-800"></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Other Goal Description -->
                <div id="other_goal_fields" class="hidden goal-section transition-all duration-500 ease-in-out">
                    <div class="mb-4 p-4 bg-purple-50 border-l-4 border-purple-500 rounded">
                        <div class="flex items-center">
                            <i class="fas fa-edit text-purple-600 mr-2"></i>
                            <p class="text-sm font-medium text-purple-900">Describe tu Objetivo Personalizado</p>
                        </div>
                    </div>
                    <label for="goal_description" class="block text-sm font-medium text-gray-700">
                        <i class="fas fa-comment mr-2 text-blue-600"></i>Describe tu Objetivo *
                    </label>
                    <textarea id="goal_description" name="goal_description" rows="3" 
                              class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                              placeholder="Describe tu objetivo financiero..."
                              oninput="validateOtherGoal()"><?php echo htmlspecialchars($old_data['goal_description'] ?? ''); ?></textarea>
                    <p class="mt-1 text-xs text-gray-500">Proporciona detalles sobre tu objetivo financiero personalizado</p>
                </div>

                <!-- Spending Limit -->
                <div class="border-t pt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        <i class="fas fa-chart-line mr-2 text-blue-600"></i>Límite Mensual de Gasto *
                    </label>
                    <div class="space-y-4">
                        <label class="flex items-center p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-blue-500 transition">
                            <input type="radio" name="spending_limit_type" value="auto" checked 
                                   onclick="toggleSpendingLimit()"
                                   class="w-5 h-5 text-blue-600 focus:ring-2 focus:ring-blue-500">
                            <span class="ml-3 text-gray-700">
                                <strong>Calcular automáticamente</strong>
                                <p class="text-sm text-gray-500">El sistema calculará el límite basado en tu ingreso y objetivo</p>
                            </span>
                        </label>
                        <label class="flex items-center p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-blue-500 transition">
                            <input type="radio" name="spending_limit_type" value="manual" 
                                   onclick="toggleSpendingLimit()"
                                   class="w-5 h-5 text-blue-600 focus:ring-2 focus:ring-blue-500">
                            <span class="ml-3 text-gray-700">
                                <strong>Ingresar manualmente</strong>
                                <p class="text-sm text-gray-500">Define tu propio límite de gasto mensual</p>
                            </span>
                        </label>
                    </div>
                    <div id="manual_limit_field" class="hidden mt-4">
                        <input id="spending_limit" name="spending_limit" type="number" step="0.01" min="0" 
                               value="<?php echo htmlspecialchars($old_data['spending_limit'] ?? ''); ?>"
                               class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="12000.00"
                               oninput="validateSpendingLimit()">
                        <p class="mt-1 text-xs text-gray-500">El límite debe ser menor o igual a tu ingreso mensual</p>
                        <div id="limit_info" class="hidden mt-2 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                            <p id="limit_info_text" class="text-xs text-blue-800"></p>
                        </div>
                        <div id="limit_warning" class="hidden mt-2 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <p id="limit_warning_text" class="text-xs text-yellow-800"></p>
                        </div>
                    </div>
                    <div id="auto_limit_info" class="hidden mt-4 p-4 bg-green-50 border border-green-200 rounded-lg transition-all duration-300">
                        <div class="flex items-start">
                            <i class="fas fa-calculator text-green-600 mt-1 mr-2"></i>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-green-900 mb-1">Límite calculado automáticamente:</p>
                                <p id="auto_limit_value" class="text-lg font-bold text-green-700"></p>
                                <p id="auto_limit_details" class="text-xs text-green-800 mt-1"></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" 
                            class="btn-primary py-3 px-8 rounded-lg font-semibold text-lg">
                        <i class="fas fa-check mr-2"></i>Guardar y Continuar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function formatCurrency(amount, currency = 'MXN') {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: currency
    }).format(amount);
}

function getMonthlyIncome() {
    return parseFloat(document.getElementById('monthly_income').value) || 0;
}

function getCurrency() {
    return document.getElementById('currency').value || 'MXN';
}

function updatePaymentCardVisual(method, isChecked, skipValidation = false) {
    const checkbox = document.querySelector(`.payment-checkbox[value="${method}"]`);
    if (!checkbox) {
        return;
    }
    
    // Sync checkbox state - use actual checkbox state as source of truth
    const actualChecked = checkbox.checked;
    
    const card = checkbox.closest('.payment-card');
    if (!card) {
        return;
    }
    
    const checkmark = card.querySelector('.payment-checkmark');
    
    // Remove all possible border classes first
    card.classList.remove('border-gray-300', 'border-red-300', 'border-green-500', 'border-blue-500');
    card.classList.remove('bg-green-50', 'bg-blue-50');
    
    if (actualChecked) {
        if (method === 'efectivo') {
            card.classList.add('border-green-500', 'bg-green-50');
        } else if (method === 'tarjeta') {
            card.classList.add('border-blue-500', 'bg-blue-50');
        }
        if (checkmark) {
            checkmark.classList.remove('hidden');
        }
        // Add pulse animation only if this is a user interaction (not initial load)
        if (!skipValidation) {
            card.style.transform = 'scale(1.02)';
            setTimeout(() => {
                card.style.transform = '';
            }, 200);
        }
    } else {
        // Card is not selected
        card.classList.add('border-gray-300');
        if (checkmark) {
            checkmark.classList.add('hidden');
        }
    }
    
    if (!skipValidation) {
        // Check if there are any selected methods after this update
        const hasAnySelected = document.querySelectorAll('.payment-checkbox:checked').length > 0;
        // Only show error if no methods are selected
        validatePaymentMethods(!hasAnySelected);
    }
}

function validatePaymentMethods(showError = false) {
    const checkboxes = document.querySelectorAll('.payment-checkbox:checked');
    const errorDiv = document.getElementById('payment_methods_error');
    const hasSelection = checkboxes.length > 0;
    
    if (hasSelection) {
        // Hide error message - methods are selected
        if (errorDiv) {
            errorDiv.classList.add('hidden');
        }
        // Remove error styling from all cards that are not selected
        document.querySelectorAll('.payment-card').forEach(card => {
            const checkbox = card.querySelector('.payment-checkbox');
            // Remove error styling from unselected cards
            if (!checkbox || !checkbox.checked) {
                if (card.classList.contains('border-red-300')) {
                    card.classList.remove('border-red-300');
                    // Only add gray border if card doesn't have selection styling
                    if (!card.classList.contains('border-green-500') && 
                        !card.classList.contains('border-blue-500')) {
                        card.classList.add('border-gray-300');
                    }
                }
            }
        });
        return true;
    } else {
        // No methods selected
        if (showError) {
            // Show error message only if explicitly requested
            if (errorDiv) {
                errorDiv.classList.remove('hidden');
            }
            // Add error styling to all cards
            document.querySelectorAll('.payment-card').forEach(card => {
                // Only add error styling if card doesn't have selection styling
                if (!card.classList.contains('border-green-500') && 
                    !card.classList.contains('border-blue-500')) {
                    card.classList.add('border-red-300');
                    card.classList.remove('border-gray-300');
                }
            });
        } else {
            // Hide error message if not showing error
            if (errorDiv) {
                errorDiv.classList.add('hidden');
            }
            // Remove error styling but keep default styling
            document.querySelectorAll('.payment-card').forEach(card => {
                if (card.classList.contains('border-red-300')) {
                    card.classList.remove('border-red-300');
                    // Only add gray if no selection styling
                    if (!card.classList.contains('border-green-500') && 
                        !card.classList.contains('border-blue-500')) {
                        card.classList.add('border-gray-300');
                    }
                }
            });
        }
        return false;
    }
}

function selectGoalCard(goalValue) {
    // Update hidden select for form validation
    const select = document.getElementById('financial_goal_select');
    if (select) {
        select.value = goalValue;
    }
    
    // Update card visuals
    document.querySelectorAll('.goal-card').forEach(card => {
        card.classList.remove('border-blue-500', 'bg-blue-50');
        card.classList.add('border-gray-300');
    });
    
    // Highlight selected card
    const selectedRadio = document.querySelector(`.goal-radio[value="${goalValue}"]`);
    if (selectedRadio) {
        const selectedCard = selectedRadio.closest('.goal-card');
        if (selectedCard) {
            selectedCard.classList.remove('border-gray-300');
            selectedCard.classList.add('border-blue-500', 'bg-blue-50');
        }
    }
}

function getSelectedGoal() {
    const selectedRadio = document.querySelector('.goal-radio:checked');
    return selectedRadio ? selectedRadio.value : document.getElementById('financial_goal_select').value;
}

function toggleGoalFields() {
    const goal = getSelectedGoal();
    const savingsFields = document.getElementById('savings_fields');
    const debtFields = document.getElementById('debt_fields');
    const otherFields = document.getElementById('other_goal_fields');
    
    // Hide all fields with animation
    [savingsFields, debtFields, otherFields].forEach(field => {
        if (!field.classList.contains('hidden')) {
            field.style.opacity = '0';
            field.style.transform = 'translateY(-10px)';
            setTimeout(() => {
                field.classList.add('hidden');
            }, 300);
        }
    });
    
    // Hide all info/warning boxes
    hideAllInfoBoxes();
    
    // Show relevant fields with animation
    setTimeout(() => {
        if (goal === 'ahorrar') {
            savingsFields.classList.remove('hidden');
            setTimeout(() => {
                savingsFields.style.opacity = '1';
                savingsFields.style.transform = 'translateY(0)';
                // Make savings goal required
                document.getElementById('savings_goal').required = true;
                document.getElementById('savings_deadline').required = false;
                document.getElementById('debt_amount').required = false;
                document.getElementById('goal_description').required = false;
                calculateRecommendedDeadline();
                validateSavingsGoal();
            }, 50);
        } else if (goal === 'pagar_deudas') {
            debtFields.classList.remove('hidden');
            setTimeout(() => {
                debtFields.style.opacity = '1';
                debtFields.style.transform = 'translateY(0)';
                // Make debt amount required
                document.getElementById('debt_amount').required = true;
                document.getElementById('debt_deadline').required = false;
                document.getElementById('monthly_payment').required = false;
                document.getElementById('debt_count').required = false;
                document.getElementById('savings_goal').required = false;
                document.getElementById('savings_deadline').required = false;
                document.getElementById('goal_description').required = false;
                validateDebtGoal();
                calculateRecommendedMonthlyPayment();
            }, 50);
        } else if (goal === 'otro') {
            otherFields.classList.remove('hidden');
            setTimeout(() => {
                otherFields.style.opacity = '1';
                otherFields.style.transform = 'translateY(0)';
                // Make description required
                document.getElementById('goal_description').required = true;
                document.getElementById('savings_goal').required = false;
                document.getElementById('savings_deadline').required = false;
                document.getElementById('debt_amount').required = false;
                validateOtherGoal();
            }, 50);
        } else {
            // No goal selected - clear requirements
            document.getElementById('savings_goal').required = false;
            document.getElementById('savings_deadline').required = false;
            document.getElementById('debt_amount').required = false;
            document.getElementById('debt_deadline').required = false;
            document.getElementById('monthly_payment').required = false;
            document.getElementById('debt_count').required = false;
            document.getElementById('goal_description').required = false;
        }
        
        updateAutoLimit();
    }, 350);
}

function hideAllInfoBoxes() {
    document.getElementById('savings_info').classList.add('hidden');
    document.getElementById('savings_warning').classList.add('hidden');
    document.getElementById('debt_info').classList.add('hidden');
    document.getElementById('debt_warning').classList.add('hidden');
    document.getElementById('limit_info').classList.add('hidden');
    document.getElementById('limit_warning').classList.add('hidden');
    document.getElementById('auto_limit_info').classList.add('hidden');
}

// Track if deadline was manually set by user
let deadlineManuallySet = false;
let hasInitialDeadline = false;

// Initialize deadline tracking
document.addEventListener('DOMContentLoaded', function() {
    const initialDeadline = document.getElementById('savings_deadline').value;
    if (initialDeadline) {
        hasInitialDeadline = true;
        deadlineManuallySet = true;
    }
});

function markDeadlineAsManuallySet() {
    deadlineManuallySet = true;
    document.getElementById('deadline_recommendation_text').classList.add('hidden');
    document.getElementById('deadline_manual_text').classList.remove('hidden');
}

function calculateRecommendedDeadline() {
    // Only calculate if deadline hasn't been manually set
    if (deadlineManuallySet && hasInitialDeadline) {
        return;
    }
    
    // Only calculate for savings goal
    const financialGoal = getSelectedGoal();
    if (financialGoal !== 'ahorrar') {
        return;
    }
    
    const savingsGoal = parseFloat(document.getElementById('savings_goal').value) || 0;
    const monthlyIncome = getMonthlyIncome();
    
    // Don't calculate if we don't have both values
    if (savingsGoal <= 0 || monthlyIncome <= 0) {
        return;
    }
    
    // Only auto-set if user hasn't manually set a deadline
    if (deadlineManuallySet && !hasInitialDeadline) {
        return;
    }
    
    // Calculate recommended savings percentage (25% of income as balanced recommendation)
    const recommendedMonthlySavings = monthlyIncome * 0.25;
    
    // Calculate months needed to reach goal
    const monthsNeeded = Math.ceil(savingsGoal / recommendedMonthlySavings);
    
    // Set reasonable bounds (minimum 4 months, maximum 60 months / 5 years)
    const minMonths = 4;
    const maxMonths = 60;
    const finalMonths = Math.max(minMonths, Math.min(maxMonths, monthsNeeded));
    
    // Calculate recommended date
    const today = new Date();
    const recommendedDate = new Date(today);
    recommendedDate.setMonth(recommendedDate.getMonth() + finalMonths);
    
    // Format date as YYYY-MM-DD
    const year = recommendedDate.getFullYear();
    const month = String(recommendedDate.getMonth() + 1).padStart(2, '0');
    const day = String(recommendedDate.getDate()).padStart(2, '0');
    const recommendedDateString = `${year}-${month}-${day}`;
    
    // Set the deadline field
    const deadlineField = document.getElementById('savings_deadline');
    const currentValue = deadlineField.value;
    
    // Only set if field is empty or if it wasn't manually set by user
    if (!currentValue || (!deadlineManuallySet && !hasInitialDeadline)) {
        deadlineField.value = recommendedDateString;
        
        // Show recommendation message only if we just set it (was empty)
        if (!currentValue) {
            const recommendationText = document.getElementById('deadline_recommendation_text');
            const manualText = document.getElementById('deadline_manual_text');
            
            if (recommendationText && manualText) {
                recommendationText.classList.remove('hidden');
                manualText.classList.add('hidden');
                
                // Hide recommendation message after 5 seconds
                setTimeout(() => {
                    if (!deadlineManuallySet) {
                        recommendationText.classList.add('hidden');
                        manualText.classList.remove('hidden');
                    }
                }, 5000);
            }
        }
    }
}

/**
 * Calculate months between two dates (matches backend logic)
 * Uses calendar months calculation for accurate results
 */
function calculateMonthsBetween(startDate, endDate) {
    const start = new Date(startDate);
    const end = new Date(endDate);
    
    // Ensure start is before end
    if (start >= end) {
        return 1; // Safety: return at least 1 month if dates are reversed or equal
    }
    
    // Calculate difference in years, months, and days
    let years = end.getFullYear() - start.getFullYear();
    let months = end.getMonth() - start.getMonth();
    let days = end.getDate() - start.getDate();
    
    // Adjust for negative days (e.g., if end day is earlier in month than start day)
    if (days < 0) {
        months--;
        // Get days in the previous month
        const prevMonth = new Date(end.getFullYear(), end.getMonth(), 0);
        days += prevMonth.getDate();
    }
    
    // Adjust for negative months
    if (months < 0) {
        years--;
        months += 12;
    }
    
    // Calculate total months
    let totalMonths = (years * 12) + months;
    
    // Handle remaining days:
    // - If we have 0 months but have days, count as at least 1 month
    // - If we have months and remaining days >= 15 (half month), add 1 more month
    if (totalMonths === 0) {
        // Less than 1 full month, but we have days - count as 1 month minimum
        if (days > 0) {
            totalMonths = 1;
        }
    } else {
        // We have at least 1 full month
        // Only add an additional month if remaining days are significant (>= 15 days)
        if (days >= 15) {
            totalMonths += 1;
        }
    }
    
    // Ensure at least 1 month for any valid date range
    return Math.max(1, totalMonths);
}

function validateSavingsGoal() {
    const savingsGoal = parseFloat(document.getElementById('savings_goal').value) || 0;
    const savingsDeadline = document.getElementById('savings_deadline').value;
    const monthlyIncome = getMonthlyIncome();
    const currency = getCurrency();
    
    const infoBox = document.getElementById('savings_info');
    const warningBox = document.getElementById('savings_warning');
    const infoList = document.getElementById('savings_info_list');
    const warningText = document.getElementById('savings_warning_text');
    
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
            // Use accurate month calculation
            const months = calculateMonthsBetween(today, deadline);
            const requiredMonthly = savingsGoal / months;
            const percentage = (requiredMonthly / monthlyIncome) * 100;
            
            info.push(`Tiempo disponible: ${months} mes${months > 1 ? 'es' : ''}`);
            info.push(`Ahorro mensual necesario: ${formatCurrency(requiredMonthly, currency)}`);
            info.push(`Porcentaje del ingreso: ${percentage.toFixed(1)}%`);
            
            if (requiredMonthly > monthlyIncome * 0.50) {
                warningMessage = `Para alcanzar tu meta en ${months} meses, necesitarías ahorrar ${formatCurrency(requiredMonthly, currency)} mensualmente (${percentage.toFixed(1)}% de tu ingreso). Esto puede ser difícil de mantener.`;
                hasWarning = true;
            } else if (requiredMonthly > monthlyIncome * 0.30) {
                warningMessage = `Necesitarás ahorrar ${formatCurrency(requiredMonthly, currency)} mensualmente (${percentage.toFixed(1)}% de tu ingreso). Asegúrate de que esto sea sostenible.`;
                hasWarning = true;
            }
            
            if (months > 120) {
                warningMessage = (warningMessage ? warningMessage + ' ' : '') + 'La fecha límite es muy lejana. Considera establecer una meta más cercana para mantener la motivación.';
                hasWarning = true;
            }
        }
    } else {
        // No deadline - show general info
        const recommendedSavings = monthlyIncome * 0.25;
        info.push(`Ahorro mensual recomendado: ${formatCurrency(recommendedSavings, currency)} (25% del ingreso)`);
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
    
    updateAutoLimit();
}

// Track if monthly payment was manually set by user
let monthlyPaymentManuallySet = false;
let hasInitialMonthlyPayment = false;

// Initialize monthly payment tracking
document.addEventListener('DOMContentLoaded', function() {
    const initialMonthlyPayment = document.getElementById('monthly_payment').value;
    if (initialMonthlyPayment) {
        hasInitialMonthlyPayment = true;
        monthlyPaymentManuallySet = true;
    }
});

function markMonthlyPaymentAsManuallySet() {
    monthlyPaymentManuallySet = true;
    document.getElementById('monthly_payment_recommendation_text').classList.add('hidden');
    document.getElementById('monthly_payment_manual_text').classList.remove('hidden');
}

function calculateRecommendedMonthlyPayment() {
    // Only calculate if monthly payment hasn't been manually set
    if (monthlyPaymentManuallySet && hasInitialMonthlyPayment) {
        return;
    }
    
    const financialGoal = getSelectedGoal();
    if (financialGoal !== 'pagar_deudas') {
        return;
    }
    
    const debtAmount = parseFloat(document.getElementById('debt_amount').value) || 0;
    const debtDeadline = document.getElementById('debt_deadline').value;
    const monthlyIncome = getMonthlyIncome();
    
    if (debtAmount <= 0 || monthlyIncome <= 0) {
        return;
    }
    
    // Only auto-set if user hasn't manually set a monthly payment
    if (monthlyPaymentManuallySet && !hasInitialMonthlyPayment) {
        return;
    }
    
    let recommendedPayment = 0;
    
    if (debtDeadline) {
        // Calculate based on deadline
        const deadline = new Date(debtDeadline);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        deadline.setHours(0, 0, 0, 0);
        
        if (deadline > today) {
            // Use accurate month calculation
            const months = calculateMonthsBetween(today, deadline);
            
            recommendedPayment = debtAmount / months;
            // Ensure payment doesn't exceed 50% of income
            recommendedPayment = Math.min(recommendedPayment, monthlyIncome * 0.50);
        }
    } else {
        // Default: recommend 30% of income or enough to pay in 24 months, whichever is higher
        const minMonthlyPayment = debtAmount / 24;
        recommendedPayment = Math.max(monthlyIncome * 0.30, minMonthlyPayment);
        recommendedPayment = Math.min(recommendedPayment, monthlyIncome * 0.50);
    }
    
    // Set the monthly payment field
    const monthlyPaymentField = document.getElementById('monthly_payment');
    const currentValue = monthlyPaymentField.value;
    
    // Only set if field is empty or if it wasn't manually set by user
    if (!currentValue || (!monthlyPaymentManuallySet && !hasInitialMonthlyPayment)) {
        monthlyPaymentField.value = recommendedPayment.toFixed(2);
        
        // Show recommendation message only if we just set it (was empty)
        if (!currentValue && recommendedPayment > 0) {
            const recommendationText = document.getElementById('monthly_payment_recommendation_text');
            const manualText = document.getElementById('monthly_payment_manual_text');
            
            if (recommendationText && manualText) {
                recommendationText.classList.remove('hidden');
                manualText.classList.add('hidden');
                
                // Hide recommendation message after 5 seconds
                setTimeout(() => {
                    if (!monthlyPaymentManuallySet) {
                        recommendationText.classList.add('hidden');
                        manualText.classList.remove('hidden');
                    }
                }, 5000);
            }
        }
    }
}

function calculateDebtDeadlineFromPayment() {
    // Only calculate if debt deadline hasn't been manually set
    const debtDeadlineField = document.getElementById('debt_deadline');
    if (debtDeadlineField.value) {
        return; // User has set a deadline, don't override
    }
    
    const financialGoal = getSelectedGoal();
    if (financialGoal !== 'pagar_deudas') {
        return;
    }
    
    const debtAmount = parseFloat(document.getElementById('debt_amount').value) || 0;
    const monthlyPayment = parseFloat(document.getElementById('monthly_payment').value) || 0;
    
    if (debtAmount <= 0 || monthlyPayment <= 0) {
        return;
    }
    
    // Calculate months needed to pay debt
    const monthsNeeded = Math.ceil(debtAmount / monthlyPayment);
    
    // Set reasonable bounds (minimum 6 months, maximum 120 months / 10 years)
    const minMonths = 6;
    const maxMonths = 120;
    const finalMonths = Math.max(minMonths, Math.min(maxMonths, monthsNeeded));
    
    // Calculate recommended date
    const today = new Date();
    const recommendedDate = new Date(today);
    recommendedDate.setMonth(recommendedDate.getMonth() + finalMonths);
    
    // Format date as YYYY-MM-DD
    const year = recommendedDate.getFullYear();
    const month = String(recommendedDate.getMonth() + 1).padStart(2, '0');
    const day = String(recommendedDate.getDate()).padStart(2, '0');
    const recommendedDateString = `${year}-${month}-${day}`;
    
    // Only set if field is empty
    if (!debtDeadlineField.value) {
        debtDeadlineField.value = recommendedDateString;
    }
}

function validateDebtGoal() {
    const debtAmount = parseFloat(document.getElementById('debt_amount').value) || 0;
    const debtDeadline = document.getElementById('debt_deadline').value;
    const monthlyPayment = parseFloat(document.getElementById('monthly_payment').value) || 0;
    const debtCount = parseInt(document.getElementById('debt_count').value) || 0;
    const monthlyIncome = getMonthlyIncome();
    const currency = getCurrency();
    
    const infoBox = document.getElementById('debt_info');
    const warningBox = document.getElementById('debt_warning');
    const infoList = document.getElementById('debt_info_list');
    const warningText = document.getElementById('debt_warning_text');
    
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
    
    // Calculate debt to income ratio
    const annualIncome = monthlyIncome * 12;
    const debtRatio = debtAmount / annualIncome;
    
    info.push(`Ratio deuda/ingreso anual: ${(debtRatio * 100).toFixed(1)}%`);
    
    if (debtCount > 0) {
        const avgDebtPerLoan = debtAmount / debtCount;
        info.push(`Número de deudas: ${debtCount}`);
        info.push(`Deuda promedio por préstamo: ${formatCurrency(avgDebtPerLoan, currency)}`);
    }
    
    if (debtRatio > 5) {
        warningMessage = 'Tu deuda es muy alta comparada con tu ingreso anual. Considera buscar asesoría financiera profesional.';
        hasWarning = true;
    }
    
    // If deadline is provided, calculate required monthly payment
    if (debtDeadline) {
        const deadline = new Date(debtDeadline);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        deadline.setHours(0, 0, 0, 0);
        
        if (deadline <= today) {
            warningMessage = 'La fecha objetivo debe ser una fecha futura';
            hasWarning = true;
        } else {
            // Use accurate month calculation (same function as for savings)
            const months = calculateMonthsBetween(today, deadline);
            const requiredMonthlyPayment = debtAmount / months;
            const paymentPercentage = (requiredMonthlyPayment / monthlyIncome) * 100;
            
            info.push(`Fecha objetivo: ${new Date(debtDeadline).toLocaleDateString('es-MX')}`);
            info.push(`Tiempo disponible: ${months} mes${months > 1 ? 'es' : ''}`);
            info.push(`Pago mensual necesario: ${formatCurrency(requiredMonthlyPayment, currency)}`);
            info.push(`Porcentaje del ingreso: ${paymentPercentage.toFixed(1)}%`);
            
            if (requiredMonthlyPayment > monthlyIncome * 0.50) {
                warningMessage = (warningMessage ? warningMessage + ' ' : '') + 
                    `Para pagar tu deuda en ${months} meses, necesitarías pagar ${formatCurrency(requiredMonthlyPayment, currency)} mensualmente (${paymentPercentage.toFixed(1)}% de tu ingreso). Esto puede ser difícil de mantener.`;
                hasWarning = true;
            } else if (requiredMonthlyPayment > monthlyIncome * 0.30) {
                warningMessage = (warningMessage ? warningMessage + ' ' : '') + 
                    `Necesitarás pagar ${formatCurrency(requiredMonthlyPayment, currency)} mensualmente (${paymentPercentage.toFixed(1)}% de tu ingreso). Asegúrate de que esto sea sostenible.`;
                hasWarning = true;
            }
            
            // Compare with user's monthly payment if provided
            if (monthlyPayment > 0) {
                const monthsToPay = Math.ceil(debtAmount / monthlyPayment);
                if (monthsToPay > months) {
                    warningMessage = (warningMessage ? warningMessage + ' ' : '') + 
                        `Con un pago mensual de ${formatCurrency(monthlyPayment, currency)}, tardarías ${monthsToPay} meses en pagar, que es más que tu fecha objetivo (${months} meses). Considera aumentar el pago mensual.`;
                    hasWarning = true;
                } else if (monthsToPay <= months) {
                    info.push(`Con tu pago mensual de ${formatCurrency(monthlyPayment, currency)}, pagarás la deuda en aproximadamente ${monthsToPay} mes${monthsToPay > 1 ? 'es' : ''}`);
                }
            }
        }
    } else if (monthlyPayment > 0) {
        // User specified monthly payment but no deadline
        const monthsToPay = Math.ceil(debtAmount / monthlyPayment);
        const paymentPercentage = (monthlyPayment / monthlyIncome) * 100;
        
        info.push(`Pago mensual: ${formatCurrency(monthlyPayment, currency)}`);
        info.push(`Porcentaje del ingreso: ${paymentPercentage.toFixed(1)}%`);
        info.push(`Tiempo estimado para pagar: ${monthsToPay} mes${monthsToPay > 1 ? 'es' : ''} (${(monthsToPay / 12).toFixed(1)} años)`);
        
        if (monthlyPayment > monthlyIncome * 0.50) {
            warningMessage = (warningMessage ? warningMessage + ' ' : '') + 
                `El pago mensual de ${formatCurrency(monthlyPayment, currency)} representa ${paymentPercentage.toFixed(1)}% de tu ingreso. Asegúrate de que esto sea sostenible.`;
            hasWarning = true;
        }
        
        if (monthsToPay > 120) {
            warningMessage = (warningMessage ? warningMessage + ' ' : '') + 
                `Con este pago mensual, tardarías aproximadamente ${monthsToPay} meses (${(monthsToPay / 12).toFixed(1)} años) en pagar tu deuda. Considera aumentar el pago mensual.`;
            hasWarning = true;
        }
    } else {
        // No deadline or monthly payment - show default calculations
        const minMonthlyPayment = debtAmount / 24;
        const paymentPercentage = (minMonthlyPayment / monthlyIncome) * 100;
        
        info.push(`Pago mensual mínimo (24 meses): ${formatCurrency(minMonthlyPayment, currency)}`);
        info.push(`Porcentaje del ingreso: ${paymentPercentage.toFixed(1)}%`);
        
        if (minMonthlyPayment > monthlyIncome * 0.50) {
            warningMessage = (warningMessage ? warningMessage + ' ' : '') + 
                `Para pagar tu deuda en 24 meses, necesitarías pagar ${formatCurrency(minMonthlyPayment, currency)} mensualmente (${paymentPercentage.toFixed(1)}% de tu ingreso). Esto puede ser difícil de mantener.`;
            hasWarning = true;
        } else if (minMonthlyPayment > monthlyIncome * 0.30) {
            warningMessage = (warningMessage ? warningMessage + ' ' : '') + 
                `Necesitarás pagar ${formatCurrency(minMonthlyPayment, currency)} mensualmente (${paymentPercentage.toFixed(1)}% de tu ingreso). Asegúrate de que esto sea sostenible.`;
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
    
    updateAutoLimit();
}

function validateOtherGoal() {
    const description = document.getElementById('goal_description').value.trim();
    const goalDescriptionField = document.getElementById('goal_description');
    
    // Remove previous validation styling
    goalDescriptionField.classList.remove('border-red-500', 'border-green-500');
    
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

function validateSpendingLimit() {
    const spendingLimit = parseFloat(document.getElementById('spending_limit').value) || 0;
    const monthlyIncome = getMonthlyIncome();
    const currency = getCurrency();
    const financialGoal = document.getElementById('financial_goal').value;
    const savingsGoal = parseFloat(document.getElementById('savings_goal').value) || 0;
    const debtAmount = parseFloat(document.getElementById('debt_amount').value) || 0;
    
    const infoBox = document.getElementById('limit_info');
    const warningBox = document.getElementById('limit_warning');
    const infoText = document.getElementById('limit_info_text');
    const warningText = document.getElementById('limit_warning_text');
    
    // Hide both initially
    infoBox.classList.add('hidden');
    warningBox.classList.add('hidden');
    
    if (spendingLimit <= 0 || monthlyIncome <= 0) {
        return;
    }
    
    let hasWarning = false;
    let warningMessage = '';
    let infoMessage = '';
    
    // Check if limit exceeds income
    if (spendingLimit > monthlyIncome) {
        warningMessage = 'El límite de gasto no puede ser mayor que tu ingreso mensual';
        hasWarning = true;
    } else {
        const percentage = (spendingLimit / monthlyIncome) * 100;
        const available = monthlyIncome - spendingLimit;
        
        infoMessage = `Disponible para ${financialGoal === 'ahorrar' ? 'ahorro' : financialGoal === 'pagar_deudas' ? 'pago de deudas' : 'otros objetivos'}: ${formatCurrency(available, currency)} (${(available / monthlyIncome * 100).toFixed(1)}%)`;
        
        if (financialGoal === 'ahorrar' && savingsGoal > 0) {
            const recommendedSavings = monthlyIncome * 0.20;
            if (available < recommendedSavings) {
                warningMessage = `Con este límite, solo quedarían ${formatCurrency(available, currency)} para ahorro (${(available / monthlyIncome * 100).toFixed(1)}% de tu ingreso). Se recomienda ahorrar al menos 20% para alcanzar tu meta.`;
                hasWarning = true;
            }
        } else if (financialGoal === 'pagar_deudas' && debtAmount > 0) {
            const recommendedPayment = monthlyIncome * 0.30;
            if (available < recommendedPayment) {
                warningMessage = `Con este límite, solo quedarían ${formatCurrency(available, currency)} para pagar deudas (${(available / monthlyIncome * 100).toFixed(1)}% de tu ingreso). Se recomienda destinar al menos 30% para pagar deudas.`;
                hasWarning = true;
            }
        }
        
        if (percentage > 90) {
            warningMessage = (warningMessage ? warningMessage + ' ' : '') + 'El límite de gasto es muy alto. Se recomienda dejar al menos 10% de margen para imprevistos.';
            hasWarning = true;
        }
    }
    
    if (infoMessage) {
        infoText.textContent = infoMessage;
        infoBox.classList.remove('hidden');
    }
    
    if (hasWarning) {
        warningText.textContent = warningMessage;
        warningBox.classList.remove('hidden');
    }
}

function updateAutoLimit() {
    const limitType = document.querySelector('input[name="spending_limit_type"]:checked');
    if (!limitType || limitType.value !== 'auto') {
        const autoLimitInfo = document.getElementById('auto_limit_info');
        if (!autoLimitInfo.classList.contains('hidden')) {
            autoLimitInfo.style.opacity = '0';
            setTimeout(() => {
                autoLimitInfo.classList.add('hidden');
            }, 300);
        }
        return;
    }
    
    const monthlyIncome = getMonthlyIncome();
    const currency = getCurrency();
    const financialGoal = getSelectedGoal();
    const savingsGoal = parseFloat(document.getElementById('savings_goal').value) || 0;
    const savingsDeadline = document.getElementById('savings_deadline').value;
    const debtAmount = parseFloat(document.getElementById('debt_amount').value) || 0;
    
    if (monthlyIncome <= 0) {
        const autoLimitInfo = document.getElementById('auto_limit_info');
        if (!autoLimitInfo.classList.contains('hidden')) {
            autoLimitInfo.style.opacity = '0';
            setTimeout(() => {
                autoLimitInfo.classList.add('hidden');
            }, 300);
        }
        return;
    }
    
    // Calculate limit (improved calculation matching server-side logic)
    let limit = monthlyIncome;
    let details = '';
    let savingsAmount = 0;
    let paymentAmount = 0;
    
    if (financialGoal === 'ahorrar') {
        if (savingsGoal > 0) {
            if (savingsDeadline) {
                const deadline = new Date(savingsDeadline);
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                deadline.setHours(0, 0, 0, 0);
                
                if (deadline > today) {
                    // Use accurate month calculation
                    const months = calculateMonthsBetween(today, deadline);
                    
                    const requiredMonthly = savingsGoal / months;
                    const maxSavings = monthlyIncome * 0.50;
                    savingsAmount = Math.min(requiredMonthly, maxSavings);
                    limit = monthlyIncome - savingsAmount;
                    details = `Basado en ahorro mensual de ${formatCurrency(savingsAmount, currency)} para alcanzar ${formatCurrency(savingsGoal, currency)} en ${months} meses`;
                } else {
                    const recommendedSavings = monthlyIncome * 0.25;
                    savingsAmount = recommendedSavings;
                    limit = monthlyIncome - recommendedSavings;
                    details = `Basado en ahorro recomendado del 25% del ingreso (${formatCurrency(recommendedSavings, currency)})`;
                }
            } else {
                const recommendedSavings = monthlyIncome * 0.25;
                savingsAmount = recommendedSavings;
                limit = monthlyIncome - recommendedSavings;
                details = `Basado en ahorro recomendado del 25% del ingreso (${formatCurrency(recommendedSavings, currency)})`;
            }
        } else {
            // No savings goal set yet
            const recommendedSavings = monthlyIncome * 0.25;
            savingsAmount = recommendedSavings;
            limit = monthlyIncome - recommendedSavings;
            details = `Ingresa una meta de ahorro para un cálculo más preciso. Por ahora: ${formatCurrency(recommendedSavings, currency)} para ahorro (25%)`;
        }
    } else if (financialGoal === 'pagar_deudas') {
        if (debtAmount > 0) {
            const monthlyPayment = parseFloat(document.getElementById('monthly_payment').value) || 0;
            const debtDeadline = document.getElementById('debt_deadline').value;
            
            // If user specified monthly payment, use it
            if (monthlyPayment > 0) {
                paymentAmount = Math.min(monthlyPayment, monthlyIncome * 0.50);
                limit = monthlyIncome - paymentAmount;
                const paymentMonths = Math.ceil(debtAmount / paymentAmount);
                details = `Basado en pago mensual de ${formatCurrency(paymentAmount, currency)} para pagar la deuda en aproximadamente ${paymentMonths} meses`;
            } else if (debtDeadline) {
                // If deadline is provided, calculate required monthly payment
                const deadline = new Date(debtDeadline);
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                deadline.setHours(0, 0, 0, 0);
                
                if (deadline > today) {
                    // Use accurate month calculation
                    const months = calculateMonthsBetween(today, deadline);
                    
                    const requiredMonthlyPayment = debtAmount / months;
                    const maxPayment = monthlyIncome * 0.50;
                    paymentAmount = Math.min(requiredMonthlyPayment, maxPayment);
                    limit = monthlyIncome - paymentAmount;
                    details = `Basado en pago mensual de ${formatCurrency(paymentAmount, currency)} para pagar la deuda en ${months} meses (fecha objetivo)`;
                } else {
                    // Invalid deadline, use default
                    const minMonthlyPayment = debtAmount / 24;
                    const recommendedPayment = Math.max(monthlyIncome * 0.30, minMonthlyPayment);
                    paymentAmount = Math.min(recommendedPayment, monthlyIncome * 0.50);
                    limit = monthlyIncome - paymentAmount;
                    const paymentMonths = Math.ceil(debtAmount / paymentAmount);
                    details = `Basado en pago mensual de ${formatCurrency(paymentAmount, currency)} para pagar la deuda en aproximadamente ${paymentMonths} meses`;
                }
            } else {
                // Default: recommend paying 30% towards debt, but at least enough to pay in 24 months
                const minMonthlyPayment = debtAmount / 24;
                const recommendedPayment = Math.max(monthlyIncome * 0.30, minMonthlyPayment);
                paymentAmount = Math.min(recommendedPayment, monthlyIncome * 0.50);
                limit = monthlyIncome - paymentAmount;
                
                const paymentMonths = Math.ceil(debtAmount / paymentAmount);
                details = `Basado en pago mensual de ${formatCurrency(paymentAmount, currency)} para pagar la deuda en aproximadamente ${paymentMonths} meses`;
            }
        } else {
            // No debt amount set yet
            const recommendedPayment = monthlyIncome * 0.30;
            paymentAmount = recommendedPayment;
            limit = monthlyIncome - recommendedPayment;
            details = `Ingresa el monto de deuda para un cálculo más preciso. Por ahora: ${formatCurrency(recommendedPayment, currency)} para pagos (30%)`;
        }
    } else if (financialGoal === 'controlar_gastos') {
        limit = monthlyIncome * 0.80;
        const savings = monthlyIncome * 0.20;
        details = `Basado en límite recomendado del 80% del ingreso. ${formatCurrency(savings, currency)} disponible para ahorro (20%)`;
    } else if (financialGoal === 'otro') {
        limit = monthlyIncome * 0.75;
        const available = monthlyIncome * 0.25;
        details = `Límite conservador del 75% del ingreso. ${formatCurrency(available, currency)} disponible para tu objetivo (25%)`;
    } else {
        limit = monthlyIncome * 0.80;
        details = 'Basado en límite recomendado del 80% del ingreso';
    }
    
    // Ensure minimum (at least 50% of income)
    const minLimit = monthlyIncome * 0.50;
    limit = Math.max(limit, minLimit);
    
    // Update display with animation
    const autoLimitInfo = document.getElementById('auto_limit_info');
    const wasHidden = autoLimitInfo.classList.contains('hidden');
    
    document.getElementById('auto_limit_value').textContent = formatCurrency(limit, currency);
    document.getElementById('auto_limit_details').textContent = details;
    
    if (wasHidden) {
        autoLimitInfo.classList.remove('hidden');
        autoLimitInfo.style.opacity = '0';
        setTimeout(() => {
            autoLimitInfo.style.opacity = '1';
        }, 50);
    }
    
    // Add visual indicator based on limit percentage
    const limitPercentage = (limit / monthlyIncome) * 100;
    const limitBox = document.getElementById('auto_limit_info');
    limitBox.classList.remove('bg-green-50', 'border-green-200', 'bg-yellow-50', 'border-yellow-200', 'bg-orange-50', 'border-orange-200');
    
    if (limitPercentage <= 75) {
        limitBox.classList.add('bg-green-50', 'border-green-200');
    } else if (limitPercentage <= 85) {
        limitBox.classList.add('bg-yellow-50', 'border-yellow-200');
    } else {
        limitBox.classList.add('bg-orange-50', 'border-orange-200');
    }
}

function toggleSpendingLimit() {
    const isManual = document.querySelector('input[name="spending_limit_type"]:checked').value === 'manual';
    const manualField = document.getElementById('manual_limit_field');
    const autoInfo = document.getElementById('auto_limit_info');
    
    if (isManual) {
        manualField.classList.remove('hidden');
        autoInfo.classList.add('hidden');
        document.getElementById('spending_limit').required = true;
        validateSpendingLimit();
    } else {
        manualField.classList.add('hidden');
        document.getElementById('spending_limit').required = false;
        updateAutoLimit();
    }
}

// Add event listeners for income and goal changes
document.addEventListener('DOMContentLoaded', function() {
    // Initialize goal sections style
    document.querySelectorAll('.goal-section').forEach(section => {
        section.style.opacity = '0';
        section.style.transform = 'translateY(-10px)';
        section.style.transition = 'opacity 0.5s ease-in-out, transform 0.5s ease-in-out';
    });
    
    // Initialize goal cards
    const selectedGoal = getSelectedGoal();
    if (selectedGoal) {
        const selectedCard = document.querySelector(`.goal-card input[value="${selectedGoal}"]`)?.closest('.goal-card');
        if (selectedCard) {
            selectedCard.classList.remove('border-gray-300');
            selectedCard.classList.add('border-blue-500', 'bg-blue-50');
        }
    }
    
    toggleGoalFields();
    
    // Update when monthly income changes
    document.getElementById('monthly_income').addEventListener('input', debounce(function() {
        updateAutoLimit();
        const goal = getSelectedGoal();
        if (goal === 'ahorrar') {
            // calculateRecommendedDeadline() already checks if deadline was manually set
            calculateRecommendedDeadline();
            validateSavingsGoal();
        }
        if (goal === 'pagar_deudas') validateDebtGoal();
        const limitType = document.querySelector('input[name="spending_limit_type"]:checked');
        if (limitType && limitType.value === 'manual') {
            validateSpendingLimit();
        }
    }, 300));
    
    // Update auto limit when currency changes
    document.getElementById('currency').addEventListener('change', function() {
        updateAutoLimit();
        const goal = getSelectedGoal();
        if (goal === 'ahorrar') {
            // calculateRecommendedDeadline() already checks if deadline was manually set
            calculateRecommendedDeadline();
            validateSavingsGoal();
        }
        if (goal === 'pagar_deudas') validateDebtGoal();
    });
    
    // Update when savings goal changes
    document.getElementById('savings_goal').addEventListener('input', debounce(function() {
        calculateRecommendedDeadline();
        // validateSavingsGoal() already calls updateAutoLimit() at the end
        validateSavingsGoal();
    }, 300));
    
    document.getElementById('savings_deadline').addEventListener('change', function() {
        // Mark as manually set when user changes it (only if not initial load)
        if (!hasInitialDeadline) {
            deadlineManuallySet = true;
            markDeadlineAsManuallySet();
        }
        // validateSavingsGoal() already calls updateAutoLimit() at the end
        validateSavingsGoal();
    });
    
    // Calculate recommended deadline on page load if goal and income are set
    setTimeout(function() {
        const savingsGoal = parseFloat(document.getElementById('savings_goal').value) || 0;
        const monthlyIncome = getMonthlyIncome();
        const financialGoal = getSelectedGoal();
        
        if (financialGoal === 'ahorrar' && savingsGoal > 0 && monthlyIncome > 0 && !hasInitialDeadline) {
            calculateRecommendedDeadline();
        }
    }, 200);
    
    // Update when debt amount changes
    document.getElementById('debt_amount').addEventListener('input', debounce(function() {
        validateDebtGoal();
        calculateRecommendedMonthlyPayment();
        updateAutoLimit();
    }, 300));
    
    // Update when debt deadline changes
    document.getElementById('debt_deadline').addEventListener('change', function() {
        validateDebtGoal();
        calculateRecommendedMonthlyPayment();
        updateAutoLimit();
    });
    
    // Update when monthly payment changes
    document.getElementById('monthly_payment').addEventListener('input', debounce(function() {
        validateDebtGoal();
        calculateDebtDeadlineFromPayment();
        updateAutoLimit();
    }, 300));
    
    // Update when debt count changes
    document.getElementById('debt_count').addEventListener('input', debounce(function() {
        validateDebtGoal();
    }, 300));
    
    // Update when goal description changes
    document.getElementById('goal_description').addEventListener('input', debounce(function() {
        validateOtherGoal();
    }, 300));
    
    // Handle checkbox changes for payment methods
    document.querySelectorAll('.payment-checkbox').forEach(checkbox => {
        // Listen to change events (fires when checkbox state changes via label click or keyboard)
        checkbox.addEventListener('change', function(e) {
            e.stopPropagation();
            updatePaymentCardVisual(this.value, this.checked);
        });
        
        // Also handle input event for better compatibility
        checkbox.addEventListener('input', function(e) {
            e.stopPropagation();
            updatePaymentCardVisual(this.value, this.checked);
        });
    });
    
    // Initialize payment methods visual states (after event listeners are set up)
    document.querySelectorAll('.payment-checkbox').forEach(checkbox => {
        updatePaymentCardVisual(checkbox.value, checkbox.checked, true);
    });
    
    // Initialize payment methods validation (don't show error on initial load)
    // Only validate silently to set up initial state
    validatePaymentMethods(false);
    
    // Form validation before submit
    document.querySelector('form').addEventListener('submit', function(e) {
        const goal = getSelectedGoal();
        let isValid = true;
        let firstError = null;
        
        // Validate goal selection
        if (!goal) {
            e.preventDefault();
            alert('Por favor selecciona un objetivo financiero');
            firstError = document.querySelector('.goal-card');
            isValid = false;
        }
        
        // Validate payment methods (always show error on submit if invalid)
        const hasPaymentMethods = document.querySelectorAll('.payment-checkbox:checked').length > 0;
        if (!hasPaymentMethods) {
            e.preventDefault();
            // Show error message
            validatePaymentMethods(true);
            if (!firstError) {
                firstError = document.getElementById('payment_methods_container') || document.getElementById('payment_methods_error');
            }
            isValid = false;
        } else {
            // Hide error if valid
            validatePaymentMethods(false);
        }
        
        // Validate goal-specific fields
        if (goal === 'ahorrar') {
            const savingsGoal = parseFloat(document.getElementById('savings_goal').value) || 0;
            if (savingsGoal <= 0) {
                e.preventDefault();
                alert('Por favor ingresa una meta de ahorro válida');
                if (!firstError) {
                    firstError = document.getElementById('savings_goal');
                }
                isValid = false;
            }
        } else if (goal === 'pagar_deudas') {
            const debtAmount = parseFloat(document.getElementById('debt_amount').value) || 0;
            if (debtAmount <= 0) {
                e.preventDefault();
                alert('Por favor ingresa el monto de deuda');
                if (!firstError) {
                    firstError = document.getElementById('debt_amount');
                }
                isValid = false;
            }
            
            // Validate debt deadline if provided
            const debtDeadline = document.getElementById('debt_deadline').value;
            if (debtDeadline) {
                const deadline = new Date(debtDeadline);
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                if (deadline <= today) {
                    e.preventDefault();
                    alert('La fecha objetivo para pagar deudas debe ser una fecha futura');
                    if (!firstError) {
                        firstError = document.getElementById('debt_deadline');
                    }
                    isValid = false;
                }
            }
        } else if (goal === 'otro') {
            const description = document.getElementById('goal_description').value.trim();
            if (description.length < 10) {
                e.preventDefault();
                alert('Por favor describe tu objetivo (mínimo 10 caracteres)');
                if (!firstError) {
                    firstError = document.getElementById('goal_description');
                }
                isValid = false;
            }
        }
        
        if (!isValid && firstError) {
            // Scroll to first error
            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            
            // Add shake animation to error element
            firstError.style.animation = 'shake 0.5s';
            setTimeout(() => {
                firstError.style.animation = '';
            }, 500);
        }
    });
    
    // Initialize auto limit display
    updateAutoLimit();
});

// Debounce function to limit function calls
function debounce(func, wait) {
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
</script>

<style>
/* Goal Card Styles */
.goal-card {
    transition: all 0.3s ease;
    position: relative;
}

.goal-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.goal-card:active {
    transform: translateY(0);
}

.goal-card.border-blue-500 {
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
}

/* Payment Card Styles */
.payment-card {
    transition: all 0.3s ease;
    position: relative;
}

.payment-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.payment-card:active {
    transform: translateY(0);
}

.payment-card.border-green-500 {
    box-shadow: 0 4px 12px rgba(34, 197, 94, 0.2);
}

.payment-card.border-blue-500 {
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
}

.payment-card.border-red-300 {
    border-color: #fca5a5 !important;
    animation: pulse-red 1s;
}

.payment-checkmark {
    animation: checkmark-pop 0.3s ease-out;
}

@keyframes checkmark-pop {
    0% {
        transform: scale(0);
        opacity: 0;
    }
    50% {
        transform: scale(1.2);
    }
    100% {
        transform: scale(1);
        opacity: 1;
    }
}

@keyframes pulse-red {
    0%, 100% {
        border-color: #fca5a5;
    }
    50% {
        border-color: #ef4444;
    }
}

@keyframes shake {
    0%, 100% {
        transform: translateX(0);
    }
    10%, 30%, 50%, 70%, 90% {
        transform: translateX(-5px);
    }
    20%, 40%, 60%, 80% {
        transform: translateX(5px);
    }
}

/* Goal Section Animations */
.goal-section {
    opacity: 0;
    transform: translateY(-10px);
    transition: opacity 0.5s ease-in-out, transform 0.5s ease-in-out;
}

.goal-section:not(.hidden) {
    opacity: 1;
    transform: translateY(0);
}

/* Input Validation Styles */
input.border-red-500:focus,
textarea.border-red-500:focus {
    border-color: #ef4444;
    ring-color: #ef4444;
}

input.border-green-500:focus,
textarea.border-green-500:focus {
    border-color: #10b981;
    ring-color: #10b981;
}

input.border-yellow-500:focus,
textarea.border-yellow-500:focus {
    border-color: #eab308;
    ring-color: #eab308;
}

/* Auto Limit Info Animation */
#auto_limit_info {
    transition: opacity 0.3s ease-in-out;
}

/* Smooth scroll behavior */
html {
    scroll-behavior: smooth;
}

/* Loading state for calculations */
.calculating {
    opacity: 0.6;
    pointer-events: none;
}

/* Payment checkbox overlay - covers entire card for clicking */
.payment-card {
    position: relative;
}

.payment-card .payment-checkbox {
    margin: 0;
    padding: 0;
    appearance: none;
    -webkit-appearance: none;
}

/* Responsive improvements */
@media (max-width: 640px) {
    .goal-card,
    .payment-card {
        padding: 1rem;
    }
    
    .goal-card h3,
    .payment-card h3 {
        font-size: 1rem;
    }
    
    .goal-card p,
    .payment-card p {
        font-size: 0.75rem;
    }
    
    .payment-checkmark {
        width: 1.25rem;
        height: 1.25rem;
    }
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

