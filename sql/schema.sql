
CREATE DATABASE IF NOT EXISTS auth_mvc CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE auth_mvc;

-- Tabla principal de usuarios
CREATE TABLE IF NOT EXISTS usuarios (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  correo VARCHAR(255) NOT NULL UNIQUE,
  nombre_usuario VARCHAR(50) NOT NULL UNIQUE,
  nombre VARCHAR(120) NOT NULL,
  password VARCHAR(255) NOT NULL,
  rol ENUM('usuario', 'admin') NOT NULL DEFAULT 'usuario',
  area VARCHAR(50) NOT NULL DEFAULT 'COM',
  codigo_verificacion VARCHAR(255) NULL,
  codigo_verificacion_expires_at DATETIME NULL,
  intentos_codigo INT UNSIGNED NOT NULL DEFAULT 0,
  fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_actualizacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Historial de intentos (auditoría / rate-limiting)
CREATE TABLE IF NOT EXISTS login_intentos (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  user_id INT UNSIGNED NULL,
  email VARCHAR(255) NULL,
  ip VARCHAR(45) NOT NULL,
  tipo ENUM('password','code','request_code') NOT NULL,
  exito TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_email_created (email, created_at),
  INDEX idx_user_created (user_id, created_at),
  INDEX idx_ip_created (ip, created_at),
  CONSTRAINT fk_login_intentos_user FOREIGN KEY (user_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Tabla de documentos
CREATE TABLE IF NOT EXISTS documentos (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  numero_registro VARCHAR(50) NOT NULL UNIQUE,
  usuario_id INT UNSIGNED NOT NULL,
  area VARCHAR(50) NOT NULL,
  fecha DATE NOT NULL,
  nombre_documento VARCHAR(255) NOT NULL,
  descripcion TEXT,
  estado ENUM('pendiente', 'cargado') NOT NULL DEFAULT 'pendiente',
  archivo_path VARCHAR(500) NULL,
  fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_actualizacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_usuario (usuario_id),
  INDEX idx_area (area),
  INDEX idx_estado (estado),
  INDEX idx_fecha (fecha),
  CONSTRAINT fk_documentos_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;
