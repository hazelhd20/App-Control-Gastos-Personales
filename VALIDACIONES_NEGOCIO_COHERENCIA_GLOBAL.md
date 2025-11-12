# Validaciones de Negocio - Coherencia Global

Este documento describe todas las validaciones de negocio que aseguran la coherencia global del sistema de control de gastos. Estas validaciones cruzan múltiples entidades y garantizan la integridad de los datos a nivel del sistema completo.

## Índice

1. [Validaciones de Coherencia entre Entidades](#validaciones-de-coherencia-entre-entidades)
2. [Validaciones de Integridad Referencial](#validaciones-de-integridad-referencial)
3. [Validaciones de Coherencia Temporal](#validaciones-de-coherencia-temporal)
4. [Validaciones de Coherencia Financiera](#validaciones-de-coherencia-financiera)
5. [Validaciones de Coherencia de Objetivos](#validaciones-de-coherencia-de-objetivos)
6. [Validaciones de Coherencia de Categorías](#validaciones-de-coherencia-de-categorías)
7. [Validaciones Implementadas](#validaciones-implementadas)
8. [Validaciones Pendientes](#validaciones-pendientes)

---

## Validaciones de Coherencia entre Entidades

### 1. Coherencia Perfil Financiero - Transacciones

#### 1.1. Moneda
- **Regla**: Todas las transacciones de un usuario deben usar la misma moneda que su perfil financiero.
- **Estado**: ✅ **Implementado**
- **Ubicación**: `TransactionController::addTransaction()`
- **Implementación**: 
  - La moneda se obtiene del perfil del usuario (`$user_profile['currency']`)
  - Los límites de transacciones se calculan según la moneda del perfil
  - No se permite cambiar la moneda de una transacción individual

#### 1.2. Métodos de Pago
- **Regla**: Los métodos de pago usados en transacciones deben estar disponibles en el perfil financiero del usuario.
- **Estado**: ✅ **Implementado**
- **Ubicación**: `TransactionController::addTransaction()` (líneas 143-162)
- **Validación**:
  ```php
  // Validate that payment method is in user's profile
  $profile = $this->profile->getByUserId($user_id);
  if ($profile && !empty($profile['payment_methods'])) {
      $valid_methods = $profile['payment_methods'];
      if (!in_array($payment_method, $valid_methods)) {
          $errors[] = "El medio de pago seleccionado no está disponible en tu perfil";
      }
  }
  ```

#### 1.3. Fecha de Inicio del Perfil
- **Regla**: Las transacciones no pueden tener fechas anteriores a la fecha de inicio del perfil financiero.
- **Estado**: ✅ **Implementado**
- **Ubicación**: `TransactionController::addTransaction()` (líneas 209-222)
- **Validación**:
  ```php
  // Validate that transaction date is not before profile start date
  if ($user_profile && !empty($user_profile['start_date'])) {
      $start_date = new DateTime($user_profile['start_date']);
      $start_date->setTime(0, 0, 0);
      $transaction_datetime->setTime(0, 0, 0);
      
      if ($transaction_datetime < $start_date) {
          $formatted_start_date = date('d/m/Y', strtotime($user_profile['start_date']));
          $errors[] = sprintf(
              "La fecha de la transacción no puede ser anterior a la fecha de inicio de tu perfil (%s)",
              $formatted_start_date
          );
      }
  }
  ```

---

## Validaciones de Integridad Referencial

### 2. Categorías y Transacciones

#### 2.1. Categoría Existe y es del Tipo Correcto
- **Regla**: Las transacciones solo pueden usar categorías que existen y son del tipo correcto (expense/income).
- **Estado**: ✅ **Implementado**
- **Ubicación**: `TransactionController::addTransaction()` (líneas 129-141)
- **Validación**:
  ```php
  $categories = $this->transaction->getCategories($user_id, $type);
  $category_names = array_column($categories, 'name');
  if (!in_array($category, $category_names)) {
      $errors[] = "La categoría seleccionada no es válida para este tipo de transacción";
  }
  ```

#### 2.2. Eliminación de Categorías con Transacciones
- **Regla**: No se debe permitir eliminar una categoría personalizada si tiene transacciones asociadas.
- **Estado**: ✅ **Implementado**
- **Ubicación**: `CategoryController::deleteCategory()` (líneas 278-291)
- **Validación**:
  ```php
  // Check if category has associated transactions
  $transaction_count = $this->transaction->countByCategory(
      $category['name'], 
      $category['type'], 
      $user_id
  );
  
  if ($transaction_count > 0) {
      $errors[] = sprintf(
          "No se puede eliminar la categoría '%s' porque tiene %d transacción(es) asociada(s). Por favor, reasigna las transacciones a otra categoría primero.",
          $category['name'],
          $transaction_count
      );
  }
  ```
- **Método auxiliar**: `Transaction::countByCategory()` (líneas 279-294)

#### 2.3. Unicidad de Categorías Personalizadas
- **Regla**: Las categorías personalizadas deben ser únicas por usuario y tipo.
- **Estado**: ✅ **Implementado**
- **Ubicación**: `CategoryController::createCategory()` y `CategoryController::updateCategory()`
- **Validación**:
  ```php
  if ($this->category->exists($name, $type, $user_id)) {
      $errors[] = "Ya existe una categoría con el nombre '{$name}' para este tipo.";
  }
  ```

---

## Validaciones de Coherencia Temporal

### 3. Fechas y Límites Temporales

#### 3.1. Fechas de Transacciones
- **Regla**: 
  - Los gastos no pueden tener fechas futuras
  - Los ingresos pueden tener fechas hasta 1 día en el futuro
  - Las transacciones no pueden ser muy antiguas (máximo 3 años para gastos, 5 años para ingresos)
- **Estado**: ✅ **Implementado**
- **Ubicación**: `TransactionController::addTransaction()` (líneas 168-209)

#### 3.2. Fechas Límite de Objetivos
- **Regla**: 
  - Fechas límite de ahorro: máximo 30 años en el futuro
  - Fechas límite de deudas: máximo 15 años en el futuro
  - Todas las fechas límite deben ser futuras
- **Estado**: ✅ **Implementado**
- **Ubicación**: `ProfileController::initialSetup()` y `ProfileController::updateProfile()`

#### 3.3. Coherencia entre Fecha de Inicio y Transacciones
- **Regla**: Las transacciones no deben ser anteriores a la fecha de inicio del perfil financiero.
- **Estado**: ✅ **Implementado**
- **Ubicación**: `TransactionController::addTransaction()` (líneas 209-222)
- **Validación**: Se verifica explícitamente que `transaction_date >= start_date` del perfil
- **Mensaje de error**: "La fecha de la transacción no puede ser anterior a la fecha de inicio de tu perfil (DD/MM/YYYY)"

---

## Validaciones de Coherencia Financiera

### 4. Límites y Restricciones Financieras

#### 4.1. Límite de Gasto vs Ingreso Mensual
- **Regla**: El límite de gasto no puede exceder el ingreso mensual.
- **Estado**: ✅ **Implementado**
- **Ubicación**: `FinancialProfile::validateSpendingLimit()` (líneas 452-456)
- **Validación**:
  ```php
  if ($spending_limit > $monthly_income) {
      $result['valid'] = false;
      $result['message'] = 'El límite de gasto no puede ser mayor que tu ingreso mensual';
  }
  ```

#### 4.2. Límites de Transacciones según Moneda
- **Regla**: Los montos de transacciones deben estar dentro de límites razonables según la moneda.
- **Estado**: ✅ **Implementado**
- **Ubicación**: `TransactionController::getTransactionLimits()`
- **Límites**:
  - **MXN**: Mínimo 0.01, Máximo 10,000,000
  - **USD**: Mínimo 0.01, Máximo 1,000,000
  - **EUR**: Mínimo 0.01, Máximo 1,000,000

#### 4.3. Coherencia entre Límite de Gasto y Objetivo Financiero
- **Regla**: El límite de gasto debe ser coherente con el objetivo financiero del usuario.
- **Estado**: ✅ **Implementado**
- **Ubicación**: `FinancialProfile::validateSpendingLimit()` (líneas 461-483)
- **Validaciones**:
  - Para objetivo "ahorrar": Debe quedar al menos 20% del ingreso para ahorro
  - Para objetivo "pagar_deudas": Debe quedar al menos 30% del ingreso para pagos
  - Advertencia si el límite es > 90% del ingreso

#### 4.4. Ratio Deuda/Ingreso
- **Regla**: La deuda total no debe ser extremadamente alta comparada con el ingreso anual.
- **Estado**: ✅ **Implementado**
- **Ubicación**: `ProfileController::initialSetup()` (líneas 213-220)
- **Validación**:
  - Si ratio > 10: Error crítico
  - Si ratio > 5: Advertencia

---

## Validaciones de Coherencia de Objetivos

### 5. Objetivos Financieros y Transacciones

#### 5.1. Coherencia de Objetivo con Límite de Gasto
- **Regla**: El límite de gasto se calcula automáticamente según el objetivo financiero.
- **Estado**: ✅ **Implementado**
- **Ubicación**: `FinancialProfile::calculateSpendingLimit()`
- **Cálculos**:
  - **Ahorrar**: Límite = Ingreso - Ahorro mensual necesario (máx 50% del ingreso)
  - **Pagar Deudas**: Límite = Ingreso - Pago mensual necesario (máx 50% del ingreso)
  - **Controlar Gastos**: Límite = 80% del ingreso
  - **Otro**: Límite = 75% del ingreso

#### 5.2. Viabilidad del Objetivo
- **Regla**: Los objetivos financieros deben ser alcanzables y sostenibles.
- **Estado**: ✅ **Implementado**
- **Ubicación**: `FinancialProfile::validateGoalFeasibility()`
- **Validaciones**:
  - Ahorro mensual necesario no debe exceder 50% del ingreso
  - Pago mensual de deudas no debe exceder 50% del ingreso
  - Fechas límite no deben ser excesivamente lejanas
  - Ratios de deuda/ingreso deben ser razonables

#### 5.3. Progreso hacia Objetivos
- **Regla**: El sistema calcula y actualiza el progreso mensual hacia los objetivos.
- **Estado**: ✅ **Implementado**
- **Ubicación**: `GoalProgressHelper::updateMonthProgress()`
- **Cálculos**:
  - **Ahorro**: Progreso = Ingresos del mes - Gastos del mes
  - **Deudas**: Progreso = Ingresos del mes - Gastos del mes (ahorro disponible para pagar)
  - **Control de Gastos**: Progreso = Gastos del mes (negativo, se quiere minimizar)

---

## Validaciones de Coherencia de Categorías

### 6. Categorías y su Uso

#### 6.1. Categorías Predefinidas vs Personalizadas
- **Regla**: 
  - Las categorías predefinidas (`user_id = NULL`) no pueden ser eliminadas ni editadas por usuarios
  - Solo las categorías personalizadas (`user_id = usuario`) pueden ser editadas o eliminadas
- **Estado**: ✅ **Implementado**
- **Ubicación**: `CategoryController::updateCategory()` y `CategoryController::deleteCategory()`
- **Validación**:
  ```php
  $existing_category = $this->category->getById($id, $user_id);
  if (!$existing_category || $existing_category['user_id'] != $user_id) {
      $errors[] = "La categoría no existe o no tienes permiso para editarla";
  }
  ```

#### 6.2. Tipo de Categoría vs Tipo de Transacción
- **Regla**: Las categorías de tipo "expense" solo pueden usarse en gastos, y las de tipo "income" solo en ingresos.
- **Estado**: ✅ **Implementado**
- **Ubicación**: `TransactionController::addTransaction()` (líneas 136-140)
- **Validación**: Se obtienen categorías filtradas por tipo antes de validar

#### 6.3. Eliminación de Categorías con Transacciones
- **Regla**: No se debe permitir eliminar categorías que tienen transacciones asociadas.
- **Estado**: ✅ **Implementado**
- **Ubicación**: `CategoryController::deleteCategory()` (líneas 278-291)
- **Validación**: Se verifica el número de transacciones asociadas antes de permitir la eliminación
- **Mensaje de error**: "No se puede eliminar la categoría 'X' porque tiene N transacción(es) asociada(s). Por favor, reasigna las transacciones a otra categoría primero."
- **Método auxiliar**: `Transaction::countByCategory()` utilizado para contar transacciones

---

## Validaciones Implementadas

### Resumen de Validaciones Existentes

| Validación | Estado | Ubicación |
|------------|--------|-----------|
| Moneda coherente entre perfil y transacciones | ✅ | `TransactionController::addTransaction()` |
| Métodos de pago válidos en perfil | ✅ | `TransactionController::addTransaction()` |
| Categorías válidas y del tipo correcto | ✅ | `TransactionController::addTransaction()` |
| Límite de gasto ≤ ingreso mensual | ✅ | `FinancialProfile::validateSpendingLimit()` |
| Límites de transacciones por moneda | ✅ | `TransactionController::getTransactionLimits()` |
| Coherencia límite-objetivo financiero | ✅ | `FinancialProfile::validateSpendingLimit()` |
| Viabilidad de objetivos financieros | ✅ | `FinancialProfile::validateGoalFeasibility()` |
| Unicidad de categorías personalizadas | ✅ | `CategoryController::createCategory()` |
| Protección de categorías predefinidas | ✅ | `CategoryController::updateCategory()` |
| Fechas límite de objetivos válidas | ✅ | `ProfileController::initialSetup()` |
| Fechas de transacciones válidas | ✅ | `TransactionController::addTransaction()` |
| Fecha de transacción ≥ fecha inicio perfil | ✅ | `TransactionController::addTransaction()` |
| Prevenir eliminación categorías con transacciones | ✅ | `CategoryController::deleteCategory()` |

---

## Validaciones Pendientes

### Validaciones Recomendadas para Implementar (Opcionales)

Todas las validaciones críticas de coherencia global han sido implementadas. Las siguientes son mejoras opcionales que podrían considerarse en el futuro:

#### 1. Validación de Coherencia de Objetivos al Actualizar Perfil
- **Prioridad**: Media
- **Descripción**: Si un usuario cambia su objetivo financiero, validar que las transacciones existentes sean coherentes
- **Ubicación Sugerida**: `ProfileController::updateProfile()`
- **Nota**: Esta validación es más compleja y puede requerir análisis de transacciones históricas

#### 2. Validación de Integridad de Datos al Cambiar Moneda
- **Prioridad**: Baja
- **Descripción**: Si un usuario cambia su moneda, las transacciones existentes mantendrán sus montos originales pero la moneda del perfil cambiará
- **Nota**: Esto puede ser intencional (usuario que cambia de país), pero podría requerir advertencia

#### 3. Validación de Límite de Gasto al Registrar Transacciones (Preventiva)
- **Prioridad**: Media
- **Descripción**: Advertir (no bloquear) cuando una transacción haría exceder el límite mensual antes de crearla
- **Estado Actual**: Se verifica después de crear la transacción (`checkSpendingLimit()`)
- **Mejora Sugerida**: Validar antes de crear la transacción para mostrar advertencia preventiva

---

## Reglas de Negocio Globales

### Principios de Coherencia

1. **Unicidad de Moneda**: Un usuario solo puede tener una moneda activa a la vez
2. **Integridad Referencial**: Las transacciones deben referenciar categorías y métodos de pago válidos
3. **Coherencia Temporal**: Las fechas deben ser lógicas y coherentes entre entidades
4. **Viabilidad Financiera**: Los objetivos y límites deben ser alcanzables y sostenibles
5. **Protección de Datos**: No se deben eliminar entidades que tienen datos dependientes sin manejo adecuado

### Flujo de Validación Recomendado

1. **Validación de Entrada**: Validar formato y tipos de datos
2. **Validación de Negocio**: Validar reglas de negocio específicas
3. **Validación de Coherencia**: Validar relaciones entre entidades
4. **Validación de Integridad**: Validar integridad referencial
5. **Persistencia**: Guardar datos solo si todas las validaciones pasan

---

## Notas Técnicas

### Orden de Validación

Las validaciones deben ejecutarse en el siguiente orden:

1. **Validaciones básicas** (formato, tipos, rangos)
2. **Validaciones de existencia** (categorías, métodos de pago)
3. **Validaciones de coherencia** (moneda, fechas, límites)
4. **Validaciones de integridad** (referencias, dependencias)
5. **Validaciones de viabilidad** (objetivos, ratios)

### Manejo de Errores

- **Errores críticos**: Previenen la operación (ej: categoría no existe)
- **Advertencias**: Informan pero no previenen (ej: límite de gasto muy alto)
- **Validaciones de coherencia**: Generalmente son errores críticos

### Testing

Se recomienda crear tests unitarios para cada validación de coherencia global:

- Tests de coherencia entre perfil y transacciones
- Tests de integridad referencial de categorías
- Tests de coherencia temporal
- Tests de viabilidad de objetivos financieros

---

**Última actualización**: Octubre 2025  
**Versión del documento**: 1.1  
**Estado**: Todas las validaciones críticas de coherencia global implementadas ✅

