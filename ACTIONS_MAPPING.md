# Mapeo de Acciones a Métodos de Controladores

Este documento describe el mapeo entre las acciones de formularios/URLs y los métodos de los controladores.

## ¿Cómo funciona el Router?

El Router convierte automáticamente las acciones con guiones a métodos en camelCase:
- `add-transaction` → `addTransaction()`
- `forgot-password` → `forgotPassword()`
- `get-monthly-comparison` → `getMonthlyComparison()`

## AuthController

| Acción | Método | Descripción |
|--------|--------|-------------|
| `login` | `login()` | Iniciar sesión de usuario |
| `register` | `register()` | Registrar nuevo usuario |
| `logout` | `logout()` | Cerrar sesión |
| `forgot-password` | `forgotPassword()` | Solicitar recuperación de contraseña |
| `reset-password` | `resetPassword()` | Restablecer contraseña con token |
| `resend-verification` | `resendVerification()` | Reenviar correo de verificación |

## ProfileController

| Acción | Método | Descripción |
|--------|--------|-------------|
| `initial-setup` | `initialSetup()` | Configuración inicial del perfil financiero |
| `update-profile` | `updateProfile()` | Actualizar información del perfil |
| `change-password` | `changePassword()` | Cambiar contraseña del usuario |
| `add-income` | `addIncome()` | Agregar ingreso adicional |

## TransactionController

| Acción | Método | Descripción |
|--------|--------|-------------|
| `add-transaction` | `addTransaction()` | Registrar nueva transacción (gasto o ingreso) |
| `delete-transaction` | `deleteTransaction()` | Eliminar una transacción |
| `export-transactions` | `exportTransactions()` | Exportar transacciones a CSV |
| `get-transactions` | `getTransactions()` | Obtener transacciones (AJAX) |

## ReportController

| Acción | Método | Descripción |
|--------|--------|-------------|
| `get-dashboard-data` | `getDashboardData()` | Obtener datos del dashboard (AJAX) |
| `get-category-chart-data` | `getCategoryChartData()` | Obtener datos de gráfica por categoría (AJAX) |
| `get-monthly-comparison` | `getMonthlyComparison()` | Obtener comparación mensual (AJAX) |
| `get-payment-method-data` | `getPaymentMethodData()` | Obtener datos por método de pago (AJAX) |

## Ubicación de las Acciones

### Formularios HTML
- `views/login.php` → `action=login`
- `views/register.php` → `action=register`
- `views/forgot_password.php` → `action=forgot-password`
- `views/reset_password.php` → `action=reset-password`
- `views/verify_email.php` → `action=resend-verification`
- `views/initial_setup.php` → `action=initial-setup`
- `views/profile.php` → `action=update-profile`, `action=change-password`
- `views/add_transaction.php` → `action=add-transaction`
- `views/transactions.php` → `action=delete-transaction`, `action=export-transactions`

### Enlaces (GET)
- `includes/navbar.php` → `action=logout`
- `views/transactions.php` → `action=export-transactions`

### Llamadas AJAX (Fetch)
- `views/reports.php` → `action=get-monthly-comparison`

## Agregar Nueva Acción

Para agregar una nueva acción, sigue estos pasos:

1. **Crear el método en el controlador apropiado:**
   ```php
   public function miNuevaAccion() {
       requireLogin(); // Si requiere autenticación
       // Tu código aquí
   }
   ```

2. **Usar la acción en la vista:**
   ```php
   <form action="<?php echo BASE_URL; ?>public/index.php?action=mi-nueva-accion" method="POST">
       <!-- Campos del formulario -->
   </form>
   ```

3. **Convención de nombres:**
   - Acción en URL: `mi-nueva-accion` (kebab-case con guiones)
   - Método en PHP: `miNuevaAccion()` (camelCase)

## Solución de Problemas

### Error: "Action not found"
- Verifica que el nombre del método coincida con la conversión de la acción
- Ejemplo: `add-transaction` debe tener un método `addTransaction()`
- El método debe ser público
- El método debe existir en alguno de los controladores

### Error: "Class not found"
- Asegúrate de que el archivo del controlador tenga el mismo nombre que la clase
- Ejemplo: `AuthController` → `AuthController.php` (case-sensitive en Linux)

