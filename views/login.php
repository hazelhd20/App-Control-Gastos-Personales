<?php
$page_title = 'Iniciar Sesión - Control de Gastos';
require_once __DIR__ . '/../includes/header.php';

$flash = getFlashMessage();
?>

<div class="min-h-screen flex items-center justify-center blue-gradient py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div class="bg-white rounded-2xl shadow-2xl p-8">
            <div class="text-center">
                <i class="fas fa-wallet text-6xl text-blue-600 mb-4"></i>
                <h2 class="text-3xl font-extrabold text-gray-900">
                    Control de Gastos
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    Inicia sesión para gestionar tus finanzas
                </p>
            </div>

            <?php if ($flash): ?>
                <div class="mt-4 p-4 rounded-lg alert-auto-hide <?php echo $flash['type'] === 'error' ? 'alert-danger' : 'alert-success'; ?>">
                    <p class="text-sm"><?php echo htmlspecialchars($flash['message']); ?></p>
                </div>
            <?php endif; ?>

            <form class="mt-8 space-y-6" action="<?php echo BASE_URL; ?>public/index.php?action=login" method="POST">
                <div class="space-y-4">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">
                            <i class="fas fa-envelope mr-2 text-blue-600"></i>Correo Electrónico
                        </label>
                        <input id="email" name="email" type="email" required 
                               class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                               placeholder="tu@email.com">
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">
                            <i class="fas fa-lock mr-2 text-blue-600"></i>Contraseña
                        </label>
                        <div class="relative mt-1">
                            <input id="password" name="password" type="password" required 
                                   class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                                   placeholder="••••••••">
                            <span class="absolute right-3 top-3 toggle-password" onclick="togglePassword('password')">
                                <i class="fas fa-eye text-gray-400"></i>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <div class="text-sm">
                        <a href="<?php echo BASE_URL; ?>public/index.php?page=forgot-password" 
                           class="font-medium text-blue-600 hover:text-blue-500">
                            ¿Olvidaste tu contraseña?
                        </a>
                    </div>
                </div>

                <div>
                    <button type="submit" 
                            class="w-full btn-primary py-3 px-4 rounded-lg font-semibold text-lg">
                        <i class="fas fa-sign-in-alt mr-2"></i>Iniciar Sesión
                    </button>
                </div>

                <div class="text-center">
                    <p class="text-sm text-gray-600">
                        ¿No tienes cuenta? 
                        <a href="<?php echo BASE_URL; ?>public/index.php?page=register" 
                           class="font-medium text-blue-600 hover:text-blue-500">
                            Regístrate aquí
                        </a>
                    </p>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const icon = event.currentTarget.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

