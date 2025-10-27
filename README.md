# 💰 Sistema de Control de Gastos Personales

Sistema completo de gestión financiera personal desarrollado en PHP con arquitectura MVC, diseñado con Tailwind CSS para proporcionar una interfaz moderna y responsiva.

## 📋 Características Principales

### 🔐 Autenticación y Seguridad
- ✅ Registro de usuarios con validación robusta
- ✅ Inicio de sesión seguro con contraseñas encriptadas
- ✅ Recuperación de contraseña por correo electrónico
- ✅ Tokens de recuperación con expiración de 5 minutos
- ✅ Sesiones seguras y cierre de sesión

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
- ✅ Categorías predefinidas con iconos
- ✅ Múltiples métodos de pago
- ✅ Descripción opcional para cada transacción
- ✅ Historial completo de transacciones
- ✅ Filtrado por fecha, categoría y tipo
- ✅ Exportación a CSV/Excel

### 📊 Dashboard y Reportes
- ✅ Resumen financiero mensual
- ✅ Gráficos interactivos (Chart.js):
  - Gastos por categoría (gráfico de dona)
  - Distribución por método de pago (gráfico de pastel)
  - Comparación mensual (gráfico de líneas)
  - Desglose detallado por categoría
- ✅ Visualización de progreso hacia metas
- ✅ Estadísticas en tiempo real

### 🔔 Alertas y Notificaciones
- ✅ Alerta visual y sonora al exceder límite de gasto
- ✅ Advertencia al alcanzar 80% del límite
- ✅ Seguimiento de metas de ahorro
- ✅ Recordatorios de deudas pendientes
- ✅ Notificaciones de inactividad

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
- **Base de datos**: MySQL 5.7+ / MariaDB
- **Frontend**: HTML5, CSS3 (Tailwind), JavaScript ES6+
- **Arquitectura**: MVC (Model-View-Controller)
- **Gráficos**: Chart.js 4.0
- **Servidor**: Apache con mod_rewrite

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
   
   Edita el archivo `config/database.php` con tus credenciales:
   ```php
   private $host = 'localhost';
   private $db_name = 'control_gastos';
   private $username = 'root';
   private $password = ''; // Tu contraseña de MySQL
   ```

4. **Configurar la URL base**
   
   Edita el archivo `config/config.php`:
   ```php
   define('BASE_URL', 'http://localhost/App-Control-Gastos/');
   ```

5. **Configurar correo electrónico (opcional)**
   
   Para habilitar la recuperación de contraseña por correo, edita `config/config.php`:
   ```php
   define('SMTP_HOST', 'smtp.gmail.com');
   define('SMTP_PORT', 587);
   define('SMTP_USERNAME', 'tu-email@gmail.com');
   define('SMTP_PASSWORD', 'tu-app-password');
   ```

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
│   ├── config.php           # Configuración general
│   └── database.php         # Conexión a base de datos
├── models/
│   ├── User.php            # Modelo de usuarios
│   ├── Transaction.php     # Modelo de transacciones
│   ├── FinancialProfile.php # Modelo de perfil financiero
│   └── Alert.php           # Modelo de alertas
├── controllers/
│   ├── AuthController.php     # Controlador de autenticación
│   ├── ProfileController.php  # Controlador de perfil
│   ├── TransactionController.php # Controlador de transacciones
│   └── ReportController.php   # Controlador de reportes
├── views/
│   ├── login.php           # Vista de inicio de sesión
│   ├── register.php        # Vista de registro
│   ├── forgot_password.php # Vista de recuperación
│   ├── reset_password.php  # Vista de restablecimiento
│   ├── initial_setup.php   # Vista de configuración inicial
│   ├── dashboard.php       # Vista del dashboard
│   ├── profile.php         # Vista de perfil
│   ├── add_transaction.php # Vista de nueva transacción
│   ├── transactions.php    # Vista de historial
│   └── reports.php         # Vista de reportes
├── includes/
│   ├── header.php          # Header común
│   ├── footer.php          # Footer común
│   └── navbar.php          # Barra de navegación
├── public/
│   ├── index.php           # Router principal
│   ├── .htaccess           # Configuración Apache
│   └── js/
│       └── main.js         # JavaScript principal
├── .htaccess               # Redirección a public/
├── database.sql            # Script de base de datos
└── README.md               # Este archivo
```

## 🚀 Uso

### Primer Uso

1. **Registro de Usuario**
   - Accede a la página de registro
   - Completa todos los campos obligatorios
   - La contraseña debe cumplir los requisitos de seguridad

2. **Configuración Inicial**
   - Después del registro, completa tu perfil financiero
   - Define tu ingreso mensual y moneda
   - Selecciona tus medios de pago
   - Establece tu objetivo financiero
   - Configura tu límite de gasto mensual

3. **Comenzar a Usar**
   - Accede al dashboard para ver tu resumen
   - Registra tu primera transacción
   - Explora los reportes y gráficos

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

#### Exportar Datos
1. Ve a "Transacciones"
2. Aplica los filtros deseados
3. Haz clic en el botón de descarga
4. Se generará un archivo CSV con tus datos

## 🔒 Seguridad

- Contraseñas encriptadas con bcrypt
- Validación de datos en servidor y cliente
- Protección contra SQL Injection (PDO preparado)
- Protección XSS (sanitización de datos)
- Tokens de recuperación con expiración
- Sesiones seguras con invalidación

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

- [ ] Exportación a PDF
- [ ] Notificaciones por email
- [ ] Múltiples cuentas bancarias
- [ ] Presupuestos por categoría
- [ ] Modo oscuro
- [ ] Aplicación móvil (PWA)
- [ ] Integración con bancos
- [ ] Recordatorios programados
- [ ] Reportes personalizados
- [ ] Multi-usuario (familia)

## 👨‍💻 Desarrollo

### Agregar una Nueva Categoría

1. Edita el archivo `database.sql`
2. Agrega un nuevo registro en la tabla `expense_categories`
3. Ejecuta la consulta en tu base de datos

### Agregar un Nuevo Método de Pago

1. Actualiza la tabla `financial_profiles` para incluir el nuevo método
2. Modifica las vistas correspondientes
3. Actualiza la lógica de validación

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

## ✨ Créditos

- **Desarrollado con**: PHP, MySQL, Tailwind CSS, Chart.js
- **Iconos**: Font Awesome
- **Fuentes**: Inter (Google Fonts)

---

**¡Gracias por usar Control de Gastos Personales!** 💰✨

Desarrollado con ❤️ para ayudarte a gestionar mejor tus finanzas personales.

