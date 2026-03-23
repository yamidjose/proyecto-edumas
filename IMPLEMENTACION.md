# Implementación del Flujo del Sistema EDUMAS

## ✅ Cambios Realizados

### 1. Base de Datos (schema.sql)
- ✓ Agregados campos a tabla `usuarios`:
  - `nombre` (VARCHAR 120) - Nombre completo del usuario
  - `rol` (ENUM: usuario/admin) - Rol del usuario
  - `area` (VARCHAR 50) - Área de trabajo
  - `fecha_actualizacion` - Timestamp de actualización

- ✓ Creada tabla `documentos` con:
  - `numero_registro` - Identificador único del documento
  - `usuario_id` - Relación con usuario
  - `area`, `fecha`, `nombre_documento`, `descripcion`
  - `estado` (ENUM: pendiente/cargado)
  - `archivo_path` - Ruta del archivo PDF
  - Índices para búsquedas rápidas

### 2. Controladores

#### AuthController.php
- ✓ Completadas validaciones de login
- ✓ Flujo de códigos de verificación por email
- ✓ Creación segura de sesión con regeneración de ID
- ✓ Almacenamiento en sesión de: id, nombre, correo, rol, area
- ✓ Redireccionamiento automático según rol:
  - Admin → `index.php?action=admin_dashboard`
  - Usuario → `index.php?action=dashboard`

#### DocumentoController.php (Completamente refactorizado)
- ✓ Validación de autenticación en cada método
- ✓ Método `crear()`: Genera número único, valida datos, crea documento
- ✓ Método `subir()`:
  - Valida que sea PDF
  - Crea directorio de uploads si no existe
  - Guarda archivo con nombre único (timestamp)
  - Actualiza estado a 'cargado'
  - Envía email de confirmación
  - Manejo robusto de errores

- ✓ Método `eliminar()`:
  - Solo admin puede eliminar
  - Elimina archivo del servidor
  - Elimina registro de BD

### 3. Modelos

#### DocumentoModel.php
- ✓ Método `filtrar()` corregido - ahora retorna datos correctamente
- ✓ Métodos añadidos:
  - `obtenerPorId()` - Obtiene documento específico
  - `obtenerAreas()` - Lista de áreas únicas
  - `contarDocumentos()` - Contador según rol
  - `obtenerUltimoRegistro()` - Último registro creado

### 4. Vistas

#### login.php
- ✓ Flujo de 2 pasos: contraseña + código de verificación
- ✓ Diseño responsivo con Tailwind CSS
- ✓ Validaciones en cliente y servidor
- ✓ Muestra estado "Usuario confirmado" cuando espera código

#### dashboard.php (Completamente nueva)
- ✓ Header con información del usuario (nombre, área, rol)
- ✓ Botón de logout
- ✓ Estadísticas en cards:
  - Total documentos
  - Pendientes de cargar
  - Ya cargados

- ✓ Formulario para crear documento (lado izquierdo):
  - Área, fecha, usuario, correo: automáticos (solo lectura)
  - Nombre documento: campo requerido
  - Descripción: campo opcional

- ✓ Sección de filtros:
  - Por nombre documento
  - Por área
  - Por rango de fechas
  - Botón aplicar filtros

- ✓ Tabla de documentos cargados:
  - Nombre, número de registro, fecha
  - Botón "Ver PDF"
  - Botón "Eliminar" (solo admin)

- ✓ Tabla de documentos pendientes:
  - Para **usuario**: formulario para subir PDF
  - Para **admin**: indicador "Esperando usuario"
  - Botón "Eliminar" (solo admin)

- ✓ Diseño responsivo para mobile

### 5. Routing (index.php)
- ✓ Refactorizado completamente
- ✓ Inicializa PDO correctamente para DocumentoController
- ✓ Separación clara:
  - Sin autenticación → mostrar login
  - Con autenticación → mostrar dashboard
- ✓ Manejo de acciones POST vs GET
- ✓ Redirecciones correctas después de operaciones

## 📋 Flujo Completo del Sistema

### 1. LOGIN
```
Usuario → Ingresa credenciales (username, password, email)
        → Sistema valida contraseña
        → Envía código de 6 dígitos al email
```

### 2. VERIFICACIÓN
```
Usuario → Ingresa código
        → Sistema valida código (valida hash, expiry)
        → Crea sesión con datos del usuario
        → Regenera ID de sesión (seguridad)
```

### 3. REDIRECCIÓN POST-LOGIN
```
Si rol = admin    → Redirige a index.php?action=admin_dashboard
Si rol = usuario  → Redirige a index.php?action=dashboard
(Nota: Usan misma vista pero filtran datos por rol)
```

### 4. DASHBOARD
Muestra:
- **Header**: Nombre usuario, área, rol, botón logout
- **Estadísticas**: contadores de documentos
- **Formulario** (izquierda):
  - Crea documento nuevo
  - Genera número único automático
  - Estado inicial: pendiente
  
- **Documentos** (derecha):
  - **Sección Verde**: PDFs cargados
    - Usuario: Ver archivo
    - Admin: Ver + Eliminar
  
  - **Sección Amarilla**: PDFs pendientes
    - Usuario: Formulario para subir archivo
    - Admin: Ver, Eliminar, o indicador "Esperando usuario"

### 5. CREAR DOCUMENTO
```
Usuario → Rellena nombre + descripción
        → Envía formulario
        → Sistema genera: EDU-YYYY-###-AREA-###
        → Guarda con estado 'pendiente'
        → Aparece en tabla pendientes
        → Muestra mensaje de éxito
```

### 6. SUBIR PDF
```
Usuario → Selecciona archivo PDF en documento pendiente
        → Envía formulario
        → Sistema valida:
          - Que sea PDF (MIME type)
          - Que no sea demasiado grande
        → Guarda archivo: uploads/timestamp_nombre.pdf
        → Actualiza estado a 'cargado'
        → Envía email de confirmación
        → Documento pasa a sección cargados
```

### 7. FILTROS
```
Usuario/Admin → Selecciona filtros:
              - Nombre documento (busca por LIKE)
              - Área (dropdown)
              - Fecha desde/hasta (rango)
            → Aplica filtros
            → Recarga tabla con resultados
```

### 8. ADMIN
- Ve todos los documentos (sin filtro por usuario)
- Puede eliminar cualquier documento
- Opcionalmente puede gestionar usuarios (futura)

### 9. LOGOUT
```
Usuario → Hace clic en "Cerrar sesión"
        → Sistema destruye sesión
        → Borra cookie de sesión
        → Redirige a login
```

---

## 🔄 **ACTUALIZACIÓN: Funcionalidad de Admin Mejorada**

### ✅ **Cambios Recientes (Formulario de Crear Usuario)**

#### DocumentoController.php
- ✓ **Nuevo método `crearUsuario()`**:
  - Solo accesible para administradores
  - Validaciones completas: email, username, nombre, password, rol, área
  - Hash seguro de contraseñas
  - Verificación de unicidad (email y username)
  - Manejo de errores con mensajes específicos

#### index.php
- ✓ **Nueva ruta `crear_usuario`**:
  - POST: Procesa creación de usuario
  - GET: Redirige al dashboard

#### dashboard.php
- ✓ **Formulario condicional**:
  - **Usuario normal**: Formulario para crear documentos
  - **Admin**: Formulario para crear usuarios
  
- ✓ **Estadísticas diferenciadas**:
  - **Usuario**: Total docs, Pendientes, Cargados
  - **Admin**: Total docs, Total usuarios, Usuarios activos (30 días)

- ✓ **Header personalizado**:
  - Muestra "Panel de Administración" para admin
  - Muestra "Usuario" vs "Administrador" en el rol

#### Formulario de Crear Usuario (Admin)
- **Campos**:
  - Correo electrónico (validación email)
  - Nombre de usuario (único)
  - Nombre completo
  - Contraseña (mín 6 caracteres)
  - Rol (Usuario/Administrador)
  - Área (COM, CU, DU, GA, JUR)

- **Validaciones**:
  - Todos los campos obligatorios
  - Email válido
  - Username único
  - Password segura
  - Rol válido
  - Área válida

#### Estadísticas para Admin
- **Total Documentos**: Todos los documentos del sistema
- **Total Usuarios**: Conteo de usuarios registrados
- **Usuarios Activos**: Usuarios con documentos en los últimos 30 días

---

## 🚀 Instrucciones de Uso

### Instalación
1. **Crear base de datos**:
   ```bash
   mysql -u root < sql/schema.sql
   ```

2. **Crear directorio de uploads**:
   ```bash
   mkdir -p public/uploads
   chmod 755 public/uploads
   ```

3. **Configurar email** (opcional):
   - Editar `config/config.php`
   - Agregar credenciales SMTP o dejar como está el Mailtrap

4. **Crear usuario inicial**:
   - Usar el script: `php scripts/create_user.php`
   - O insertar manualmente en BD

5. **Acceder**:
   - http://localhost/proyecto-edumas/public/
   - Username: (del usuario creado)
   - Email: (email válido)
   - Password: (password del usuario)

### Crear Usuario Test

```php
// En scripts/create_user.php
$email = 'user@example.com';
$username = 'usuario123';
$password = 'password123';
$nombre = 'Juan Pérez';
$rol = 'usuario'; // o 'admin'
$area = 'COM'; // COM, CU, DU, GA, JUR
```

## 📁 Estructura de Directorios

```
proyecto-edumas/
├── config/
│   └── config.php              ← Configuración (DB, mail, seguridad)
├── controller/
│   ├── AuthController.php       ← Autenticación y login
│   └── DocumentoController.php  ← Gestión de documentos
├── model/
│   ├── Database.php             ← Conexión PDO
│   ├── UserModel.php            ← Modelo de usuarios
│   └── DocumentoModel.php       ← Modelo de documentos
├── public/
│   ├── index.php                ← Router principal
│   ├── uploads/                 ← Archivos PDF (creado dinámicamente)
│   ├── css/
│   │   └── styles.css
│   └── js/
│       └── app.js
├── view/
│   ├── login.php                ← Formulario de login
│   ├── dashboard.php            ← Dashboard principal
│   ├── emails/
│   │   └── codigo_verificacion.php
│   └── partials/
│       └── flash.php            ← Mensajes flashback
├── sql/
│   └── schema.sql               ← Definición de BD
├── scripts/
│   ├── create_user.php
│   └── test_mail.php
└── README.md
```

## 🔒 Seguridad Implementada

- ✓ Hash de contraseñas with PASSWORD_ARGON2ID (o BCRYPT)
- ✓ Códigos de verificación con hash
- ✓ Tokens CSRF en formularios
- ✓ Rate limiting: máx 5 fallos en 15 min
- ✓ Rate limiting: máx 50 intentos de código/hora
- ✓ Regeneración de ID de sesión post-login
- ✓ Validación MIME type de archivos
- ✓ Session fixation protection
- ✓ Prepared statements en consultas SQL
- ✓ htmlspecialchars() en outputs

## 💡 Mejoras Futuras

- [ ] Gestión de usuarios (crear/editar/eliminar)
- [ ] Cambio de contraseña por usuario
- [ ] Historial de acciones (auditoría)
- [ ] Búsqueda fulltext
- [ ] Descarga de reportes PDF
- [ ] Notificaciones en tiempo real
- [ ] Sistema de permisos granulares
- [ ] 2FA con app authenticator

## 📞 Soporte

Para problemas:
1. Revisar logs de error en Apache/PHP
2. Validar credenciales en config.php
3. Verificar permisos de directorio uploads
4. Checkear conexión a base de datos
