# Solución al Problema de Envío de Correos

## Problema Identificado

La aplicación se quedaba cargando al intentar enviar correos electrónicos debido a:

1. **Configuración incorrecta de encriptación SMTP**: Se usaba `STARTTLS` con el puerto 465, cuando el puerto 465 requiere `SSL/TLS`.
2. **Sin timeout configurado**: No había timeout para conexiones SMTP, causando que la página se quedara cargando indefinidamente.
3. **Falta de debugging**: No había logs suficientes para diagnosticar problemas.

## Cambios Realizados

### 1. `config/EmailService.php`

✅ **Corregido**: Cambiado de `ENCRYPTION_STARTTLS` a `ENCRYPTION_SMTPS` para puerto 465
✅ **Agregado**: Timeout de 10 segundos para evitar carga infinita
✅ **Agregado**: Mejor logging de errores
✅ **Agregado**: Limpieza de direcciones y adjuntos antes de cada envío

```php
$this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // SSL/TLS for port 465
$this->mailer->Timeout    = 10; // 10 seconds timeout
```

### 2. Creado `test_email.php`

Archivo de prueba para verificar la configuración SMTP.

## Instrucciones de Prueba

### Opción 1: Probar la Configuración SMTP

1. Abre tu navegador y ve a:
   ```
   http://localhost/App-Control-Gastos/test_email.php
   ```

2. Este script:
   - Mostrará tu configuración SMTP
   - Intentará conectarse al servidor
   - Enviará un correo de prueba
   - Mostrará logs detallados del proceso

3. **IMPORTANTE**: Elimina el archivo `test_email.php` después de la prueba por seguridad.

### Opción 2: Probar el Registro Normal

1. Ve a la página de registro
2. Registra un nuevo usuario
3. El correo debería enviarse en menos de 10 segundos
4. Si hay problemas, revisa los logs en:
   - Logs de Apache: `C:\xampp\apache\logs\error.log`
   - Logs de PHP: Verifica tu configuración de error_log

## Configuración SMTP Actual

- **Host**: mail.hazelhd.com
- **Puerto**: 465
- **Encriptación**: SSL/TLS (SMTPS)
- **Usuario**: no-reply@hazelhd.com

## Si Aún No Funciona

### Verificar Puerto y Encriptación

Si todavía tienes problemas, intenta cambiar la configuración:

#### Para usar puerto 587 (STARTTLS):

En `config/config.php`, cambia:
```php
define('SMTP_PORT', 587);
```

Y en `config/EmailService.php`, cambia:
```php
$this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
```

### Habilitar Debug Mode

Para ver más detalles de la conexión, en `config/EmailService.php` línea 31, cambia:
```php
$this->mailer->SMTPDebug  = 2; // 0 = off, 2 = detailed debug
```

**IMPORTANTE**: Desactiva el debug (vuelve a 0) en producción.

### Verificar Firewall

Asegúrate de que:
- El puerto 465 (o 587) no esté bloqueado por tu firewall
- Tu proveedor de internet no bloquee puertos SMTP
- El servidor SMTP permite conexiones desde tu IP

### Verificar Credenciales

- Confirma que el usuario y contraseña SMTP sean correctos
- Verifica que la cuenta de email tenga permisos para enviar correos
- Algunos proveedores requieren "contraseñas de aplicación" en lugar de la contraseña normal

## Alternativas

Si el servidor SMTP continúa fallando, considera:

1. **Usar Gmail SMTP**:
   ```php
   define('SMTP_HOST', 'smtp.gmail.com');
   define('SMTP_PORT', 587);
   define('SMTP_USERNAME', 'tu-email@gmail.com');
   define('SMTP_PASSWORD', 'tu-app-password');
   ```
   Nota: Gmail requiere "Contraseñas de aplicación" (App Passwords)

2. **Usar servicios de email transaccional**:
   - SendGrid
   - Mailgun
   - Amazon SES
   - Postmark

## Logs a Revisar

Los logs ahora incluyen:
- Intentos de conexión SMTP
- Correos enviados exitosamente
- Errores detallados con información del servidor

Revisa los logs de Apache/PHP para ver estos mensajes.

## Resumen de Comandos Útiles

```bash
# Ver logs de Apache en tiempo real
tail -f C:\xampp\apache\logs\error.log

# Probar conexión al servidor SMTP (desde terminal)
telnet mail.hazelhd.com 465
```

## Checklist de Solución

- [ ] Aplicar cambios en `config/EmailService.php`
- [ ] Ejecutar `test_email.php` para verificar conexión
- [ ] Revisar logs si hay errores
- [ ] Probar registro de nuevo usuario
- [ ] Verificar recepción de email
- [ ] Eliminar `test_email.php` por seguridad
- [ ] Desactivar debug mode en producción

---

**Fecha**: 28 de Octubre, 2025
**Archivos modificados**: 
- `config/EmailService.php`
- `test_email.php` (nuevo, eliminar después de prueba)

