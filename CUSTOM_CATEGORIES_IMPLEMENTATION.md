# Implementación de Categorías Personalizadas

## Resumen
Se ha implementado un sistema completo de gestión de categorías personalizadas para gastos e ingresos que permite a los usuarios crear, editar y eliminar sus propias categorías con iconos y colores personalizados.

## Archivos Creados/Modificados

### Nuevos Archivos
1. **models/Category.php** - Modelo para gestionar categorías
   - CRUD completo de categorías
   - Métodos para obtener categorías por usuario y tipo
   - Iconos predefinidos por tipo
   - Colores predefinidos disponibles

2. **controllers/CategoryController.php** - Controlador de categorías
   - `manageCategories()` - Muestra la página de gestión
   - `createCategory()` - Crea nuevas categorías
   - `updateCategory()` - Actualiza categorías existentes
   - `deleteCategory()` - Elimina categorías personalizadas
   - `getCategoriesByType()` - API AJAX para obtener categorías

3. **views/manage_categories.php** - Vista de gestión de categorías
   - Formulario para crear nuevas categorías
   - Selector de iconos dinámico según el tipo
   - Selector de colores
   - Lista de categorías personalizadas por tipo
   - Modal para editar categorías
   - Validación JavaScript

4. **migrations/002_update_icons_to_fontawesome.sql** - Migración de base de datos
   - Actualiza iconos de emojis a Font Awesome
   - Actualiza categorías por defecto y personalizadas
   - Agrega nuevas categorías por defecto (15 gastos + 8 ingresos)

### Archivos Modificados
1. **database.sql** - Esquema principal actualizado
   - Tabla `expense_categories` con nuevos campos
   - Categorías por defecto para ingresos y gastos
   - Constraint único por usuario

2. **models/Transaction.php** - Método getCategories actualizado
   - Ahora acepta `user_id` y `type`
   - Retorna categorías del usuario y por defecto

3. **controllers/TransactionController.php** - Validación actualizada
   - Requiere categoría tanto para gastos como ingresos
   - Validación específica de método de pago solo para gastos

4. **views/add_transaction.php** - Formulario de transacciones actualizado
   - Carga categorías de gastos e ingresos
   - JavaScript dinámico para cambiar categorías según tipo
   - Muestra categorías con iconos

5. **includes/navbar.php** - Menú actualizado
   - Enlace a "Gestionar Categorías" añadido
   - Disponible en navegación desktop y móvil

## Características Implementadas

### 1. Creación de Categorías Personalizadas
- El usuario puede crear categorías para gastos o ingresos
- Selección de nombre personalizado
- Grid de iconos predefinidos (30 iconos por tipo)
- Grid de colores predefinidos (20 colores)
- Validación de nombres únicos por tipo

### 2. Iconos Predefinidos
**Para Gastos:**
🍔 🚗 🏠 💊 📚 👔 💡 🍕 🍺 🎬 🎮 ⚽ 🎨 🛍️ 🧴 🧼 💰 💵 🎁 📦 🚇 ✈️ 🏦 🏥 📱 💻 🖥️ 📺 🎵 📷

**Para Ingresos:**
💼 💻 📈 💰 🎁 💵 🏦 💳 📱 🤝 🎓 🏆 ⭐ 🎉 🚀 💡 🔔 🎯 🌟 ✨

### 3. Colores Predefinidos
20 colores disponibles en una paleta profesional:
- Rojos y naranjas
- Azules y verdes
- Púrpuras y rosas
- Grises y neutros

### 4. Gestión de Categorías
- **Ver**: Lista todas las categorías personalizadas
- **Crear**: Formulario con validación
- **Editar**: Modal para modificar nombre, icono y color
- **Eliminar**: Confirmación antes de eliminar
- Separación visual por tipo (gastos vs ingresos)

### 5. Integración con Transacciones
- Las categorías personalizadas aparecen en el formulario de transacciones
- Cambio dinámico de categorías según selección de gasto/ingreso
- Prioridad a categorías personalizadas sobre las por defecto
- Iconos y colores se muestran en los selects

## Flujo de Usuario

1. **Acceso**: Usuario hace clic en "Categorías" en el menú
2. **Creación**:
   - Selecciona tipo (Gasto/Ingreso)
   - Escribe nombre
   - Selecciona icono
   - Selecciona color
   - Guarda
3. **Uso**: Al crear transacciones, las categorías personalizadas aparecen primero
4. **Edición**: Puede modificar nombre, icono y color
5. **Eliminación**: Puede eliminar categorías que no use

## Base de Datos

### Tabla: expense_categories
```sql
- id (PK)
- user_id (FK, NULL para categorías por defecto)
- name
- type (ENUM: 'expense', 'income')
- icon
- color
- created_at
- updated_at
```

### Constraints
- UNIQUE (user_id, name, type) - No duplicados por usuario y tipo
- FOREIGN KEY (user_id) - Cascada al eliminar usuario
- INDEX (user_id, type) - Optimización de consultas

## Instalación

### Para Usuarios Existentes
Ejecutar el archivo de migración:
```sql
SOURCE migrations/002_update_icons_to_fontawesome.sql;
```

### Para Instalaciones Nuevas
El archivo `database.sql` ya incluye todos los cambios.

## Notas Técnicas

1. **Seguridad**: Todas las operaciones verifican `user_id` para prevenir acceso no autorizado
2. **Validación**: JavaScript y PHP validan datos antes de guardar
3. **UX**: Feedback visual inmediato con iconos y colores
4. **Responsive**: Diseño adaptable a móviles y tablets
5. **Performance**: Índices optimizan consultas de categorías

## Próximas Mejoras Posibles

- [ ] Categorías heredadas/plantillas
- [ ] Importar/exportar categorías
- [ ] Estadísticas por categoría personalizada
- [ ] Iconos personalizados (upload de imágenes)
- [ ] Paleta de colores más amplia
- [ ] Categorías por defecto editables por administradores

