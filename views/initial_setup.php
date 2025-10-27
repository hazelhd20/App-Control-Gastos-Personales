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
                <div id="savings_fields" class="hidden grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="savings_goal" class="block text-sm font-medium text-gray-700">
                            <i class="fas fa-piggy-bank mr-2 text-pink-600"></i>Meta de Ahorro
                        </label>
                        <input id="savings_goal" name="savings_goal" type="number" step="0.01" min="0" 
                               value="<?php echo htmlspecialchars($old_data['savings_goal'] ?? ''); ?>"
                               class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="10000.00">
                    </div>
                    <div>
                        <label for="savings_deadline" class="block text-sm font-medium text-gray-700">
                            <i class="fas fa-calendar-check mr-2 text-pink-600"></i>Fecha Objetivo
                        </label>
                        <input id="savings_deadline" name="savings_deadline" type="date" 
                               value="<?php echo htmlspecialchars($old_data['savings_deadline'] ?? ''); ?>"
                               class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                </div>

                <!-- Debt Fields -->
                <div id="debt_fields" class="hidden">
                    <label for="debt_amount" class="block text-sm font-medium text-gray-700">
                        <i class="fas fa-hand-holding-usd mr-2 text-red-600"></i>Monto Total de Deuda
                    </label>
                    <input id="debt_amount" name="debt_amount" type="number" step="0.01" min="0" 
                           value="<?php echo htmlspecialchars($old_data['debt_amount'] ?? ''); ?>"
                           class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="50000.00">
                </div>

                <!-- Other Goal Description -->
                <div id="other_goal_fields" class="hidden">
                    <label for="goal_description" class="block text-sm font-medium text-gray-700">
                        <i class="fas fa-comment mr-2 text-blue-600"></i>Describe tu Objetivo
                    </label>
                    <textarea id="goal_description" name="goal_description" rows="3" 
                              class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                              placeholder="Describe tu objetivo financiero..."><?php echo htmlspecialchars($old_data['goal_description'] ?? ''); ?></textarea>
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
                               placeholder="12000.00">
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
function toggleGoalFields() {
    const goal = document.getElementById('financial_goal').value;
    document.getElementById('savings_fields').classList.add('hidden');
    document.getElementById('debt_fields').classList.add('hidden');
    document.getElementById('other_goal_fields').classList.add('hidden');
    
    if (goal === 'ahorrar') {
        document.getElementById('savings_fields').classList.remove('hidden');
    } else if (goal === 'pagar_deudas') {
        document.getElementById('debt_fields').classList.remove('hidden');
    } else if (goal === 'otro') {
        document.getElementById('other_goal_fields').classList.remove('hidden');
    }
}

function toggleSpendingLimit() {
    const isManual = document.querySelector('input[name="spending_limit_type"]:checked').value === 'manual';
    const manualField = document.getElementById('manual_limit_field');
    
    if (isManual) {
        manualField.classList.remove('hidden');
        document.getElementById('spending_limit').required = true;
    } else {
        manualField.classList.add('hidden');
        document.getElementById('spending_limit').required = false;
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleGoalFields();
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

