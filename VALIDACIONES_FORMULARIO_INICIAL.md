# Documentación de Validaciones - Formulario Inicial

Este documento describe todas las validaciones implementadas en el formulario de configuración inicial (`views/initial_setup.php`).

## Índice

1. [Validaciones Generales](#validaciones-generales)
2. [Validaciones por Campo](#validaciones-por-campo)
3. [Validaciones por Objetivo Financiero](#validaciones-por-objetivo-financiero)
4. [Validaciones del Lado del Cliente (JavaScript)](#validaciones-del-lado-del-cliente)
5. [Validaciones del Lado del Servidor (PHP)](#validaciones-del-lado-del-servidor)
6. [Validaciones de Límites Financieros](#validaciones-de-límites-financieros)

---

## Validaciones Generales

### Campos Obligatorios

Los siguientes campos son obligatorios en todos los casos:

1. **Ingreso Mensual** (`monthly_income`)
   - Tipo: `number`
   - Atributo HTML: `required`
   - Validación: Debe ser mayor a 0

2. **Moneda** (`currency`)
   - Tipo: `select`
   - Atributo HTML: `required`
   - Opciones válidas: MXN, USD, EUR

3. **Fecha de Inicio** (`start_date`)
   - Tipo: `date`
   - Atributo HTML: `required`
   - Validación: Debe ser una fecha válida

4. **Medios de Pago** (`payment_methods[]`)
   - Tipo: `checkbox` (múltiple selección)
   - Validación: Al menos uno debe estar seleccionado
   - Opciones: `efectivo`, `tarjeta`

5. **Objetivo Financiero** (`financial_goal`)
   - Tipo: `radio`
   - Atributo HTML: `required` (en select oculto)
   - Opciones: `ahorrar`, `pagar_deudas`, `controlar_gastos`, `otro`

---

## Validaciones por Campo

### 1. Ingreso Mensual (`monthly_income`)

#### Cliente (JavaScript)
- **Tipo**: `number`
- **Atributos HTML**: `step="0.01"`, `min="0"`, `required`
- **Validación en tiempo real**: Se actualiza el límite automático al cambiar el valor

#### Servidor (PHP)
- Debe ser mayor a 0
- Debe estar dentro de los límites según la moneda:
  - **MXN**: Mínimo 1,000.00, Máximo 10,000,000.00
  - **USD**: Mínimo 100.00, Máximo 1,000,000.00
  - **EUR**: Mínimo 100.00, Máximo 1,000,000.00
- Mensajes de error:
  - "El ingreso mensual debe ser mayor a 0"
  - "El ingreso mensual es muy bajo. El mínimo recomendado es [monto] [moneda]"
  - "El ingreso mensual es demasiado alto. El máximo permitido es [monto] [moneda]"

---

### 2. Moneda (`currency`)

#### Cliente (JavaScript)
- **Tipo**: `select`
- **Atributo HTML**: `required`
- **Opciones**: MXN, USD, EUR

#### Servidor (PHP)
- Debe ser una de las monedas válidas: `['MXN', 'USD', 'EUR']`
- Mensaje de error: "La moneda seleccionada no es válida"

---

### 3. Fecha de Inicio (`start_date`)

#### Cliente (JavaScript)
- **Tipo**: `date`
- **Atributo HTML**: `required`
- **Valor por defecto**: Fecha actual

#### Servidor (PHP)
- No puede estar vacía
- Debe tener formato válido (YYYY-MM-DD)
- Debe ser una fecha válida según `checkdate()`
- Mensajes de error:
  - "La fecha de inicio es obligatoria"
  - "La fecha de inicio no es válida"

---

### 4. Medios de Pago (`payment_methods[]`)

#### Cliente (JavaScript)
- **Tipo**: `checkbox` (múltiple)
- **Validación en tiempo real**: 
  - Función `validatePaymentMethods(showError)`
  - Muestra/oculta mensaje de error visual
  - Aplica estilos de error a las tarjetas no seleccionadas
- **Validación en submit**:
  - Verifica que al menos un checkbox esté marcado
  - Muestra mensaje: "Debes seleccionar al menos un medio de pago"
  - Previene el envío del formulario si no hay selección

#### Servidor (PHP)
- Debe ser un array no vacío
- Mensaje de error: "Debe seleccionar al menos un medio de pago"

---

### 5. Objetivo Financiero (`financial_goal`)

#### Cliente (JavaScript)
- **Tipo**: `radio` (con select oculto para validación HTML5)
- **Validación en submit**:
  - Verifica que un objetivo esté seleccionado
  - Mensaje: "Por favor selecciona un objetivo financiero"
  - Previene el envío si no hay selección

#### Servidor (PHP)
- No puede estar vacío
- Mensaje de error: "Debe seleccionar un objetivo financiero"

---

## Validaciones por Objetivo Financiero

### Objetivo: Ahorrar (`ahorrar`)

#### Campos Adicionales Requeridos

1. **Meta de Ahorro** (`savings_goal`)
   - **Cliente**:
     - Tipo: `number`, `step="0.01"`, `min="0"`
     - Validación en tiempo real: `validateSavingsGoal()`
     - Validación en submit: Debe ser mayor a 0
     - Mensaje: "Por favor ingresa una meta de ahorro válida"
   - **Servidor**:
     - Debe ser mayor a 0
     - Límites según moneda:
       - **MXN**: Mínimo 100.00, Máximo 10,000,000.00
       - **USD**: Mínimo 10.00, Máximo 1,000,000.00
       - **EUR**: Mínimo 10.00, Máximo 1,000,000.00
     - Mensajes de error:
       - "Debe ingresar una meta de ahorro mayor a 0"
       - "La meta de ahorro es muy baja. El mínimo recomendado es [monto] [moneda]"
       - "La meta de ahorro es demasiado alta. El máximo permitido es [monto] [moneda]"

2. **Fecha Objetivo** (`savings_deadline`) - Opcional
   - **Cliente**:
     - Tipo: `date`
     - Atributo: `min` = fecha de mañana
     - Validación en tiempo real: `validateSavingsGoal()`
     - Cálculo automático: `calculateRecommendedDeadline()`
     - Validaciones:
       - Debe ser fecha futura
       - Muestra advertencias si el ahorro mensual necesario es > 30% o > 50% del ingreso
       - Muestra advertencia si la fecha es > 120 meses (10 años)
   - **Servidor**:
     - Si se proporciona, debe ser fecha válida
     - Debe ser fecha futura
     - Máximo 30 años en el futuro
     - Mensajes de error:
       - "La fecha límite de ahorro no es válida"
       - "La fecha límite de ahorro debe ser una fecha futura"
       - "La fecha límite no puede ser más de 30 años en el futuro"

#### Validaciones de Viabilidad (Cliente)

La función `validateSavingsGoal()` realiza las siguientes validaciones:

- **Cálculo de ahorro mensual necesario**:
  - Si hay fecha límite: `ahorro_mensual = meta_ahorro / meses_disponibles`
  - Si no hay fecha: Usa 25% del ingreso como recomendación

- **Advertencias mostradas**:
  - Si ahorro mensual > 50% del ingreso: "Esto puede ser difícil de mantener"
  - Si ahorro mensual > 30% del ingreso: "Asegúrate de que esto sea sostenible"
  - Si fecha límite > 120 meses: "La fecha límite es muy lejana"

---

### Objetivo: Pagar Deudas (`pagar_deudas`)

#### Campos Adicionales Requeridos

1. **Monto Total de Deuda** (`debt_amount`)
   - **Cliente**:
     - Tipo: `number`, `step="0.01"`, `min="0"`
     - Validación en tiempo real: `validateDebtGoal()`
     - Validación en submit: Debe ser mayor a 0
     - Mensaje: "Por favor ingresa el monto de deuda"
   - **Servidor**:
     - Debe ser mayor a 0
     - Límites según moneda (mismos que meta de ahorro)
     - Validación de ratio deuda/ingreso anual:
       - Si ratio > 10: Error crítico
     - Mensajes de error:
       - "Debe ingresar un monto de deuda mayor a 0 cuando selecciona 'Pagar Deudas'"
       - "El monto de la deuda debe ser mayor a [monto] [moneda]"
       - "El monto de la deuda es demasiado alto. El máximo permitido es [monto] [moneda]"
       - "La deuda es extremadamente alta comparada con tu ingreso anual. Por favor verifica los datos ingresados."

2. **Número de Deudas** (`debt_count`) - Opcional
   - **Cliente**: Tipo `number`, `step="1"`, `min="1"`
   - **Servidor**:
     - Si se proporciona, debe estar entre 1 y 50
     - Mensaje: "El número de deudas debe estar entre 1 y 50"

3. **Fecha Objetivo para Pagar** (`debt_deadline`) - Opcional
   - **Cliente**:
     - Tipo: `date`
     - Atributo: `min` = 1 mes en el futuro
     - Validación en tiempo real: `validateDebtGoal()`
     - Cálculo automático: `calculateDebtDeadlineFromPayment()`
   - **Servidor**:
     - Si se proporciona, debe ser fecha válida
     - Debe ser fecha futura
     - Máximo 15 años en el futuro
     - Mensajes de error:
       - "La fecha objetivo para pagar deudas no es válida"
       - "La fecha objetivo para pagar deudas debe ser una fecha futura"
       - "La fecha objetivo no puede ser más de 15 años en el futuro para pagar deudas"

4. **Pago Mensual** (`monthly_payment`) - Opcional
   - **Cliente**:
     - Tipo: `number`, `step="0.01"`, `min="0"`
     - Validación en tiempo real: `validateDebtGoal()`
     - Cálculo automático: `calculateRecommendedMonthlyPayment()`
   - **Servidor**:
     - Si se proporciona:
       - No puede exceder el máximo permitido según moneda
       - No puede exceder el 95% del ingreso mensual
     - Mensajes de error:
       - "El pago mensual es demasiado alto. El máximo permitido es [monto] [moneda]"
       - "El pago mensual no puede exceder el 95% de tu ingreso mensual. Debes dejar algo para gastos básicos."

#### Validaciones de Viabilidad (Cliente)

La función `validateDebtGoal()` realiza las siguientes validaciones:

- **Cálculo de ratio deuda/ingreso anual**: `ratio = deuda_total / (ingreso_mensual * 12)`
- **Cálculo de pago mensual necesario** (si hay fecha límite):
  - `pago_mensual = deuda_total / meses_disponibles`
- **Advertencias mostradas**:
  - Si ratio > 5: "Tu deuda es muy alta comparada con tu ingreso anual"
  - Si pago mensual necesario > 50% del ingreso: "Esto puede ser difícil de mantener"
  - Si pago mensual necesario > 30% del ingreso: "Asegúrate de que esto sea sostenible"
  - Si tiempo estimado > 120 meses: "Considera aumentar el pago mensual"

---

### Objetivo: Controlar Gastos (`controlar_gastos`)

- **Cliente**: No requiere campos adicionales
- **Servidor**: No requiere campos adicionales
- El límite de gasto se calcula automáticamente (80% del ingreso)

---

### Objetivo: Otro (`otro`)

#### Campos Adicionales Requeridos

1. **Descripción del Objetivo** (`goal_description`)
   - **Cliente**:
     - Tipo: `textarea`
     - Validación en tiempo real: `validateOtherGoal()`
     - Validación en submit: Mínimo 10 caracteres
     - Indicadores visuales:
       - Borde rojo: Campo vacío
       - Borde amarillo: Menos de 10 caracteres
       - Borde verde: 10 o más caracteres
     - Mensaje: "Por favor describe tu objetivo (mínimo 10 caracteres)"
   - **Servidor**:
     - No puede estar vacío
     - Mínimo 10 caracteres
     - Máximo 500 caracteres
     - Mensajes de error:
       - "Debe describir su objetivo financiero cuando selecciona 'Otro'"
       - "La descripción del objetivo debe tener al menos 10 caracteres"
       - "La descripción del objetivo es demasiado larga (máximo 500 caracteres)"

---

## Límite Mensual de Gasto (`spending_limit`)

### Modo Automático (`spending_limit_type = "auto"`)

- **Cliente**:
  - Función: `updateAutoLimit()`
  - Se calcula automáticamente según:
    - Objetivo financiero seleccionado
    - Meta de ahorro (si aplica)
    - Monto de deuda (si aplica)
    - Fechas límite (si aplican)
  - Se actualiza en tiempo real al cambiar cualquier campo relacionado
  - Muestra información visual con el límite calculado

- **Servidor**:
  - Se calcula usando `ProfileModel::calculateSpendingLimit()`
  - No requiere validación adicional

### Modo Manual (`spending_limit_type = "manual"`)

#### Cliente (JavaScript)
- **Tipo**: `number`, `step="0.01"`, `min="0"`
- **Atributo HTML**: `required` (solo cuando modo manual está activo)
- **Validación en tiempo real**: `validateSpendingLimit()`
- **Validaciones**:
  - No puede ser mayor que el ingreso mensual
  - Muestra advertencias si:
    - El límite es > 90% del ingreso
    - Para objetivo "ahorrar": Si disponible para ahorro < 20% del ingreso
    - Para objetivo "pagar_deudas": Si disponible para pagos < 30% del ingreso

#### Servidor (PHP)
- Debe ser mayor a 0
- No puede exceder el máximo permitido según moneda
- Se valida viabilidad usando `ProfileModel::validateSpendingLimit()`
- Mensajes de error:
  - "El límite de gasto manual debe ser mayor a 0"
  - "El límite de gasto es demasiado alto. El máximo permitido es [monto] [moneda]"
  - Mensajes de advertencia de viabilidad del modelo

---

## Validaciones del Lado del Cliente (JavaScript)

### Funciones de Validación Principales

1. **`validatePaymentMethods(showError)`**
   - Valida que al menos un medio de pago esté seleccionado
   - Muestra/oculta mensaje de error visual
   - Aplica estilos de error a las tarjetas

2. **`validateSavingsGoal()`**
   - Valida meta de ahorro y fecha límite
   - Calcula ahorro mensual necesario
   - Muestra información y advertencias

3. **`validateDebtGoal()`**
   - Valida monto de deuda, fecha límite y pago mensual
   - Calcula ratio deuda/ingreso
   - Muestra información y advertencias

4. **`validateOtherGoal()`**
   - Valida descripción del objetivo
   - Aplica estilos visuales según longitud

5. **`validateSpendingLimit()`**
   - Valida límite de gasto manual
   - Verifica que no exceda el ingreso
   - Muestra advertencias de viabilidad

6. **Validación en Submit** (líneas 1619-1706)
   - Valida objetivo financiero seleccionado
   - Valida medios de pago
   - Valida campos específicos según objetivo:
     - `ahorrar`: Meta de ahorro > 0
     - `pagar_deudas`: Monto de deuda > 0, fecha límite futura (si se proporciona)
     - `otro`: Descripción con mínimo 10 caracteres
   - Previene envío si hay errores
   - Hace scroll al primer error
   - Aplica animación de "shake" al elemento con error

### Funciones de Cálculo Automático

1. **`calculateRecommendedDeadline()`**
   - Calcula fecha límite recomendada para meta de ahorro
   - Basado en 25% del ingreso como ahorro mensual
   - Límites: Mínimo 4 meses, máximo 60 meses

2. **`calculateRecommendedMonthlyPayment()`**
   - Calcula pago mensual recomendado para deudas
   - Basado en fecha límite o 30% del ingreso (mínimo para pagar en 24 meses)
   - Límite máximo: 50% del ingreso

3. **`calculateDebtDeadlineFromPayment()`**
   - Calcula fecha límite basada en pago mensual ingresado
   - Límites: Mínimo 6 meses, máximo 120 meses

4. **`updateAutoLimit()`**
   - Calcula y muestra límite de gasto automático
   - Se actualiza en tiempo real según cambios en el formulario

---

## Validaciones del Lado del Servidor (PHP)

### Ubicación
- **Archivo**: `controllers/ProfileController.php`
- **Método**: `initialSetup()` (líneas 55-352)

### Flujo de Validación

1. **Verificación de sesión**: Usuario debe estar autenticado
2. **Verificación de setup previo**: Si ya está completo, redirige al dashboard
3. **Sanitización de datos**: Todos los inputs se sanitizan
4. **Validaciones básicas**:
   - Moneda válida
   - Ingreso mensual dentro de límites
   - Fecha de inicio válida
   - Al menos un medio de pago
   - Objetivo financiero seleccionado

5. **Validaciones por objetivo**:
   - **Ahorrar**: Meta de ahorro, fecha límite
   - **Pagar Deudas**: Monto de deuda, número de deudas, fecha límite, pago mensual
   - **Otro**: Descripción del objetivo

6. **Validación de viabilidad del objetivo**:
   - Usa `ProfileModel::validateGoalFeasibility()`
   - Verifica que el objetivo sea alcanzable
   - Genera advertencias si es difícil de mantener

7. **Validación de límite de gasto**:
   - Si es automático: Se calcula
   - Si es manual: Se valida que sea válido y viable

8. **Si hay errores**:
   - Se guardan en `$_SESSION['setup_errors']`
   - Se guardan datos en `$_SESSION['setup_data']`
   - Se redirige de vuelta al formulario

9. **Si no hay errores**:
   - Se crea el perfil financiero
   - Se muestra mensaje de éxito
   - Se redirige al dashboard

---

## Validaciones de Límites Financieros

### Límites por Moneda

Los límites se obtienen de `ProfileController::getFinancialLimits($currency)`:

#### MXN (Peso Mexicano)
- **Ingreso mínimo**: 1,000.00
- **Ingreso máximo**: 10,000,000.00
- **Meta de ahorro mínimo**: 100.00
- **Monto mínimo general**: 100.00
- **Monto máximo general**: 10,000,000.00

#### USD (Dólar Estadounidense)
- **Ingreso mínimo**: 100.00
- **Ingreso máximo**: 1,000,000.00
- **Meta de ahorro mínimo**: 10.00
- **Monto mínimo general**: 10.00
- **Monto máximo general**: 1,000,000.00

#### EUR (Euro)
- **Ingreso mínimo**: 100.00
- **Ingreso máximo**: 1,000,000.00
- **Meta de ahorro mínimo**: 10.00
- **Monto mínimo general**: 10.00
- **Monto máximo general**: 1,000,000.00

---

## Mensajes de Error y Advertencias

### Mensajes de Error (Previenen el envío)

- "El ingreso mensual debe ser mayor a 0"
- "La moneda seleccionada no es válida"
- "La fecha de inicio es obligatoria"
- "Debe seleccionar al menos un medio de pago"
- "Debe seleccionar un objetivo financiero"
- "Debe ingresar una meta de ahorro mayor a 0"
- "La fecha límite de ahorro debe ser una fecha futura"
- "Debe ingresar un monto de deuda mayor a 0 cuando selecciona 'Pagar Deudas'"
- "La fecha objetivo para pagar deudas debe ser una fecha futura"
- "Por favor describe tu objetivo (mínimo 10 caracteres)"
- "El límite de gasto manual debe ser mayor a 0"

### Advertencias (Informativas, no previenen envío)

- Advertencias sobre porcentajes de ingreso para ahorro/pagos
- Advertencias sobre fechas límite muy lejanas
- Advertencias sobre ratios de deuda/ingreso altos
- Advertencias sobre límites de gasto muy altos

---

## Notas Técnicas

### Sistema de Validación Unificado

El formulario inicial **NO** usa el sistema de validación unificado (`FormValidator` de `form-validation.js`) porque tiene validaciones personalizadas complejas. El formulario tiene:
- `data-validate="false"`
- `data-no-validate="true"`

Esto significa que las validaciones se manejan completamente con JavaScript personalizado dentro del mismo archivo.

### Validaciones en Tiempo Real

La mayoría de las validaciones se ejecutan en tiempo real usando:
- Eventos `input` con debounce (300ms)
- Eventos `change` para campos de fecha
- Eventos `submit` para validación final

### Feedback Visual

- **Errores**: Bordes rojos, mensajes de error, animación "shake"
- **Advertencias**: Cajas amarillas con iconos de advertencia
- **Información**: Cajas azules con información calculada
- **Éxito**: Bordes verdes en campos válidos

---

## Resumen de Validaciones por Campo

| Campo | Requerido | Tipo | Validaciones Cliente | Validaciones Servidor |
|-------|-----------|------|---------------------|----------------------|
| `monthly_income` | Sí | number | > 0, actualiza límite | > 0, límites por moneda |
| `currency` | Sí | select | Selección válida | MXN/USD/EUR |
| `start_date` | Sí | date | Fecha válida | Fecha válida, formato correcto |
| `payment_methods[]` | Sí | checkbox | Al menos 1 seleccionado | Array no vacío |
| `financial_goal` | Sí | radio | Selección válida | No vacío |
| `savings_goal` | Condicional* | number | > 0, validación viabilidad | > 0, límites por moneda |
| `savings_deadline` | No | date | Futura, validación viabilidad | Futura, max 30 años |
| `debt_amount` | Condicional* | number | > 0, validación ratio | > 0, límites, ratio < 10 |
| `debt_count` | No | number | - | 1-50 si se proporciona |
| `debt_deadline` | No | date | Futura, validación viabilidad | Futura, max 15 años |
| `monthly_payment` | No | number | Validación viabilidad | < 95% ingreso, límites |
| `goal_description` | Condicional* | textarea | Min 10 caracteres | Min 10, max 500 |
| `spending_limit` | Condicional** | number | < ingreso, validación viabilidad | > 0, límites, viabilidad |

*Requerido solo si el objetivo financiero correspondiente está seleccionado
**Requerido solo si `spending_limit_type = "manual"`

---

**Última actualización**: Generado automáticamente desde el código fuente
**Versión del documento**: 1.0

