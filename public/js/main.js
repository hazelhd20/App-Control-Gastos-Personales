/**
 * Main JavaScript for Control de Gastos
 */

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    initializeAlerts();
    initializeFormValidation();
    initializePasswordToggles();
    
    // El menú móvil se inicializa en navbar.php
    // para evitar conflictos y asegurar que se ejecute correctamente
});

/**
 * Alert Auto-hide
 */
function initializeAlerts() {
    // Auto-hide flash messages
    const autoHideAlerts = document.querySelectorAll('.alert-auto-hide:not([data-initialized])');
    autoHideAlerts.forEach(alert => {
        // Mark as initialized to prevent multiple initializations
        alert.setAttribute('data-initialized', 'true');
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.remove();
                }
            }, 500);
        }, 5000);
    });
}

/**
 * Form Validation (Unified System)
 * Uses FormValidator class from form-validation.js
 */
function initializeFormValidation() {
    // Legacy support: Add data-validate to forms that should be validated
    let needsReinit = false;
    document.querySelectorAll('form').forEach(form => {
        // Skip if already has validator
        if (form.hasAttribute('data-validator')) {
            return;
        }
        
        // Add basic validation to forms with required fields
        const hasRequiredFields = form.querySelectorAll('input[required], select[required], textarea[required]').length > 0;
        if (hasRequiredFields && !form.hasAttribute('data-no-validate') && !form.hasAttribute('data-validate')) {
            form.setAttribute('data-validate', 'true');
            form.setAttribute('data-validate-on-input', 'true');
            form.setAttribute('data-validate-on-blur', 'true');
            needsReinit = true;
        }
    });
    
    // Initialize form validators (only once, after all attributes are set)
    if (typeof initializeFormValidators === 'function') {
        initializeFormValidators();
    }
}

/**
 * Password Toggle
 * Usa event delegation para funcionar incluso si los elementos se agregan dinámicamente
 */
let passwordToggleInitialized = false;

function initializePasswordToggles() {
    // Evitar agregar múltiples listeners
    if (passwordToggleInitialized) {
        return;
    }
    
    passwordToggleInitialized = true;
    
    // Usar event delegation en el documento para capturar todos los clicks
    document.addEventListener('click', function(e) {
        // Verificar si el click fue en un toggle-password o en su icono
        const toggleButton = e.target.closest('.toggle-password');
        
        if (!toggleButton) {
            return;
        }
        
        // Prevenir que el evento active la validación
        e.preventDefault();
        e.stopPropagation();
        
        // Buscar el input dentro del contenedor padre (más robusto)
        // El toggle-password está dentro de un div.relative junto con el input
        const container = toggleButton.parentElement;
        
        // Buscar cualquier input dentro del contenedor (más robusto)
        let input = container.querySelector('input');
        
        // Si no se encuentra en el contenedor directo, buscar en el padre
        if (!input && container.parentElement) {
            input = container.parentElement.querySelector('input');
        }
        
        const icon = toggleButton.querySelector('i');
        
        if (!input || !icon) {
            return;
        }
        
        // Guardar el valor actual para restaurarlo después
        const currentValue = input.value;
        const currentType = input.type;
        const isPassword = currentType === 'password';
        
        // Marcar que estamos cambiando el tipo para evitar validación
        input.setAttribute('data-toggle-password', 'true');
        
        // Cambiar el tipo del input
        input.type = isPassword ? 'text' : 'password';
        
        // Restaurar el valor (puede perderse al cambiar el tipo)
        if (input.value !== currentValue) {
            input.value = currentValue;
        }
        
        // Actualizar el icono
        if (isPassword) {
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
        
        // Remover el atributo después de un breve delay para permitir que los eventos se procesen
        setTimeout(() => {
            input.removeAttribute('data-toggle-password');
        }, 100);
    });
}

/**
 * Format currency input
 */
function formatCurrencyInput(input) {
    let value = input.value.replace(/[^0-9.]/g, '');
    
    // Ensure only one decimal point
    const parts = value.split('.');
    if (parts.length > 2) {
        value = parts[0] + '.' + parts.slice(1).join('');
    }
    
    // Limit decimal places to 2
    if (parts.length === 2) {
        value = parts[0] + '.' + parts[1].substring(0, 2);
    }
    
    input.value = value;
}

/**
 * Confirm deletion
 */
function confirmDelete(message) {
    return confirm(message || '¿Estás seguro de que deseas eliminar este elemento?');
}

/**
 * Show loading spinner
 */
function showLoading() {
    const loader = document.createElement('div');
    loader.id = 'loading-spinner';
    loader.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
    loader.innerHTML = `
        <div class="bg-white rounded-lg p-8 flex flex-col items-center">
            <div class="animate-spin rounded-full h-16 w-16 border-b-4 border-blue-600"></div>
            <p class="mt-4 text-gray-700 font-semibold">Cargando...</p>
        </div>
    `;
    document.body.appendChild(loader);
}

/**
 * Hide loading spinner
 */
function hideLoading() {
    const loader = document.getElementById('loading-spinner');
    if (loader) {
        loader.remove();
    }
}

/**
 * Show notification
 */
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-md animate-slide-in ${
        type === 'error' ? 'bg-red-100 text-red-800 border-l-4 border-red-500' :
        type === 'success' ? 'bg-green-100 text-green-800 border-l-4 border-green-500' :
        type === 'warning' ? 'bg-yellow-100 text-yellow-800 border-l-4 border-yellow-500' :
        'bg-blue-100 text-blue-800 border-l-4 border-blue-500'
    }`;
    
    notification.innerHTML = `
        <div class="flex items-center">
            <i class="fas ${
                type === 'error' ? 'fa-exclamation-circle' :
                type === 'success' ? 'fa-check-circle' :
                type === 'warning' ? 'fa-exclamation-triangle' :
                'fa-info-circle'
            } mr-3 text-xl"></i>
            <p>${message}</p>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.transition = 'opacity 0.5s ease';
        notification.style.opacity = '0';
        setTimeout(() => notification.remove(), 500);
    }, 4000);
}

/**
 * AJAX Form Submit
 */
function submitFormAjax(form, successCallback, errorCallback) {
    const formData = new FormData(form);
    const url = form.action;
    
    showLoading();
    
    fetch(url, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            if (successCallback) successCallback(data);
        } else {
            if (errorCallback) errorCallback(data);
        }
    })
    .catch(error => {
        hideLoading();
        showNotification('Error al procesar la solicitud', 'error');
        if (errorCallback) errorCallback(error);
    });
}

/**
 * Number animation
 */
function animateNumber(element, start, end, duration) {
    const range = end - start;
    const increment = range / (duration / 16);
    let current = start;
    
    const timer = setInterval(() => {
        current += increment;
        if ((increment > 0 && current >= end) || (increment < 0 && current <= end)) {
            current = end;
            clearInterval(timer);
        }
        element.textContent = Math.round(current);
    }, 16);
}

/**
 * Export utilities
 */
window.ControlGastos = {
    formatCurrencyInput,
    confirmDelete,
    showLoading,
    hideLoading,
    showNotification,
    submitFormAjax,
    animateNumber
};

