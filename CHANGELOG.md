# Changelog

Todos los cambios notables de este proyecto serán documentados en este archivo.

## [1.0.1] - 2025-10-27

### 🐛 Correcciones

- **Menú móvil**: Corregido el problema donde el menú hamburguesa no se abría al hacer clic
  - Agregado `DOMContentLoaded` para asegurar que el DOM esté completamente cargado
  - Agregada validación de existencia de elementos antes de agregar event listeners
  - Agregada funcionalidad para cerrar el menú al hacer clic en un enlace
  - Agregada funcionalidad para cerrar el menú al hacer clic fuera de él
  - Agregadas transiciones suaves con animación CSS
  - Eliminada función duplicada en main.js para evitar conflictos

### ⚡ Mejoras

- Mejor experiencia de usuario en dispositivos móviles
- Animaciones suaves al abrir/cerrar el menú
- Cierre automático del menú al navegar

## [1.0.0] - 2025-10-27

### ✨ Características Principales

#### Autenticación y Seguridad
- ✅ Sistema de registro de usuarios con validación robusta
- ✅ Inicio de sesión seguro con contraseñas encriptadas (bcrypt)
- ✅ Recuperación de contraseña con tokens temporales (5 minutos)
- ✅ Sistema de sesiones seguras
- ✅ Cierre de sesión con invalidación de sesión
- ✅ Validación de contraseñas: mínimo 8 caracteres, mayúsculas, números y caracteres especiales

#### Perfil Financiero
- ✅ Configuración inicial obligatoria al primer uso
- ✅ Gestión de ingreso mensual
- ✅ Soporte para múltiples monedas (MXN, USD, EUR)
- ✅ Selección de medios de pago (efectivo, tarjeta)
- ✅ Objetivos financieros configurables:
  - Ahorrar (con meta y fecha objetivo)
  - Pagar deudas (con monto total)
  - Controlar gastos
  - Personalizado
- ✅ Límite de gasto mensual automático o manual
- ✅ Edición completa del perfil

#### Gestión de Transacciones
- ✅ Registro de gastos con categorización
- ✅ Registro de ingresos adicionales
- ✅ 9 categorías predefinidas con iconos
- ✅ Soporte para múltiples métodos de pago
- ✅ Descripción opcional para transacciones
- ✅ Selección de fecha de transacción
- ✅ Historial completo con filtros avanzados
- ✅ Filtrado por:
  - Mes y año
  - Categoría
  - Tipo (ingreso/gasto)
- ✅ Eliminación de transacciones
- ✅ Exportación a CSV/Excel

#### Dashboard y Visualización
- ✅ Resumen financiero en tiempo real
- ✅ Tarjetas informativas:
  - Total de ingresos
  - Total de gastos
  - Balance disponible
  - Porcentaje de límite usado
- ✅ Transacciones recientes
- ✅ Acciones rápidas
- ✅ Progreso de metas (ahorro/deudas)

#### Reportes y Gráficos
- ✅ Gráficos interactivos con Chart.js:
  - Comparación mensual (líneas)
  - Gastos por categoría (dona)
  - Distribución por método de pago (pastel/barras)
- ✅ Desglose detallado por categoría
- ✅ Estadísticas mensuales
- ✅ Promedio de gasto diario
- ✅ Seguimiento de progreso hacia metas
- ✅ Filtros por período

#### Alertas y Notificaciones
- ✅ Alerta visual al exceder límite de gasto
- ✅ Alerta sonora para notificaciones críticas
- ✅ Advertencia al alcanzar 80% del límite
- ✅ Notificaciones en dashboard
- ✅ Sistema de alertas no leídas

#### Diseño y UX
- ✅ Interfaz moderna con Tailwind CSS
- ✅ Diseño responsive (móvil, tablet, desktop)
- ✅ Paleta de colores azules profesional
- ✅ Botones con estilo personalizado
- ✅ Animaciones y transiciones suaves
- ✅ Iconos de Font Awesome
- ✅ Tipografía Inter (Google Fonts)
- ✅ Efectos hover en tarjetas
- ✅ Estados visuales claros
- ✅ Flash messages auto-hide

### 🏗️ Arquitectura

- ✅ Patrón MVC (Model-View-Controller)
- ✅ Separación de responsabilidades
- ✅ Código modular y mantenible
- ✅ PDO para base de datos
- ✅ Prepared statements (seguridad)
- ✅ Autoload de clases
- ✅ Routing con .htaccess
- ✅ Configuración centralizada

### 📊 Base de Datos

- ✅ 6 tablas principales:
  - users
  - financial_profiles
  - transactions
  - expense_categories
  - alerts
  - income_sources
- ✅ Relaciones con claves foráneas
- ✅ Índices para optimización
- ✅ Soporte UTF-8
- ✅ Timestamps automáticos

### 🔧 Funcionalidades Técnicas

- ✅ Sistema de validación en servidor
- ✅ Validación en cliente con JavaScript
- ✅ Sanitización de datos
- ✅ Protección XSS
- ✅ Protección SQL Injection
- ✅ Manejo de errores
- ✅ Flash messages
- ✅ Helpers y utilidades
- ✅ Formateo de moneda
- ✅ Cálculos automáticos

### 📱 Compatibilidad

- ✅ Chrome 90+
- ✅ Firefox 88+
- ✅ Safari 14+
- ✅ Edge 90+
- ✅ Opera 76+

### 📝 Documentación

- ✅ README completo
- ✅ Guía de instalación (INSTALL.md)
- ✅ Comentarios en código
- ✅ Estructura clara de archivos
- ✅ Ejemplos de uso

### 🐛 Correcciones

- Primera versión estable

### 🔐 Seguridad

- ✅ Contraseñas encriptadas con bcrypt
- ✅ Tokens seguros para recuperación
- ✅ Sesiones con expiración
- ✅ Validación de datos exhaustiva
- ✅ Sanitización de inputs
- ✅ Protección CSRF (sesiones)

### ⚡ Rendimiento

- ✅ Consultas optimizadas con índices
- ✅ Carga diferida de recursos
- ✅ Assets desde CDN
- ✅ Código minificado cuando es posible

---

## Roadmap Futuro

### v1.1.0 (Planificado)
- [ ] Exportación a PDF
- [ ] Múltiples cuentas bancarias
- [ ] Presupuestos por categoría
- [ ] Modo oscuro

### v1.2.0 (Planificado)
- [ ] Notificaciones por email
- [ ] Recordatorios programados
- [ ] Reportes personalizados
- [ ] Gráficos adicionales

### v2.0.0 (Futuro)
- [ ] API REST
- [ ] Aplicación móvil (PWA)
- [ ] Integración con bancos
- [ ] Multi-usuario (familia)
- [ ] Sincronización en la nube

---

**Nota**: El formato es [MAJOR.MINOR.PATCH] siguiendo Semantic Versioning.

