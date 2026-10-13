CREATE DATABASE IF NOT EXISTS vino_costero;
USE vino_costero;

-- Seguridad

CREATE TABLE IF NOT EXISTS roles (
    id_rol INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(40) NOT NULL UNIQUE,
    descripcion VARCHAR(200),
    estado ENUM('Activo', 'Inactivo') DEFAULT 'Activo'
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    id_rol INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    correo VARCHAR(120) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    estado ENUM('Activo', 'Inactivo') DEFAULT 'Activo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_rol) REFERENCES roles(id_rol)
) ENGINE=InnoDB;

insert into usuarios (id_rol, nombre, correo, password_hash) values (1, "Javier", "jacs@gmail.com", "$2y$10$U2B5tYvEeVmjTWPUs42HleK2xA4K/aCESc4aM.NBP/dNLcX5OX86q");
-- Logica de negocio

-- parcelas
CREATE TABLE IF NOT EXISTS parcelas (
    id_parcela INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(80) NOT NULL UNIQUE,
    ubicacion VARCHAR(150),
    descripcion TEXT,
    estado ENUM('Activa', 'Inactiva') DEFAULT 'Activa',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- control de tierra de parcelas
CREATE TABLE IF NOT EXISTS controles_tierra (
    id_control_tierra INT AUTO_INCREMENT PRIMARY KEY,
    id_parcela INT NOT NULL,
    fecha_control DATE NOT NULL,
    ph DECIMAL(4,2),
    humedad DECIMAL(5,2),
    nitrogeno DECIMAL(8,2),
    fosforo DECIMAL(8,2),
    potasio DECIMAL(8,2),
    observaciones TEXT,
    FOREIGN KEY (id_parcela) REFERENCES parcelas(id_parcela),
    INDEX idx_tierra_fecha (fecha_control)
) ENGINE=InnoDB;

-- siembras
CREATE TABLE IF NOT EXISTS siembras (
    id_siembra INT AUTO_INCREMENT PRIMARY KEY,
    id_parcela INT NOT NULL,
    variedad_uva VARCHAR(80) NOT NULL,
    fecha_siembra DATE NOT NULL,
    cantidad_plantas INT NOT NULL,
    estado ENUM(
        'En crecimiento',
        'En producción',
        'Finalizada',
        'Suspendida'
    ) DEFAULT 'En crecimiento',
    observaciones TEXT,
    FOREIGN KEY (id_parcela) REFERENCES parcelas(id_parcela),
    CHECK (cantidad_plantas > 0),
    INDEX idx_siembra_fecha (fecha_siembra)
) ENGINE=InnoDB;

-- control de siembra
CREATE TABLE IF NOT EXISTS controles_siembra (
    id_control_siembra INT AUTO_INCREMENT PRIMARY KEY,
    id_siembra INT NOT NULL,
    fecha_control DATE NOT NULL,
    estado_general ENUM(
        'Excelente',
        'Bueno',
        'Regular',
        'Malo'
    ) NOT NULL,
    riego_realizado BOOLEAN DEFAULT FALSE,
    fertilizacion_realizada BOOLEAN DEFAULT FALSE,
    observaciones TEXT,
    FOREIGN KEY (id_siembra) REFERENCES siembras(id_siembra),
    INDEX idx_control_siembra_fecha (fecha_control)
) ENGINE=InnoDB;

-- siembras enfermas 
CREATE TABLE IF NOT EXISTS siembras_enfermas (
    id_siembra_enferma INT AUTO_INCREMENT PRIMARY KEY,
    id_siembra INT NOT NULL,
    nombre_enfermedad VARCHAR(120) NOT NULL,
    fecha_deteccion DATE NOT NULL,
    cantidad_plantas_afectadas INT NOT NULL,
    nivel_gravedad ENUM(
        'Leve',
        'Moderada',
        'Grave'
    ) NOT NULL,
    tratamiento TEXT,
    estado ENUM(
        'Detectada',
        'En tratamiento',
        'Controlada',
        'Sin recuperacion'
    ) DEFAULT 'Detectada',
    observaciones TEXT,
    FOREIGN KEY (id_siembra) REFERENCES siembras(id_siembra),
    CHECK (cantidad_plantas_afectadas > 0),
    INDEX idx_enfermedad_fecha (fecha_deteccion)
) ENGINE=InnoDB;

-- produccion de vinos
CREATE TABLE IF NOT EXISTS producciones (
    id_produccion INT AUTO_INCREMENT PRIMARY KEY,
    id_parcela INT NOT NULL,
    nombre_vino VARCHAR(100) NOT NULL,
    variedad_uva VARCHAR(80) NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE,
    cantidad_uva_kg DECIMAL(12,2) NOT NULL DEFAULT 0,
    cantidad_vino_litros DECIMAL(12,2) NOT NULL DEFAULT 0,
    estado ENUM(
        'Planificada',
        'En proceso',
        'Finalizada',
        'Cancelada'
    ) DEFAULT 'Planificada',
    observaciones TEXT,
    FOREIGN KEY (id_parcela) REFERENCES parcelas(id_parcela),
    CHECK (cantidad_uva_kg >= 0),
    CHECK (cantidad_vino_litros >= 0),
    CHECK (fecha_fin IS NULL OR fecha_fin >= fecha_inicio),
    INDEX idx_produccion_fecha (fecha_inicio),
    INDEX idx_produccion_estado (estado)
) ENGINE=InnoDB;

-- fases de produccion
CREATE TABLE IF NOT EXISTS fases_produccion (
    id_fase INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(80) NOT NULL UNIQUE,
    descripcion TEXT,
    orden_fase INT NOT NULL UNIQUE,
    estado ENUM('Activa', 'Inactiva') DEFAULT 'Activa'
) ENGINE=InnoDB;

-- Seguimiento de las fases por producción
CREATE TABLE IF NOT EXISTS seguimiento_fases (
    id_seguimiento INT AUTO_INCREMENT PRIMARY KEY,
    id_produccion INT NOT NULL,
    id_fase INT NOT NULL,
    fecha_inicio DATETIME NOT NULL,
    fecha_fin DATETIME,
    estado ENUM(
        'Pendiente',
        'En proceso',
        'Completada',
        'Cancelada'
    ) DEFAULT 'Pendiente',
    observaciones TEXT,
    FOREIGN KEY (id_produccion) REFERENCES producciones(id_produccion),
    FOREIGN KEY (id_fase) REFERENCES fases_produccion(id_fase),
    CHECK (fecha_fin IS NULL OR fecha_fin >= fecha_inicio),
    INDEX idx_seguimiento_produccion (id_produccion)
) ENGINE=InnoDB;

-- catas
CREATE TABLE IF NOT EXISTS catas (
    id_cata INT AUTO_INCREMENT PRIMARY KEY,
    id_produccion INT NOT NULL,
    fecha_cata DATE NOT NULL,
    aroma VARCHAR(200),
    sabor VARCHAR(200),
    color VARCHAR(100),
    cuerpo VARCHAR(100),
    puntuacion DECIMAL(4,2) NOT NULL,
    resultado ENUM(
        'Aprobado',
        'Requiere mejoras',
        'Rechazado'
    ) NOT NULL,
    observaciones TEXT,
    FOREIGN KEY (id_produccion) REFERENCES producciones(id_produccion),
    CHECK (puntuacion >= 0 AND puntuacion <= 100),
    INDEX idx_cata_fecha (fecha_cata)
) ENGINE=InnoDB;

-- Datos de roles

INSERT IGNORE INTO roles (nombre, descripcion) VALUES
('Administrador', 'Acceso administrativo al sistema'),
('Enologo', 'Control de producción y evaluación de vinos'),
('Ingeniero Agronomo', 'Control de parcelas, tierra y siembras'),
('Operador', 'Registro de actividades operativas'),

-- Fases de produccion

INSERT IGNORE INTO fases_produccion
(nombre, descripcion, orden_fase) VALUES
('Recepción de uvas', 'Recepción y revisión de la materia prima', 1),
('Prensado', 'Extracción del mosto de las uvas', 2),
('Fermentación', 'Transformación del mosto en vino', 3),
('Clarificación', 'Separación de partículas e impurezas', 4),
('Estabilización', 'Preparación y estabilización del vino', 5),
('Filtrado', 'Filtración previa al envasado', 6),
('Control de calidad', 'Evaluación final de la producción', 7);


