<?php
$page_title = 'Registrar Transacción - Control de Gastos';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

$database = new Database();
$db = $database->getConnection();

$transaction_model = new Transaction($db);
$profile_model = new FinancialProfile($db);

// Get categories by type - will be updated dynamically with JS
$expense_categories = $transaction_model->getCategories($_SESSION['user_id'], 'expense');
$income_categories = $transaction_model->getCategories($_SESSION['user_id'], 'income');
$profile = $profile_model->getByUserId($_SESSION['user_id']);

$errors = $_SESSION['transaction_errors'] ?? [];
$old_data = $_SESSION['transaction_data'] ?? [];
unset($_SESSION['transaction_errors'], $_SESSION['transaction_data']);
?>

<div class="min-h-screen bg-gray-50 py-6 sm:py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <a href="<?php echo BASE_URL; ?>public/index.php?page=dashboard" 
               class="text-blue-600 hover:text-blue-700 inline-flex items-center mb-4 text-sm sm:text-base">
                <i class="fas fa-arrow-left mr-2"></i>Volver al Dashboard
            </a>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">
                <i class="fas fa-plus-circle mr-2 sm:mr-3 text-blue-600"></i>Registrar Transacción
            </h1>
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

        <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6 lg:p-8">
            <!-- Transaction Type Selection -->
            <div class="mb-6 sm:mb-8">
                <label class="block text-sm font-medium text-gray-700 mb-3 sm:mb-4">
                    Tipo de Transacción *
                </label>
                <div class="grid grid-cols-2 gap-3 sm:gap-4">
                    <label class="cursor-pointer">
                        <input type="radio" name="type" value="expense" checked 
                               onchange="toggleTransactionType()"
                               class="hidden transaction-type-radio">
                        <div class="p-4 sm:p-6 border-2 border-red-500 bg-red-50 rounded-xl text-center transaction-type-option active">
                            <i class="fas fa-arrow-up text-3xl sm:text-4xl text-red-600 mb-2 sm:mb-3"></i>
                            <p class="font-bold text-gray-900 text-sm sm:text-base">Gasto</p>
                            <p class="text-xs sm:text-sm text-gray-600 mt-1">Registrar un gasto</p>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="type" value="income" 
                               onchange="toggleTransactionType()"
                               class="hidden transaction-type-radio">
                        <div class="p-4 sm:p-6 border-2 border-gray-300 rounded-xl text-center transaction-type-option">
                            <i class="fas fa-arrow-down text-3xl sm:text-4xl text-green-600 mb-2 sm:mb-3"></i>
                            <p class="font-bold text-gray-900 text-sm sm:text-base">Ingreso</p>
                            <p class="text-xs sm:text-sm text-gray-600 mt-1">Registrar un ingreso adicional</p>
                        </div>
                    </label>
                </div>
            </div>

            <form id="transactionForm" action="<?php echo BASE_URL; ?>public/index.php?action=add-transaction" method="POST" class="space-y-5 sm:space-y-6">
                <input type="hidden" name="type" id="type_input" value="expense">

                <!-- Amount -->
                <div>
                    <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-dollar-sign mr-2 text-green-600"></i>Monto *
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <span class="text-gray-500 text-lg font-semibold">$</span>
                        </div>
                        <input id="amount" name="amount" type="number" step="0.01" min="0.01" required 
                               value="<?php echo htmlspecialchars($old_data['amount'] ?? ''); ?>"
                               class="block w-full pl-8 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg font-semibold transition-all"
                               placeholder="0.00">
                    </div>
                </div>

                <!-- Category (for expenses and income) -->
                <div id="category_field">
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        <i class="fas fa-tags mr-2 text-blue-600"></i>Categoría *
                    </label>
                    <input type="hidden" id="category" name="category" value="<?php echo htmlspecialchars($old_data['category'] ?? ''); ?>" required>
                    <div id="category_grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 max-h-64 overflow-y-auto p-2 border border-gray-200 rounded-lg bg-gray-50">
                        <!-- Categories will be populated by JavaScript -->
                    </div>
                    <p id="category_error" class="mt-2 text-sm text-red-600 hidden">Por favor selecciona una categoría</p>
                </div>

                <!-- Payment Method (for expenses) -->
                <div id="payment_method_field">
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        <i class="fas fa-credit-card mr-2 text-blue-600"></i>Método de Pago *
                    </label>
                    <div class="grid grid-cols-2 gap-3 sm:gap-4">
                        <?php foreach ($profile['payment_methods'] as $method): ?>
                            <label class="payment-method-option flex items-center justify-center p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-blue-500 hover:bg-blue-50 transition-all duration-200 <?php echo ($old_data['payment_method'] ?? '') === $method ? 'border-blue-500 bg-blue-50 shadow-md' : ''; ?>">
                                <input type="radio" name="payment_method" value="<?php echo htmlspecialchars($method); ?>" 
                                       <?php echo ($old_data['payment_method'] ?? '') === $method ? 'checked' : ''; ?>
                                       class="sr-only payment-method-radio">
                                <div class="flex flex-col items-center">
                                    <i class="fas <?php echo $method === 'efectivo' ? 'fa-money-bill-wave' : 'fa-credit-card'; ?> text-2xl mb-2 <?php echo $method === 'efectivo' ? 'text-green-600' : 'text-blue-600'; ?>"></i>
                                    <span class="text-gray-700 font-semibold text-sm sm:text-base">
                                        <?php echo $method === 'efectivo' ? 'Efectivo' : 'Tarjeta'; ?>
                                    </span>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Transaction Date -->
                <div>
                    <label for="transaction_date" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-calendar mr-2 text-blue-600"></i>Fecha *
                    </label>
                    <div class="relative">
                        <input id="transaction_date" name="transaction_date" type="date" required 
                               value="<?php echo htmlspecialchars($old_data['transaction_date'] ?? date('Y-m-d')); ?>"
                               max="<?php echo date('Y-m-d'); ?>"
                               class="block w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-comment mr-2 text-blue-600"></i>Descripción (opcional)
                    </label>
                    <textarea id="description" name="description" rows="3" 
                              class="block w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all resize-none"
                              placeholder="Agrega una nota o descripción adicional..."><?php echo htmlspecialchars($old_data['description'] ?? ''); ?></textarea>
                </div>

                <!-- Submit Buttons -->
                <div class="flex flex-col sm:flex-row justify-end gap-3 sm:gap-4 sm:space-x-4 pt-4">
                    <a href="<?php echo BASE_URL; ?>public/index.php?page=dashboard" 
                       class="px-4 sm:px-6 py-2.5 sm:py-3 border-2 border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-100 transition text-center text-sm sm:text-base w-full sm:w-auto">
                        Cancelar
                    </a>
                    <button type="submit" 
                            class="btn-primary py-2.5 sm:py-3 px-6 sm:px-8 rounded-lg font-semibold text-base sm:text-lg w-full sm:w-auto">
                        <i class="fas fa-save mr-2"></i>Registrar Transacción
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const expenseCategories = <?php echo json_encode($expense_categories); ?>;
const incomeCategories = <?php echo json_encode($income_categories); ?>;

function populateCategories(type) {
    const categoryGrid = document.getElementById('category_grid');
    const categoryInput = document.getElementById('category');
    const currentValue = categoryInput.value;
    
    // Clear existing categories
    categoryGrid.innerHTML = '';
    
    const categories = type === 'expense' ? expenseCategories : incomeCategories;
    
    if (categories.length === 0) {
        categoryGrid.innerHTML = '<p class="col-span-full text-center text-gray-500 py-4">No hay categorías disponibles. <a href="<?php echo BASE_URL; ?>public/index.php?page=manage-categories" class="text-blue-600 hover:underline">Crear categoría</a></p>';
        return;
    }
    
    categories.forEach(cat => {
        const icon = cat.icon || 'fa-tag';
        const color = cat.color || '#6B7280';
        const isSelected = currentValue === cat.name;
        
        const categoryCard = document.createElement('div');
        categoryCard.className = `category-card p-3 rounded-lg border-2 cursor-pointer transition-all duration-200 hover:scale-105 hover:shadow-md ${isSelected ? 'border-blue-500 bg-blue-50 shadow-md' : 'border-gray-200 bg-white hover:border-gray-300'}`;
        categoryCard.dataset.categoryName = cat.name;
        categoryCard.dataset.categoryIcon = icon;
        categoryCard.dataset.categoryColor = color;
        
        categoryCard.innerHTML = `
            <div class="flex flex-col items-center text-center relative">
                <div class="w-10 h-10 rounded-full flex items-center justify-center mb-2 transition-all duration-200" style="background-color: ${color}20; border: 2px solid ${color}40;">
                    <i class="fas ${icon} text-lg transition-transform duration-200" style="color: ${color};"></i>
                </div>
                <span class="text-xs font-medium text-gray-700 leading-tight">${cat.name}</span>
                ${isSelected ? '<div class="absolute -top-1 -right-1 w-5 h-5 bg-blue-500 rounded-full flex items-center justify-center"><i class="fas fa-check text-white text-xs"></i></div>' : ''}
            </div>
        `;
        
        categoryCard.addEventListener('click', function() {
            // Remove selected state from all cards
            document.querySelectorAll('.category-card').forEach(card => {
                card.classList.remove('border-blue-500', 'bg-blue-50', 'shadow-md');
                card.classList.add('border-gray-200', 'bg-white');
                // Remove checkmark
                const checkmark = card.querySelector('.absolute');
                if (checkmark) checkmark.remove();
            });
            
            // Add selected state to clicked card
            this.classList.remove('border-gray-200', 'bg-white');
            this.classList.add('border-blue-500', 'bg-blue-50', 'shadow-md');
            
            // Add checkmark with animation
            const cardContent = this.querySelector('.flex.flex-col');
            if (cardContent && !cardContent.querySelector('.absolute')) {
                const checkmark = document.createElement('div');
                checkmark.className = 'absolute -top-1 -right-1 w-5 h-5 bg-blue-500 rounded-full flex items-center justify-center';
                checkmark.innerHTML = '<i class="fas fa-check text-white text-xs"></i>';
                checkmark.style.animation = 'checkmarkPop 0.3s ease-out';
                cardContent.appendChild(checkmark);
            }
            
            // Update hidden input
            categoryInput.value = cat.name;
            
            // Hide error message
            document.getElementById('category_error').classList.add('hidden');
        });
        
        categoryGrid.appendChild(categoryCard);
    });
    
    // Restore selection if there was one
    if (currentValue) {
        const selectedCard = categoryGrid.querySelector(`[data-category-name="${currentValue}"]`);
        if (selectedCard) {
            selectedCard.click();
        }
    }
}

function toggleTransactionType() {
    const radios = document.querySelectorAll('.transaction-type-radio');
    const options = document.querySelectorAll('.transaction-type-option');
    const typeInput = document.getElementById('type_input');
    const categoryField = document.getElementById('category_field');
    const paymentMethodField = document.getElementById('payment_method_field');
    const categoryInput = document.getElementById('category');
    const paymentMethodRadios = document.querySelectorAll('input[name="payment_method"]');
    
    radios.forEach((radio, index) => {
        if (radio.checked) {
            options[index].classList.add('active');
            options[index].classList.remove('border-gray-300');
            
            if (radio.value === 'expense') {
                options[index].classList.add('border-red-500', 'bg-red-50');
                typeInput.value = 'expense';
                categoryField.style.display = 'block';
                paymentMethodField.style.display = 'block';
                categoryInput.required = true;
                paymentMethodRadios.forEach(r => r.required = true);
                // Reset category selection when switching types
                categoryInput.value = '';
                populateCategories('expense');
            } else {
                options[index].classList.add('border-green-500', 'bg-green-50');
                typeInput.value = 'income';
                categoryField.style.display = 'block';
                paymentMethodField.style.display = 'none';
                categoryInput.required = true;
                paymentMethodRadios.forEach(r => r.required = false);
                // Reset category selection when switching types
                categoryInput.value = '';
                populateCategories('income');
            }
        } else {
            options[index].classList.remove('active', 'border-red-500', 'bg-red-50', 'border-green-500', 'bg-green-50');
            options[index].classList.add('border-gray-300');
        }
    });
}

// Payment method selection handler
function updatePaymentMethodStyles() {
    document.querySelectorAll('.payment-method-radio').forEach(radio => {
        const option = radio.closest('.payment-method-option');
        if (radio.checked) {
            option.classList.remove('border-gray-300');
            option.classList.add('border-blue-500', 'bg-blue-50', 'shadow-md');
        } else {
            option.classList.remove('border-blue-500', 'bg-blue-50', 'shadow-md');
            option.classList.add('border-gray-300');
        }
    });
}

document.querySelectorAll('.payment-method-radio').forEach(radio => {
    radio.addEventListener('change', updatePaymentMethodStyles);
});

// Initialize payment method styles on page load
updatePaymentMethodStyles();

// Form validation
document.getElementById('transactionForm').addEventListener('submit', function(e) {
    const categoryInput = document.getElementById('category');
    const categoryError = document.getElementById('category_error');
    
    if (!categoryInput.value) {
        e.preventDefault();
        categoryError.classList.remove('hidden');
        // Scroll to category field
        categoryInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return false;
    } else {
        categoryError.classList.add('hidden');
    }
});

// Handle option clicks
document.querySelectorAll('.transaction-type-option').forEach((option, index) => {
    option.addEventListener('click', function() {
        const radio = this.previousElementSibling || this.parentElement.querySelector('input');
        radio.checked = true;
        toggleTransactionType();
    });
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleTransactionType();
});
</script>

<style>
.transaction-type-option {
    transition: all 0.3s ease;
}

.transaction-type-option.active {
    transform: scale(1.02);
}

.category-card {
    min-height: 90px;
    position: relative;
}

.category-card:hover {
    transform: translateY(-2px);
}

.category-card:active {
    transform: scale(0.98);
}

/* Checkmark animation */
@keyframes checkmarkPop {
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

.category-card .absolute {
    animation: checkmarkPop 0.3s ease-out;
}

/* Payment method selection styles */
.payment-method-option {
    position: relative;
}

.payment-method-option:hover {
    transform: translateY(-2px);
}

/* Smooth scrollbar for category grid */
#category_grid::-webkit-scrollbar {
    width: 8px;
}

#category_grid::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

#category_grid::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}

#category_grid::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Improved input focus states */
input[type="number"]:focus,
input[type="date"]:focus,
textarea:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Animation for form fields */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

#category_field,
#payment_method_field {
    animation: fadeIn 0.3s ease-out;
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

