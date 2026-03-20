
# Auth MVC (PHP + MySQL + Tailwind + PHPMailer)

Sistema de login profesional con dos opciones: contraseña y código temporal enviado por correo. Estructura MVC, validaciones lado servidor/cliente, rate limiting, sesiones seguras, Tailwind y **PHPMailer** listo con Composer.

## Requisitos
- PHP 8.0+
- MySQL 5.7+/8.0+
- Composer 2+
- Servidor SMTP (para envío real de correos)

## Instalación
1. **Clonar o descomprimir** este proyecto.
2. **Crear DB y tablas**:
   ```sql
   -- o usa sql/schema.sql
   CREATE DATABASE IF NOT EXISTS auth_mvc CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
   Importa `sql/schema.sql`.
3. **Configurar credenciales** en `config/config.php`:
   - DB: host, usuario, contraseña.
   - Mail: `use_phpmailer` = `true` (ya viene), SMTP (`host`, `port`, `username`, `password`, `encryption`).
   - Seguridad: en producción usa HTTPS y `session_secure=true`.
4. **Instalar dependencias**:
   ```bash
   composer install
   ```
5. **Crear usuario de prueba** (CLI):
   ```bash
   php scripts/create_user.php
   # o con argumentos
   php scripts/create_user.php test@ejemplo.com usuario_test MiPass123
   ```
6. **Probar correo SMTP** (opcional):
   ```bash
   php scripts/test_mail.php destinatario@correo.com
   ```
7. **Levantar servidor de desarrollo**:
   ```bash
   php -S localhost:8080 -t public
   ```
   Visita `http://localhost:8080`.

## Flujos soportados
- **Login con contraseña** (hash Argon2id o bcrypt).
- **Login con código temporal** (6 dígitos, hash en DB, expira 5 min, un solo uso, límite 5 intentos/hora).
- **Rate limiting** adicional para password (5 fallos/15min).
- Mensajes **genéricos** para evitar enumeración de usuarios.

## Notas de producción
- Habilita HTTPS y `session_secure=true`.
- Revisa logs del servidor ante errores SMTP.
- Considera reCAPTCHA, bloqueo por IP y políticas de contraseña.

## Créditos
- PHPMailer: https://github.com/PHPMailer/PHPMailer
- Tailwind CSS: https://tailwindcss.com/
