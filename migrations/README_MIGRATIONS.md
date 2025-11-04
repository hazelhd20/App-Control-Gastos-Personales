# Migraciones de Base de Datos

Este directorio contiene las migraciones SQL para actualizar la base de datos del sistema de control de gastos.

## Orden de Ejecución

Las migraciones deben ejecutarse en el siguiente orden según tu situación:

1. **database.sql** - Script inicial de creación de la base de datos (si es una instalación nueva)
2. **003_rename_table_to_categories.sql** - Si tienes la tabla `expense_categories`, renómbrala primero (solo si aplica)
3. **002_update_icons_to_fontawesome.sql** - Actualiza iconos de emojis a Font Awesome (OBLIGATORIO si ya tienes datos)

## Instrucciones

### Para Instalación Nueva

Si estás instalando la aplicación por primera vez, simplemente ejecuta `database.sql` que ya incluye:
- La tabla `categories` con toda la estructura completa
- Todas las categorías por defecto (15 gastos + 8 ingresos) con iconos Font Awesome
- Todas las columnas necesarias (user_id, type, icon, color, etc.)

**No necesitas ejecutar ninguna migración adicional.**

### Para Instalación Existente

Si ya tienes una base de datos con datos, ejecuta las migraciones según tu situación:

#### Si tienes la tabla `expense_categories`:

```bash
# 1. Primero renombra la tabla
mysql -u usuario -p nombre_base_datos < migrations/003_rename_table_to_categories.sql

# 2. Luego actualiza los iconos
mysql -u usuario -p nombre_base_datos < migrations/002_update_icons_to_fontawesome.sql
```

#### Si ya tienes la tabla `categories`:

```bash
# Solo actualiza los iconos
mysql -u usuario -p nombre_base_datos < migrations/002_update_icons_to_fontawesome.sql
```

### Desde phpMyAdmin

1. Selecciona tu base de datos
2. Ve a la pestaña "SQL"
3. Copia y pega el contenido del archivo de migración
4. Ejecuta la consulta

### Desde MySQL Workbench o línea de comandos

```bash
mysql -u root -p control_gastos < migrations/002_update_icons_to_fontawesome.sql
```

## Migración 002: Actualización de Iconos

Esta migración:

- ✅ Actualiza todas las categorías por defecto existentes de emojis a iconos Font Awesome
- ✅ Actualiza categorías personalizadas de usuarios que aún usen emojis
- ✅ Agrega nuevas categorías por defecto adicionales (15 gastos, 8 ingresos)
- ✅ Mantiene los colores existentes de las categorías

**Nota:** Esta migración es segura y no elimina datos. Solo actualiza los iconos y agrega nuevas categorías por defecto.

## Migración 003: Renombrar Tabla

Esta migración:

- ✅ Renombra la tabla `expense_categories` a `categories`
- ✅ Nombre más descriptivo que refleja que maneja ambos tipos (gastos e ingresos)
- ✅ Cambio mínimo y seguro

**Nota:** Esta migración solo es necesaria si tu base de datos tiene la tabla `expense_categories`. Si ya tienes `categories`, omite esta migración.

## Categorías por Defecto

### Gastos (15 categorías)
- Alimentación, Transporte, Entretenimiento, Vivienda, Salud, Educación, Ropa, Servicios, Compras, Restaurantes, Deportes, Tecnología, Viajes, Bancos, Otros

### Ingresos (8 categorías)
- Salario, Freelance, Inversiones, Venta, Regalo, Bonificación, Préstamo, Otros

## Verificación

Después de ejecutar la migración, verifica que los iconos se muestren correctamente:

```sql
SELECT name, type, icon, color FROM categories WHERE user_id IS NULL;
```

Todos los iconos deberían comenzar con `fa-` (ejemplo: `fa-utensils`, `fa-car`, etc.)

