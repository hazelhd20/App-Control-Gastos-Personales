<?php
/**
 * Email Service using PHPMailer
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailService {
    private $mailer;

    public function __construct() {
        $this->mailer = new PHPMailer(true);
        
        try {
            // Server settings
            $this->mailer->isSMTP();
            $this->mailer->Host       = SMTP_HOST;
            $this->mailer->SMTPAuth   = true;
            $this->mailer->Username   = SMTP_USERNAME;
            $this->mailer->Password   = SMTP_PASSWORD;
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // SSL/TLS for port 465
            $this->mailer->Port       = SMTP_PORT;
            $this->mailer->CharSet    = 'UTF-8';
            
            // Timeouts to prevent hanging
            $this->mailer->Timeout    = 10; // 10 seconds timeout
            $this->mailer->SMTPKeepAlive = false;
            
            // Debug mode (disable in production)
            $this->mailer->SMTPDebug  = 0; // 0 = off, 2 = detailed debug
            
            // From
            $this->mailer->setFrom(FROM_EMAIL, FROM_NAME);
        } catch (Exception $e) {
            error_log("EmailService error: {$e->getMessage()}");
        }
    }

    /**
     * Send verification email
     */
    public function sendVerificationEmail($to_email, $to_name, $verification_token) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();
            
            $verification_link = BASE_URL . 'public/index.php?page=verify-email&token=' . $verification_token;
            
            $this->mailer->addAddress($to_email, $to_name);
            $this->mailer->Subject = 'Verifica tu correo electrónico - Control de Gastos';
            
            $this->mailer->isHTML(true);
            $this->mailer->Body = $this->getVerificationEmailTemplate($to_name, $verification_link);
            $this->mailer->AltBody = "Hola $to_name,\n\nGracias por registrarte en Control de Gastos.\n\nPor favor verifica tu correo electrónico haciendo clic en el siguiente enlace:\n$verification_link\n\nEste enlace es válido por 24 horas.\n\nSi no creaste esta cuenta, puedes ignorar este correo.";
            
            $result = $this->mailer->send();
            if ($result) {
                error_log("Verification email sent successfully to: $to_email");
            }
            return $result;
        } catch (Exception $e) {
            error_log("Error sending verification email to $to_email: {$this->mailer->ErrorInfo}");
            error_log("Exception: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send password reset email
     */
    public function sendPasswordResetEmail($to_email, $to_name, $reset_token) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();
            
            $reset_link = BASE_URL . 'public/index.php?page=reset-password&token=' . $reset_token;
            
            $this->mailer->addAddress($to_email, $to_name);
            $this->mailer->Subject = 'Recuperación de contraseña - Control de Gastos';
            
            $this->mailer->isHTML(true);
            $this->mailer->Body = $this->getPasswordResetEmailTemplate($to_name, $reset_link);
            $this->mailer->AltBody = "Hola $to_name,\n\nRecibimos una solicitud para restablecer tu contraseña.\n\nHaz clic en el siguiente enlace para crear una nueva contraseña:\n$reset_link\n\nEste enlace es válido por 5 minutos.\n\nSi no solicitaste restablecer tu contraseña, puedes ignorar este correo.";
            
            $result = $this->mailer->send();
            if ($result) {
                error_log("Password reset email sent successfully to: $to_email");
            }
            return $result;
        } catch (Exception $e) {
            error_log("Error sending password reset email to $to_email: {$this->mailer->ErrorInfo}");
            error_log("Exception: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get verification email HTML template
     */
    private function getVerificationEmailTemplate($name, $link) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f4f4f4; margin: 0; padding: 0; }
                .container { max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; }
                .header h1 { margin: 0; font-size: 28px; }
                .content { padding: 40px 30px; }
                .content h2 { color: #667eea; margin-top: 0; }
                .button { display: inline-block; padding: 15px 30px; background: #667eea; color: white !important; text-decoration: none; border-radius: 5px; font-weight: bold; margin: 20px 0; }
                .button:hover { background: #5568d3; }
                .footer { background: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #666; }
                .warning { background: #fff3cd; border-left: 4px solid #ffc107; padding: 10px; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1><i class='fas fa-bullseye' style='font-size: 28px; vertical-align: middle; margin-right: 10px;'></i>Control de Gastos</h1>
                </div>
                <div class='content'>
                    <h2>¡Bienvenido, $name!</h2>
                    <p>Gracias por registrarte en Control de Gastos. Estamos emocionados de ayudarte a gestionar tus finanzas personales.</p>
                    <p>Para completar tu registro y comenzar a usar la aplicación, por favor verifica tu correo electrónico haciendo clic en el botón de abajo:</p>
                    <center>
                        <a href='$link' class='button'>Verificar Correo Electrónico</a>
                    </center>
                    <div class='warning'>
                        <strong><i class='fas fa-clock'></i> Importante:</strong> Este enlace es válido por 24 horas.
                    </div>
                    <p>Si no creaste esta cuenta, puedes ignorar este correo de forma segura.</p>
                </div>
                <div class='footer'>
                    <p>Este es un correo automático, por favor no respondas a este mensaje.</p>
                    <p>&copy; 2025 Control de Gastos. Todos los derechos reservados.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }

    /**
     * Get password reset email HTML template
     */
    private function getPasswordResetEmailTemplate($name, $link) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f4f4f4; margin: 0; padding: 0; }
                .container { max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; }
                .header h1 { margin: 0; font-size: 28px; }
                .content { padding: 40px 30px; }
                .content h2 { color: #667eea; margin-top: 0; }
                .button { display: inline-block; padding: 15px 30px; background: #667eea; color: white !important; text-decoration: none; border-radius: 5px; font-weight: bold; margin: 20px 0; }
                .button:hover { background: #5568d3; }
                .footer { background: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #666; }
                .warning { background: #fff3cd; border-left: 4px solid #ffc107; padding: 10px; margin: 20px 0; }
                .alert { background: #f8d7da; border-left: 4px solid #dc3545; padding: 10px; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1><i class='fas fa-lock' style='font-size: 28px; vertical-align: middle; margin-right: 10px;'></i>Control de Gastos</h1>
                </div>
                <div class='content'>
                    <h2>Recuperación de Contraseña</h2>
                    <p>Hola, $name</p>
                    <p>Recibimos una solicitud para restablecer la contraseña de tu cuenta en Control de Gastos.</p>
                    <p>Para crear una nueva contraseña, haz clic en el siguiente botón:</p>
                    <center>
                        <a href='$link' class='button'>Restablecer Contraseña</a>
                    </center>
                    <div class='warning'>
                        <strong><i class='fas fa-clock'></i> Importante:</strong> Este enlace es válido por 5 minutos por seguridad.
                    </div>
                    <div class='alert'>
                        <strong><i class='fas fa-shield-alt'></i> Seguridad:</strong> Si no solicitaste restablecer tu contraseña, ignora este correo. Tu cuenta está segura.
                    </div>
                </div>
                <div class='footer'>
                    <p>Este es un correo automático, por favor no respondas a este mensaje.</p>
                    <p>&copy; 2025 Control de Gastos. Todos los derechos reservados.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
}

