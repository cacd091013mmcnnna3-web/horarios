-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 30-09-2026 a las 19:20:59
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `bd_asistencia`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `docentes`
--

CREATE TABLE `docentes` (
  `id_docente` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `correo` varchar(150) DEFAULT NULL,
  `estatus` enum('Activo','Inactivo') DEFAULT 'Activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `docentes`
--

INSERT INTO `docentes` (`id_docente`, `nombre`, `correo`, `estatus`) VALUES
(1, 'LIC. ALAN JAFET SANCHEZ IGNACIO', '', 'Activo'),
(2, 'LIC. LUIS GERARDO LUNA VALDÉS', NULL, 'Activo'),
(3, 'MTRA MARIA IVONNE LAGUNAS GONZALEZ', NULL, 'Activo'),
(4, 'MTRA. NOELIA TRONCOSO FABIAN', NULL, 'Activo'),
(5, 'LIC. MARIA GUADALUPE SOLANO PALMA', NULL, 'Activo'),
(6, 'LIC. MARIA GUADALUPE FLORES COLÍN', NULL, 'Activo'),
(7, 'LIC. FABIAN ALEJANDRO GREEN ARREDONDO', NULL, 'Activo'),
(8, 'DR. RICARDO ALFONSO OSORIO QUINTANA', NULL, 'Activo'),
(9, 'MTRO. SAÚL REYES JUAREZ', NULL, 'Inactivo'),
(10, 'DRA ROSA MARIA GONZALEZ CALLEJA', NULL, 'Activo'),
(11, 'LIC. KARINA SANDÍN ESCUTIA', NULL, 'Activo'),
(12, 'MTRA. KARLA JANENCYN CAZAREZ PÉREZ', NULL, 'Activo'),
(13, 'DRA. JOANA IVONNE JUAREZ LUNA', NULL, 'Activo'),
(14, 'LIC MARIAN GISEL MARTINEZ LEÓN', NULL, 'Activo'),
(15, 'MTRO. J. TRINIDAD VALENZUELA', NULL, 'Inactivo'),
(16, 'LIC. BRENDA DANIELA BLACIO ZAVALA', NULL, 'Activo'),
(17, 'LIC. JAIR DANIEL VALDEZ CRUZ', NULL, 'Activo'),
(18, 'LIC. YUNUEN AGUILAR CORTES', NULL, 'Activo'),
(19, 'DR. MIGUEL ÁNGEL SÁNCHEZ LÓPEZ', NULL, 'Activo'),
(20, 'DRA. PERLA CELESTRE PIÑA REYES', NULL, 'Activo'),
(21, 'MTRA. NORMA ANGELICA TREJO BARRERA', NULL, 'Activo'),
(22, 'MTRA. PATRICIA JAZMÍN ZARATE ARELLANO', NULL, 'Activo'),
(23, 'LIC. KAREN CASTILLO GAYTAN', NULL, 'Activo'),
(24, 'LIC. ITZEL GUADALUPE VERON LLAGANO', NULL, 'Activo'),
(25, 'MTRA ESPERANZA SOTO PADILLA', NULL, 'Activo'),
(26, 'C.P ABELARDO GONZALEZ ITURIO', NULL, 'Activo'),
(27, 'MTRA. MARIBEL RAMOS HUERTA', NULL, 'Activo'),
(28, 'MTRO. BRAULIO ANDRÉS GOMEZ PEREZ', NULL, 'Activo'),
(29, 'DR. GUADALUPE ESTRADA MARTÍNEZ', NULL, 'Activo'),
(30, 'C.P RUBÉN ELIAS CEJA JIMENEZ', NULL, 'Activo'),
(31, 'C.P AGUSTIN EDUARDO MONTIEL PATIÑO', NULL, 'Activo'),
(32, 'PROF. ADRIAN ALFREDO RUIZ SANCHEZ', NULL, 'Inactivo'),
(33, 'LIC. JESSICA GABRIELA CAMPOS OJEDA', NULL, 'Activo'),
(34, 'TEACH. ARMANDO CADENA CHAVERO', NULL, 'Activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `horarios`
--

CREATE TABLE `horarios` (
  `id_horario` int(11) NOT NULL,
  `carrera` varchar(100) NOT NULL,
  `cuatrimestre` varchar(50) NOT NULL,
  `asignatura` varchar(120) NOT NULL,
  `semestre` int(2) DEFAULT NULL,
  `id_docente` int(11) NOT NULL,
  `dia_semana` varchar(20) NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `horarios`
--

INSERT INTO `horarios` (`id_horario`, `carrera`, `cuatrimestre`, `asignatura`, `semestre`, `id_docente`, `dia_semana`, `hora_inicio`, `hora_fin`) VALUES
(1, 'Lic. en Criminología', '1° Cuatrimestre', 'INTRODUCCIÓN AL ESTUDIO DEL DERECHO', NULL, 1, 'Sábado', '07:00:00', '08:30:00'),
(2, 'Lic. en Derecho', '3° Cuatrimestre', 'DERECHO CIVIL I', NULL, 2, 'Sábado', '07:00:00', '08:30:00'),
(3, 'Lic. en Derecho', '3° Cuatrimestre', 'CONTRATOS MERCANTILES', NULL, 3, 'Sábado', '08:30:00', '10:00:00'),
(4, 'Lic. en Psicología', '2° Cuatrimestre', 'TEORÍAS Y SISTEMAS CONTEMPORÁNEOS EN PSICOLOGÍA', NULL, 3, 'Sábado', '10:30:00', '12:00:00'),
(5, 'Lic. en Pedagogía', '1° Cuatrimestre', 'TEORÍA PEDAGÓGICA I', NULL, 4, 'Sábado', '08:30:00', '10:00:00'),
(6, 'Lic. en Derecho', '3° Cuatrimestre', 'INGLÉS III', NULL, 5, 'Sábado', '12:00:00', '13:30:00'),
(7, 'Lic. en Derecho', '3° Cuatrimestre', 'TEORÍA GENERAL DEL ESTADO', NULL, 6, 'Sábado', '13:30:00', '15:00:00'),
(8, 'Lic. en Derecho', '3° Cuatrimestre', 'DERECHO ECONÓMICO', NULL, 7, 'Sábado', '15:00:00', '16:30:00'),
(9, 'Lic. en Criminología', '5° Cuatrimestre', 'DACTILOSCOPIA Y LOFOSCOPIA', NULL, 8, 'Sábado', '07:00:00', '08:30:00'),
(10, 'Lic. en Criminología', '5° Cuatrimestre', 'ANÁLISIS DE FLUIDOS CORPORALES', NULL, 8, 'Sábado', '12:00:00', '13:30:00'),
(11, 'Lic. en Pedagogía', '1° Cuatrimestre', 'DESARROLLO HUMANO I', NULL, 9, 'Sábado', '10:30:00', '12:00:00'),
(12, 'Lic. en Psicología', '2° Cuatrimestre', 'TEORÍAS DEL APRENDIZAJE', NULL, 9, 'Sábado', '13:30:00', '15:00:00'),
(13, 'Lic. en Contador Público', '3° Cuatrimestre', 'COMUNICACIÓN ORAL Y ESCRITA', NULL, 10, 'Sábado', '15:00:00', '16:30:00'),
(14, 'Lic. en Pedagogía', '2° Cuatrimestre', 'PSICOLOGÍA EDUCATIVA', NULL, 11, 'Sábado', '07:00:00', '08:30:00'),
(15, 'Lic. en Derecho', '6° Cuatrimestre', 'DERECHO FISCAL I', NULL, 12, 'Sábado', '15:00:00', '16:30:00'),
(16, 'Lic. en Pedagogía', '8° Cuatrimestre', 'PRÁCTICA DE EDUCACIÓN ESPECIAL', NULL, 13, 'Sábado', '07:00:00', '08:30:00'),
(17, 'Lic. en Contador Público', '3° Cuatrimestre', 'DERECHO III', NULL, 14, 'Sábado', '12:00:00', '13:30:00'),
(18, 'Lic. en Pedagogía', '1° Cuatrimestre', 'PENSAMIENTO Y LENGUAJE', NULL, 15, 'Sábado', '07:00:00', '08:30:00'),
(19, 'Lic. en Contador Público', '3° Cuatrimestre', 'CONTABILIDAD III', NULL, 16, 'Sábado', '08:30:00', '10:00:00'),
(20, 'Lic. en Derecho', '4° Cuatrimestre', 'DERECHO PENAL II', NULL, 17, 'Sábado', '08:30:00', '10:00:00'),
(21, 'Lic. en Pedagogía', '1° Cuatrimestre', 'DIDÁCTICA', NULL, 18, 'Sábado', '12:00:00', '13:30:00'),
(22, 'Lic. en Derecho', '6° Cuatrimestre', 'DERECHO DEL TRABAJO II', NULL, 19, 'Sábado', '10:30:00', '12:00:00'),
(23, 'Lic. en Derecho', '6° Cuatrimestre', 'DERECHO CIVIL IV', NULL, 20, 'Sábado', '08:30:00', '10:00:00'),
(24, 'Lic. en Pedagogía', '1° Cuatrimestre', 'TEORÍAS DEL APRENDIZAJE', NULL, 21, 'Sábado', '13:30:00', '15:00:00'),
(25, 'Lic. en Contador Público', '9° Cuatrimestre', 'ÉTICA PROFESIONAL', NULL, 22, 'Sábado', '07:00:00', '08:30:00'),
(26, 'Lic. en Criminología', '3° Cuatrimestre', 'CRIMINOLOGÍA I', NULL, 23, 'Sábado', '10:30:00', '12:00:00'),
(27, 'Lic. en Derecho', '6° Cuatrimestre', 'TALLER DE JUICIOS ORALES EN MATERIA FAMILIAR Y MERCANTIL', NULL, 24, 'Sábado', '07:00:00', '08:30:00'),
(28, 'Lic. en Pedagogía', '5° Cuatrimestre', 'EVALUACIÓN Y DIAGNÓSTICO DE LOS TRASTORNOS DE APRENDIZAJE', NULL, 25, 'Sábado', '10:30:00', '12:00:00'),
(29, 'Lic. en Psicología', '2° Cuatrimestre', 'MÉTODOS DE ESTADÍSTICA EN PSICOLOGÍA II', NULL, 26, 'Sábado', '08:30:00', '10:00:00'),
(30, 'Lic. en Contador Público', '3° Cuatrimestre', 'ECONOMÍA', NULL, 27, 'Sábado', '10:30:00', '12:00:00'),
(31, 'Lic. en Derecho', '6° Cuatrimestre', 'TEORÍA DE LA ADMINISTRACIÓN PÚBLICA III', NULL, 28, 'Sábado', '13:30:00', '15:00:00'),
(32, 'Lic. en Derecho', '9° Cuatrimestre', 'METODOLOGÍA DE LA INVESTIGACIÓN JURÍDICA', NULL, 29, 'Sábado', '08:30:00', '10:00:00'),
(33, 'Lic. en Contador Público', '3° Cuatrimestre', 'ADMINISTRACIÓN FINANCIERA I', NULL, 30, 'Sábado', '07:00:00', '08:30:00'),
(34, 'Lic. en Contador Público', '9° Cuatrimestre', 'OBLIGACIONES FISCALES II', NULL, 31, 'Sábado', '08:30:00', '10:00:00'),
(35, 'Lic. en Lenguas Extranjeras', '4° Cuatrimestre', 'LITERATURA HISPANOAMERICANA', NULL, 32, 'Sábado', '10:30:00', '12:00:00'),
(36, 'Lic. en Psicología', '2° Cuatrimestre', 'TEORÍA NEUROPSICOLÓGICA', NULL, 33, 'Sábado', '07:00:00', '08:30:00'),
(37, 'Lic. en Psicología', '2° Cuatrimestre', 'INGLÉS III', NULL, 34, 'Sábado', '12:00:00', '13:30:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `registros_asistencia`
--

CREATE TABLE `registros_asistencia` (
  `id_registro` int(11) NOT NULL,
  `id_docente` int(11) NOT NULL,
  `id_horario` int(11) DEFAULT NULL,
  `fecha` date NOT NULL,
  `hora` time DEFAULT NULL,
  `hora_entrada` time DEFAULT NULL,
  `hora_salida` time DEFAULT NULL,
  `estatus` enum('A Tiempo','Retardo','Falta','Salida Registrada') DEFAULT 'A Tiempo',
  `observaciones` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `registros_asistencia`
--

INSERT INTO `registros_asistencia` (`id_registro`, `id_docente`, `id_horario`, `fecha`, `hora`, `hora_entrada`, `hora_salida`, `estatus`, `observaciones`) VALUES
(169, 1, 1, '2026-09-30', '11:20:02', NULL, '09:53:04', 'Falta', NULL),
(170, 2, 2, '2026-09-30', NULL, NULL, '10:55:43', '', 'Salida anticipada: [Motivo de Salud / Médica] - Emergencia de salud');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('admin','usuario') NOT NULL DEFAULT 'usuario'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `usuario`, `password`, `rol`) VALUES
(5, 'user', 'user123', 'usuario'),
(6, 'admin', 'admin123', 'admin');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `docentes`
--
ALTER TABLE `docentes`
  ADD PRIMARY KEY (`id_docente`);

--
-- Indices de la tabla `horarios`
--
ALTER TABLE `horarios`
  ADD PRIMARY KEY (`id_horario`),
  ADD KEY `id_docente` (`id_docente`);

--
-- Indices de la tabla `registros_asistencia`
--
ALTER TABLE `registros_asistencia`
  ADD PRIMARY KEY (`id_registro`),
  ADD KEY `id_docente` (`id_docente`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `usuario` (`usuario`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `docentes`
--
ALTER TABLE `docentes`
  MODIFY `id_docente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT de la tabla `horarios`
--
ALTER TABLE `horarios`
  MODIFY `id_horario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT de la tabla `registros_asistencia`
--
ALTER TABLE `registros_asistencia`
  MODIFY `id_registro` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=171;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `horarios`
--
ALTER TABLE `horarios`
  ADD CONSTRAINT `fk_horarios_docentes` FOREIGN KEY (`id_docente`) REFERENCES `docentes` (`id_docente`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `registros_asistencia`
--
ALTER TABLE `registros_asistencia`
  ADD CONSTRAINT `registros_asistencia_ibfk_1` FOREIGN KEY (`id_docente`) REFERENCES `docentes` (`id_docente`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
