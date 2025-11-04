<?php
$page_title = 'Gestionar Categorías - Control de Gastos';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

$database = new Database();
$db = $database->getConnection();
$category_model = new Category($db);

$user_id = $_SESSION['user_id'];

// Group custom categories by type
$custom_categories = $category_model->getCustomCategoriesByUser($user_id);
$expense_categories = array_filter($custom_categories, fn($cat) => $cat['type'] === 'expense');
$income_categories = array_filter($custom_categories, fn($cat) => $cat['type'] === 'income');

$errors = $_SESSION['category_errors'] ?? [];
unset($_SESSION['category_errors']);

$flash = getFlashMessage();
?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <a href="<?php echo BASE_URL; ?>public/index.php?page=dashboard" 
               class="text-blue-600 hover:text-blue-700 inline-flex items-center mb-4">
                <i class="fas fa-arrow-left mr-2"></i>Volver al Dashboard
            </a>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">
                <i class="fas fa-tags mr-2 sm:mr-3 text-blue-600"></i>Gestionar Categorías
            </h1>
            <p class="text-sm sm:text-base text-gray-600 mt-2">Crea y personaliza tus propias categorías para gastos e ingresos</p>
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

        <!-- Add New Category Form -->
        <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6 mb-6">
            <h2 class="text-lg sm:text-xl font-bold text-gray-900 mb-4">
                <i class="fas fa-plus-circle mr-2 text-blue-600"></i>Agregar Nueva Categoría
            </h2>
            
            <form id="categoryForm" action="<?php echo BASE_URL; ?>public/index.php?action=create-category" method="POST" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Category Name -->
                    <div>
                        <label for="category_name" class="block text-sm font-medium text-gray-700 mb-2">
                            Nombre de la Categoría *
                        </label>
                        <input type="text" id="category_name" name="name" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Ej: Gym, Netflix, etc.">
                    </div>

                    <!-- Category Type -->
                    <div>
                        <label for="category_type" class="block text-sm font-medium text-gray-700 mb-2">
                            Tipo *
                        </label>
                        <select id="category_type" name="type" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                onchange="updateIconGrid()">
                            <option value="expense">Gasto</option>
                            <option value="income">Ingreso</option>
                        </select>
                    </div>
                </div>

                <!-- Icon Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Icono *
                    </label>
                    <div id="iconGrid" class="grid grid-cols-5 sm:grid-cols-6 md:grid-cols-8 lg:grid-cols-10 gap-2 p-2 sm:p-4 border border-gray-300 rounded-lg bg-gray-50 max-h-64 overflow-y-auto">
                        <?php
                        $icons = Category::getIconsByType('expense');
                        foreach ($icons as $icon) {
                            echo '<div class="icon-option cursor-pointer p-2 text-center rounded hover:bg-blue-100 border border-transparent hover:border-blue-500 transition" 
                                    data-icon="' . htmlspecialchars($icon) . '"
                                    onclick="selectIcon(this)">
                                    <i class="fas ' . htmlspecialchars($icon) . ' text-xl sm:text-2xl"></i>
                                  </div>';
                        }
                        ?>
                    </div>
                    <input type="hidden" id="selected_icon" name="icon" required>
                </div>

                <!-- Color Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Color *
                    </label>
                    <div class="grid grid-cols-5 sm:grid-cols-6 md:grid-cols-8 lg:grid-cols-10 gap-2 p-2 sm:p-4 border border-gray-300 rounded-lg bg-gray-50">
                        <?php
                        $colors = Category::getColors();
                        foreach ($colors as $color) {
                            echo '<div class="color-option cursor-pointer w-8 h-8 sm:w-10 sm:h-10 rounded-full border-2 border-transparent hover:border-gray-600 transition" 
                                    data-color="' . htmlspecialchars($color) . '"
                                    style="background-color: ' . htmlspecialchars($color) . ';"
                                    onclick="selectColor(this)">
                                  </div>';
                        }
                        ?>
                    </div>
                    <input type="hidden" id="selected_color" name="color" required>
                </div>

                <div class="flex flex-col sm:flex-row justify-end gap-2 sm:gap-0">
                    <button type="submit" class="btn-primary py-2 px-4 sm:px-6 rounded-lg font-semibold text-sm sm:text-base w-full sm:w-auto">
                        <i class="fas fa-save mr-2"></i>Guardar Categoría
                    </button>
                </div>
            </form>
        </div>

        <!-- Existing Categories -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
            <!-- Expense Categories -->
            <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6">
                <h2 class="text-lg sm:text-xl font-bold text-gray-900 mb-4">
                    <i class="fas fa-arrow-up mr-2 text-red-600"></i>Categorías de Gastos
                </h2>
                
                <?php if (!empty($expense_categories)): ?>
                    <div class="space-y-3">
                        <?php foreach ($expense_categories as $cat): ?>
                            <div class="flex items-center justify-between p-3 sm:p-4 bg-red-50 rounded-lg border border-red-200 category-item" 
                                 data-id="<?php echo $cat['id']; ?>"
                                 data-type="<?php echo $cat['type']; ?>"
                                 data-name="<?php echo htmlspecialchars($cat['name']); ?>"
                                 data-icon="<?php echo htmlspecialchars($cat['icon']); ?>"
                                 data-color="<?php echo htmlspecialchars($cat['color']); ?>">
                                <div class="flex items-center flex-1 min-w-0">
                                    <i class="fas <?php echo htmlspecialchars($cat['icon']); ?> text-xl sm:text-2xl mr-2 sm:mr-3 flex-shrink-0" style="color: <?php echo htmlspecialchars($cat['color']); ?>;"></i>
                                    <span class="font-medium text-gray-900 truncate text-sm sm:text-base"><?php echo htmlspecialchars($cat['name']); ?></span>
                                </div>
                                <div class="flex items-center space-x-1 sm:space-x-2 flex-shrink-0 ml-2">
                                    <button onclick="editCategory(this)" 
                                            class="p-2 sm:p-2 text-blue-600 hover:bg-blue-100 rounded-lg transition text-sm sm:text-base">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="deleteCategory(<?php echo $cat['id']; ?>)" 
                                            class="p-2 sm:p-2 text-red-600 hover:bg-red-100 rounded-lg transition text-sm sm:text-base">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-tags text-4xl mb-3 opacity-50"></i>
                        <p>No hay categorías personalizadas de gastos</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Income Categories -->
            <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6">
                <h2 class="text-lg sm:text-xl font-bold text-gray-900 mb-4">
                    <i class="fas fa-arrow-down mr-2 text-green-600"></i>Categorías de Ingresos
                </h2>
                
                <?php if (!empty($income_categories)): ?>
                    <div class="space-y-3">
                        <?php foreach ($income_categories as $cat): ?>
                            <div class="flex items-center justify-between p-3 sm:p-4 bg-green-50 rounded-lg border border-green-200 category-item"
                                 data-id="<?php echo $cat['id']; ?>"
                                 data-type="<?php echo $cat['type']; ?>"
                                 data-name="<?php echo htmlspecialchars($cat['name']); ?>"
                                 data-icon="<?php echo htmlspecialchars($cat['icon']); ?>"
                                 data-color="<?php echo htmlspecialchars($cat['color']); ?>">
                                <div class="flex items-center flex-1 min-w-0">
                                    <i class="fas <?php echo htmlspecialchars($cat['icon']); ?> text-xl sm:text-2xl mr-2 sm:mr-3 flex-shrink-0" style="color: <?php echo htmlspecialchars($cat['color']); ?>;"></i>
                                    <span class="font-medium text-gray-900 truncate text-sm sm:text-base"><?php echo htmlspecialchars($cat['name']); ?></span>
                                </div>
                                <div class="flex items-center space-x-1 sm:space-x-2 flex-shrink-0 ml-2">
                                    <button onclick="editCategory(this)" 
                                            class="p-2 sm:p-2 text-blue-600 hover:bg-blue-100 rounded-lg transition text-sm sm:text-base">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="deleteCategory(<?php echo $cat['id']; ?>)" 
                                            class="p-2 sm:p-2 text-red-600 hover:bg-red-100 rounded-lg transition text-sm sm:text-base">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-tags text-4xl mb-3 opacity-50"></i>
                        <p>No hay categorías personalizadas de ingresos</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-xl p-4 sm:p-6 lg:p-8 max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <h3 class="text-xl sm:text-2xl font-bold text-gray-900 mb-4 sm:mb-6">
            <i class="fas fa-edit mr-2 text-blue-600"></i>Editar Categoría
        </h3>
        
        <form id="editCategoryForm" action="<?php echo BASE_URL; ?>public/index.php?action=update-category" method="POST" class="space-y-4">
            <input type="hidden" id="edit_id" name="id">
            
            <div>
                <label for="edit_name" class="block text-sm font-medium text-gray-700 mb-2">
                    Nombre de la Categoría *
                </label>
                <input type="text" id="edit_name" name="name" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Icono *
                </label>
                <div id="editIconGrid" class="grid grid-cols-5 sm:grid-cols-6 md:grid-cols-8 lg:grid-cols-10 gap-2 p-2 sm:p-4 border border-gray-300 rounded-lg bg-gray-50 max-h-64 overflow-y-auto">
                    <?php
                    $icons = Category::getIconsByType('expense');
                    foreach ($icons as $icon) {
                        echo '<div class="icon-option cursor-pointer p-2 text-center rounded hover:bg-blue-100 border border-transparent hover:border-blue-500 transition" 
                                data-icon="' . htmlspecialchars($icon) . '"
                                onclick="selectEditIcon(this)">
                                <i class="fas ' . htmlspecialchars($icon) . ' text-xl sm:text-2xl"></i>
                              </div>';
                    }
                    ?>
                </div>
                <input type="hidden" id="edit_selected_icon" name="icon" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Color *
                </label>
                <div class="grid grid-cols-5 sm:grid-cols-6 md:grid-cols-8 lg:grid-cols-10 gap-2 p-2 sm:p-4 border border-gray-300 rounded-lg bg-gray-50">
                    <?php
                    $colors = Category::getColors();
                    foreach ($colors as $color) {
                        echo '<div class="color-option cursor-pointer w-8 h-8 sm:w-10 sm:h-10 rounded-full border-2 border-transparent hover:border-gray-600 transition" 
                                data-color="' . htmlspecialchars($color) . '"
                                style="background-color: ' . htmlspecialchars($color) . ';"
                                onclick="selectEditColor(this)">
                              </div>';
                    }
                    ?>
                </div>
                <input type="hidden" id="edit_selected_color" name="color" required>
            </div>

            <div class="flex flex-col sm:flex-row justify-end gap-2 sm:gap-4 sm:space-x-4">
                <button type="button" onclick="closeEditModal()" 
                        class="px-4 sm:px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 transition text-sm sm:text-base w-full sm:w-auto">
                    Cancelar
                </button>
                <button type="submit" class="btn-primary py-2 px-4 sm:px-6 rounded-lg font-semibold text-sm sm:text-base w-full sm:w-auto">
                    <i class="fas fa-save mr-2"></i>Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Helper function to convert hex to rgba with opacity
function hexToRgba(hex, opacity) {
    const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
    if (!result) return hex;
    const r = parseInt(result[1], 16);
    const g = parseInt(result[2], 16);
    const b = parseInt(result[3], 16);
    return `rgba(${r}, ${g}, ${b}, ${opacity})`;
}

// Icon and Color Selection
function selectIcon(element) {
    const selectedColor = document.getElementById('selected_color').value;
    document.querySelectorAll('#iconGrid .icon-option').forEach(el => {
        el.classList.remove('bg-blue-500', 'border-blue-500', 'selected-icon');
        el.style.backgroundColor = '';
        el.style.borderColor = '';
        el.style.borderWidth = '';
        const icon = el.querySelector('i');
        if (icon) {
            icon.style.color = '';
        }
    });
    
    if (selectedColor) {
        element.style.backgroundColor = hexToRgba(selectedColor, 0.25);
        element.style.borderColor = selectedColor;
        element.style.borderWidth = '2px';
        const icon = element.querySelector('i');
        if (icon) {
            icon.style.color = selectedColor;
        }
    } else {
        element.classList.add('bg-blue-500', 'border-blue-500');
        const icon = element.querySelector('i');
        if (icon) {
            icon.style.color = '#3b82f6'; // Blue color
        }
    }
    element.classList.add('selected-icon');
    document.getElementById('selected_icon').value = element.dataset.icon;
}

function selectColor(element) {
    const selectedColor = element.dataset.color;
    document.querySelectorAll('#categoryForm .color-option').forEach(el => {
        el.classList.remove('border-gray-800', 'border-4');
        el.classList.add('border-transparent', 'border-2');
    });
    element.classList.remove('border-transparent', 'border-2');
    element.classList.add('border-gray-800', 'border-4');
    document.getElementById('selected_color').value = selectedColor;
    
    // Update selected icon color if one is selected
    const selectedIcon = document.querySelector('#iconGrid .icon-option.selected-icon');
    if (selectedIcon) {
        selectedIcon.style.backgroundColor = hexToRgba(selectedColor, 0.25);
        selectedIcon.style.borderColor = selectedColor;
        selectedIcon.style.borderWidth = '2px';
        selectedIcon.classList.remove('bg-blue-500', 'border-blue-500');
        const icon = selectedIcon.querySelector('i');
        if (icon) {
            icon.style.color = selectedColor;
        }
    }
}

function selectEditIcon(element) {
    const selectedColor = document.getElementById('edit_selected_color').value;
    document.querySelectorAll('#editIconGrid .icon-option').forEach(el => {
        el.classList.remove('bg-blue-500', 'border-blue-500', 'selected-edit-icon');
        el.style.backgroundColor = '';
        el.style.borderColor = '';
        el.style.borderWidth = '';
        const icon = el.querySelector('i');
        if (icon) {
            icon.style.color = '';
        }
    });
    
    if (selectedColor) {
        element.style.backgroundColor = hexToRgba(selectedColor, 0.25);
        element.style.borderColor = selectedColor;
        element.style.borderWidth = '2px';
        const icon = element.querySelector('i');
        if (icon) {
            icon.style.color = selectedColor;
        }
    } else {
        element.classList.add('bg-blue-500', 'border-blue-500');
        const icon = element.querySelector('i');
        if (icon) {
            icon.style.color = '#3b82f6'; // Blue color
        }
    }
    element.classList.add('selected-edit-icon');
    document.getElementById('edit_selected_icon').value = element.dataset.icon;
}

function selectEditColor(element) {
    const selectedColor = element.dataset.color;
    document.querySelectorAll('#editCategoryForm .color-option').forEach(el => {
        el.classList.remove('border-gray-800', 'border-4');
        el.classList.add('border-transparent', 'border-2');
    });
    element.classList.remove('border-transparent', 'border-2');
    element.classList.add('border-gray-800', 'border-4');
    document.getElementById('edit_selected_color').value = selectedColor;
    
    // Update selected icon color if one is selected
    const selectedIcon = document.querySelector('#editIconGrid .icon-option.selected-edit-icon');
    if (selectedIcon) {
        selectedIcon.style.backgroundColor = hexToRgba(selectedColor, 0.25);
        selectedIcon.style.borderColor = selectedColor;
        selectedIcon.style.borderWidth = '2px';
        selectedIcon.classList.remove('bg-blue-500', 'border-blue-500');
        const icon = selectedIcon.querySelector('i');
        if (icon) {
            icon.style.color = selectedColor;
        }
    }
}

// Update icon grid based on category type
function updateIconGrid() {
    const type = document.getElementById('category_type').value;
    const expenseIcons = <?php echo json_encode(Category::getIconsByType('expense')); ?>;
    const incomeIcons = <?php echo json_encode(Category::getIconsByType('income')); ?>;
    
    const icons = type === 'expense' ? expenseIcons : incomeIcons;
    const grid = document.getElementById('iconGrid');
    grid.innerHTML = '';
    
    icons.forEach(icon => {
        const div = document.createElement('div');
        div.className = 'icon-option cursor-pointer p-2 text-center rounded hover:bg-blue-100 border border-transparent hover:border-blue-500 transition';
        div.dataset.icon = icon;
        div.onclick = function() { selectIcon(this); };
        div.innerHTML = '<i class="fas ' + icon + ' text-xl sm:text-2xl"></i>';
        grid.appendChild(div);
    });
    
    // Clear selected icon
    document.getElementById('selected_icon').value = '';
    document.querySelectorAll('#iconGrid .icon-option').forEach(el => {
        el.classList.remove('selected-icon', 'bg-blue-500', 'border-blue-500');
        el.style.backgroundColor = '';
        el.style.borderColor = '';
        el.style.borderWidth = '';
        const icon = el.querySelector('i');
        if (icon) {
            icon.style.color = '';
        }
    });
}

// Edit Category
function editCategory(button) {
    const categoryItem = button.closest('.category-item');
    const id = categoryItem.dataset.id;
    const name = categoryItem.dataset.name;
    const icon = categoryItem.dataset.icon;
    const color = categoryItem.dataset.color;
    
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_selected_icon').value = icon;
    document.getElementById('edit_selected_color').value = color;
    
    // Highlight selected icon and color
    document.querySelectorAll('#editIconGrid .icon-option').forEach(el => {
        if (el.dataset.icon === icon) {
            if (color) {
                el.style.backgroundColor = hexToRgba(color, 0.25);
                el.style.borderColor = color;
                el.style.borderWidth = '2px';
                el.classList.remove('bg-blue-500', 'border-blue-500');
                const iconElement = el.querySelector('i');
                if (iconElement) {
                    iconElement.style.color = color;
                }
            } else {
                el.classList.add('bg-blue-500', 'border-blue-500');
                const iconElement = el.querySelector('i');
                if (iconElement) {
                    iconElement.style.color = '#3b82f6';
                }
            }
            el.classList.add('selected-edit-icon');
        } else {
            el.classList.remove('bg-blue-500', 'border-blue-500', 'selected-edit-icon');
            el.style.backgroundColor = '';
            el.style.borderColor = '';
            el.style.borderWidth = '';
            const iconElement = el.querySelector('i');
            if (iconElement) {
                iconElement.style.color = '';
            }
        }
    });
    
    document.querySelectorAll('#editCategoryForm .color-option').forEach(el => {
        if (el.dataset.color === color) {
            el.classList.add('border-gray-800', 'border-4');
            el.classList.remove('border-transparent', 'border-2');
        } else {
            el.classList.remove('border-gray-800', 'border-4');
            el.classList.add('border-transparent', 'border-2');
        }
    });
    
    // Update icon color if icon is selected
    const selectedEditIcon = document.querySelector('#editIconGrid .icon-option.selected-edit-icon');
    if (selectedEditIcon && color) {
        selectedEditIcon.style.backgroundColor = hexToRgba(color, 0.25);
        selectedEditIcon.style.borderColor = color;
        selectedEditIcon.style.borderWidth = '2px';
        const iconElement = selectedEditIcon.querySelector('i');
        if (iconElement) {
            iconElement.style.color = color;
        }
    }
    
    document.getElementById('editModal').classList.remove('hidden');
    document.getElementById('editModal').classList.add('flex');
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
    document.getElementById('editModal').classList.remove('flex');
}

// Delete Category
function deleteCategory(id) {
    if (!confirm('¿Estás seguro de que deseas eliminar esta categoría?')) {
        return;
    }
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '<?php echo BASE_URL; ?>public/index.php?action=delete-category';
    
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'id';
    input.value = id;
    
    form.appendChild(input);
    document.body.appendChild(form);
    form.submit();
}

// Form validation
document.getElementById('categoryForm').addEventListener('submit', function(e) {
    if (!document.getElementById('selected_icon').value) {
        e.preventDefault();
        alert('Por favor selecciona un icono');
        return false;
    }
    if (!document.getElementById('selected_color').value) {
        e.preventDefault();
        alert('Por favor selecciona un color');
        return false;
    }
});
</script>

<style>
.icon-option {
    transition: all 0.2s ease;
    min-width: 44px; /* Minimum touch target size */
    min-height: 44px;
}

.icon-option.selected-icon,
.icon-option.selected-edit-icon {
    transition: all 0.2s ease;
}

.color-option {
    min-width: 44px; /* Minimum touch target size for mobile */
    min-height: 44px;
}

@media (min-width: 640px) {
    .color-option {
        min-width: 40px;
        min-height: 40px;
    }
}

/* Improve touch targets on mobile */
@media (max-width: 640px) {
    .category-item button {
        min-width: 44px;
        min-height: 44px;
    }
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

