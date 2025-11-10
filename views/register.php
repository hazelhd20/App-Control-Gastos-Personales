<?php
$page_title = 'Registro - Control de Gastos';
require_once __DIR__ . '/../includes/header.php';

$errors = $_SESSION['register_errors'] ?? [];
$old_data = $_SESSION['register_data'] ?? [];
unset($_SESSION['register_errors'], $_SESSION['register_data']);
?>

<div class="min-h-screen flex items-center justify-center blue-gradient py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl w-full">
        <div class="bg-white rounded-2xl shadow-2xl p-8">
            <div class="text-center mb-8">
                <i class="fas fa-user-plus text-6xl text-blue-600 mb-4"></i>
                <h2 class="text-3xl font-extrabold text-gray-900">
                    Crear Cuenta Nueva
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    Comienza a controlar tus gastos hoy
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

            <form action="<?php echo BASE_URL; ?>public/index.php?action=register" method="POST" class="space-y-6" data-validate="true" data-validate-on-input="true" data-validate-on-blur="true">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="full_name" class="block text-sm font-medium text-gray-700">
                            <i class="fas fa-user mr-2 text-blue-600"></i>Nombre Completo *
                        </label>
                        <input id="full_name" name="full_name" type="text" required 
                               value="<?php echo htmlspecialchars($old_data['full_name'] ?? ''); ?>"
                               class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Juan Pérez">
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700">
                            <i class="fas fa-phone mr-2 text-blue-600"></i>Teléfono *
                        </label>
                        <input id="phone" name="phone" type="tel" required 
                               value="<?php echo htmlspecialchars($old_data['phone'] ?? ''); ?>"
                               class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="555-123-4567">
                    </div>

                    <div>
                        <label for="occupation" class="block text-sm font-medium text-gray-700">
                            <i class="fas fa-briefcase mr-2 text-blue-600"></i>Ocupación *
                        </label>
                        <input id="occupation" name="occupation" type="text" required 
                               value="<?php echo htmlspecialchars($old_data['occupation'] ?? ''); ?>"
                               class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Ingeniero">
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700" data-label="Correo Electrónico">
                            <i class="fas fa-envelope mr-2 text-blue-600"></i>Correo Electrónico *
                        </label>
                        <input id="email" name="email" type="email" required 
                               value="<?php echo htmlspecialchars($old_data['email'] ?? ''); ?>"
                               class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="tu@email.com">
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700" data-label="Contraseña">
                            <i class="fas fa-lock mr-2 text-blue-600"></i>Contraseña *
                        </label>
                        <div class="relative mt-1">
                            <input id="password" name="password" type="password" required 
                                   data-validate-password="true"
                                   data-min-length="8"
                                   class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="••••••••">
                            <span class="absolute right-3 top-3 toggle-password cursor-pointer">
                                <i class="fas fa-eye text-gray-400"></i>
                            </span>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">
                            Mínimo 8 caracteres, una mayúscula, un número y un carácter especial
                        </p>
                    </div>

                    <div>
                        <label for="confirm_password" class="block text-sm font-medium text-gray-700" data-label="Confirmar Contraseña">
                            <i class="fas fa-lock mr-2 text-blue-600"></i>Confirmar Contraseña *
                        </label>
                        <div class="relative mt-1">
                            <input id="confirm_password" name="confirm_password" type="password" required 
                                   data-confirm-password="password"
                                   class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="••••••••">
                            <span class="absolute right-3 top-3 toggle-password cursor-pointer">
                                <i class="fas fa-eye text-gray-400"></i>
                            </span>
                        </div>
                    </div>
                </div>

                <div>
                    <button type="submit" 
                            class="w-full btn-primary py-3 px-4 rounded-lg font-semibold text-lg">
                        <i class="fas fa-user-plus mr-2"></i>Crear Cuenta
                    </button>
                </div>

                <div class="text-center">
                    <p class="text-sm text-gray-600">
                        ¿Ya tienes cuenta? 
                        <a href="<?php echo BASE_URL; ?>public/index.php?page=login" 
                           class="font-medium text-blue-600 hover:text-blue-500">
                            Inicia sesión aquí
                        </a>
                    </p>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

