<?php
$page_title = 'Recuperar Contraseña - Control de Gastos';
require_once __DIR__ . '/../includes/header.php';

$flash = getFlashMessage();
$reset_link = $_SESSION['reset_link'] ?? null;
unset($_SESSION['reset_link']);
?>

<div class="min-h-screen flex items-center justify-center blue-gradient py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div class="bg-white rounded-2xl shadow-2xl p-8">
            <div class="text-center">
                <i class="fas fa-key text-6xl text-blue-600 mb-4"></i>
                <h2 class="text-3xl font-extrabold text-gray-900">
                    Recuperar Contraseña
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    Ingresa tu correo electrónico para recibir instrucciones
                </p>
            </div>

            <?php if ($flash): ?>
                <div class="mt-6 alert-auto-hide <?php echo $flash['type'] === 'error' ? 'alert-danger' : 'alert-success'; ?>">
                    <i class="fas <?php echo $flash['type'] === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle'; ?>"></i>
                    <p class="text-sm"><?php echo htmlspecialchars($flash['message']); ?></p>
                </div>
            <?php endif; ?>

            <?php if ($reset_link): ?>
                <div class="mt-6 p-4 rounded-lg bg-blue-50 border-l-4 border-blue-500">
                    <p class="text-sm text-blue-800 font-medium mb-2">
                        <i class="fas fa-info-circle mr-2"></i>Enlace de recuperación generado:
                    </p>
                    <div class="bg-white p-3 rounded border border-blue-200 break-all text-xs">
                        <?php echo htmlspecialchars($reset_link); ?>
                    </div>
                    <p class="text-xs text-blue-700 mt-2">
                        <i class="fas fa-clock mr-1"></i>Válido por 5 minutos
                    </p>
                    <a href="<?php echo htmlspecialchars($reset_link); ?>" 
                       class="mt-3 inline-block w-full text-center btn-primary py-2 px-4 rounded-lg font-semibold">
                        Ir al enlace de recuperación
                    </a>
                </div>
            <?php endif; ?>

            <form class="mt-8 space-y-6" action="<?php echo BASE_URL; ?>public/index.php?action=forgot-password" method="POST" data-validate="true">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">
                        <i class="fas fa-envelope mr-2 text-blue-600"></i>Correo Electrónico
                    </label>
                    <input id="email" name="email" type="email" required 
                           class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                           placeholder="tu@email.com">
                </div>

                <div>
                    <button type="submit" 
                            class="w-full btn-primary py-3 px-4 rounded-lg font-semibold text-lg">
                        <i class="fas fa-paper-plane mr-2"></i>Enviar Enlace de Recuperación
                    </button>
                </div>

                <div class="text-center">
                    <a href="<?php echo BASE_URL; ?>public/index.php?page=login" 
                       class="text-sm font-medium text-blue-600 hover:text-blue-500">
                        <i class="fas fa-arrow-left mr-2"></i>Volver al inicio de sesión
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

