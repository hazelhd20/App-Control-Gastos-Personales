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
                <div class="mb-6 p-4 rounded-lg alert-danger">
                    <ul class="list-disc list-inside text-sm">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="<?php echo BASE_URL; ?>public/index.php?action=initial-setup" method="POST" class="space-y-8">
                <!-- Income and Currency -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="monthly_income" class="block text-sm font-medium text-gray-700">
                            <i class="fas fa-dollar-sign mr-2 text-green-600"></i>Ingreso Mensual *
                        </label>
                        <input id="monthly_income" name="monthly_income" type="number" step="0.01" min="0" required 
                               value="<?php echo htmlspecialchars($old_data['monthly_income'] ?? ''); ?>"
                               class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="15000.00">
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
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-credit-card mr-2 text-blue-600"></i>Medios de Pago *
                    </label>
                    <div class="grid grid-cols-2 gap-4">
                        <label class="flex items-center p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-blue-500 transition">
                            <input type="checkbox" name="payment_methods[]" value="efectivo" 
                                   <?php echo in_array('efectivo', $old_data['payment_methods'] ?? []) ? 'checked' : ''; ?>
                                   class="w-5 h-5 text-blue-600 rounded focus:ring-2 focus:ring-blue-500">
                            <span class="ml-3 text-gray-700 font-medium">💵 Efectivo</span>
                        </label>
                        <label class="flex items-center p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-blue-500 transition">
                            <input type="checkbox" name="payment_methods[]" value="tarjeta" 
                                   <?php echo in_array('tarjeta', $old_data['payment_methods'] ?? []) ? 'checked' : ''; ?>
                                   class="w-5 h-5 text-blue-600 rounded focus:ring-2 focus:ring-blue-500">
                            <span class="ml-3 text-gray-700 font-medium">💳 Tarjeta</span>
                        </label>
                    </div>
                </div>

                <!-- Financial Goal -->
                <div>
                    <label for="financial_goal" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-bullseye mr-2 text-blue-600"></i>Objetivo Financiero Principal *
                    </label>
                    <select id="financial_goal" name="financial_goal" required 
                            onchange="toggleGoalFields()"
                            class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Selecciona un objetivo</option>
                        <option value="ahorrar" <?php echo ($old_data['financial_goal'] ?? '') === 'ahorrar' ? 'selected' : ''; ?>>💰 Ahorrar</option>
                        <option value="pagar_deudas" <?php echo ($old_data['financial_goal'] ?? '') === 'pagar_deudas' ? 'selected' : ''; ?>>💳 Pagar Deudas</option>
                        <option value="controlar_gastos" <?php echo ($old_data['financial_goal'] ?? '') === 'controlar_gastos' ? 'selected' : ''; ?>>📊 Controlar Gastos</option>
                        <option value="otro" <?php echo ($old_data['financial_goal'] ?? '') === 'otro' ? 'selected' : ''; ?>>📝 Otro</option>
                    </select>
                </div>

                <!-- Savings Goal Fields -->
                <div id="savings_fields" class="hidden">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                        <div>
                            <label for="savings_goal" class="block text-sm font-medium text-gray-700">
                                <i class="fas fa-piggy-bank mr-2 text-pink-600"></i>Meta de Ahorro *
                            </label>
                            <input id="savings_goal" name="savings_goal" type="number" step="0.01" min="0" 
                                   value="<?php echo htmlspecialchars($old_data['savings_goal'] ?? ''); ?>"
                                   class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="10000.00"
                                   oninput="validateSavingsGoal()">
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
                                   onchange="validateSavingsGoal()">
                            <p class="mt-1 text-xs text-gray-500">Fecha límite para alcanzar tu meta (opcional)</p>
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
                <div id="debt_fields" class="hidden">
                    <div class="mb-4">
                        <label for="debt_amount" class="block text-sm font-medium text-gray-700">
                            <i class="fas fa-hand-holding-usd mr-2 text-red-600"></i>Monto Total de Deuda *
                        </label>
                        <input id="debt_amount" name="debt_amount" type="number" step="0.01" min="0" 
                               value="<?php echo htmlspecialchars($old_data['debt_amount'] ?? ''); ?>"
                               class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="50000.00"
                               oninput="validateDebtGoal()">
                        <p class="mt-1 text-xs text-gray-500">Ingresa el monto total de todas tus deudas</p>
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
                <div id="other_goal_fields" class="hidden">
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
                    <div id="auto_limit_info" class="hidden mt-4 p-4 bg-green-50 border border-green-200 rounded-lg">
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

function toggleGoalFields() {
    const goal = document.getElementById('financial_goal').value;
    document.getElementById('savings_fields').classList.add('hidden');
    document.getElementById('debt_fields').classList.add('hidden');
    document.getElementById('other_goal_fields').classList.add('hidden');
    
    // Hide all info/warning boxes
    hideAllInfoBoxes();
    
    if (goal === 'ahorrar') {
        document.getElementById('savings_fields').classList.remove('hidden');
        validateSavingsGoal();
    } else if (goal === 'pagar_deudas') {
        document.getElementById('debt_fields').classList.remove('hidden');
        validateDebtGoal();
    } else if (goal === 'otro') {
        document.getElementById('other_goal_fields').classList.remove('hidden');
        validateOtherGoal();
    }
    
    updateAutoLimit();
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
        
        if (deadline <= today) {
            warningMessage = 'La fecha límite debe ser una fecha futura';
            hasWarning = true;
        } else {
            const months = Math.max(1, Math.ceil((deadline - today) / (1000 * 60 * 60 * 24 * 30)));
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

function validateDebtGoal() {
    const debtAmount = parseFloat(document.getElementById('debt_amount').value) || 0;
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
    
    if (debtRatio > 5) {
        warningMessage = 'Tu deuda es muy alta comparada con tu ingreso anual. Considera buscar asesoría financiera profesional.';
        hasWarning = true;
    }
    
    // Calculate minimum payment for 24 months
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
    // Basic validation - just check if empty
    // More complex validation can be added if needed
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
        document.getElementById('auto_limit_info').classList.add('hidden');
        return;
    }
    
    const monthlyIncome = getMonthlyIncome();
    const currency = getCurrency();
    const financialGoal = document.getElementById('financial_goal').value;
    const savingsGoal = parseFloat(document.getElementById('savings_goal').value) || 0;
    const savingsDeadline = document.getElementById('savings_deadline').value;
    const debtAmount = parseFloat(document.getElementById('debt_amount').value) || 0;
    
    if (monthlyIncome <= 0) {
        document.getElementById('auto_limit_info').classList.add('hidden');
        return;
    }
    
    // Calculate limit (simplified version of server-side calculation)
    let limit = monthlyIncome;
    let details = '';
    
    if (financialGoal === 'ahorrar' && savingsGoal > 0) {
        if (savingsDeadline) {
            const deadline = new Date(savingsDeadline);
            const today = new Date();
            const months = Math.max(1, Math.ceil((deadline - today) / (1000 * 60 * 60 * 24 * 30)));
            const requiredMonthly = savingsGoal / months;
            const maxSavings = monthlyIncome * 0.50;
            const monthlySavings = Math.min(requiredMonthly, maxSavings);
            limit = monthlyIncome - monthlySavings;
            details = `Basado en ahorro mensual de ${formatCurrency(monthlySavings, currency)} para alcanzar ${formatCurrency(savingsGoal, currency)} en ${months} meses`;
        } else {
            const recommendedSavings = monthlyIncome * 0.25;
            limit = monthlyIncome - recommendedSavings;
            details = `Basado en ahorro recomendado del 25% del ingreso (${formatCurrency(recommendedSavings, currency)})`;
        }
    } else if (financialGoal === 'pagar_deudas' && debtAmount > 0) {
        const minMonthlyPayment = debtAmount / 24;
        const recommendedPayment = Math.max(monthlyIncome * 0.30, minMonthlyPayment);
        const finalPayment = Math.min(recommendedPayment, monthlyIncome * 0.50);
        limit = monthlyIncome - finalPayment;
        details = `Basado en pago mensual de ${formatCurrency(finalPayment, currency)} para pagar la deuda`;
    } else {
        limit = monthlyIncome * 0.80;
        details = 'Basado en límite recomendado del 80% del ingreso';
    }
    
    // Ensure minimum
    const minLimit = monthlyIncome * 0.50;
    limit = Math.max(limit, minLimit);
    
    document.getElementById('auto_limit_value').textContent = formatCurrency(limit, currency);
    document.getElementById('auto_limit_details').textContent = details;
    document.getElementById('auto_limit_info').classList.remove('hidden');
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
    toggleGoalFields();
    
    // Update auto limit when income or currency changes
    document.getElementById('monthly_income').addEventListener('input', function() {
        updateAutoLimit();
        const goal = document.getElementById('financial_goal').value;
        if (goal === 'ahorrar') validateSavingsGoal();
        if (goal === 'pagar_deudas') validateDebtGoal();
        if (document.querySelector('input[name="spending_limit_type"]:checked').value === 'manual') {
            validateSpendingLimit();
        }
    });
    
    document.getElementById('currency').addEventListener('change', function() {
        updateAutoLimit();
    });
    
    // Initialize auto limit display
    updateAutoLimit();
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

