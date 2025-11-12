# 💰 Sistema de Control de Gastos Personales

Sistema completo de gestión financiera personal desarrollado en PHP con arquitectura MVC, diseñado con Tailwind CSS para proporcionar una interfaz moderna y responsiva.

## 📋 Características Principales

### 🔐 Autenticación y Seguridad
- ✅ Registro de usuarios con validación robusta
- ✅ Verificación de correo electrónico
- ✅ Inicio de sesión seguro con contraseñas encriptadas (bcrypt)
- ✅ Recuperación de contraseña por correo electrónico (PHPMailer)
- ✅ Tokens de recuperación con expiración de 5 minutos
- ✅ Sesiones seguras y cierre de sesión
- ✅ Protección CSRF mediante sesiones

### 💼 Perfil Financiero
- ✅ Configuración inicial obligatoria al primer inicio de sesión
- ✅ Gestión de ingreso mensual
- ✅ Selección de moneda (MXN, USD, EUR)
- ✅ Medios de pago configurables (efectivo, tarjeta)
- ✅ Objetivos financieros personalizables:
  - Ahorrar (con meta y fecha objetivo)
  - Pagar deudas (con monto total)
  - Controlar gastos
  - Otro (personalizable)
- ✅ Límite de gasto mensual automático o manual

### 💸 Gestión de Transacciones
- ✅ Registro de gastos por categoría
- ✅ Registro de ingresos adicionales
- ✅ 15 categorías predefinidas de gastos con iconos Font Awesome
- ✅ 5 categorías predefinidas de ingresos
- ✅ **Categorías personalizadas**: Crea, edita y elimina tus propias categorías
- ✅ Iconos y colores personalizables para categorías
- ✅ Múltiples métodos de pago (efectivo, tarjeta)
- ✅ Descripción opcional para cada transacción
- ✅ Historial completo de transacciones
- ✅ Filtrado avanzado por fecha, categoría y tipo
- ✅ Eliminación de transacciones con confirmación
- ✅ Exportación a CSV/Excel

### 📊 Dashboard y Reportes
- ✅ Resumen financiero mensual en tiempo real
- ✅ Tarjetas informativas: ingresos, gastos, balance y límite usado
- ✅ Transacciones recientes
- ✅ Gráficos interactivos (Chart.js 4.0):
  - Gastos por categoría (gráfico de dona)
  - Distribución por método de pago (gráfico de pastel/barras)
  - Comparación mensual (gráfico de líneas)
  - Desglose detallado por categoría
- ✅ Visualización de progreso hacia metas (ahorro/deudas)
- ✅ Seguimiento mensual de objetivos (MonthlyGoalProgress)
- ✅ Estadísticas y promedios calculados automáticamente

### 🔔 Alertas y Notificaciones
- ✅ Sistema completo de alertas y notificaciones
- ✅ Alerta visual y sonora al exceder límite de gasto
- ✅ Advertencia al alcanzar 80% del límite
- ✅ Seguimiento de metas de ahorro con progreso visual
- ✅ Recordatorios de deudas pendientes
- ✅ Notificaciones en dashboard
- ✅ Sistema de alertas no leídas
- ✅ Flash messages con auto-hide

## 🎨 Diseño

- **Framework CSS**: Tailwind CSS
- **Paleta de colores**: Azules (claros y oscuros)
- **Botones**: Blancos con texto azul
- **Alertas**: Color rojo para advertencias
- **Responsive**: Compatible con móviles, tablets y escritorio
- **Iconos**: Font Awesome 6
- **Gráficos**: Chart.js

## 🛠️ Tecnologías

- **Backend**: PHP 7.4+
- **Base de datos**: MySQL 5.7+ / MariaDB 10.3+
- **Frontend**: HTML5, CSS3 (Tailwind CSS), JavaScript ES6+
- **Arquitectura**: MVC (Model-View-Controller)
- **Email**: PHPMailer (Composer)
- **Gráficos**: Chart.js 4.0
- **Iconos**: Font Awesome 6.4.0
- **Servidor**: Apache con mod_rewrite
- **Router**: Sistema de enrutamiento personalizado
- **Autoload**: Composer + SPL Autoloader

## 📦 Instalación

### Requisitos Previos

- PHP 7.4 o superior
- MySQL 5.7+ o MariaDB 10.3+
- Apache con mod_rewrite habilitado
- Extensiones PHP requeridas:
  - PDO
  - PDO_MySQL
  - mbstring
  - openssl

### Pasos de Instalación

1. **Clonar o descargar el proyecto**
   ```bash
   cd C:\xampp\htdocs
   # Si estás usando Git:
   git clone <repository-url> App-Control-Gastos
   # O simplemente copia los archivos al directorio
   ```

2. **Configurar la base de datos**
   
   Importa el archivo `database.sql` en phpMyAdmin o desde la línea de comandos:
   ```bash
   mysql -u root -p < database.sql
   ```
   
   O desde phpMyAdmin:
   - Abre phpMyAdmin (http://localhost/phpmyadmin)
   - Crea una nueva base de datos llamada `control_gastos`
   - Importa el archivo `database.sql`

3. **Configurar la conexión a la base de datos**
   
   Edita el archivo `config/Database.php` con tus credenciales:
   ```php
   private $host = 'localhost';
   private $db_name = 'control_gastos';
   private $username = 'root';
   private $password = ''; // Tu contraseña de MySQL
   ```
   
   O copia el archivo de ejemplo:
   ```bash
   cp config/Database.example.php config/Database.php
   # Luego edita Database.php con tus credenciales
   ```

4. **Configurar correo electrónico (opcional pero recomendado)**
   
   La configuración de email está en `config/config.php`. El sistema usa PHPMailer para envío de correos.
   
   Para configurar SMTP, edita `config/config.php`:
   ```php
   define('SMTP_HOST', 'smtp.gmail.com');
   define('SMTP_PORT', 587);
   define('SMTP_USERNAME', 'tu-email@gmail.com');
   define('SMTP_PASSWORD', 'tu-app-password');
   define('FROM_EMAIL', 'tu-email@gmail.com');
   define('FROM_NAME', 'Control de Gastos');
   ```
   
   O copia el archivo de ejemplo:
   ```bash
   cp config/email_config.example.php config/email_config.php
   ```

5. **Instalar dependencias de Composer (opcional)**
   
   Si usas PHPMailer, asegúrate de tener Composer instalado:
   ```bash
   composer install
   ```
   
   Nota: Las dependencias ya están incluidas en el proyecto, pero puedes actualizarlas con este comando.

6. **Verificar permisos**
   
   Asegúrate de que el servidor web tenga permisos de lectura en todos los archivos.

7. **Acceder a la aplicación**
   
   Abre tu navegador y visita:
   ```
   http://localhost/App-Control-Gastos/
   ```

## 📁 Estructura del Proyecto

```
App-Control-Gastos/
├── config/
│   ├── config.php              # Configuración general y helpers
│   ├── Database.php            # Conexión a base de datos (PDO)
│   ├── Database.example.php    # Ejemplo de configuración
│   ├── Router.php              # Sistema de enrutamiento
│   ├── EmailService.php        # Servicio de envío de correos (PHPMailer)
│   └── email_config.example.php # Ejemplo de configuración de email
├── models/
│   ├── User.php                # Modelo de usuarios
│   ├── Transaction.php         # Modelo de transacciones
│   ├── FinancialProfile.php    # Modelo de perfil financiero
│   ├── Category.php            # Modelo de categorías
│   ├── Alert.php              # Modelo de alertas
│   └── MonthlyGoalProgress.php # Modelo de progreso mensual
├── controllers/
│   ├── AuthController.php      # Controlador de autenticación
│   ├── ProfileController.php   # Controlador de perfil
│   ├── TransactionController.php # Controlador de transacciones
│   ├── CategoryController.php  # Controlador de categorías
│   └── ReportController.php    # Controlador de reportes
├── views/
│   ├── login.php               # Vista de inicio de sesión
│   ├── register.php            # Vista de registro
│   ├── verify_email.php        # Vista de verificación de email
│   ├── forgot_password.php     # Vista de recuperación
│   ├── reset_password.php      # Vista de restablecimiento
│   ├── initial_setup.php       # Vista de configuración inicial
│   ├── dashboard.php           # Vista del dashboard
│   ├── profile.php             # Vista de perfil
│   ├── add_transaction.php     # Vista de nueva transacción
│   ├── transactions.php        # Vista de historial
│   ├── manage_categories.php   # Vista de gestión de categorías
│   └── reports.php             # Vista de reportes
├── includes/
│   ├── header.php              # Header común
│   ├── footer.php              # Footer común
│   └── navbar.php              # Barra de navegación
├── helpers/
│   └── GoalProgressHelper.php  # Helper para cálculo de progreso
├── migrations/
│   ├── README_MIGRATIONS.md    # Documentación de migraciones
│   └── *.sql                   # Scripts de migración
├── public/
│   ├── index.php               # Router principal
│   ├── .htaccess               # Configuración Apache
│   ├── css/                    # Estilos CSS (si los hay)
│   └── js/
│       ├── main.js             # JavaScript principal
│       └── form-validation.js  # Validación de formularios
├── vendor/                     # Dependencias de Composer (PHPMailer)
├── .htaccess                   # Redirección a public/
├── database.sql                # Script de base de datos inicial
├── database_migration_monthly_progress.sql # Migración de progreso mensual
├── README.md                   # Este archivo
├── CHANGELOG.md                # Historial de cambios
├── INSTALL.md                  # Guía de instalación detallada
├── USER_GUIDE.md               # Manual de usuario
├── SECURITY.md                 # Políticas de seguridad
├── PROJECT_STRUCTURE.md        # Estructura detallada del proyecto
└── LICENSE                     # Licencia MIT
```

## 🚀 Uso

### Primer Uso

1. **Registro de Usuario**
   - Accede a la página de registro
   - Completa todos los campos obligatorios:
     - Nombre completo
     - Email (se usará para verificación y recuperación)
     - Teléfono
     - Ocupación
     - Contraseña (mínimo 8 caracteres, mayúscula, número y carácter especial)
   - Verifica tu correo electrónico (si está configurado)

2. **Configuración Inicial**
   - Después del registro, completa tu perfil financiero (obligatorio)
   - Define tu ingreso mensual y moneda (MXN, USD, EUR)
   - Selecciona tus medios de pago (efectivo, tarjeta)
   - Establece tu objetivo financiero:
     - Ahorrar (con meta y fecha objetivo)
     - Pagar deudas (con monto total)
     - Controlar gastos
     - Otro (personalizable)
   - Configura tu límite de gasto mensual (automático o manual)

3. **Personalizar Categorías (Opcional)**
   - Ve a "Gestionar Categorías" en el menú
   - Crea categorías personalizadas para gastos o ingresos
   - Personaliza iconos y colores
   - Edita o elimina categorías existentes

4. **Comenzar a Usar**
   - Accede al dashboard para ver tu resumen financiero
   - Registra tu primera transacción
   - Explora los reportes y gráficos interactivos

### Funcionalidades Principales

#### Registrar un Gasto
1. Haz clic en "Registrar Gasto" o "Nueva Transacción"
2. Selecciona el tipo (Gasto o Ingreso)
3. Ingresa el monto
4. Selecciona la categoría
5. Elige el método de pago
6. Agrega una descripción (opcional)
7. Selecciona la fecha
8. Guarda la transacción

#### Ver Reportes
1. Accede a la sección "Reportes"
2. Selecciona el mes y año a visualizar
3. Explora los gráficos interactivos
4. Revisa el desglose por categorías
5. Verifica tu progreso hacia tus metas

#### Gestionar Categorías
1. Ve a "Gestionar Categorías" en el menú
2. Selecciona el tipo (Gastos o Ingresos)
3. Haz clic en "Nueva Categoría"
4. Ingresa el nombre
5. Selecciona un icono de Font Awesome
6. Elige un color de la paleta
7. Guarda la categoría
8. Puedes editar o eliminar categorías personalizadas

#### Exportar Datos
1. Ve a "Transacciones"
2. Aplica los filtros deseados (año, mes, tipo, categoría)
3. Haz clic en el botón de descarga (📥)
4. Se generará un archivo CSV con tus datos filtrados
5. Puedes abrirlo en Excel o Google Sheets

## 🔒 Seguridad

- ✅ Contraseñas encriptadas con bcrypt (cost factor 10)
- ✅ Validación de datos en servidor y cliente
- ✅ Protección contra SQL Injection (PDO con prepared statements)
- ✅ Protección XSS (sanitización de datos con `htmlspecialchars`)
- ✅ Tokens de recuperación con expiración (5 minutos)
- ✅ Tokens de verificación de email con expiración
- ✅ Sesiones seguras con invalidación al cerrar sesión
- ✅ Protección CSRF mediante sesiones
- ✅ Validación de permisos de usuario en todas las operaciones
- ✅ Foreign keys con cascada para integridad referencial
- ✅ Sanitización de todos los inputs del usuario
- ✅ Validación de tipos de datos
- ✅ Límites de longitud en campos de texto

## 🎯 Validaciones Implementadas

### Registro de Usuario
- Nombre completo requerido
- Email válido y único
- Teléfono requerido
- Ocupación requerida
- Contraseña mínimo 8 caracteres
- Al menos una mayúscula
- Al menos un número
- Al menos un carácter especial
- Confirmación de contraseña

### Transacciones
- Monto mayor a 0
- Categoría obligatoria (para gastos)
- Método de pago obligatorio (para gastos)
- Fecha válida (no futura)

### Perfil Financiero
- Ingreso mensual mayor a 0
- Al menos un medio de pago seleccionado
- Objetivo financiero obligatorio
- Límite de gasto mayor a 0

## 📱 Responsive Design

La aplicación está completamente optimizada para:
- 📱 Móviles (320px - 767px)
- 📱 Tablets (768px - 1023px)
- 💻 Desktop (1024px+)

## 🐛 Solución de Problemas

### Error de conexión a la base de datos
- Verifica las credenciales en `config/database.php`
- Asegúrate de que MySQL esté ejecutándose
- Verifica que la base de datos `control_gastos` exista

### Página en blanco
- Habilita `display_errors` en `config/config.php`
- Revisa los logs de error de Apache
- Verifica los permisos de archivos

### .htaccess no funciona
- Habilita mod_rewrite en Apache
- Verifica que AllowOverride esté en "All"
- Reinicia Apache

### Los gráficos no se muestran
- Verifica tu conexión a internet (Chart.js se carga desde CDN)
- Revisa la consola del navegador para errores JavaScript
- Asegúrate de que haya datos para mostrar

## 🔄 Actualizaciones Futuras

### v1.1.0 (Planificado)
- [ ] Exportación a PDF de reportes
- [ ] Notificaciones por email programadas
- [ ] Múltiples cuentas bancarias
- [ ] Presupuestos por categoría
- [ ] Modo oscuro
- [ ] Mejoras en gráficos interactivos

### v1.2.0 (Planificado)
- [ ] Aplicación móvil (PWA)
- [ ] Sincronización en la nube
- [ ] Recordatorios programados
- [ ] Reportes personalizados avanzados
- [ ] API REST para integraciones

### v2.0.0 (Futuro)
- [ ] Integración con bancos (Open Banking)
- [ ] Multi-usuario (gestión familiar)
- [ ] Machine Learning para predicciones
- [ ] App móvil nativa (iOS/Android)
- [ ] Análisis de tendencias avanzado

## 👨‍💻 Desarrollo

### Agregar una Nueva Categoría Predefinida

1. Edita el archivo `database.sql`
2. Agrega un nuevo registro en la tabla `categories` con `user_id = NULL`:
   ```sql
   INSERT INTO categories (user_id, name, type, icon, color) VALUES
   (NULL, 'Nueva Categoría', 'expense', 'fa-icon-name', '#HEXCOLOR');
   ```
3. Ejecuta la consulta en tu base de datos

### Agregar un Nuevo Método de Pago

1. Actualiza la tabla `financial_profiles` para incluir el nuevo método en el campo JSON `payment_methods`
2. Modifica las vistas correspondientes (`initial_setup.php`, `profile.php`, `add_transaction.php`)
3. Actualiza la lógica de validación en los controladores

### Sistema de Migraciones

El proyecto incluye un sistema de migraciones para actualizar la base de datos:

1. Revisa los archivos en `migrations/`
2. Ejecuta las migraciones en orden según `README_MIGRATIONS.md`
3. Las migraciones incluyen cambios como:
   - Actualización de iconos a Font Awesome
   - Renombrado de tablas
   - Nuevos campos (deudas, progreso mensual, etc.)

### Estructura de Código

- **Models**: Lógica de acceso a datos (PDO)
- **Controllers**: Lógica de negocio y validación
- **Views**: Presentación (HTML + PHP)
- **Config**: Configuración centralizada
- **Helpers**: Funciones auxiliares reutilizables

## 📄 Licencia

Este proyecto es de código abierto y está disponible bajo la licencia MIT.

## 🤝 Contribuciones

Las contribuciones son bienvenidas. Por favor:
1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

## 📞 Soporte

Si encuentras algún problema o tienes preguntas:
- Abre un issue en el repositorio
- Revisa la documentación
- Consulta la sección de solución de problemas

## 📚 Documentación Adicional

El proyecto incluye documentación detallada:

- **INSTALL.md**: Guía paso a paso de instalación
- **USER_GUIDE.md**: Manual completo de usuario
- **SECURITY.md**: Políticas y medidas de seguridad
- **PROJECT_STRUCTURE.md**: Estructura detallada del proyecto
- **CHANGELOG.md**: Historial de versiones y cambios
- **VALIDACIONES_FORMULARIO_INICIAL.md**: Validaciones del formulario de configuración inicial
- **VALIDACIONES_NEGOCIO_COHERENCIA_GLOBAL.md**: Validaciones de negocio y coherencia global del sistema
- **migrations/README_MIGRATIONS.md**: Guía de migraciones de base de datos

## ✨ Créditos

- **Desarrollado con**: PHP 7.4+, MySQL/MariaDB, Tailwind CSS, Chart.js 4.0
- **Email**: PHPMailer (Composer)
- **Iconos**: Font Awesome 6.4.0
- **Fuentes**: Inter (Google Fonts)
- **Gráficos**: Chart.js
- **Arquitectura**: MVC Pattern

## 📊 Estadísticas del Proyecto

- **Archivos PHP**: ~22 archivos
- **Modelos**: 6 modelos de datos
- **Controladores**: 5 controladores
- **Vistas**: 12 vistas
- **Líneas de código**: ~7,000+ líneas
- **Categorías predefinidas**: 15 gastos + 5 ingresos
- **Tablas de BD**: 6 tablas principales

---

**¡Gracias por usar Control de Gastos Personales!** 💰✨

Desarrollado con ❤️ para ayudarte a gestionar mejor tus finanzas personales.

**Versión actual**: 1.0.1  
**Última actualización**: Octubre 2025

