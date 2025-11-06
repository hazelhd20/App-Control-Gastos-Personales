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
                    <label for="amount" class="block text-sm font-medium text-gray-700">
                        <i class="fas fa-dollar-sign mr-2 text-green-600"></i>Monto *
                    </label>
                    <input id="amount" name="amount" type="number" step="0.01" min="0.01" required 
                           value="<?php echo htmlspecialchars($old_data['amount'] ?? ''); ?>"
                           class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-lg"
                           placeholder="0.00">
                </div>

                <!-- Category (for expenses and income) -->
                <div id="category_field">
                    <label for="category" class="block text-sm font-medium text-gray-700">
                        <i class="fas fa-tags mr-2 text-blue-600"></i>Categoría *
                    </label>
                    <select id="category" name="category" 
                            class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Selecciona una categoría</option>
                        <?php foreach ($expense_categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat['name']); ?>" 
                                    data-icon="<?php echo htmlspecialchars($cat['icon']); ?>"
                                    data-color="<?php echo htmlspecialchars($cat['color'] ?? ''); ?>"
                                    <?php echo ($old_data['category'] ?? '') === $cat['name'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Payment Method (for expenses) -->
                <div id="payment_method_field">
                    <label for="payment_method" class="block text-sm font-medium text-gray-700">
                        <i class="fas fa-credit-card mr-2 text-blue-600"></i>Método de Pago *
                    </label>
                    <div class="mt-2 grid grid-cols-2 gap-3 sm:gap-4">
                        <?php foreach ($profile['payment_methods'] as $method): ?>
                            <label class="flex items-center p-3 sm:p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-blue-500 transition">
                                <input type="radio" name="payment_method" value="<?php echo htmlspecialchars($method); ?>" 
                                       <?php echo ($old_data['payment_method'] ?? '') === $method ? 'checked' : ''; ?>
                                       class="w-4 h-4 sm:w-5 sm:h-5 text-blue-600">
                                <span class="ml-2 sm:ml-3 text-gray-700 font-medium text-sm sm:text-base">
                                    <?php echo $method === 'efectivo' ? '💵 Efectivo' : '💳 Tarjeta'; ?>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Transaction Date -->
                <div>
                    <label for="transaction_date" class="block text-sm font-medium text-gray-700">
                        <i class="fas fa-calendar mr-2 text-blue-600"></i>Fecha *
                    </label>
                    <input id="transaction_date" name="transaction_date" type="date" required 
                           value="<?php echo htmlspecialchars($old_data['transaction_date'] ?? date('Y-m-d')); ?>"
                           max="<?php echo date('Y-m-d'); ?>"
                           class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">
                        <i class="fas fa-comment mr-2 text-blue-600"></i>Descripción (opcional)
                    </label>
                    <textarea id="description" name="description" rows="3" 
                              class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                              placeholder="Agrega una nota o descripción..."><?php echo htmlspecialchars($old_data['description'] ?? ''); ?></textarea>
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
    const categorySelect = document.getElementById('category');
    const currentValue = categorySelect.value;
    
    // Clear existing options except the first one
    categorySelect.innerHTML = '<option value="">Selecciona una categoría</option>';
    
    const categories = type === 'expense' ? expenseCategories : incomeCategories;
    categories.forEach(cat => {
        const option = document.createElement('option');
        option.value = cat.name;
        option.textContent = cat.name;
        option.dataset.icon = cat.icon || '';
        option.dataset.color = cat.color || '';
        if (currentValue === cat.name) {
            option.selected = true;
        }
        categorySelect.appendChild(option);
    });
}

function toggleTransactionType() {
    const radios = document.querySelectorAll('.transaction-type-radio');
    const options = document.querySelectorAll('.transaction-type-option');
    const typeInput = document.getElementById('type_input');
    const categoryField = document.getElementById('category_field');
    const paymentMethodField = document.getElementById('payment_method_field');
    const categorySelect = document.getElementById('category');
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
                categorySelect.required = true;
                paymentMethodRadios.forEach(r => r.required = true);
                populateCategories('expense');
            } else {
                options[index].classList.add('border-green-500', 'bg-green-50');
                typeInput.value = 'income';
                categoryField.style.display = 'block';
                paymentMethodField.style.display = 'none';
                categorySelect.required = true;
                paymentMethodRadios.forEach(r => r.required = false);
                populateCategories('income');
            }
        } else {
            options[index].classList.remove('active', 'border-red-500', 'bg-red-50', 'border-green-500', 'bg-green-50');
            options[index].classList.add('border-gray-300');
        }
    });
}

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
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

