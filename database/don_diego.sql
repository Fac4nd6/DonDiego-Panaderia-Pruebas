-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 17-08-2026 a las 23:41:50
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
-- Base de datos: `don_diego`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id`, `nombre`) VALUES
(1, 'Dulces'),
(5, 'Panadería'),
(4, 'Repostería'),
(2, 'Salados'),
(3, 'Tortas');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL,
  `categoria_id` int(11) NOT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `nombre`, `descripcion`, `precio`, `categoria_id`, `imagen`, `activo`, `created_at`, `updated_at`) VALUES
(1, 'Alfajores', 'Alfajores artesanales elaborados por Don Diego.', 120.00, 1, 'alfajores.avif', 1, '2026-08-17 17:34:48', '2026-08-17 17:34:48'),
(2, 'Brownie', 'Brownie artesanal de chocolate.', 180.00, 1, 'brownie.avif', 1, '2026-08-17 17:34:48', '2026-08-17 17:34:48'),
(3, 'Galletitas de merengue', 'Galletitas artesanales con merengue.', 150.00, 1, 'galletitas-merengue.avif', 1, '2026-08-17 17:34:48', '2026-08-17 17:34:48'),
(4, 'Muffins', 'Muffins artesanales.', 150.00, 1, 'muffins.avif', 1, '2026-08-17 17:34:48', '2026-08-17 17:34:48'),
(5, 'Pasta flora', 'Pasta flora artesanal.', 250.00, 1, 'pasta-flora.avif', 1, '2026-08-17 17:34:48', '2026-08-17 17:34:48'),
(6, 'Torta de chocolate', 'Torta artesanal de chocolate.', 950.00, 3, 'torta-chocolate.avif', 1, '2026-08-17 17:34:48', '2026-08-17 17:34:48'),
(7, 'Torta de crema', 'Torta artesanal con crema.', 950.00, 3, 'torta-crema.avif', 1, '2026-08-17 17:34:48', '2026-08-17 17:34:48'),
(8, 'Torta amarilla', 'Torta artesanal especial de Don Diego.', 950.00, 3, 'torta-amarilla.avif', 1, '2026-08-17 17:34:48', '2026-08-17 17:34:48'),
(9, 'Torta roja', 'Torta artesanal de color rojo.', 950.00, 3, 'torta-roja.avif', 1, '2026-08-17 17:34:48', '2026-08-17 17:34:48'),
(10, 'Postre', 'Postre artesanal de Don Diego.', 450.00, 4, 'postre.avif', 1, '2026-08-17 17:34:48', '2026-08-17 17:34:48'),
(11, 'Postre de chocolate', 'Postre artesanal de chocolate.', 500.00, 4, 'postre-chocolate.avif', 1, '2026-08-17 17:34:48', '2026-08-17 17:34:48'),
(12, 'Postre Massini', 'Postre Massini artesanal.', 550.00, 4, 'postre-massini.avif', 1, '2026-08-17 17:34:48', '2026-08-17 17:34:48'),
(13, 'Tronco de Navidad', 'Tronco de Navidad artesanal.', 700.00, 4, 'tronco-navidad.avif', 1, '2026-08-17 17:34:48', '2026-08-17 17:34:48'),
(14, 'Tronco de Navidad con cerezas', 'Tronco de Navidad artesanal decorado con cerezas.', 750.00, 4, 'tronco-navidad-cerezas.avif', 1, '2026-08-17 17:34:48', '2026-08-17 17:34:48'),
(15, 'Yema quemada', 'Postre artesanal con yema quemada.', 550.00, 4, 'yema-quemada.avif', 0, '2026-08-17 17:34:48', '2026-08-17 20:53:36'),
(16, 'Yema quemada roja', 'Postre artesanal de yema quemada.', 550.00, 4, 'yema-quemada-roja.avif', 1, '2026-08-17 17:34:48', '2026-08-17 17:34:48'),
(17, 'Empanadas', 'Empanadas caseras elaboradas artesanalmente.', 130.00, 2, 'empanadas.avif', 0, '2026-08-17 17:34:48', '2026-08-17 20:44:59'),
(18, 'Arrollado de carne', 'Arrollado artesanal relleno de carne.', 650.00, 2, 'arrollado-carne.avif', 1, '2026-08-17 17:34:48', '2026-08-17 17:34:48'),
(19, 'Arrollado de pollo', 'Arrollado artesanal relleno de pollo.', 650.00, 2, 'arrollado-pollo.avif', 0, '2026-08-17 17:34:48', '2026-08-17 20:34:27'),
(20, 'Arrollado', 'Arrollado artesanal de Don Diego.', 650.00, 2, 'arrollado.avif', 1, '2026-08-17 17:34:48', '2026-08-17 17:34:48'),
(21, 'Sándwiches', 'Sándwiches artesanales preparados por Don Diego.', 180.00, 2, 'sandwiches.avif', 1, '2026-08-17 17:34:48', '2026-08-17 21:06:14'),
(22, 'Pan casero', 'Pan artesanal recién horneado.', 180.00, 5, 'pan.avif', 1, '2026-08-17 17:34:48', '2026-08-17 20:53:18');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre_completo` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('cliente','empleado','admin') DEFAULT 'cliente',
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre_completo`, `email`, `password`, `rol`, `fecha_registro`) VALUES
(1, 'Facundo Leites', 'fac4nd6@gmail.com', '$2y$10$irfcPZofXadQA0.q9.U76uZXp.LbIwmNNp2LWUMwcVndGYTWl0r.G', 'admin', '2026-08-17 12:43:07');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_producto_categoria` (`categoria_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `fk_producto_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
