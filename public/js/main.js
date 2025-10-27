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
 * Alert Auto-hide and Sound
 */
function initializeAlerts() {
    // Auto-hide flash messages
    const autoHideAlerts = document.querySelectorAll('.alert-auto-hide');
    autoHideAlerts.forEach(alert => {
        // Play sound for error alerts
        if (alert.classList.contains('alert-danger')) {
            playAlertSound();
        }
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });
}

/**
 * Play alert sound
 */
function playAlertSound() {
    // Create and play a simple beep sound
    const audioContext = new (window.AudioContext || window.webkitAudioContext)();
    const oscillator = audioContext.createOscillator();
    const gainNode = audioContext.createGain();
    
    oscillator.connect(gainNode);
    gainNode.connect(audioContext.destination);
    
    oscillator.frequency.value = 800;
    oscillator.type = 'sine';
    
    gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
    gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);
    
    oscillator.start(audioContext.currentTime);
    oscillator.stop(audioContext.currentTime + 0.5);
}

/**
 * Form Validation
 */
function initializeFormValidation() {
    const forms = document.querySelectorAll('form[data-validate]');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(form)) {
                e.preventDefault();
            }
        });
    });
}

/**
 * Validate form
 */
function validateForm(form) {
    let isValid = true;
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            isValid = false;
            input.classList.add('border-red-500');
            
            // Remove error class on input
            input.addEventListener('input', function() {
                this.classList.remove('border-red-500');
            });
        }
    });
    
    return isValid;
}

/**
 * Password Toggle
 */
function initializePasswordToggles() {
    const toggleButtons = document.querySelectorAll('.toggle-password');
    
    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            const input = this.previousElementSibling;
            const icon = this.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
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

