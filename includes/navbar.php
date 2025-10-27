<?php
$current_page = $_GET['page'] ?? 'dashboard';
?>

<nav class="bg-white shadow-lg sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <a href="<?php echo BASE_URL; ?>public/index.php?page=dashboard" class="flex items-center">
                    <i class="fas fa-wallet text-3xl text-blue-600"></i>
                    <span class="ml-2 text-xl font-bold text-blue-900">Control de Gastos</span>
                </a>
            </div>
            
            <div class="hidden md:flex items-center space-x-1">
                <a href="<?php echo BASE_URL; ?>public/index.php?page=dashboard" 
                   class="px-4 py-2 rounded-lg <?php echo $current_page === 'dashboard' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-gray-100'; ?>">
                    <i class="fas fa-home mr-2"></i>Dashboard
                </a>
                <a href="<?php echo BASE_URL; ?>public/index.php?page=transactions" 
                   class="px-4 py-2 rounded-lg <?php echo $current_page === 'transactions' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-gray-100'; ?>">
                    <i class="fas fa-list mr-2"></i>Transacciones
                </a>
                <a href="<?php echo BASE_URL; ?>public/index.php?page=reports" 
                   class="px-4 py-2 rounded-lg <?php echo $current_page === 'reports' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-gray-100'; ?>">
                    <i class="fas fa-chart-bar mr-2"></i>Reportes
                </a>
                <a href="<?php echo BASE_URL; ?>public/index.php?page=profile" 
                   class="px-4 py-2 rounded-lg <?php echo $current_page === 'profile' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-gray-100'; ?>">
                    <i class="fas fa-user mr-2"></i>Perfil
                </a>
                <a href="<?php echo BASE_URL; ?>public/index.php?action=logout" 
                   class="px-4 py-2 rounded-lg text-red-600 hover:bg-red-50">
                    <i class="fas fa-sign-out-alt mr-2"></i>Salir
                </a>
            </div>
            
            <!-- Mobile menu button -->
            <div class="md:hidden flex items-center">
                <button type="button" class="text-gray-700 hover:text-blue-600" id="mobile-menu-button">
                    <i class="fas fa-bars text-2xl"></i>
                </button>
            </div>
        </div>
    </div>
    
    <!-- Mobile menu -->
    <div class="md:hidden hidden transition-all duration-300 ease-in-out" id="mobile-menu">
        <div class="px-2 pt-2 pb-3 space-y-1 bg-white border-t border-gray-200">
            <a href="<?php echo BASE_URL; ?>public/index.php?page=dashboard" 
               class="block px-3 py-2 rounded-lg <?php echo $current_page === 'dashboard' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-gray-100'; ?>">
                <i class="fas fa-home mr-2"></i>Dashboard
            </a>
            <a href="<?php echo BASE_URL; ?>public/index.php?page=transactions" 
               class="block px-3 py-2 rounded-lg <?php echo $current_page === 'transactions' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-gray-100'; ?>">
                <i class="fas fa-list mr-2"></i>Transacciones
            </a>
            <a href="<?php echo BASE_URL; ?>public/index.php?page=reports" 
               class="block px-3 py-2 rounded-lg <?php echo $current_page === 'reports' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-gray-100'; ?>">
                <i class="fas fa-chart-bar mr-2"></i>Reportes
            </a>
            <a href="<?php echo BASE_URL; ?>public/index.php?page=profile" 
               class="block px-3 py-2 rounded-lg <?php echo $current_page === 'profile' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-gray-100'; ?>">
                <i class="fas fa-user mr-2"></i>Perfil
            </a>
            <a href="<?php echo BASE_URL; ?>public/index.php?action=logout" 
               class="block px-3 py-2 rounded-lg text-red-600 hover:bg-red-50">
                <i class="fas fa-sign-out-alt mr-2"></i>Salir
            </a>
        </div>
    </div>
</nav>

<script>
    // Esperar a que el DOM esté completamente cargado
    document.addEventListener('DOMContentLoaded', function() {
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');
        
        if (mobileMenuButton && mobileMenu) {
            mobileMenuButton.addEventListener('click', function(e) {
                e.preventDefault();
                mobileMenu.classList.toggle('hidden');
            });
            
            // Cerrar menú al hacer clic en un enlace
            const menuLinks = mobileMenu.querySelectorAll('a');
            menuLinks.forEach(link => {
                link.addEventListener('click', function() {
                    mobileMenu.classList.add('hidden');
                });
            });
            
            // Cerrar menú al hacer clic fuera de él
            document.addEventListener('click', function(event) {
                const isClickInside = mobileMenu.contains(event.target) || 
                                    mobileMenuButton.contains(event.target);
                
                if (!isClickInside && !mobileMenu.classList.contains('hidden')) {
                    mobileMenu.classList.add('hidden');
                }
            });
        }
    });
</script>

