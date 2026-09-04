-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 03-09-2026 a las 14:10:31
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
-- Estructura de tabla para la tabla `pedidos`
--

CREATE TABLE `pedidos` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `fecha_pedido` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_recepcion` date NOT NULL,
  `franja_horaria` varchar(50) NOT NULL,
  `direccion_entrega` varchar(255) NOT NULL,
  `metodo_pago` enum('efectivo') NOT NULL DEFAULT 'efectivo',
  `estado` enum('pendiente','confirmado','en_preparacion','listo','entregado','cancelado') NOT NULL DEFAULT 'pendiente',
  `total` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `pedidos`
--

INSERT INTO `pedidos` (`id`, `usuario_id`, `fecha_pedido`, `fecha_recepcion`, `franja_horaria`, `direccion_entrega`, `metodo_pago`, `estado`, `total`) VALUES
(13, 11, '2026-08-26 15:25:26', '2026-08-28', '12:00 - 14:00', 'Salto, Uruguay 1348', 'efectivo', 'confirmado', 380.00),
(15, 12, '2026-08-26 20:37:34', '2026-08-26', '08:00 - 10:00', 'Salto, Calle de prueba 123', 'efectivo', 'confirmado', 120.00),
(16, 12, '2026-08-26 20:45:10', '2026-08-28', '12:00 - 14:00', 'Salto, Uruguay 1234', 'efectivo', 'confirmado', 200.00),
(17, 11, '2026-08-27 12:15:25', '2026-08-29', '12:00 - 14:00', 'Salto, Uruguay 1313', 'efectivo', 'confirmado', 1580.00),
(18, 11, '2026-08-27 12:15:31', '2026-08-29', '12:00 - 14:00', 'Salto, Uruguay 1313', 'efectivo', 'confirmado', 1580.00),
(23, 11, '2026-08-27 13:25:01', '2026-08-29', '14:00 - 16:00', 'Salto, Uruguay 1232', 'efectivo', 'confirmado', 490.00),
(24, 11, '2026-08-27 13:28:13', '2026-09-03', '14:00 - 16:00', 'Salto, Uruguay 1313', 'efectivo', 'confirmado', 200.00),
(25, 11, '2026-08-28 15:10:52', '2026-08-29', '18:00 - 20:00', 'Salto, Uruguay 23123', 'efectivo', 'confirmado', 180.00),
(26, 11, '2026-08-28 15:51:52', '2026-08-29', '14:00 - 16:00', 'Salto, Uruguay 1313', 'efectivo', 'confirmado', 180.00),
(27, 11, '2026-08-28 17:02:43', '2026-08-29', '12:00 - 14:00', 'Salto, Uruguay 2123', 'efectivo', 'cancelado', 1270.00),
(28, 11, '2026-08-28 18:53:00', '2026-09-05', '10:00 - 12:00', 'Salto, jgjhhghjg 1313', 'efectivo', 'confirmado', 1270.00),
(29, 11, '2026-08-29 19:28:43', '2026-08-31', '12:00 - 14:00', 'Salto, Uruguay 1234', 'efectivo', 'confirmado', 180.00),
(30, 11, '2026-08-29 19:39:30', '2026-09-04', '14:00 - 16:00', 'Salto, Uruguay 12321', 'efectivo', 'confirmado', 130.00),
(31, 11, '2026-08-29 19:50:16', '2026-08-31', '16:00 - 18:00', 'Salto, Uruguay 12312', 'efectivo', 'confirmado', 130.00),
(32, 11, '2026-08-29 19:56:11', '2026-08-31', '12:00 - 14:00', 'Salto, Uruguay 1231', 'efectivo', 'confirmado', 650.00),
(33, 11, '2026-08-29 20:09:44', '2026-09-01', '10:00 - 12:00', 'Salto, Uruguay 1231', 'efectivo', 'en_preparacion', 130.00),
(34, 11, '2026-08-29 21:07:26', '2026-09-01', '12:00 - 14:00', 'Salto, Uruguay 1234', 'efectivo', 'pendiente', 130.00),
(35, 11, '2026-08-29 21:21:52', '2026-09-04', '14:00 - 16:00', 'Salto, Uruguay 1231', 'efectivo', 'pendiente', 780.00),
(36, 11, '2026-08-31 16:02:20', '2026-09-03', '14:00 - 16:00', 'Salto, Uruguay 1234', 'efectivo', 'confirmado', 540.00),
(37, 11, '2026-08-31 17:53:50', '2026-09-04', '12:00 - 14:00', 'Salto, Uruguay 1234', 'efectivo', 'pendiente', 1080.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedido_detalles`
--

CREATE TABLE `pedido_detalles` (
  `id` int(11) NOT NULL,
  `pedido_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `pedido_detalles`
--

INSERT INTO `pedido_detalles` (`id`, `pedido_id`, `producto_id`, `cantidad`, `precio_unitario`, `subtotal`) VALUES
(15, 13, 22, 1, 200.00, 200.00),
(16, 13, 21, 1, 180.00, 180.00),
(17, 15, 1, 1, 120.00, 120.00),
(18, 16, 22, 1, 200.00, 200.00),
(19, 17, 21, 1, 180.00, 180.00),
(20, 17, 13, 2, 700.00, 1400.00),
(21, 18, 21, 1, 180.00, 180.00),
(22, 18, 13, 2, 700.00, 1400.00),
(29, 23, 17, 1, 130.00, 130.00),
(30, 23, 1, 3, 120.00, 360.00),
(31, 24, 22, 1, 200.00, 200.00),
(32, 25, 21, 1, 180.00, 180.00),
(33, 26, 21, 1, 180.00, 180.00),
(34, 27, 21, 4, 180.00, 720.00),
(35, 27, 16, 1, 550.00, 550.00),
(36, 28, 21, 4, 180.00, 720.00),
(37, 28, 16, 1, 550.00, 550.00),
(38, 29, 21, 1, 180.00, 180.00),
(39, 30, 17, 1, 130.00, 130.00),
(40, 31, 17, 1, 130.00, 130.00),
(41, 32, 20, 1, 650.00, 650.00),
(42, 33, 17, 1, 130.00, 130.00),
(43, 34, 17, 1, 130.00, 130.00),
(44, 35, 20, 1, 650.00, 650.00),
(45, 35, 17, 1, 130.00, 130.00),
(46, 36, 21, 3, 180.00, 540.00),
(47, 37, 21, 6, 180.00, 1080.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 100,
  `unidad_venta` varchar(30) NOT NULL DEFAULT 'unidad',
  `categoria_id` int(11) NOT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `nombre`, `descripcion`, `precio`, `stock`, `categoria_id`, `imagen`, `activo`, `created_at`, `updated_at`) VALUES
(1, 'Alfajores', 'Alfajores artesanales elaborados por Don Diego.', 120.00, 100, 1, 'alfajores.avif', 1, '2026-08-17 17:34:48', '2026-08-27 13:36:42'),
(2, 'Brownie', 'Brownie artesanal de chocolate.', 180.00, 100, 1, 'brownie.avif', 1, '2026-08-17 17:34:48', '2026-08-17 17:34:48'),
(3, 'Galletitas de merengue', 'Galletitas artesanales con merengue.', 150.00, 100, 1, 'galletitas-merengue.avif', 1, '2026-08-17 17:34:48', '2026-08-17 17:34:48'),
(4, 'Muffins', 'Muffins artesanales.', 150.00, 100, 1, 'muffins.avif', 1, '2026-08-17 17:34:48', '2026-08-17 17:34:48'),
(5, 'Pasta flora', 'Pasta flora artesanal.', 250.00, 100, 1, 'pasta-flora.avif', 1, '2026-08-17 17:34:48', '2026-08-17 17:34:48'),
(6, 'Torta de chocolate', 'Torta artesanal de chocolate.', 950.00, 100, 3, 'torta-chocolate.avif', 1, '2026-08-17 17:34:48', '2026-08-17 17:34:48'),
(7, 'Torta de crema', 'Torta artesanal con crema.', 950.00, 100, 3, 'torta-crema.avif', 1, '2026-08-17 17:34:48', '2026-08-17 17:34:48'),
(8, 'Torta amarilla', 'Torta artesanal especial de Don Diego.', 950.00, 100, 3, 'torta-amarilla.avif', 1, '2026-08-17 17:34:48', '2026-08-17 17:34:48'),
(9, 'Torta roja', 'Torta artesanal de color rojo.', 950.00, 100, 3, 'torta-roja.avif', 1, '2026-08-17 17:34:48', '2026-08-17 17:34:48'),
(10, 'Postre', 'Postre artesanal de Don Diego.', 450.00, 100, 4, 'postre.avif', 1, '2026-08-17 17:34:48', '2026-08-17 17:34:48'),
(11, 'Postre de chocolate', 'Postre artesanal de chocolate.', 500.00, 100, 4, 'postre-chocolate.avif', 1, '2026-08-17 17:34:48', '2026-08-17 17:34:48'),
(12, 'Postre Massini', 'Postre Massini artesanal.', 550.00, 100, 4, 'postre-massini.avif', 1, '2026-08-17 17:34:48', '2026-08-17 17:34:48'),
(13, 'Tronco de Navidad', 'Tronco de Navidad artesanal.', 700.00, 100, 4, 'tronco-navidad.avif', 1, '2026-08-17 17:34:48', '2026-08-17 17:34:48'),
(14, 'Tronco de Navidad con cerezas', 'Tronco de Navidad artesanal decorado con cerezas.', 750.00, 100, 4, 'tronco-navidad-cerezas.avif', 1, '2026-08-17 17:34:48', '2026-08-17 17:34:48'),
(15, 'Yema quemada', 'Postre artesanal con yema quemada.', 550.00, 100, 4, 'yema-quemada.avif', 1, '2026-08-17 17:34:48', '2026-08-17 23:03:19'),
(16, 'Yema quemada roja', 'Postre artesanal de yema quemada.', 550.00, 99, 4, 'yema-quemada-roja.avif', 1, '2026-08-17 17:34:48', '2026-08-28 18:53:00'),
(17, 'Empanadas', 'Empanadas caseras elaboradas artesanalmente.', 130.00, 94, 2, 'empanadas.avif', 1, '2026-08-17 17:34:48', '2026-08-29 21:21:52'),
(18, 'Arrollado de carne', 'Arrollado artesanal relleno de carne.', 650.00, 100, 2, 'arrollado-carne.avif', 0, '2026-08-17 17:34:48', '2026-08-17 23:15:05'),
(19, 'Arrollado de pollo', 'Arrollado artesanal relleno de pollo.', 650.00, 100, 2, 'arrollado-pollo.avif', 0, '2026-08-17 17:34:48', '2026-08-17 23:15:01'),
(20, 'Arrollado', 'Arrollado artesanal de Don Diego.', 650.00, 98, 2, 'arrollado.avif', 1, '2026-08-17 17:34:48', '2026-08-29 21:21:52'),
(21, 'Sándwiches', 'Sándwiches artesanales preparados por Don Diego.', 180.00, 84, 2, 'sandwiches.avif', 1, '2026-08-17 17:34:48', '2026-08-31 17:53:50'),
(22, 'Pan casero', 'Pan artesanal recién horneado.', 120.00, 10, 5, 'producto_6a8397d2024dd0.57171791.jpg', 1, '2026-08-17 17:34:48', '2026-08-31 18:04:23');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre_completo` varchar(150) NOT NULL,
  `nombre_comercio` varchar(150) DEFAULT NULL,
  `email` varchar(150) NOT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('cliente','empleado','admin') DEFAULT 'cliente',
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `email_verificado` tinyint(1) NOT NULL DEFAULT 0,
  `token_verificacion` varchar(64) DEFAULT NULL,
  `token_expira` datetime DEFAULT NULL,
  `codigo_recuperacion` varchar(255) DEFAULT NULL,
  `codigo_recuperacion_expira` datetime DEFAULT NULL,
  `ultimo_reenvio_verificacion` datetime DEFAULT NULL,
  `intentos_reenvio_verificacion` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre_completo`, `nombre_comercio`, `email`, `telefono`, `direccion`, `password`, `rol`, `fecha_registro`, `email_verificado`, `token_verificacion`, `token_expira`, `ultimo_reenvio_verificacion`, `intentos_reenvio_verificacion`) VALUES
(11, 'Facundo Leites', '', 'fac4nd6@gmail.com', '', '25 Mayo', '$2y$10$uP1ustgKzOyYBg/Bhe4M4.FCAifgJ50uILMPsqQ15JDgMQoOtcy/m', 'admin', '2026-08-25 19:36:06', 1, NULL, NULL, NULL, 0),
(12, 'Cliente', NULL, 'cliente@gmail.com', NULL, NULL, '$2y$10$7IWB.gyPzAyLI0vK9HyzGupR7yJOxhfdTV3oPdwXW/kpASnt5IPlK', 'empleado', '2026-08-25 20:08:26', 1, 'bb9a6e806f74caf4e0029e3a42f460dce2b733391ad394c7a4c611af86969a15', '2026-08-26 22:08:26', NULL, 0),
(13, 'pepe', NULL, 'pepe@gmail.com', NULL, NULL, '$2y$10$4g.2ImSrlMUe66tMr5PqburFTpo/cU0PcH6cxcpEcAi.dVCq7KCWC', 'cliente', '2026-08-25 20:40:53', 0, 'cf964e3abde8c3e8ac07ec9ac8b821c851bdd66443f234d6f7ba05c36e2321b9', '2026-08-26 22:40:53', NULL, 0),
(14, 'empleado', NULL, 'empleado@gmail.com', NULL, NULL, '$2y$10$dxA9xOh8VOaLkPH/1aB/2eaEOG0P4cQuzb/nF6z4ODYbDxhwMjlp.', 'empleado', '2026-08-25 20:50:29', 1, 'e974532dee11145209191a781913f415adffeeaf9afcf35d4d3a85c564423ecd', '2026-08-26 22:50:29', NULL, 0),
(15, 'admin', NULL, 'admin@gmail.com', NULL, NULL, '$2y$10$Ur6FnUxFn8.KxtMZLiGsKejpdjZTy5zCS1zAeluoHhmdfiIGLzY6m', 'admin', '2026-08-25 20:50:55', 1, '834939eaf9ebe7c915690b3ddc9b9eea8fa604136021f3c1783cd8ddc251830a', '2026-08-26 22:50:55', NULL, 0);

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
-- Indices de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_pedido_usuario` (`usuario_id`);

--
-- Indices de la tabla `pedido_detalles`
--
ALTER TABLE `pedido_detalles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_detalle_pedido` (`pedido_id`),
  ADD KEY `fk_detalle_producto` (`producto_id`);

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
  ADD UNIQUE KEY `unique_email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT de la tabla `pedido_detalles`
--
ALTER TABLE `pedido_detalles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD CONSTRAINT `fk_pedido_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `pedido_detalles`
--
ALTER TABLE `pedido_detalles`
  ADD CONSTRAINT `fk_detalle_pedido` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_detalle_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `fk_producto_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
