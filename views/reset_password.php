<?php
$page_title = 'Restablecer Contraseña - Control de Gastos';
require_once __DIR__ . '/../includes/header.php';

$token = $_GET['token'] ?? '';
$errors = $_SESSION['reset_errors'] ?? [];
unset($_SESSION['reset_errors']);

// Verify token
$database = new Database();
$db = $database->getConnection();
$user = new User($db);
$valid_token = $user->verifyResetToken($token);
?>

<div class="min-h-screen flex items-center justify-center blue-gradient py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div class="bg-white rounded-2xl shadow-2xl p-8">
            <div class="text-center">
                <i class="fas fa-lock-open text-6xl text-blue-600 mb-4"></i>
                <h2 class="text-3xl font-extrabold text-gray-900">
                    Restablecer Contraseña
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    Ingresa tu nueva contraseña
                </p>
            </div>

            <?php if (!$valid_token): ?>
                <div class="mt-6 p-4 rounded-lg alert-danger">
                    <p class="text-sm">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        El enlace de recuperación ha expirado o no es válido. Por favor solicita uno nuevo.
                    </p>
                </div>
                <div class="mt-6">
                    <a href="<?php echo BASE_URL; ?>public/index.php?page=forgot-password" 
                       class="w-full inline-block text-center btn-primary py-3 px-4 rounded-lg font-semibold">
                        Solicitar nuevo enlace
                    </a>
                </div>
            <?php else: ?>
                <?php if (!empty($errors)): ?>
                    <div class="mt-6 p-4 rounded-lg alert-danger">
                        <ul class="list-disc list-inside text-sm">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form class="mt-8 space-y-6" action="<?php echo BASE_URL; ?>public/index.php?action=reset-password" method="POST">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">
                            <i class="fas fa-lock mr-2 text-blue-600"></i>Nueva Contraseña
                        </label>
                        <div class="relative mt-1">
                            <input id="password" name="password" type="password" required 
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
                        <label for="confirm_password" class="block text-sm font-medium text-gray-700">
                            <i class="fas fa-lock mr-2 text-blue-600"></i>Confirmar Nueva Contraseña
                        </label>
                        <div class="relative mt-1">
                            <input id="confirm_password" name="confirm_password" type="password" required 
                                   class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="••••••••">
                            <span class="absolute right-3 top-3 toggle-password cursor-pointer">
                                <i class="fas fa-eye text-gray-400"></i>
                            </span>
                        </div>
                    </div>

                    <div>
                        <button type="submit" 
                                class="w-full btn-primary py-3 px-4 rounded-lg font-semibold text-lg">
                            <i class="fas fa-check mr-2"></i>Restablecer Contraseña
                        </button>
                    </div>
                </form>
            <?php endif; ?>

            <div class="mt-6 text-center">
                <a href="<?php echo BASE_URL; ?>public/index.php?page=login" 
                   class="text-sm font-medium text-blue-600 hover:text-blue-500">
                    <i class="fas fa-arrow-left mr-2"></i>Volver al inicio de sesión
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

