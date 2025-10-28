-- Script para actualizar la base de datos existente
-- Ejecuta este script si ya tienes la base de datos creada

USE control_gastos;

-- Agregar columnas de verificación de email a la tabla users
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS email_verified BOOLEAN DEFAULT FALSE AFTER password,
ADD COLUMN IF NOT EXISTS verification_token VARCHAR(64) NULL AFTER email_verified,
ADD COLUMN IF NOT EXISTS verification_token_expiry DATETIME NULL AFTER verification_token;

-- Para usuarios existentes, marcar sus emails como verificados
-- (opcional - comenta estas líneas si quieres que los usuarios existentes también verifiquen su email)
UPDATE users SET email_verified = TRUE WHERE email_verified = FALSE;

-- Verificar los cambios
DESCRIBE users;

