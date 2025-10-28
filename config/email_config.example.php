<?php
/**
 * Configuración de correo electrónico - EJEMPLO
 * 
 * Copia este archivo como parte de la configuración inicial
 * Las constantes de configuración están en config.php
 */

/*
 * CONFIGURACIÓN PARA GMAIL:
 * 
 * 1. Habilita la verificación en 2 pasos en tu cuenta de Google
 * 2. Ve a https://myaccount.google.com/apppasswords
 * 3. Crea una "Contraseña de aplicación"
 * 4. Usa esa contraseña en SMTP_PASSWORD (no tu contraseña de Gmail)
 * 
 * En config.php debe verse así:
 * 
 * define('SMTP_HOST', 'smtp.gmail.com');
 * define('SMTP_PORT', 587);
 * define('SMTP_USERNAME', 'tuusuario@gmail.com');
 * define('SMTP_PASSWORD', 'tu-contraseña-de-aplicacion-de-16-digitos');
 * define('FROM_EMAIL', 'noreply@tudominio.com');
 * define('FROM_NAME', 'Control de Gastos');
 * 
 * 
 * CONFIGURACIÓN PARA OTROS PROVEEDORES:
 * 
 * Outlook/Hotmail:
 * - SMTP_HOST: 'smtp-mail.outlook.com'
 * - SMTP_PORT: 587
 * 
 * Yahoo:
 * - SMTP_HOST: 'smtp.mail.yahoo.com'
 * - SMTP_PORT: 465 o 587
 * 
 * SMTP Personalizado:
 * - Consulta con tu proveedor de hosting los datos de configuración SMTP
 */

