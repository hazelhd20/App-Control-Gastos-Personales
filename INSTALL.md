# 🚀 Guía Rápida de Instalación

## Instalación en XAMPP (Windows)

### Paso 1: Preparar los archivos
```
1. Abre XAMPP Control Panel
2. Asegúrate de que Apache y MySQL estén iniciados
3. Copia la carpeta del proyecto a: C:\xampp\htdocs\App-Control-Gastos
```

### Paso 2: Crear la base de datos
```
1. Abre tu navegador
2. Ve a: http://localhost/phpmyadmin
3. Haz clic en "Nuevo" para crear una base de datos
4. Nombre: control_gastos
5. Cotejamiento: utf8mb4_unicode_ci
6. Haz clic en "Crear"
7. Selecciona la base de datos "control_gastos"
8. Haz clic en la pestaña "Importar"
9. Selecciona el archivo "database.sql" del proyecto
10. Haz clic en "Continuar"
```

### Paso 3: Configurar la aplicación
```
1. Abre el archivo: config/database.php
2. Verifica que los datos sean correctos:
   - host: localhost
   - db_name: control_gastos
   - username: root
   - password: (déjalo vacío si no cambiaste la contraseña)
```

### Paso 4: Acceder a la aplicación
```
1. Abre tu navegador
2. Ve a: http://localhost/App-Control-Gastos/
3. ¡Listo! Deberías ver la pantalla de inicio de sesión
```

## Primera Configuración

### 1. Crear tu primera cuenta
```
- Haz clic en "Regístrate aquí"
- Completa todos los campos
- La contraseña debe tener:
  * Mínimo 8 caracteres
  * Una letra mayúscula
  * Un número
  * Un carácter especial (@, #, $, %, etc.)
```

### 2. Configuración inicial del perfil
```
Después del registro, configura:
- Tu ingreso mensual
- La moneda que usas (MXN, USD, EUR)
- Fecha de inicio
- Medios de pago (efectivo, tarjeta, o ambos)
- Tu objetivo financiero principal
- Límite de gasto mensual (automático o manual)
```

### 3. Comenzar a usar
```
- Dashboard: Ver resumen de tus finanzas
- Nueva Transacción: Registrar gastos e ingresos
- Transacciones: Ver historial completo
- Reportes: Gráficos y estadísticas
- Perfil: Editar tu información
```

## Solución de Problemas Comunes

### Error: "Connection Error"
```
Solución:
1. Verifica que MySQL esté ejecutándose en XAMPP
2. Revisa que la base de datos "control_gastos" exista
3. Verifica las credenciales en config/database.php
```

### Error: Página en blanco
```
Solución:
1. Verifica que Apache esté ejecutándose
2. Revisa la ruta del proyecto
3. Verifica que los archivos .htaccess existan
4. Habilita mod_rewrite en Apache
```

### Error: "Page not found" en las rutas
```
Solución:
1. Verifica que mod_rewrite esté habilitado en Apache
2. En XAMPP, edita: C:\xampp\apache\conf\httpd.conf
3. Busca: #LoadModule rewrite_module modules/mod_rewrite.so
4. Quita el # al inicio de la línea
5. Busca: AllowOverride None
6. Cámbialo a: AllowOverride All
7. Reinicia Apache
```

### Los estilos no se cargan
```
Solución:
1. Verifica tu conexión a internet (Tailwind se carga desde CDN)
2. Limpia la caché del navegador (Ctrl + F5)
3. Verifica que la ruta BASE_URL en config/config.php sea correcta
```

### Los gráficos no aparecen
```
Solución:
1. Verifica tu conexión a internet (Chart.js se carga desde CDN)
2. Abre la consola del navegador (F12) y busca errores
3. Asegúrate de tener transacciones registradas
```

## Verificación de la Instalación

✅ **Checklist de verificación:**

- [ ] Apache está ejecutándose
- [ ] MySQL está ejecutándose
- [ ] Base de datos "control_gastos" existe
- [ ] Tablas de la base de datos están creadas
- [ ] Puedes acceder a http://localhost/App-Control-Gastos/
- [ ] Ves la pantalla de inicio de sesión
- [ ] Puedes registrarte
- [ ] Después del registro, te lleva a configuración inicial
- [ ] Puedes completar la configuración inicial
- [ ] Accedes al dashboard
- [ ] Los estilos se ven correctamente
- [ ] Los gráficos se muestran

## Usuarios de Prueba

Después de instalar, puedes crear usuarios de prueba con estos datos:

```
Usuario 1:
- Nombre: Juan Pérez
- Email: juan@example.com
- Teléfono: 555-123-4567
- Ocupación: Ingeniero
- Contraseña: Test@123

Usuario 2:
- Nombre: María García
- Email: maria@example.com
- Teléfono: 555-987-6543
- Ocupación: Diseñadora
- Contraseña: Test@456
```

## Configuración Avanzada

### Cambiar el puerto de Apache
```
Si el puerto 80 está ocupado:
1. Abre XAMPP Control Panel
2. Haz clic en "Config" junto a Apache
3. Selecciona "httpd.conf"
4. Busca: Listen 80
5. Cámbialo a otro puerto, ej: Listen 8080
6. Guarda y reinicia Apache
7. Accede a: http://localhost:8080/App-Control-Gastos/
```

### Habilitar correos (opcional)
```
Para recuperación de contraseña:
1. Abre: config/config.php
2. Configura tus credenciales SMTP:
   - SMTP_HOST: smtp.gmail.com
   - SMTP_PORT: 587
   - SMTP_USERNAME: tu-email@gmail.com
   - SMTP_PASSWORD: tu-app-password
3. Si usas Gmail, genera una contraseña de aplicación
```

## Datos de Ejemplo

Para probar la aplicación con datos de ejemplo, puedes usar estas categorías:

- 🍔 Alimentación
- 🚗 Transporte
- 🎮 Entretenimiento
- 🏠 Vivienda
- 💊 Salud
- 📚 Educación
- 👔 Ropa
- 💡 Servicios
- 📦 Otros

## Respaldo de Datos

### Exportar datos
```
1. Abre phpMyAdmin
2. Selecciona la base de datos "control_gastos"
3. Haz clic en "Exportar"
4. Selecciona "Rápido" o "Personalizado"
5. Formato: SQL
6. Haz clic en "Continuar"
7. Guarda el archivo .sql
```

### Importar respaldo
```
1. Abre phpMyAdmin
2. Selecciona la base de datos "control_gastos"
3. Haz clic en "Importar"
4. Selecciona tu archivo de respaldo .sql
5. Haz clic en "Continuar"
```

## Contacto y Soporte

Si tienes problemas con la instalación:
1. Revisa esta guía completa
2. Consulta el README.md para más información
3. Verifica los logs de error de Apache y PHP
4. Busca el error específico en Google

## ¡Listo para Usar! 🎉

Una vez completados todos los pasos, tu aplicación estará lista para:
- 💰 Controlar tus gastos diarios
- 📊 Ver reportes detallados
- 🎯 Alcanzar tus metas financieras
- 💳 Gestionar múltiples métodos de pago
- 📈 Analizar tus patrones de consumo

**¡Disfruta de tu nuevo sistema de control de gastos!**

