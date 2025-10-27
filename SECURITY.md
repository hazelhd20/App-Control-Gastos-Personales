# 🔒 Política de Seguridad

## Versiones Soportadas

| Versión | Soportada          |
| ------- | ------------------ |
| 1.0.x   | :white_check_mark: |

## Reportar una Vulnerabilidad

Si descubres una vulnerabilidad de seguridad, por favor:

1. **NO** abras un issue público
2. Envía un correo a: security@example.com (configurar según necesidad)
3. Incluye:
   - Descripción detallada de la vulnerabilidad
   - Pasos para reproducirla
   - Impacto potencial
   - Versión afectada

Responderemos dentro de 48 horas.

## Medidas de Seguridad Implementadas

### 🔐 Autenticación

- **Contraseñas**:
  - Encriptación con bcrypt (cost factor: 10)
  - Validación de complejidad mínima
  - No se almacenan en texto plano
  - Hash único por contraseña (salt automático)

- **Sesiones**:
  - Sesiones PHP nativas con seguridad mejorada
  - Regeneración de ID de sesión al login
  - Timeout de inactividad
  - Invalidación completa al logout

- **Recuperación de Contraseña**:
  - Tokens criptográficamente seguros (random_bytes)
  - Expiración de 5 minutos
  - Un solo token válido por usuario
  - Tokens se invalidan después del uso

### 🛡️ Protección contra Ataques

#### SQL Injection
- **Prevención**:
  - PDO con prepared statements en todas las consultas
  - Parametrización de queries
  - No concatenación de SQL
  - Validación de tipos de datos

```php
// Ejemplo seguro
$stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
$stmt->bindParam(':email', $email);
```

#### XSS (Cross-Site Scripting)
- **Prevención**:
  - Sanitización de inputs con htmlspecialchars()
  - strip_tags() en entradas de usuario
  - Escape de salida en todas las vistas
  - Content Security Policy headers (recomendado para producción)

```php
// Ejemplo seguro
echo htmlspecialchars($user_input, ENT_QUOTES, 'UTF-8');
```

#### CSRF (Cross-Site Request Forgery)
- **Prevención actual**:
  - Verificación de sesión activa
  - Validación de origin
  
- **Recomendado agregar**:
  - Tokens CSRF en formularios
  - Verificación de tokens en POST requests

### 🔍 Validación de Datos

**Servidor (PHP)**:
- Validación exhaustiva de todos los inputs
- Tipo de datos correcto
- Longitud de strings
- Formato de emails
- Rangos numéricos
- Caracteres permitidos

**Cliente (JavaScript)**:
- Validación en tiempo real
- Feedback inmediato al usuario
- Prevención de envíos inválidos

## Configuración de Seguridad Recomendada

### Para Producción

#### 1. PHP Configuration (php.ini)

```ini
# Desactivar mostrar errores
display_errors = Off
display_startup_errors = Off

# Activar logging de errores
log_errors = On
error_log = /path/to/logs/php-errors.log

# Configuración de sesión
session.cookie_httponly = 1
session.cookie_secure = 1  # Solo HTTPS
session.use_strict_mode = 1
session.use_only_cookies = 1
session.cookie_samesite = "Strict"

# Límites de subida
upload_max_filesize = 2M
post_max_size = 2M

# Desactivar funciones peligrosas
disable_functions = exec,passthru,shell_exec,system,proc_open,popen
```

#### 2. Apache Configuration

**Headers de Seguridad (.htaccess adicional para producción)**:

```apache
# Prevenir clickjacking
Header always set X-Frame-Options "SAMEORIGIN"

# XSS Protection
Header always set X-XSS-Protection "1; mode=block"

# Content Type Options
Header always set X-Content-Type-Options "nosniff"

# HTTPS Strict Transport Security (solo con HTTPS)
Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"

# Content Security Policy
Header always set Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com; font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com; img-src 'self' data:;"

# Referrer Policy
Header always set Referrer-Policy "strict-origin-when-cross-origin"
```

#### 3. Database Security

```sql
-- Crear usuario específico para la aplicación
CREATE USER 'app_gastos'@'localhost' IDENTIFIED BY 'password_seguro_aqui';

-- Otorgar solo permisos necesarios
GRANT SELECT, INSERT, UPDATE, DELETE ON control_gastos.* TO 'app_gastos'@'localhost';

-- NO otorgar:
-- - DROP (eliminar tablas)
-- - CREATE (crear tablas)
-- - ALTER (modificar estructura)
-- - GRANT OPTION (dar permisos)

FLUSH PRIVILEGES;
```

#### 4. File Permissions (Linux/Unix)

```bash
# Archivos de configuración (solo lectura para web server)
chmod 640 config/*.php

# Directorios
chmod 755 views/ models/ controllers/ public/

# Archivos PHP
chmod 644 **/*.php

# Archivos sensibles
chmod 600 config/database.php
```

#### 5. Configuración de Aplicación

**config/config.php para producción**:

```php
// Desactivar errores visibles
error_reporting(0);
ini_set('display_errors', 0);

// Activar logging
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/logs/app-errors.log');

// Sesiones seguras
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);  // Solo con HTTPS
ini_set('session.use_strict_mode', 1);
```

### SSL/HTTPS

**Es CRÍTICO usar HTTPS en producción**:

1. Obtén un certificado SSL (Let's Encrypt es gratis)
2. Configura tu servidor web para usar HTTPS
3. Redirige todo el tráfico HTTP a HTTPS
4. Actualiza BASE_URL a https://

```apache
# Forzar HTTPS (.htaccess)
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

## Checklist de Seguridad Pre-Producción

- [ ] Cambiar credenciales de base de datos
- [ ] Usar usuario de BD con permisos limitados
- [ ] Desactivar display_errors
- [ ] Activar HTTPS
- [ ] Configurar headers de seguridad
- [ ] Implementar rate limiting (login, API)
- [ ] Configurar logs de error
- [ ] Revisar permisos de archivos
- [ ] Cambiar secretos y salts
- [ ] Implementar backup automático
- [ ] Configurar firewall
- [ ] Actualizar todas las dependencias
- [ ] Realizar prueba de penetración
- [ ] Configurar monitoreo de seguridad

## Mejores Prácticas

### Para Desarrolladores

1. **Nunca confíes en datos del cliente**
   - Siempre valida en servidor
   - Sanitiza todos los inputs
   - Verifica tipos de datos

2. **Usa prepared statements**
   - Para TODAS las consultas
   - Incluso las "seguras"
   - Nunca concatenar SQL

3. **Escapa salidas**
   - En todas las vistas
   - Según el contexto (HTML, JS, URL)
   - Usa funciones apropiadas

4. **Maneja errores correctamente**
   - Log errores, no los muestres
   - Mensajes genéricos al usuario
   - Detalles en logs

5. **Mantén secretos seguros**
   - No commits de credenciales
   - Usa variables de entorno
   - .gitignore apropiado

### Para Usuarios

1. **Contraseñas seguras**
   - Mínimo 12 caracteres
   - Mix de mayúsculas, minúsculas, números, símbolos
   - Únicas para cada servicio
   - Usa un gestor de contraseñas

2. **Actualiza regularmente**
   - Mantén el sistema actualizado
   - Revisa changelog
   - Aplica parches de seguridad

3. **Backups frecuentes**
   - Exporta datos regularmente
   - Guarda en lugar seguro
   - Prueba la restauración

4. **Cierra sesión**
   - Especialmente en dispositivos compartidos
   - Después de usar
   - Si detectas actividad sospechosa

## Auditoría de Seguridad

### Última Auditoría
- **Fecha**: 2025-10-27
- **Versión**: 1.0.0
- **Estado**: Aprobado para uso local/desarrollo

### Próxima Auditoría Programada
- **Fecha**: Antes de producción
- **Scope**: Completo con pruebas de penetración

## Respuesta a Incidentes

En caso de brecha de seguridad:

1. **Contención**
   - Tomar sistema offline si es necesario
   - Cambiar todas las credenciales
   - Revisar logs

2. **Investigación**
   - Identificar el vector de ataque
   - Determinar datos afectados
   - Documentar hallazgos

3. **Remediación**
   - Aplicar parches
   - Actualizar código
   - Fortalecer medidas

4. **Notificación**
   - Informar a usuarios afectados
   - Transparencia sobre el incidente
   - Pasos tomados

5. **Prevención**
   - Lecciones aprendidas
   - Actualizar procedimientos
   - Capacitación de equipo

## Recursos Adicionales

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [PHP Security Guide](https://www.php.net/manual/en/security.php)
- [MySQL Security](https://dev.mysql.com/doc/refman/8.0/en/security.html)

## Contacto

Para reportes de seguridad: security@example.com

---

**Última actualización**: Octubre 2025
**Versión del documento**: 1.0

