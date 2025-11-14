-- ============================================
-- Base de datos: Plataforma Académica
-- Contraseñas: Todos los usuarios tienen "123"
-- ============================================

CREATE DATABASE IF NOT EXISTS plataforma_academica CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE plataforma_academica;

-- ============================================
-- TABLAS
-- ============================================

-- Tabla de usuarios (administradores, maestros y estudiantes)
CREATE TABLE usuarios (
    id_usuario INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    rol ENUM('administrador', 'maestro', 'estudiante') NOT NULL,
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    activo BOOLEAN DEFAULT TRUE,
    foto_perfil VARCHAR(255) DEFAULT NULL,
    INDEX idx_email (email),
    INDEX idx_rol (rol)
) ENGINE=InnoDB;

-- Tabla de materias
CREATE TABLE materias (
    id_materia INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT,
    id_maestro INT NOT NULL,
    cuatrimestre VARCHAR(20),
    creditos INT DEFAULT 0,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    activa BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (id_maestro) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    INDEX idx_materia_maestro (id_maestro),
    INDEX idx_activa (activa)
) ENGINE=InnoDB;

-- Tabla de inscripciones (con sistema de aprobación)
CREATE TABLE inscripciones (
    id_inscripcion INT PRIMARY KEY AUTO_INCREMENT,
    id_estudiante INT NOT NULL,
    id_materia INT NOT NULL,
    estado ENUM('pendiente', 'aprobado', 'rechazado') DEFAULT 'pendiente',
    motivo_rechazo TEXT,
    fecha_solicitud DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_respuesta DATETIME,
    FOREIGN KEY (id_estudiante) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_materia) REFERENCES materias(id_materia) ON DELETE CASCADE,
    UNIQUE KEY unique_inscripcion (id_estudiante, id_materia),
    INDEX idx_inscripcion_estado (estado),
    INDEX idx_estudiante (id_estudiante),
    INDEX idx_materia (id_materia)
) ENGINE=InnoDB;

-- Tabla de actividades/tareas
CREATE TABLE actividades (
    id_actividad INT PRIMARY KEY AUTO_INCREMENT,
    id_materia INT NOT NULL,
    titulo VARCHAR(200) NOT NULL,
    descripcion TEXT,
    fecha_limite DATETIME NOT NULL,
    ponderacion DECIMAL(5,2) NOT NULL COMMENT 'Porcentaje que vale (ej: 15.50)',
    tipo ENUM('tarea', 'examen', 'proyecto', 'participacion') DEFAULT 'tarea',
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    activa BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (id_materia) REFERENCES materias(id_materia) ON DELETE CASCADE,
    INDEX idx_actividad_materia (id_materia),
    INDEX idx_fecha_limite (fecha_limite)
) ENGINE=InnoDB;

-- Tabla de calificaciones
CREATE TABLE calificaciones (
    id_calificacion INT PRIMARY KEY AUTO_INCREMENT,
    id_estudiante INT NOT NULL,
    id_actividad INT NOT NULL,
    calificacion DECIMAL(5,2) COMMENT 'Calificación numérica (0-100)',
    comentarios TEXT,
    fecha_entrega DATETIME,
    fecha_calificacion DATETIME,
    FOREIGN KEY (id_estudiante) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_actividad) REFERENCES actividades(id_actividad) ON DELETE CASCADE,
    UNIQUE KEY unique_calificacion (id_estudiante, id_actividad),
    INDEX idx_calificacion_estudiante (id_estudiante)
) ENGINE=InnoDB;

-- Tabla de logros (gamificación)
CREATE TABLE logros (
    id_logro INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    icono VARCHAR(255),
    criterio VARCHAR(200) COMMENT 'ej: "promedio_alto", "cumplido_tiempo", "constancia"'
) ENGINE=InnoDB;

-- Tabla intermedia: logros obtenidos por usuarios
CREATE TABLE usuarios_logros (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    id_logro INT NOT NULL,
    fecha_obtencion DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_logro) REFERENCES logros(id_logro) ON DELETE CASCADE,
    UNIQUE KEY unique_usuario_logro (id_usuario, id_logro)
) ENGINE=InnoDB;

-- Tabla para tokens de recuperación de contraseña
CREATE TABLE password_reset (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    token VARCHAR(191) NOT NULL,
    fecha_expiracion DATETIME NOT NULL,
    usado BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    INDEX idx_token (token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- DATOS DE PRUEBA CON CONTRASEÑAS CORRECTAS
-- ============================================

-- Hash CORRECTO de la contraseña "123" usando bcrypt
-- $2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5NANc6jH8fHOS

-- Insertar usuarios de prueba con contraseñas CORRECTAS
INSERT INTO usuarios (nombre, apellido, email, password, rol) VALUES
-- Administrador
('Juan', 'Pérez', 'admin@plataforma.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5NANc6jH8fHOS', 'administrador'),

-- Maestros
('María', 'González', 'maria.gonzalez@plataforma.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5NANc6jH8fHOS', 'maestro'),
('Carlos', 'Rodríguez', 'carlos.rodriguez@plataforma.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5NANc6jH8fHOS', 'maestro'),
('Ana', 'Martínez', 'ana.martinez@plataforma.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5NANc6jH8fHOS', 'maestro'),

-- Estudiantes
('Pedro', 'López', 'pedro.lopez@plataforma.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5NANc6jH8fHOS', 'estudiante'),
('Laura', 'Sánchez', 'laura.sanchez@plataforma.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5NANc6jH8fHOS', 'estudiante'),
('Miguel', 'Torres', 'miguel.torres@plataforma.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5NANc6jH8fHOS', 'estudiante'),
('Sofía', 'Ramírez', 'sofia.ramirez@plataforma.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5NANc6jH8fHOS', 'estudiante'),
('Diego', 'Flores', 'diego.flores@plataforma.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5NANc6jH8fHOS', 'estudiante'),
('Valentina', 'Morales', 'valentina.morales@plataforma.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5NANc6jH8fHOS', 'estudiante');

-- Insertar materias
INSERT INTO materias (nombre, descripcion, id_maestro, cuatrimestre, creditos, activa) VALUES
-- Materias de María González (id_maestro = 2)
('Programación Web', 'Desarrollo de aplicaciones web con HTML, CSS, JavaScript y PHP', 2, '3er Cuatrimestre', 8, TRUE),
('Bases de Datos', 'Diseño e implementación de bases de datos relacionales', 2, '3er Cuatrimestre', 7, TRUE),

-- Materias de Carlos Rodríguez (id_maestro = 3)
('Matemáticas Avanzadas', 'Cálculo diferencial e integral aplicado', 3, '2do Cuatrimestre', 8, TRUE),
('Estructuras de Datos', 'Listas, árboles, grafos y algoritmos de búsqueda', 3, '4to Cuatrimestre', 7, TRUE),

-- Materias de Ana Martínez (id_maestro = 4)
('Inglés Técnico', 'Comprensión de textos técnicos en inglés', 4, '1er Cuatrimestre', 5, TRUE),
('Redes de Computadoras', 'Fundamentos de redes y protocolos de comunicación', 4, '5to Cuatrimestre', 6, TRUE);

-- Insertar inscripciones (algunas aprobadas, algunas pendientes, algunas rechazadas)
INSERT INTO inscripciones (id_estudiante, id_materia, estado, fecha_solicitud, fecha_respuesta) VALUES
-- Estudiante Pedro López (id = 5)
(5, 1, 'aprobado', '2024-08-15 10:00:00', '2024-08-15 14:00:00'),
(5, 2, 'aprobado', '2024-08-15 10:05:00', '2024-08-15 14:05:00'),
(5, 3, 'pendiente', '2024-11-10 09:00:00', NULL),

-- Estudiante Laura Sánchez (id = 6)
(6, 1, 'aprobado', '2024-08-16 11:00:00', '2024-08-16 15:00:00'),
(6, 3, 'aprobado', '2024-08-16 11:10:00', '2024-08-16 15:10:00'),
(6, 5, 'rechazado', '2024-09-01 10:00:00', '2024-09-02 09:00:00'),

-- Estudiante Miguel Torres (id = 7)
(7, 2, 'aprobado', '2024-08-17 09:30:00', '2024-08-17 16:00:00'),
(7, 4, 'aprobado', '2024-08-17 09:35:00', '2024-08-17 16:05:00'),
(7, 1, 'pendiente', '2024-11-11 08:00:00', NULL),

-- Estudiante Sofía Ramírez (id = 8)
(8, 1, 'aprobado', '2024-08-18 10:00:00', '2024-08-18 14:00:00'),
(8, 3, 'aprobado', '2024-08-18 10:10:00', '2024-08-18 14:10:00'),
(8, 5, 'aprobado', '2024-08-18 10:15:00', '2024-08-18 14:15:00'),

-- Estudiante Diego Flores (id = 9)
(9, 2, 'aprobado', '2024-08-19 11:00:00', '2024-08-19 15:00:00'),
(9, 4, 'pendiente', '2024-11-09 10:00:00', NULL),

-- Estudiante Valentina Morales (id = 10)
(10, 1, 'aprobado', '2024-08-20 09:00:00', '2024-08-20 13:00:00'),
(10, 6, 'aprobado', '2024-08-20 09:10:00', '2024-08-20 13:10:00');

-- Insertar actividades
INSERT INTO actividades (id_materia, titulo, descripcion, fecha_limite, ponderacion, tipo, activa) VALUES
-- Actividades de Programación Web (materia 1)
(1, 'Tarea 1: HTML y CSS Básico', 'Crear una página web con HTML y CSS siguiendo las especificaciones del documento', '2024-11-20 23:59:00', 10.00, 'tarea', TRUE),
(1, 'Proyecto 1: Sitio Web Responsivo', 'Desarrollar un sitio web completo con diseño responsivo', '2024-12-05 23:59:00', 25.00, 'proyecto', TRUE),
(1, 'Examen Parcial', 'Examen teórico-práctico de HTML, CSS y JavaScript', '2024-11-25 10:00:00', 30.00, 'examen', TRUE),
(1, 'Tarea 2: JavaScript Interactivo', 'Crear una aplicación web interactiva con JavaScript', '2024-11-15 23:59:00', 15.00, 'tarea', TRUE),

-- Actividades de Bases de Datos (materia 2)
(2, 'Tarea 1: Modelo Entidad-Relación', 'Diseñar el modelo ER para un sistema de biblioteca', '2024-11-18 23:59:00', 12.00, 'tarea', TRUE),
(2, 'Proyecto: Base de Datos Completa', 'Implementar una base de datos completa con consultas', '2024-12-10 23:59:00', 30.00, 'proyecto', TRUE),
(2, 'Examen Parcial SQL', 'Examen práctico de consultas SQL', '2024-11-22 09:00:00', 25.00, 'examen', TRUE),

-- Actividades de Matemáticas Avanzadas (materia 3)
(3, 'Tarea 1: Límites y Continuidad', 'Resolver ejercicios del capítulo 2', '2024-11-19 23:59:00', 10.00, 'tarea', TRUE),
(3, 'Examen Parcial', 'Examen de cálculo diferencial', '2024-11-28 08:00:00', 35.00, 'examen', TRUE),
(3, 'Tarea 2: Derivadas', 'Problemas de aplicación de derivadas', '2024-12-03 23:59:00', 10.00, 'tarea', TRUE),

-- Actividades de Estructuras de Datos (materia 4)
(4, 'Tarea 1: Listas Enlazadas', 'Implementar una lista enlazada en Java', '2024-11-17 23:59:00', 15.00, 'tarea', TRUE),
(4, 'Proyecto: Implementación de Árbol', 'Crear una implementación completa de árbol binario', '2024-12-08 23:59:00', 30.00, 'proyecto', TRUE),

-- Actividades de Inglés Técnico (materia 5)
(5, 'Tarea 1: Traducción de Manual', 'Traducir manual técnico del inglés', '2024-11-21 23:59:00', 20.00, 'tarea', TRUE),
(5, 'Examen Oral', 'Presentación en inglés sobre un tema técnico', '2024-11-30 10:00:00', 30.00, 'examen', TRUE),

-- Actividades de Redes de Computadoras (materia 6)
(6, 'Tarea 1: Modelo OSI', 'Investigar y documentar el modelo OSI', '2024-11-16 23:59:00', 10.00, 'tarea', TRUE),
(6, 'Proyecto: Configuración de Red', 'Configurar una red local con servicios', '2024-12-12 23:59:00', 35.00, 'proyecto', TRUE);

-- Insertar calificaciones
INSERT INTO calificaciones (id_estudiante, id_actividad, calificacion, comentarios, fecha_entrega, fecha_calificacion) VALUES
-- Calificaciones de Pedro López (id = 5)
(5, 1, 95.00, 'Excelente trabajo, muy bien estructurado', '2024-11-19 20:30:00', '2024-11-20 10:00:00'),
(5, 4, 88.00, 'Buen trabajo, pero puede mejorar la lógica', '2024-11-14 22:00:00', '2024-11-15 14:00:00'),
(5, 5, 92.00, 'Modelo bien diseñado y documentado', '2024-11-17 19:00:00', '2024-11-18 09:00:00'),

-- Calificaciones de Laura Sánchez (id = 6)
(6, 1, 87.00, 'Buen diseño, falta un poco de creatividad', '2024-11-20 18:00:00', '2024-11-21 11:00:00'),
(6, 4, 91.00, 'Código limpio y bien comentado', '2024-11-15 21:00:00', '2024-11-16 10:00:00'),
(6, 8, 85.00, 'Respuestas correctas, revisa la sección 3', '2024-11-19 23:00:00', '2024-11-20 08:00:00'),

-- Calificaciones de Miguel Torres (id = 7)
(7, 5, 78.00, 'Faltan algunas relaciones importantes', '2024-11-18 23:30:00', '2024-11-19 13:00:00'),
(7, 11, 82.00, 'Implementación correcta pero ineficiente', '2024-11-16 20:00:00', '2024-11-17 15:00:00'),

-- Calificaciones de Sofía Ramírez (id = 8)
(8, 1, 96.00, 'Excelente, uno de los mejores trabajos', '2024-11-18 17:00:00', '2024-11-19 09:00:00'),
(8, 4, 94.00, 'Perfecto manejo de JavaScript', '2024-11-14 19:00:00', '2024-11-15 11:00:00'),
(8, 8, 89.00, 'Muy buen dominio del tema', '2024-11-18 22:00:00', '2024-11-19 10:00:00'),

-- Calificaciones de Diego Flores (id = 9)
(9, 5, 90.00, 'Excelente modelo, bien normalizado', '2024-11-17 21:00:00', '2024-11-18 14:00:00'),

-- Calificaciones de Valentina Morales (id = 10)
(10, 1, 83.00, 'Buen trabajo, revisa la validación de formularios', '2024-11-20 22:00:00', '2024-11-21 09:00:00'),
(10, 4, 86.00, 'Funcionalidad correcta, mejorar la UI', '2024-11-15 20:00:00', '2024-11-16 12:00:00'),
(10, 13, 88.00, 'Buena investigación del tema', '2024-11-15 18:00:00', '2024-11-16 10:00:00');

-- Insertar logros
INSERT INTO logros (nombre, descripcion, icono, criterio) VALUES
('Primera Entrega', 'Entregaste tu primera tarea a tiempo', '🎯', 'primera_entrega'),
('Promedio Alto', 'Mantuviste un promedio mayor a 90', '⭐', 'promedio_alto'),
('Racha Perfecta', 'Entregaste 5 tareas consecutivas a tiempo', '🔥', 'racha_perfecta'),
('Estudiante Constante', 'Completaste todas las actividades del mes', '💪', 'constancia'),
('Excelencia Académica', 'Obtuviste calificación perfecta en un examen', '🏆', 'excelencia'),
('Participación Activa', 'Participaste en todas las clases del mes', '🗣️', 'participacion');

-- Asignar algunos logros a estudiantes
INSERT INTO usuarios_logros (id_usuario, id_logro, fecha_obtencion) VALUES
(5, 1, '2024-11-14 10:00:00'),
(5, 2, '2024-11-20 15:00:00'),
(6, 1, '2024-11-15 11:00:00'),
(8, 1, '2024-11-14 12:00:00'),
(8, 2, '2024-11-21 10:00:00'),
(8, 5, '2024-11-19 14:00:00'),
(10, 1, '2024-11-15 09:00:00');

-- ============================================
-- VERIFICACIÓN FINAL
-- ============================================

SELECT '=== BASE DE DATOS CREADA EXITOSAMENTE ===' as Mensaje;

SELECT 
    (SELECT COUNT(*) FROM usuarios) as 'Total Usuarios',
    (SELECT COUNT(*) FROM materias) as 'Total Materias',
    (SELECT COUNT(*) FROM inscripciones) as 'Total Inscripciones',
    (SELECT COUNT(*) FROM actividades) as 'Total Actividades',
    (SELECT COUNT(*) FROM calificaciones) as 'Total Calificaciones';

SELECT '=== USUARIOS DISPONIBLES (Contraseña: 123) ===' as Info;

SELECT 
    CONCAT(nombre, ' ', apellido) as 'Nombre Completo',
    email as 'Email',
    rol as 'Rol',
    '123' as 'Contraseña'
FROM usuarios
ORDER BY 
    CASE rol
        WHEN 'administrador' THEN 1
        WHEN 'maestro' THEN 2
        WHEN 'estudiante' THEN 3
    END,
    id_usuario;