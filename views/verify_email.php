<?php
$page_title = 'Verificar Correo - Control de Gastos';
require_once __DIR__ . '/../includes/header.php';

$pending_email = $_SESSION['pending_verification_email'] ?? '';
?>

<div class="min-h-screen flex items-center justify-center blue-gradient py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full">
        <div class="bg-white rounded-2xl shadow-2xl p-8">
            <div class="text-center mb-8">
                <i class="fas fa-envelope-open-text text-6xl text-blue-600 mb-4"></i>
                <h2 class="text-3xl font-extrabold text-gray-900">
                    Verifica tu Correo
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    Te hemos enviado un enlace de verificación
                </p>
            </div>

            <?php 
            $flash = getFlashMessage();
            if ($flash): 
            ?>
                <div class="mb-6 p-4 rounded-lg alert-<?php echo $flash['type']; ?>">
                    <?php echo htmlspecialchars($flash['message']); ?>
                </div>
            <?php endif; ?>

            <div class="space-y-6">
                <?php if ($pending_email): ?>
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-start">
                            <i class="fas fa-info-circle text-blue-600 mt-1 mr-3"></i>
                            <div>
                                <p class="text-sm text-gray-700">
                                    Hemos enviado un correo de verificación a:
                                </p>
                                <p class="font-semibold text-gray-900 mt-1">
                                    <?php echo htmlspecialchars($pending_email); ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <div class="flex items-start">
                            <i class="fas fa-exclamation-triangle text-yellow-600 mt-1 mr-3"></i>
                            <div class="text-sm text-gray-700">
                                <p class="font-semibold mb-2">Instrucciones:</p>
                                <ol class="list-decimal list-inside space-y-1">
                                    <li>Revisa tu bandeja de entrada</li>
                                    <li>Busca el correo de Control de Gastos</li>
                                    <li>Haz clic en el enlace de verificación</li>
                                    <li>Si no lo ves, revisa tu carpeta de spam</li>
                                </ol>
                            </div>
                        </div>
                    </div>

                    <div class="text-center">
                        <p class="text-sm text-gray-600 mb-3">
                            ¿No recibiste el correo?
                        </p>
                        <form action="<?php echo BASE_URL; ?>public/index.php?action=resend-verification" method="POST" class="space-y-4">
                            <input type="hidden" name="email" value="<?php echo htmlspecialchars($pending_email); ?>">
                            <button type="submit" 
                                    class="w-full btn-secondary py-3 px-4 rounded-lg font-semibold">
                                <i class="fas fa-redo mr-2"></i>Reenviar Correo de Verificación
                            </button>
                        </form>
                    </div>

                <?php else: ?>
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                        <p class="text-sm text-gray-700 text-center">
                            Si necesitas verificar tu correo, ingresa tu email abajo:
                        </p>
                    </div>

                    <form action="<?php echo BASE_URL; ?>public/index.php?action=resend-verification" method="POST" class="space-y-4">
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">
                                <i class="fas fa-envelope mr-2 text-blue-600"></i>Correo Electrónico
                            </label>
                            <input id="email" name="email" type="email" required 
                                   class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="tu@email.com">
                        </div>
                        <button type="submit" 
                                class="w-full btn-primary py-3 px-4 rounded-lg font-semibold">
                            <i class="fas fa-paper-plane mr-2"></i>Enviar Correo de Verificación
                        </button>
                    </form>
                <?php endif; ?>

                <div class="text-center pt-4 border-t border-gray-200">
                    <p class="text-sm text-gray-600">
                        <a href="<?php echo BASE_URL; ?>public/index.php?page=login" 
                           class="font-medium text-blue-600 hover:text-blue-500">
                            <i class="fas fa-arrow-left mr-1"></i>Volver al inicio de sesión
                        </a>
                    </p>
                </div>
            </div>
        </div>

        <div class="mt-6 text-center">
            <div class="bg-white bg-opacity-90 rounded-lg p-4 shadow">
                <p class="text-xs text-gray-600">
                    <i class="fas fa-shield-alt mr-1 text-blue-600"></i>
                    El enlace de verificación es válido por 24 horas
                </p>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

