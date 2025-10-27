# 📁 Estructura Completa del Proyecto

## Árbol de Archivos

```
App-Control-Gastos/
│
├── 📄 .gitignore                    # Archivos a ignorar en Git
├── 📄 .htaccess                     # Redirección a carpeta public
├── 📄 CHANGELOG.md                  # Historial de cambios
├── 📄 INSTALL.md                    # Guía rápida de instalación
├── 📄 LICENSE                       # Licencia MIT
├── 📄 PROJECT_STRUCTURE.md          # Este archivo
├── 📄 README.md                     # Documentación principal
├── 📄 SECURITY.md                   # Políticas de seguridad
├── 📄 USER_GUIDE.md                 # Guía de usuario
├── 📄 database.sql                  # Script de base de datos
│
├── 📁 config/                       # Configuración
│   ├── 📄 config.php               # Configuración general
│   └── 📄 database.php             # Conexión a base de datos
│
├── 📁 models/                       # Modelos (capa de datos)
│   ├── 📄 Alert.php                # Modelo de alertas
│   ├── 📄 FinancialProfile.php     # Modelo de perfil financiero
│   ├── 📄 Transaction.php          # Modelo de transacciones
│   └── 📄 User.php                 # Modelo de usuarios
│
├── 📁 controllers/                  # Controladores (lógica)
│   ├── 📄 AuthController.php       # Autenticación
│   ├── 📄 ProfileController.php    # Gestión de perfil
│   ├── 📄 ReportController.php     # Reportes y estadísticas
│   └── 📄 TransactionController.php # Transacciones
│
├── 📁 views/                        # Vistas (interfaz)
│   ├── 📄 add_transaction.php      # Formulario nueva transacción
│   ├── 📄 dashboard.php            # Panel principal
│   ├── 📄 forgot_password.php      # Recuperación de contraseña
│   ├── 📄 initial_setup.php        # Configuración inicial
│   ├── 📄 login.php                # Inicio de sesión
│   ├── 📄 profile.php              # Edición de perfil
│   ├── 📄 register.php             # Registro de usuario
│   ├── 📄 reports.php              # Reportes y gráficos
│   ├── 📄 reset_password.php       # Restablecer contraseña
│   └── 📄 transactions.php         # Lista de transacciones
│
├── 📁 includes/                     # Componentes comunes
│   ├── 📄 footer.php               # Footer de las páginas
│   ├── 📄 header.php               # Header con estilos
│   └── 📄 navbar.php               # Barra de navegación
│
└── 📁 public/                       # Carpeta pública
    ├── 📄 .htaccess                # Configuración Apache
    ├── 📄 index.php                # Router principal
    └── 📁 js/
        └── 📄 main.js              # JavaScript principal
```

## Detalles por Directorio

### 📂 config/
Archivos de configuración del sistema.

**config.php**:
- Configuración general de la aplicación
- Constantes globales (BASE_URL, rutas, etc.)
- Funciones helper (sanitize, formatCurrency, etc.)
- Configuración de sesiones
- Autoload de clases

**database.php**:
- Clase Database para conexión PDO
- Gestión de conexión a MySQL
- Manejo de errores de conexión

### 📂 models/
Clases que representan entidades y gestionan datos.

**User.php**:
- Registro de usuarios
- Login y autenticación
- Gestión de contraseñas
- Recuperación de cuenta
- Actualización de perfil

**FinancialProfile.php**:
- Creación de perfil financiero
- Configuración inicial
- Gestión de ingreso mensual
- Objetivos financieros
- Cálculo de límites de gasto

**Transaction.php**:
- Registro de transacciones (gastos/ingresos)
- Consulta por período
- Filtrado y búsqueda
- Resúmenes mensuales
- Agrupación por categoría
- Exportación de datos

**Alert.php**:
- Creación de alertas
- Gestión de notificaciones
- Marcado de leídas
- Conteo de no leídas

### 📂 controllers/
Lógica de negocio y procesamiento.

**AuthController.php**:
- Procesamiento de registro
- Validación de login
- Gestión de logout
- Recuperación de contraseña
- Restablecimiento de contraseña

**ProfileController.php**:
- Setup inicial de perfil
- Actualización de información
- Gestión de ingresos adicionales
- Cálculo de límites

**TransactionController.php**:
- Registro de nuevas transacciones
- Validación de datos
- Verificación de límites
- Generación de alertas
- Eliminación de transacciones
- Exportación a CSV

**ReportController.php**:
- Generación de datos para dashboard
- Datos para gráficos
- Comparaciones mensuales
- Estadísticas por categoría
- Distribución por método de pago

### 📂 views/
Interfaces de usuario (HTML + PHP).

**Autenticación**:
- `login.php`: Formulario de inicio de sesión
- `register.php`: Formulario de registro
- `forgot_password.php`: Solicitud de recuperación
- `reset_password.php`: Formulario de nueva contraseña

**Perfil**:
- `initial_setup.php`: Configuración inicial obligatoria
- `profile.php`: Edición de perfil y configuración

**Transacciones**:
- `add_transaction.php`: Formulario para nuevo movimiento
- `transactions.php`: Lista completa con filtros

**Dashboard y Reportes**:
- `dashboard.php`: Panel principal con resumen
- `reports.php`: Gráficos y estadísticas detalladas

### 📂 includes/
Componentes reutilizables.

**header.php**:
- DOCTYPE y meta tags
- Carga de Tailwind CSS
- Carga de Chart.js
- Font Awesome
- Estilos personalizados
- Google Fonts

**navbar.php**:
- Barra de navegación principal
- Menú responsive
- Links a secciones
- Botón de logout

**footer.php**:
- Cierre de HTML
- Scripts JavaScript
- Funcionalidades comunes

### 📂 public/
Punto de entrada público.

**index.php**:
- Router principal de la aplicación
- Gestión de páginas
- Enrutamiento de acciones
- Control de acceso
- Verificación de setup inicial

**.htaccess**:
- Reescritura de URLs
- Configuración de rutas
- Prevención de listado de directorios

**js/main.js**:
- Menú móvil
- Validación de formularios
- Toggle de contraseñas
- Alertas y notificaciones
- Funciones helper
- Animaciones

### 📄 Archivos Raíz

**database.sql**:
- Script de creación de base de datos
- Definición de tablas
- Índices y relaciones
- Datos iniciales (categorías)

**README.md**:
- Documentación principal
- Características completas
- Instalación detallada
- Estructura del proyecto
- Guía de uso

**INSTALL.md**:
- Guía rápida paso a paso
- Instalación en XAMPP
- Solución de problemas
- Verificación de instalación

**USER_GUIDE.md**:
- Manual de usuario completo
- Funcionalidades explicadas
- Preguntas frecuentes
- Mejores prácticas
- Consejos financieros

**SECURITY.md**:
- Políticas de seguridad
- Medidas implementadas
- Configuración para producción
- Checklist de seguridad
- Reporte de vulnerabilidades

**CHANGELOG.md**:
- Historial de versiones
- Características por versión
- Correcciones de bugs
- Roadmap futuro

**LICENSE**:
- Licencia MIT
- Términos de uso
- Derechos de autor

**.gitignore**:
- Archivos a excluir de Git
- Configuración sensible
- Archivos temporales
- Logs y cache

**PROJECT_STRUCTURE.md**:
- Este archivo
- Descripción de estructura
- Índice de archivos

## Estadísticas del Proyecto

### Archivos por Tipo

| Tipo | Cantidad | Descripción |
|------|----------|-------------|
| PHP  | 22       | Backend y vistas |
| JS   | 1        | Frontend interactivo |
| SQL  | 1        | Base de datos |
| MD   | 7        | Documentación |
| HTACCESS | 2   | Configuración Apache |
| Otros | 2       | LICENSE, .gitignore |
| **Total** | **35** | Archivos totales |

### Líneas de Código Aproximadas

| Componente | Líneas |
|------------|--------|
| Models | ~800 |
| Controllers | ~900 |
| Views | ~2,200 |
| Config | ~200 |
| JavaScript | ~300 |
| SQL | ~150 |
| Documentación | ~2,500 |
| **Total** | **~7,050** |

### Funcionalidades Implementadas

- ✅ 22 archivos PHP de código
- ✅ 10 vistas de usuario completas
- ✅ 4 modelos de datos
- ✅ 4 controladores
- ✅ 6 tablas de base de datos
- ✅ 9 categorías de gastos predefinidas
- ✅ 3 tipos de gráficos interactivos
- ✅ Sistema completo de autenticación
- ✅ Gestión de perfil financiero
- ✅ Sistema de alertas
- ✅ Exportación de datos
- ✅ Dashboard interactivo
- ✅ Reportes visuales

## Tecnologías Utilizadas

### Backend
- **PHP 7.4+**: Lenguaje principal
- **MySQL/MariaDB**: Base de datos
- **PDO**: Abstracción de base de datos
- **Sessions**: Gestión de usuarios

### Frontend
- **HTML5**: Estructura
- **Tailwind CSS**: Estilos
- **JavaScript ES6+**: Interactividad
- **Chart.js 4.0**: Gráficos
- **Font Awesome 6**: Iconos

### Arquitectura
- **MVC**: Patrón de diseño
- **PDO Prepared Statements**: Seguridad
- **RESTful-like**: Estilo de URLs
- **Responsive Design**: Mobile-first

### Herramientas
- **Apache**: Servidor web
- **XAMPP**: Entorno de desarrollo
- **Git**: Control de versiones
- **phpMyAdmin**: Administración de BD

## Flujo de la Aplicación

```
1. Usuario accede → public/index.php (Router)
   ↓
2. Router determina página/acción
   ↓
3. Si es acción → Controller procesa
   ↓
4. Controller usa Model para datos
   ↓
5. Model interactúa con BD
   ↓
6. Controller procesa respuesta
   ↓
7. Router carga View apropiada
   ↓
8. View renderiza HTML con datos
   ↓
9. Usuario ve resultado
```

## Dependencias Externas (CDN)

- Tailwind CSS: `https://cdn.tailwindcss.com`
- Chart.js: `https://cdn.jsdelivr.net/npm/chart.js`
- Font Awesome: `https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css`
- Google Fonts (Inter): `https://fonts.googleapis.com/css2?family=Inter`

## Próximas Expansiones Planeadas

**v1.1.0**:
- Exportación PDF
- Múltiples cuentas
- Presupuestos por categoría
- Modo oscuro

**v1.2.0**:
- API REST
- PWA (Progressive Web App)
- Notificaciones push
- Sincronización

**v2.0.0**:
- Multi-usuario (familia)
- Integración bancaria
- Machine Learning para predicciones
- App móvil nativa

## Mantenimiento

### Archivos que Requieren Actualización Regular

- `config/database.php`: Credenciales
- `config/config.php`: URLs y configuración
- `database.sql`: Estructura de BD
- `CHANGELOG.md`: Historial de versiones
- `README.md`: Documentación

### Backups Recomendados

- Base de datos: Diario
- Archivos de configuración: Semanal
- Código completo: Cada commit Git

---

**Última actualización**: Octubre 2025
**Versión del proyecto**: 1.0.0
**Total de archivos**: 35
**Líneas de código**: ~7,050

