
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Base de datos: `4681910_plataforma`
--
CREATE DATABASE IF NOT EXISTS `4681910_plataforma` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `4681910_plataforma`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `actividades`
--

CREATE TABLE `actividades` (
  `id_actividad` int NOT NULL,
  `id_materia` int NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `descripcion` text,
  `fecha_limite` datetime NOT NULL,
  `ponderacion` decimal(5,2) NOT NULL COMMENT 'Porcentaje que vale (ej: 15.50)',
  `tipo` enum('tarea','examen','proyecto','participacion') DEFAULT 'tarea',
  `fecha_creacion` datetime DEFAULT CURRENT_TIMESTAMP,
  `activa` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `actividades`
--

INSERT INTO `actividades` (`id_actividad`, `id_materia`, `titulo`, `descripcion`, `fecha_limite`, `ponderacion`, `tipo`, `fecha_creacion`, `activa`) VALUES
(1, 1, 'Tarea 1: HTML y CSS Básico', 'Crear una página web con HTML y CSS siguiendo las especificaciones del documento', '2024-11-20 23:59:00', 10.00, 'tarea', '2025-11-14 23:50:36', 1),
(2, 1, 'Proyecto 1: Sitio Web Responsivo', 'Desarrollar un sitio web completo con diseño responsivo', '2024-12-05 23:59:00', 25.00, 'proyecto', '2025-11-14 23:50:36', 1),
(3, 1, 'Examen Parcial', 'Examen teórico-práctico de HTML, CSS y JavaScript', '2024-11-25 10:00:00', 30.00, 'examen', '2025-11-14 23:50:36', 1),
(4, 1, 'Tarea 2: JavaScript Interactivo', 'Crear una aplicación web interactiva con JavaScript', '2024-11-15 23:59:00', 15.00, 'tarea', '2025-11-14 23:50:36', 1),
(5, 2, 'Tarea 1: Modelo Entidad-Relación', 'Diseñar el modelo ER para un sistema de biblioteca', '2024-11-18 23:59:00', 12.00, 'tarea', '2025-11-14 23:50:36', 1),
(6, 2, 'Proyecto: Base de Datos Completa', 'Implementar una base de datos completa con consultas', '2024-12-10 23:59:00', 30.00, 'proyecto', '2025-11-14 23:50:36', 1),
(7, 2, 'Examen Parcial SQL', 'Examen práctico de consultas SQL', '2024-11-22 09:00:00', 25.00, 'examen', '2025-11-14 23:50:36', 1),
(8, 3, 'Tarea 1: Límites y Continuidad', 'Resolver ejercicios del capítulo 2', '2024-11-19 23:59:00', 10.00, 'tarea', '2025-11-14 23:50:36', 1),
(9, 3, 'Examen Parcial', 'Examen de cálculo diferencial', '2024-11-28 08:00:00', 35.00, 'examen', '2025-11-14 23:50:36', 1),
(10, 3, 'Tarea 2: Derivadas', 'Problemas de aplicación de derivadas', '2024-12-03 23:59:00', 10.00, 'tarea', '2025-11-14 23:50:36', 1),
(11, 4, 'Tarea 1: Listas Enlazadas', 'Implementar una lista enlazada en Java', '2024-11-17 23:59:00', 15.00, 'tarea', '2025-11-14 23:50:36', 1),
(12, 4, 'Proyecto: Implementación de Árbol', 'Crear una implementación completa de árbol binario', '2024-12-08 23:59:00', 30.00, 'proyecto', '2025-11-14 23:50:36', 1),
(13, 5, 'Tarea 1: Traducción de Manual', 'Traducir manual técnico del inglés', '2024-11-21 23:59:00', 20.00, 'tarea', '2025-11-14 23:50:36', 1),
(14, 5, 'Examen Oral', 'Presentación en inglés sobre un tema técnico', '2024-11-30 10:00:00', 30.00, 'examen', '2025-11-14 23:50:36', 1),
(15, 6, 'Tarea 1: Modelo OSI', 'Investigar y documentar el modelo OSI', '2024-11-16 23:59:00', 10.00, 'tarea', '2025-11-14 23:50:36', 1),
(16, 6, 'Proyecto: Configuración de Red', 'Configurar una red local con servicios', '2024-12-12 23:59:00', 35.00, 'proyecto', '2025-11-14 23:50:36', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `calificaciones`
--

CREATE TABLE `calificaciones` (
  `id_calificacion` int NOT NULL,
  `id_estudiante` int NOT NULL,
  `id_actividad` int NOT NULL,
  `calificacion` decimal(5,2) DEFAULT NULL COMMENT 'Calificación numérica (0-100)',
  `comentarios` text,
  `fecha_entrega` datetime DEFAULT NULL,
  `fecha_calificacion` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `calificaciones`
--

INSERT INTO `calificaciones` (`id_calificacion`, `id_estudiante`, `id_actividad`, `calificacion`, `comentarios`, `fecha_entrega`, `fecha_calificacion`) VALUES
(1, 5, 1, 95.00, 'Excelente trabajo, muy bien estructurado', '2024-11-19 20:30:00', '2024-11-20 10:00:00'),
(2, 5, 4, 88.00, 'Buen trabajo, pero puede mejorar la lógica', '2024-11-14 22:00:00', '2024-11-15 14:00:00'),
(3, 5, 5, 92.00, 'Modelo bien diseñado y documentado', '2024-11-17 19:00:00', '2024-11-18 09:00:00'),
(4, 6, 1, 87.00, 'Buen diseño, falta un poco de creatividad', '2024-11-20 18:00:00', '2024-11-21 11:00:00'),
(5, 6, 4, 91.00, 'Código limpio y bien comentado', '2024-11-15 21:00:00', '2024-11-16 10:00:00'),
(6, 6, 8, 85.00, 'Respuestas correctas, revisa la sección 3', '2024-11-19 23:00:00', '2024-11-20 08:00:00'),
(7, 7, 5, 78.00, 'Faltan algunas relaciones importantes', '2024-11-18 23:30:00', '2024-11-19 13:00:00'),
(8, 7, 11, 82.00, 'Implementación correcta pero ineficiente', '2024-11-16 20:00:00', '2024-11-17 15:00:00'),
(9, 8, 1, 96.00, 'Excelente, uno de los mejores trabajos', '2024-11-18 17:00:00', '2024-11-19 09:00:00'),
(10, 8, 4, 94.00, 'Perfecto manejo de JavaScript', '2024-11-14 19:00:00', '2024-11-15 11:00:00'),
(11, 8, 8, 89.00, 'Muy buen dominio del tema', '2024-11-18 22:00:00', '2024-11-19 10:00:00'),
(12, 9, 5, 90.00, 'Excelente modelo, bien normalizado', '2024-11-17 21:00:00', '2024-11-18 14:00:00'),
(13, 10, 1, 83.00, 'Buen trabajo, revisa la validación de formularios', '2024-11-20 22:00:00', '2024-11-21 09:00:00'),
(14, 10, 4, 86.00, 'Funcionalidad correcta, mejorar la UI', '2024-11-15 20:00:00', '2024-11-16 12:00:00'),
(15, 10, 13, 88.00, 'Buena investigación del tema', '2024-11-15 18:00:00', '2024-11-16 10:00:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inscripciones`
--

CREATE TABLE `inscripciones` (
  `id_inscripcion` int NOT NULL,
  `id_estudiante` int NOT NULL,
  `id_materia` int NOT NULL,
  `estado` enum('pendiente','aprobado','rechazado') DEFAULT 'pendiente',
  `motivo_rechazo` text,
  `fecha_solicitud` datetime DEFAULT CURRENT_TIMESTAMP,
  `fecha_respuesta` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `inscripciones`
--

INSERT INTO `inscripciones` (`id_inscripcion`, `id_estudiante`, `id_materia`, `estado`, `motivo_rechazo`, `fecha_solicitud`, `fecha_respuesta`) VALUES
(1, 5, 1, 'aprobado', NULL, '2024-08-15 10:00:00', '2024-08-15 14:00:00'),
(2, 5, 2, 'aprobado', NULL, '2024-08-15 10:05:00', '2024-08-15 14:05:00'),
(3, 5, 3, 'pendiente', NULL, '2024-11-10 09:00:00', NULL),
(4, 6, 1, 'aprobado', NULL, '2024-08-16 11:00:00', '2024-08-16 15:00:00'),
(5, 6, 3, 'aprobado', NULL, '2024-08-16 11:10:00', '2024-08-16 15:10:00'),
(6, 6, 5, 'rechazado', NULL, '2024-09-01 10:00:00', '2024-09-02 09:00:00'),
(7, 7, 2, 'aprobado', NULL, '2024-08-17 09:30:00', '2024-08-17 16:00:00'),
(8, 7, 4, 'aprobado', NULL, '2024-08-17 09:35:00', '2024-08-17 16:05:00'),
(9, 7, 1, 'aprobado', NULL, '2024-11-11 08:00:00', '2025-11-15 00:08:58'),
(10, 8, 1, 'aprobado', NULL, '2024-08-18 10:00:00', '2024-08-18 14:00:00'),
(11, 8, 3, 'aprobado', NULL, '2024-08-18 10:10:00', '2024-08-18 14:10:00'),
(12, 8, 5, 'aprobado', NULL, '2024-08-18 10:15:00', '2024-08-18 14:15:00'),
(13, 9, 2, 'aprobado', NULL, '2024-08-19 11:00:00', '2024-08-19 15:00:00'),
(14, 9, 4, 'pendiente', NULL, '2024-11-09 10:00:00', NULL),
(15, 10, 1, 'aprobado', NULL, '2024-08-20 09:00:00', '2024-08-20 13:00:00'),
(16, 10, 6, 'aprobado', NULL, '2024-08-20 09:10:00', '2024-08-20 13:10:00'),
(17, 5, 4, 'pendiente', NULL, '2025-11-15 00:09:41', NULL),
(18, 5, 5, 'aprobado', NULL, '2025-11-15 19:52:56', '2025-11-18 16:26:08'),
(24, 5, 6, 'aprobado', NULL, '2025-11-18 16:10:39', '2025-11-18 16:26:05');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `logros`
--

CREATE TABLE `logros` (
  `id_logro` int NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text,
  `icono` varchar(255) DEFAULT NULL,
  `criterio` varchar(200) DEFAULT NULL COMMENT 'ej: "promedio_alto", "cumplido_tiempo", "constancia"'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `logros`
--

INSERT INTO `logros` (`id_logro`, `nombre`, `descripcion`, `icono`, `criterio`) VALUES
(1, 'Primera Entrega', 'Entregaste tu primera tarea a tiempo', '🎯', 'primera_entrega'),
(2, 'Promedio Alto', 'Mantuviste un promedio mayor a 90', '⭐', 'promedio_alto'),
(3, 'Racha Perfecta', 'Entregaste 5 tareas consecutivas a tiempo', '🔥', 'racha_perfecta'),
(4, 'Estudiante Constante', 'Completaste todas las actividades del mes', '💪', 'constancia'),
(5, 'Excelencia Académica', 'Obtuviste calificación perfecta en un examen', '🏆', 'excelencia'),
(6, 'Participación Activa', 'Participaste en todas las clases del mes', '🗣️', 'participacion');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `materias`
--

CREATE TABLE `materias` (
  `id_materia` int NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `descripcion` text,
  `id_maestro` int NOT NULL,
  `cuatrimestre` varchar(20) DEFAULT NULL,
  `creditos` int DEFAULT '0',
  `fecha_creacion` datetime DEFAULT CURRENT_TIMESTAMP,
  `activa` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `materias`
--

INSERT INTO `materias` (`id_materia`, `nombre`, `descripcion`, `id_maestro`, `cuatrimestre`, `creditos`, `fecha_creacion`, `activa`) VALUES
(1, 'Programación Web', 'Desarrollo de aplicaciones web con HTML, CSS, JavaScript y PHP', 2, '3er Cuatrimestre', 8, '2025-11-14 23:50:36', 1),
(2, 'Bases de Datos', 'Diseño e implementación de bases de datos relacionales', 2, '3er Cuatrimestre', 7, '2025-11-14 23:50:36', 1),
(3, 'Matemáticas Avanzadas', 'Cálculo diferencial e integral aplicado', 3, '2do Cuatrimestre', 8, '2025-11-14 23:50:36', 1),
(4, 'Estructuras de Datos', 'Listas, árboles, grafos y algoritmos de búsqueda', 3, '4to Cuatrimestre', 7, '2025-11-14 23:50:36', 1),
(5, 'Inglés Técnico', 'Comprensión de textos técnicos en inglés', 4, '1er Cuatrimestre', 5, '2025-11-14 23:50:36', 1),
(6, 'Redes de Computadoras', 'Fundamentos de redes y protocolos de comunicación', 4, '5to Cuatrimestre', 6, '2025-11-14 23:50:36', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `password_reset`
--

CREATE TABLE `password_reset` (
  `id` int NOT NULL,
  `id_usuario` int NOT NULL,
  `token` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_expiracion` datetime NOT NULL,
  `usado` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('administrador','maestro','estudiante') NOT NULL,
  `fecha_registro` datetime DEFAULT CURRENT_TIMESTAMP,
  `activo` tinyint(1) DEFAULT '1',
  `foto_perfil` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nombre`, `apellido`, `email`, `password`, `rol`, `fecha_registro`, `activo`, `foto_perfil`) VALUES
(1, 'Juan', 'Pérez', 'admin@plataforma.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5NANc6jH8fHOS', 'administrador', '2025-11-14 23:50:36', 1, NULL),
(2, 'María', 'González', 'maria.gonzalez@plataforma.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5NANc6jH8fHOS', 'maestro', '2025-11-14 23:50:36', 1, NULL),
(3, 'Carlos', 'Rodríguez', 'carlos.rodriguez@plataforma.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5NANc6jH8fHOS', 'maestro', '2025-11-14 23:50:36', 1, NULL),
(4, 'Ana', 'Martínez', 'ana.martinez@plataforma.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5NANc6jH8fHOS', 'maestro', '2025-11-14 23:50:36', 1, NULL),
(5, 'Pedro', 'López', 'pedro.lopez@plataforma.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5NANc6jH8fHOS', 'estudiante', '2025-11-14 23:50:36', 1, NULL),
(6, 'Laura', 'Sánchez', 'laura.sanchez@plataforma.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5NANc6jH8fHOS', 'estudiante', '2025-11-14 23:50:36', 1, NULL),
(7, 'Miguel', 'Torres', 'miguel.torres@plataforma.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5NANc6jH8fHOS', 'estudiante', '2025-11-14 23:50:36', 1, NULL),
(8, 'Sofía', 'Ramírez', 'sofia.ramirez@plataforma.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5NANc6jH8fHOS', 'estudiante', '2025-11-14 23:50:36', 1, NULL),
(9, 'Diego', 'Flores', 'diego.flores@plataforma.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5NANc6jH8fHOS', 'estudiante', '2025-11-14 23:50:36', 1, NULL),
(10, 'Valentina', 'Morales', 'valentina.morales@plataforma.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5NANc6jH8fHOS', 'estudiante', '2025-11-14 23:50:36', 1, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios_logros`
--

CREATE TABLE `usuarios_logros` (
  `id` int NOT NULL,
  `id_usuario` int NOT NULL,
  `id_logro` int NOT NULL,
  `fecha_obtencion` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `usuarios_logros`
--

INSERT INTO `usuarios_logros` (`id`, `id_usuario`, `id_logro`, `fecha_obtencion`) VALUES
(1, 5, 1, '2024-11-14 10:00:00'),
(2, 5, 2, '2024-11-20 15:00:00'),
(3, 6, 1, '2024-11-15 11:00:00'),
(4, 8, 1, '2024-11-14 12:00:00'),
(5, 8, 2, '2024-11-21 10:00:00'),
(6, 8, 5, '2024-11-19 14:00:00'),
(7, 10, 1, '2024-11-15 09:00:00');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `actividades`
--
ALTER TABLE `actividades`
  ADD PRIMARY KEY (`id_actividad`),
  ADD KEY `idx_actividad_materia` (`id_materia`),
  ADD KEY `idx_fecha_limite` (`fecha_limite`);

--
-- Indices de la tabla `calificaciones`
--
ALTER TABLE `calificaciones`
  ADD PRIMARY KEY (`id_calificacion`),
  ADD UNIQUE KEY `unique_calificacion` (`id_estudiante`,`id_actividad`),
  ADD KEY `id_actividad` (`id_actividad`),
  ADD KEY `idx_calificacion_estudiante` (`id_estudiante`);

--
-- Indices de la tabla `inscripciones`
--
ALTER TABLE `inscripciones`
  ADD PRIMARY KEY (`id_inscripcion`),
  ADD UNIQUE KEY `unique_inscripcion` (`id_estudiante`,`id_materia`),
  ADD KEY `idx_inscripcion_estado` (`estado`),
  ADD KEY `idx_estudiante` (`id_estudiante`),
  ADD KEY `idx_materia` (`id_materia`);

--
-- Indices de la tabla `logros`
--
ALTER TABLE `logros`
  ADD PRIMARY KEY (`id_logro`);

--
-- Indices de la tabla `materias`
--
ALTER TABLE `materias`
  ADD PRIMARY KEY (`id_materia`),
  ADD KEY `idx_materia_maestro` (`id_maestro`),
  ADD KEY `idx_activa` (`activa`);

--
-- Indices de la tabla `password_reset`
--
ALTER TABLE `password_reset`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `idx_token` (`token`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_rol` (`rol`);

--
-- Indices de la tabla `usuarios_logros`
--
ALTER TABLE `usuarios_logros`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_usuario_logro` (`id_usuario`,`id_logro`),
  ADD KEY `id_logro` (`id_logro`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `actividades`
--
ALTER TABLE `actividades`
  MODIFY `id_actividad` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `calificaciones`
--
ALTER TABLE `calificaciones`
  MODIFY `id_calificacion` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `inscripciones`
--
ALTER TABLE `inscripciones`
  MODIFY `id_inscripcion` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de la tabla `logros`
--
ALTER TABLE `logros`
  MODIFY `id_logro` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `materias`
--
ALTER TABLE `materias`
  MODIFY `id_materia` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `password_reset`
--
ALTER TABLE `password_reset`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `usuarios_logros`
--
ALTER TABLE `usuarios_logros`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `actividades`
--
ALTER TABLE `actividades`
  ADD CONSTRAINT `actividades_ibfk_1` FOREIGN KEY (`id_materia`) REFERENCES `materias` (`id_materia`) ON DELETE CASCADE;

--
-- Filtros para la tabla `calificaciones`
--
ALTER TABLE `calificaciones`
  ADD CONSTRAINT `calificaciones_ibfk_1` FOREIGN KEY (`id_estudiante`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE,
  ADD CONSTRAINT `calificaciones_ibfk_2` FOREIGN KEY (`id_actividad`) REFERENCES `actividades` (`id_actividad`) ON DELETE CASCADE;

--
-- Filtros para la tabla `inscripciones`
--
ALTER TABLE `inscripciones`
  ADD CONSTRAINT `inscripciones_ibfk_1` FOREIGN KEY (`id_estudiante`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE,
  ADD CONSTRAINT `inscripciones_ibfk_2` FOREIGN KEY (`id_materia`) REFERENCES `materias` (`id_materia`) ON DELETE CASCADE;

--
-- Filtros para la tabla `materias`
--
ALTER TABLE `materias`
  ADD CONSTRAINT `materias_ibfk_1` FOREIGN KEY (`id_maestro`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `password_reset`
--
ALTER TABLE `password_reset`
  ADD CONSTRAINT `password_reset_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `usuarios_logros`
--
ALTER TABLE `usuarios_logros`
  ADD CONSTRAINT `usuarios_logros_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE,
  ADD CONSTRAINT `usuarios_logros_ibfk_2` FOREIGN KEY (`id_logro`) REFERENCES `logros` (`id_logro`) ON DELETE CASCADE;
COMMIT;
