/**
 * Unified Form Validation System
 * Provides consistent validation behavior and styling across all forms
 */

class FormValidator {
    constructor(formElement, options = {}) {
        this.form = formElement;
        this.options = {
            validateOnInput: true,
            validateOnBlur: true,
            showErrorsOnSubmit: true,
            scrollToError: true,
            ...options
        };
        this.errors = {};
        this.init();
    }

    init() {
        // Add data attributes to form
        this.form.setAttribute('data-validator', 'true');
        
        // Add validation classes to inputs
        this.form.querySelectorAll('input, select, textarea').forEach(field => {
            if (field.hasAttribute('required') || field.hasAttribute('data-validate')) {
                field.classList.add('form-field');
                
                // Add real-time validation
                if (this.options.validateOnInput) {
                    field.addEventListener('input', () => this.validateField(field, false));
                }
                
                if (this.options.validateOnBlur) {
                    field.addEventListener('blur', () => this.validateField(field, true));
                }
            }
        });

        // Handle form submission
        this.form.addEventListener('submit', (e) => {
            if (!this.validateForm()) {
                e.preventDefault();
                if (this.options.showErrorsOnSubmit) {
                    this.showFormErrors();
                }
                if (this.options.scrollToError) {
                    this.scrollToFirstError();
                }
                return false;
            }
        });
    }

    validateField(field, showError = true) {
        const fieldName = field.name || field.id;
        const value = field.value.trim();
        const rules = this.getFieldRules(field);
        const errors = [];

        // Clear previous errors
        this.clearFieldError(field);

        // Required validation
        if (field.hasAttribute('required') && !value) {
            errors.push(this.getErrorMessage('required', field));
        }

        // Type-specific validation
        if (value) {
            switch (field.type) {
                case 'email':
                    if (!this.isValidEmail(value)) {
                        errors.push(this.getErrorMessage('email', field));
                    }
                    break;
                case 'tel':
                    if (rules.pattern && !new RegExp(rules.pattern).test(value)) {
                        errors.push(this.getErrorMessage('pattern', field));
                    }
                    break;
                case 'number':
                    if (rules.min !== undefined && parseFloat(value) < rules.min) {
                        errors.push(this.getErrorMessage('min', field, rules.min));
                    }
                    if (rules.max !== undefined && parseFloat(value) > rules.max) {
                        errors.push(this.getErrorMessage('max', field, rules.max));
                    }
                    break;
            }

            // Password validation
            if (field.type === 'password' && field.hasAttribute('data-validate-password')) {
                const passwordErrors = this.validatePassword(value);
                errors.push(...passwordErrors);
            }

            // Custom validation rules
            if (rules.custom) {
                const customErrors = rules.custom(value, field);
                if (customErrors && customErrors.length > 0) {
                    errors.push(...customErrors);
                }
            }

            // Pattern validation
            if (rules.pattern && !new RegExp(rules.pattern).test(value)) {
                errors.push(this.getErrorMessage('pattern', field));
            }

            // Min length
            if (rules.minLength && value.length < rules.minLength) {
                errors.push(this.getErrorMessage('minLength', field, rules.minLength));
            }

            // Max length
            if (rules.maxLength && value.length > rules.maxLength) {
                errors.push(this.getErrorMessage('maxLength', field, rules.maxLength));
            }
        }

        // Confirm password validation
        if (field.hasAttribute('data-confirm-password')) {
            const passwordFieldName = field.getAttribute('data-confirm-password') || 'password';
            const passwordField = this.form.querySelector(`[name="${passwordFieldName}"], #${passwordFieldName}`);
            if (passwordField && value && value !== passwordField.value) {
                errors.push('Las contraseñas no coinciden');
            }
        }

        // Update field state
        if (errors.length > 0) {
            if (showError) {
                this.showFieldError(field, errors[0]);
            }
            this.errors[fieldName] = errors;
            return false;
        } else {
            this.showFieldSuccess(field);
            delete this.errors[fieldName];
            return true;
        }
    }

    validateForm() {
        this.errors = {};
        let isValid = true;

        // Validate all fields
        this.form.querySelectorAll('.form-field, input[required], select[required], textarea[required]').forEach(field => {
            if (!this.validateField(field, false)) {
                isValid = false;
            }
        });

        // Custom form validation
        if (this.options.customValidation) {
            const customErrors = this.options.customValidation(this.form);
            if (customErrors && Object.keys(customErrors).length > 0) {
                Object.assign(this.errors, customErrors);
                isValid = false;
            }
        }

        return isValid;
    }

    getFieldRules(field) {
        const rules = {};
        
        // Get rules from data attributes
        if (field.hasAttribute('data-min')) {
            rules.min = parseFloat(field.getAttribute('data-min'));
        }
        if (field.hasAttribute('data-max')) {
            rules.max = parseFloat(field.getAttribute('data-max'));
        }
        if (field.hasAttribute('data-min-length')) {
            rules.minLength = parseInt(field.getAttribute('data-min-length'));
        }
        if (field.hasAttribute('data-max-length')) {
            rules.maxLength = parseInt(field.getAttribute('data-max-length'));
        }
        if (field.hasAttribute('data-pattern')) {
            rules.pattern = field.getAttribute('data-pattern');
        }
        
        return rules;
    }

    isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    validatePassword(password) {
        const errors = [];
        
        if (password.length < 8) {
            errors.push('La contraseña debe tener al menos 8 caracteres');
        }
        if (!/[A-Z]/.test(password)) {
            errors.push('La contraseña debe contener al menos una letra mayúscula');
        }
        if (!/[0-9]/.test(password)) {
            errors.push('La contraseña debe contener al menos un número');
        }
        if (!/[^A-Za-z0-9]/.test(password)) {
            errors.push('La contraseña debe contener al menos un carácter especial');
        }
        
        return errors;
    }

    getErrorMessage(type, field, value = null) {
        const fieldLabel = field.getAttribute('data-label') || 
                          field.previousElementSibling?.textContent?.replace('*', '').trim() ||
                          field.name ||
                          'Este campo';

        const messages = {
            required: `${fieldLabel} es obligatorio`,
            email: `${fieldLabel} no es un correo electrónico válido`,
            pattern: `${fieldLabel} no tiene el formato correcto`,
            min: `${fieldLabel} debe ser mayor o igual a ${value}`,
            max: `${fieldLabel} debe ser menor o igual a ${value}`,
            minLength: `${fieldLabel} debe tener al menos ${value} caracteres`,
            maxLength: `${fieldLabel} no debe exceder ${value} caracteres`,
        };

        return messages[type] || `${fieldLabel} no es válido`;
    }

    showFieldError(field, message) {
        // Add error class to field
        field.classList.add('field-error');
        field.classList.remove('field-success', 'field-warning');

        // Remove existing error message
        this.clearFieldError(field);

        // Create error message element
        const errorElement = document.createElement('div');
        errorElement.className = 'field-error-message';
        errorElement.innerHTML = `<i class="fas fa-exclamation-circle mr-1"></i>${message}`;
        
        // Find the best place to insert the error message
        // If field is inside a relative container (like input with icon), insert after the container
        const fieldContainer = field.closest('.relative, .mt-1, .form-group');
        if (fieldContainer && fieldContainer !== field.parentNode) {
            // Insert after the container
            fieldContainer.parentNode.insertBefore(errorElement, fieldContainer.nextSibling);
        } else {
            // Insert after the field's parent (usually the label container)
            const parent = field.parentNode;
            if (parent && parent.tagName !== 'FORM') {
                parent.insertBefore(errorElement, field.nextSibling);
            } else {
                // Fallback: insert after field
                field.parentNode.insertBefore(errorElement, field.nextSibling);
            }
        }

        // Add shake animation
        field.style.animation = 'field-shake 0.5s';
        setTimeout(() => {
            field.style.animation = '';
        }, 500);
    }

    showFieldSuccess(field) {
        field.classList.add('field-success');
        field.classList.remove('field-error', 'field-warning');
        this.clearFieldError(field);
    }

    showFieldWarning(field, message) {
        field.classList.add('field-warning');
        field.classList.remove('field-error', 'field-success');
        
        // Remove existing warning
        const fieldContainer = field.closest('.relative, .mt-1, .form-group');
        const existingWarning = (fieldContainer && fieldContainer.querySelector('.field-warning-message')) || 
                                field.parentNode.querySelector('.field-warning-message');
        if (existingWarning) {
            existingWarning.remove();
        }

        // Create warning message element
        const warningElement = document.createElement('div');
        warningElement.className = 'field-warning-message';
        warningElement.innerHTML = `<i class="fas fa-exclamation-triangle mr-1"></i>${message}`;
        
        // Find the best place to insert the warning message
        const fieldContainer = field.closest('.relative, .mt-1, .form-group');
        if (fieldContainer && fieldContainer !== field.parentNode) {
            fieldContainer.parentNode.insertBefore(warningElement, fieldContainer.nextSibling);
        } else {
            const parent = field.parentNode;
            if (parent && parent.tagName !== 'FORM') {
                parent.insertBefore(warningElement, field.nextSibling);
            } else {
                field.parentNode.insertBefore(warningElement, field.nextSibling);
            }
        }
    }

    clearFieldError(field) {
        field.classList.remove('field-error', 'field-success', 'field-warning');
        
        // Remove error/warning messages from field's container and parent
        const fieldContainer = field.closest('.relative, .mt-1, .form-group, div');
        const containers = fieldContainer ? [fieldContainer, field.parentNode, fieldContainer.parentNode] : [field.parentNode];
        
        containers.forEach(container => {
            if (container) {
                const errorMessage = container.querySelector('.field-error-message');
                const warningMessage = container.querySelector('.field-warning-message');
                if (errorMessage) errorMessage.remove();
                if (warningMessage) warningMessage.remove();
            }
        });
    }

    showFormErrors() {
        // Show error summary at top of form
        let errorSummary = this.form.querySelector('.form-error-summary');
        
        if (!errorSummary) {
            errorSummary = document.createElement('div');
            errorSummary.className = 'form-error-summary alert-danger';
            this.form.insertBefore(errorSummary, this.form.firstChild);
        }

        const errorCount = Object.keys(this.errors).length;
        if (errorCount > 0) {
            errorSummary.innerHTML = `
                <div class="flex items-start">
                    <i class="fas fa-exclamation-circle mt-0.5 mr-2"></i>
                    <div class="flex-1">
                        <p class="font-semibold mb-2">Por favor corrige los siguientes errores:</p>
                        <ul class="list-disc list-inside space-y-1 text-sm">
                            ${Object.values(this.errors).flat().map(error => `<li>${error}</li>`).join('')}
                        </ul>
                    </div>
                </div>
            `;
            errorSummary.classList.remove('hidden');
            
            // Add fade-in animation
            errorSummary.style.opacity = '0';
            setTimeout(() => {
                errorSummary.style.opacity = '1';
            }, 10);
        } else {
            errorSummary.classList.add('hidden');
        }

        // Show individual field errors
        Object.keys(this.errors).forEach(fieldName => {
            const field = this.form.querySelector(`[name="${fieldName}"], #${fieldName}`);
            if (field && this.errors[fieldName].length > 0) {
                this.showFieldError(field, this.errors[fieldName][0]);
            }
        });
    }

    scrollToFirstError() {
        const firstError = this.form.querySelector('.field-error');
        if (firstError) {
            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            firstError.focus();
        } else {
            const errorSummary = this.form.querySelector('.form-error-summary');
            if (errorSummary) {
                errorSummary.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    }

    // Static helper methods
    static validateEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    static validatePassword(password) {
        const errors = [];
        if (password.length < 8) errors.push('Mínimo 8 caracteres');
        if (!/[A-Z]/.test(password)) errors.push('Al menos una mayúscula');
        if (!/[0-9]/.test(password)) errors.push('Al menos un número');
        if (!/[^A-Za-z0-9]/.test(password)) errors.push('Al menos un carácter especial');
        return errors;
    }

    static validateNumber(value, min = null, max = null) {
        const num = parseFloat(value);
        if (isNaN(num)) return false;
        if (min !== null && num < min) return false;
        if (max !== null && num > max) return false;
        return true;
    }

    static validateDate(date, minDate = null, maxDate = null) {
        const dateObj = new Date(date);
        if (isNaN(dateObj.getTime())) return false;
        if (minDate && dateObj < new Date(minDate)) return false;
        if (maxDate && dateObj > new Date(maxDate)) return false;
        return true;
    }
}

// Initialize validators for all forms
function initializeFormValidators() {
    document.querySelectorAll('form[data-validate]:not([data-validator])').forEach(form => {
        // Skip if already has a validator
        if (form.hasAttribute('data-validator')) {
            return;
        }
        
        const options = {
            validateOnInput: form.hasAttribute('data-validate-on-input') || form.getAttribute('data-validate-on-input') !== 'false',
            validateOnBlur: form.hasAttribute('data-validate-on-blur') || form.getAttribute('data-validate-on-blur') !== 'false',
            showErrorsOnSubmit: true,
            scrollToError: true
        };

        // Add custom validation if specified
        const customValidationAttr = form.getAttribute('data-custom-validation');
        if (customValidationAttr && window[customValidationAttr]) {
            options.customValidation = window[customValidationAttr];
        }

        new FormValidator(form, options);
    });
}

// Export for use in other scripts
window.FormValidator = FormValidator;
window.initializeFormValidators = initializeFormValidators;

