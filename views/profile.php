<?php
$page_title = 'Mi Perfil - Control de Gastos';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

$database = new Database();
$db = $database->getConnection();

$user_model = new User($db);
$profile_model = new FinancialProfile($db);

$user = $user_model->getById($_SESSION['user_id']);
$profile = $profile_model->getByUserId($_SESSION['user_id']);

$flash = getFlashMessage();
$errors = $_SESSION['profile_errors'] ?? [];
unset($_SESSION['profile_errors']);

$password_errors = $_SESSION['password_errors'] ?? [];
unset($_SESSION['password_errors']);
?>

<div class="min-h-screen bg-gray-50 py-6 sm:py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">
                <i class="fas fa-user-circle mr-2 sm:mr-3 text-blue-600"></i>Mi Perfil
            </h1>
            <p class="text-sm sm:text-base text-gray-600 mt-2">Administra tu información personal y configuración financiera</p>
        </div>

        <?php if ($flash): ?>
            <div class="mb-6 p-4 rounded-lg alert-auto-hide <?php echo $flash['type'] === 'error' ? 'alert-danger' : 'alert-success'; ?>">
                <p class="text-sm"><?php echo htmlspecialchars($flash['message']); ?></p>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="mb-6 p-4 rounded-lg alert-danger">
                <ul class="list-disc list-inside text-sm">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
            <!-- Summary Cards -->
            <div class="lg:col-span-1 space-y-4 sm:space-y-6">
                <div class="bg-gradient-to-br from-blue-500 to-blue-700 rounded-xl shadow-lg p-4 sm:p-6 text-white">
                    <div class="flex items-center mb-3 sm:mb-4">
                        <i class="fas fa-wallet text-2xl sm:text-3xl"></i>
                        <div class="ml-3 sm:ml-4">
                            <p class="text-xs sm:text-sm opacity-90">Ingreso Mensual</p>
                            <p class="text-xl sm:text-2xl font-bold">
                                <?php echo formatCurrency($profile['monthly_income'], $profile['currency']); ?>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-3 sm:mb-4">
                        <i class="fas fa-bullseye mr-2 text-blue-600"></i>Objetivo Financiero
                    </h3>
                    <div class="space-y-2">
                        <p class="text-gray-700">
                            <?php
                            $goals = [
                                'ahorrar' => '💰 Ahorrar',
                                'pagar_deudas' => '💳 Pagar Deudas',
                                'controlar_gastos' => '📊 Controlar Gastos',
                                'otro' => '📝 Otro'
                            ];
                            echo $goals[$profile['financial_goal']] ?? 'No definido';
                            ?>
                        </p>
                        <?php if ($profile['savings_goal']): ?>
                            <p class="text-sm text-gray-600">
                                Meta: <?php echo formatCurrency($profile['savings_goal'], $profile['currency']); ?>
                            </p>
                        <?php endif; ?>
                        <?php if ($profile['debt_amount']): ?>
                            <p class="text-sm text-gray-600">
                                Deuda: <?php echo formatCurrency($profile['debt_amount'], $profile['currency']); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-3 sm:mb-4">
                        <i class="fas fa-chart-line mr-2 text-blue-600"></i>Límite de Gasto
                    </h3>
                    <p class="text-xl sm:text-2xl font-bold text-blue-600">
                        <?php echo formatCurrency($profile['spending_limit'], $profile['currency']); ?>
                    </p>
                    <p class="text-xs sm:text-sm text-gray-600 mt-2">por mes</p>
                </div>
            </div>

            <!-- Profile Form -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6">
                    <h2 class="text-xl sm:text-2xl font-bold text-gray-900 mb-4 sm:mb-6">
                        <i class="fas fa-edit mr-2 text-blue-600"></i>Editar Perfil
                    </h2>

                    <form action="<?php echo BASE_URL; ?>public/index.php?action=update-profile" method="POST" class="space-y-5 sm:space-y-6">
                        <!-- Personal Information -->
                        <div>
                            <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-3 sm:mb-4">Información Personal</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4">
                                <div>
                                    <label for="full_name" class="block text-sm font-medium text-gray-700">
                                        Nombre Completo *
                                    </label>
                                    <input id="full_name" name="full_name" type="text" required 
                                           value="<?php echo htmlspecialchars($user['full_name']); ?>"
                                           class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label for="phone" class="block text-sm font-medium text-gray-700">
                                        Teléfono *
                                    </label>
                                    <input id="phone" name="phone" type="tel" required 
                                           value="<?php echo htmlspecialchars($user['phone']); ?>"
                                           class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label for="occupation" class="block text-sm font-medium text-gray-700">
                                        Ocupación *
                                    </label>
                                    <input id="occupation" name="occupation" type="text" required 
                                           value="<?php echo htmlspecialchars($user['occupation']); ?>"
                                           class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700">
                                        Correo Electrónico *
                                    </label>
                                    <input id="email" name="email" type="email" required 
                                           value="<?php echo htmlspecialchars($user['email']); ?>"
                                           class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                        </div>

                        <!-- Financial Information -->
                        <div class="border-t pt-4 sm:pt-6">
                            <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-3 sm:mb-4">Información Financiera</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4">
                                <div>
                                    <label for="monthly_income" class="block text-sm font-medium text-gray-700">
                                        Ingreso Mensual *
                                    </label>
                                    <input id="monthly_income" name="monthly_income" type="number" step="0.01" required 
                                           value="<?php echo htmlspecialchars($profile['monthly_income']); ?>"
                                           class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label for="currency" class="block text-sm font-medium text-gray-700">
                                        Moneda *
                                    </label>
                                    <select id="currency" name="currency" required 
                                            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                        <option value="MXN" <?php echo $profile['currency'] === 'MXN' ? 'selected' : ''; ?>>MXN</option>
                                        <option value="USD" <?php echo $profile['currency'] === 'USD' ? 'selected' : ''; ?>>USD</option>
                                        <option value="EUR" <?php echo $profile['currency'] === 'EUR' ? 'selected' : ''; ?>>EUR</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="spending_limit" class="block text-sm font-medium text-gray-700">
                                        Límite Mensual de Gasto *
                                    </label>
                                    <input id="spending_limit" name="spending_limit" type="number" step="0.01" required 
                                           value="<?php echo htmlspecialchars($profile['spending_limit']); ?>"
                                           class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label for="financial_goal" class="block text-sm font-medium text-gray-700">
                                        Objetivo Financiero *
                                    </label>
                                    <select id="financial_goal" name="financial_goal" required 
                                            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                        <option value="ahorrar" <?php echo $profile['financial_goal'] === 'ahorrar' ? 'selected' : ''; ?>>Ahorrar</option>
                                        <option value="pagar_deudas" <?php echo $profile['financial_goal'] === 'pagar_deudas' ? 'selected' : ''; ?>>Pagar Deudas</option>
                                        <option value="controlar_gastos" <?php echo $profile['financial_goal'] === 'controlar_gastos' ? 'selected' : ''; ?>>Controlar Gastos</option>
                                        <option value="otro" <?php echo $profile['financial_goal'] === 'otro' ? 'selected' : ''; ?>>Otro</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="savings_goal" class="block text-sm font-medium text-gray-700">
                                        Meta de Ahorro
                                    </label>
                                    <input id="savings_goal" name="savings_goal" type="number" step="0.01" 
                                           value="<?php echo htmlspecialchars($profile['savings_goal'] ?? ''); ?>"
                                           class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label for="savings_deadline" class="block text-sm font-medium text-gray-700">
                                        Fecha Objetivo
                                    </label>
                                    <input id="savings_deadline" name="savings_deadline" type="date" 
                                           value="<?php echo htmlspecialchars($profile['savings_deadline'] ?? ''); ?>"
                                           class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>

                            <div class="mt-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Medios de Pago *
                                </label>
                                <div class="flex gap-4">
                                    <label class="flex items-center">
                                        <input type="checkbox" name="payment_methods[]" value="efectivo" 
                                               <?php echo in_array('efectivo', $profile['payment_methods']) ? 'checked' : ''; ?>
                                               class="w-4 h-4 text-blue-600 rounded">
                                        <span class="ml-2">Efectivo</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="payment_methods[]" value="tarjeta" 
                                               <?php echo in_array('tarjeta', $profile['payment_methods']) ? 'checked' : ''; ?>
                                               class="w-4 h-4 text-blue-600 rounded">
                                        <span class="ml-2">Tarjeta</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row justify-end gap-2 sm:gap-0 pt-4">
                            <button type="submit" 
                                    class="btn-primary py-2 px-4 sm:px-6 rounded-lg font-semibold text-sm sm:text-base w-full sm:w-auto">
                                <i class="fas fa-save mr-2"></i>Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Change Password Section -->
                <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6 mt-4 sm:mt-6">
                    <h2 class="text-xl sm:text-2xl font-bold text-gray-900 mb-4 sm:mb-6">
                        <i class="fas fa-lock mr-2 text-blue-600"></i>Cambiar Contraseña
                    </h2>

                    <?php if (!empty($password_errors)): ?>
                        <div class="mb-6 p-4 rounded-lg alert-danger">
                            <ul class="list-disc list-inside text-sm">
                                <?php foreach ($password_errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form action="<?php echo BASE_URL; ?>public/index.php?action=change-password" method="POST" class="space-y-3 sm:space-y-4">
                        <div>
                            <label for="current_password" class="block text-sm font-medium text-gray-700">
                                Contraseña Actual *
                            </label>
                            <div class="relative mt-1">
                                <input id="current_password" name="current_password" type="password" required 
                                       class="block w-full px-4 py-2 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <span class="absolute right-3 top-3 toggle-password cursor-pointer">
                                    <i class="fas fa-eye text-gray-400"></i>
                                </span>
                            </div>
                        </div>

                        <div>
                            <label for="new_password" class="block text-sm font-medium text-gray-700">
                                Nueva Contraseña *
                            </label>
                            <div class="relative mt-1">
                                <input id="new_password" name="new_password" type="password" required 
                                       class="block w-full px-4 py-2 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <span class="absolute right-3 top-3 toggle-password cursor-pointer">
                                    <i class="fas fa-eye text-gray-400"></i>
                                </span>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">
                                Mínimo 8 caracteres, debe incluir mayúsculas, números y caracteres especiales
                            </p>
                        </div>

                        <div>
                            <label for="confirm_password" class="block text-sm font-medium text-gray-700">
                                Confirmar Nueva Contraseña *
                            </label>
                            <div class="relative mt-1">
                                <input id="confirm_password" name="confirm_password" type="password" required 
                                       class="block w-full px-4 py-2 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <span class="absolute right-3 top-3 toggle-password cursor-pointer">
                                    <i class="fas fa-eye text-gray-400"></i>
                                </span>
                            </div>
                        </div>

                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700">
                                        La nueva contraseña debe ser diferente a tu contraseña actual por seguridad.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row justify-end gap-2 sm:gap-0 pt-4">
                            <button type="submit" 
                                    class="btn-primary py-2 px-4 sm:px-6 rounded-lg font-semibold text-sm sm:text-base w-full sm:w-auto">
                                <i class="fas fa-key mr-2"></i>Actualizar Contraseña
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

