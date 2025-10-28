# Validación: No Reutilizar Contraseña

## Descripción

Se ha implementado una validación de seguridad que **impide que los usuarios utilicen la misma contraseña** al restablecer o cambiar su contraseña. Esta es una buena práctica de seguridad que garantiza que cuando un usuario cambia su contraseña, realmente esté usando una nueva.

## Funcionalidades Implementadas

### 1. Validación en Restablecimiento de Contraseña (Olvidé mi contraseña)

Cuando un usuario usa el enlace de recuperación de contraseña:
- ✅ Se verifica que la nueva contraseña sea diferente a la contraseña actual
- ✅ Si intenta usar la misma contraseña, se muestra el error: **"La nueva contraseña debe ser diferente a la contraseña anterior"**
- ✅ El token de recuperación sigue siendo válido después del error (puede intentar con otra contraseña)

### 2. Validación en Cambio de Contraseña desde el Perfil (NUEVO)

Se ha agregado una sección completa en el perfil para cambiar la contraseña:
- ✅ Requiere la contraseña actual para mayor seguridad
- ✅ Valida que la contraseña actual sea correcta
- ✅ Verifica que la nueva contraseña sea diferente a la contraseña actual
- ✅ Aplica todas las validaciones de fortaleza de contraseña (8+ caracteres, mayúsculas, números, caracteres especiales)

## Archivos Modificados

### 1. `models/User.php`

**Método `resetPassword`:**
```php
// Ahora verifica que la nueva contraseña sea diferente a la actual
if (password_verify($new_password, $current['password'])) {
    return 'same_password'; // Código especial de error
}
```

**Método `updatePassword` (mejorado):**
```php
// Nuevo parámetro opcional para verificar contraseña actual
public function updatePassword($user_id, $new_password, $current_password = null)

// Valida contraseña actual si se proporciona
if ($current_password !== null && !password_verify($current_password, $current['password'])) {
    return 'wrong_current_password';
}

// Valida que la nueva sea diferente
if (password_verify($new_password, $current['password'])) {
    return 'same_password';
}
```

### 2. `controllers/AuthController.php`

**Método `resetPassword`:**
```php
$result = $this->user->resetPassword($token, $password);

if ($result === true) {
    // Éxito
} elseif ($result === 'same_password') {
    $errors[] = "La nueva contraseña debe ser diferente a la contraseña anterior";
} else {
    // Token inválido/expirado
}
```

### 3. `controllers/ProfileController.php` (NUEVO)

**Método `changePassword` (nuevo):**
- Valida contraseña actual
- Valida fortaleza de la nueva contraseña
- Verifica que sean diferentes
- Maneja todos los casos de error

### 4. `public/index.php`

Agregada nueva ruta:
```php
case 'change-password':
    $controller = new ProfileController();
    $controller->changePassword();
    break;
```

### 5. `views/profile.php`

Agregada sección completa de "Cambiar Contraseña":
- Formulario con contraseña actual, nueva y confirmación
- Validaciones en frontend
- Mensajes de error específicos
- Advertencia visual sobre la política de contraseñas diferentes

## Flujos de Usuario

### Flujo 1: Restablecer Contraseña (Olvidé mi contraseña)

1. Usuario solicita recuperación de contraseña
2. Recibe email con enlace
3. Ingresa nueva contraseña
4. **Sistema valida que sea diferente a la actual**
5. Si es la misma → Error y permanece en la página
6. Si es diferente → Éxito y redirige a login

### Flujo 2: Cambiar Contraseña desde Perfil

1. Usuario va a "Mi Perfil"
2. Busca sección "Cambiar Contraseña"
3. Ingresa:
   - Contraseña actual
   - Nueva contraseña
   - Confirmación
4. **Sistema valida:**
   - Contraseña actual sea correcta
   - Nueva contraseña cumpla requisitos de fortaleza
   - Nueva contraseña sea diferente a la actual
5. Muestra mensaje de éxito o errores específicos

## Mensajes de Error

| Escenario | Mensaje |
|-----------|---------|
| Contraseña actual incorrecta | "La contraseña actual es incorrecta" |
| Nueva contraseña igual a la actual | "La nueva contraseña debe ser diferente a la contraseña actual" |
| Contraseña muy corta | "La contraseña debe tener al menos 8 caracteres" |
| Sin mayúsculas | "La contraseña debe contener al menos una letra mayúscula" |
| Sin números | "La contraseña debe contener al menos un número" |
| Sin caracteres especiales | "La contraseña debe contener al menos un carácter especial" |
| Contraseñas no coinciden | "Las contraseñas no coinciden" |

## Requisitos de Contraseña

Todas las contraseñas deben cumplir:
- ✅ Mínimo 8 caracteres
- ✅ Al menos una letra mayúscula
- ✅ Al menos un número
- ✅ Al menos un carácter especial (@, #, $, %, etc.)
- ✅ **Debe ser diferente a la contraseña actual**

## Seguridad

### Técnicas Implementadas:

1. **Password Hashing**: Se usa `password_hash()` con BCRYPT
2. **Password Verification**: Se usa `password_verify()` para comparar
3. **Validación doble**: Tanto en restablecimiento como en cambio de contraseña
4. **Sin almacenar contraseñas en texto plano**: Nunca se guardan contraseñas sin hash
5. **Mensajes específicos**: El usuario sabe exactamente qué debe cambiar

## Pruebas Recomendadas

### Caso de Prueba 1: Restablecer con misma contraseña
1. Crear cuenta con contraseña: `MiPass123!`
2. Usar "Olvidé mi contraseña"
3. Intentar restablecer con: `MiPass123!`
4. ✅ Debe mostrar error

### Caso de Prueba 2: Restablecer con contraseña diferente
1. Crear cuenta con contraseña: `MiPass123!`
2. Usar "Olvidé mi contraseña"
3. Restablecer con: `NuevaPass456!`
4. ✅ Debe permitir el cambio

### Caso de Prueba 3: Cambiar desde perfil con misma contraseña
1. Iniciar sesión
2. Ir a "Mi Perfil"
3. Ingresar contraseña actual correcta
4. Intentar cambiar a la misma contraseña
5. ✅ Debe mostrar error

### Caso de Prueba 4: Cambiar desde perfil con contraseña incorrecta
1. Iniciar sesión
2. Ir a "Mi Perfil"
3. Ingresar contraseña actual incorrecta
4. ✅ Debe mostrar error: "La contraseña actual es incorrecta"

### Caso de Prueba 5: Cambio exitoso desde perfil
1. Iniciar sesión
2. Ir a "Mi Perfil"
3. Ingresar contraseña actual correcta
4. Ingresar nueva contraseña válida y diferente
5. ✅ Debe permitir el cambio y mostrar éxito

## Acceso a la Funcionalidad

### Restablecer Contraseña:
```
http://localhost/App-Control-Gastos/public/index.php?page=forgot-password
```

### Cambiar Contraseña:
1. Iniciar sesión
2. Ir a "Mi Perfil" en el menú
3. Desplazarse hasta la sección "Cambiar Contraseña"

## Código de Retorno

Los métodos `resetPassword` y `updatePassword` ahora retornan:

| Valor | Significado |
|-------|-------------|
| `true` | Contraseña actualizada exitosamente |
| `false` | Error general o token inválido |
| `'same_password'` | La nueva contraseña es igual a la actual |
| `'wrong_current_password'` | La contraseña actual proporcionada es incorrecta |

## Beneficios de Seguridad

1. ✅ **Previene reutilización de contraseñas comprometidas**: Si una contraseña se vio comprometida, el usuario está forzado a usar una nueva
2. ✅ **Cumple estándares de seguridad**: Muchas políticas corporativas requieren que las contraseñas cambien realmente
3. ✅ **Evita el "cambio falso"**: Usuarios que cambian y vuelven a la misma contraseña inmediatamente
4. ✅ **Mejora la postura de seguridad**: Fomenta el uso de contraseñas únicas en diferentes momentos

## Consideraciones Futuras

Para mejorar aún más la seguridad, se podría implementar:

- [ ] Historial de contraseñas (impedir reusar las últimas 5 contraseñas)
- [ ] Verificación de contraseñas comprometidas con APIs como Have I Been Pwned
- [ ] Autenticación de dos factores (2FA)
- [ ] Política de expiración de contraseñas cada X meses
- [ ] Notificación por email cuando se cambia la contraseña

---

**Fecha de Implementación**: 28 de Octubre, 2025
**Archivos modificados**: 5
**Nuevas funcionalidades**: Cambio de contraseña desde perfil
**Nivel de seguridad**: ⭐⭐⭐⭐ (4/5)

