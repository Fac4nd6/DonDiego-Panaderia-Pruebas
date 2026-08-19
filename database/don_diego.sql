-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 19-08-2026 a las 19:25:55
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
  `metodo_pago` enum('efectivo','mercado_pago') NOT NULL DEFAULT 'efectivo',
  `estado` enum('pendiente','confirmado','en_preparacion','listo','entregado','cancelado') NOT NULL DEFAULT 'pendiente',
  `total` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `pedidos`
--

INSERT INTO `pedidos` (`id`, `usuario_id`, `fecha_pedido`, `fecha_recepcion`, `franja_horaria`, `direccion_entrega`, `metodo_pago`, `estado`, `total`) VALUES
(1, 1, '2026-08-18 19:40:07', '2026-08-20', '12:00 - 14:00', 'Salto, Uruguay 1348', 'efectivo', 'pendiente', 360.00),
(2, 1, '2026-08-18 19:43:20', '2026-08-28', '12:00 - 14:00', 'Salto, Uruguay 1348', 'efectivo', 'pendiente', 180.00),
(3, 1, '2026-08-18 19:47:26', '2026-08-18', '10:00 - 12:00', 'Salto, Uruguay 1348', 'efectivo', 'pendiente', 180.00),
(4, 1, '2026-08-18 19:48:26', '2026-08-31', '12:00 - 14:00', 'Salto, Uruguay 1348', 'efectivo', 'pendiente', 130.00),
(5, 2, '2026-08-19 10:19:18', '2026-08-28', '12:00 - 14:00', 'Salto, jgjhhghjg 2123', 'efectivo', 'pendiente', 6300.00),
(6, 2, '2026-08-19 10:22:31', '2026-08-26', '14:00 - 16:00', 'Salto, sfsdfsd 1234', 'efectivo', 'pendiente', 3900.00),
(7, 4, '2026-08-19 10:36:31', '2026-09-15', '12:00 - 14:00', 'Salto, juan abreu 1313, tiene 99 pisos', 'efectivo', 'pendiente', 12870.00);

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
(1, 1, 21, 2, 180.00, 360.00),
(2, 2, 21, 1, 180.00, 180.00),
(3, 3, 22, 1, 180.00, 180.00),
(4, 4, 17, 1, 130.00, 130.00),
(5, 5, 22, 4, 180.00, 720.00),
(6, 5, 21, 6, 180.00, 1080.00),
(7, 5, 14, 6, 750.00, 4500.00),
(8, 6, 20, 6, 650.00, 3900.00),
(9, 7, 17, 99, 130.00, 12870.00);

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
(15, 'Yema quemada', 'Postre artesanal con yema quemada.', 550.00, 4, 'yema-quemada.avif', 1, '2026-08-17 17:34:48', '2026-08-17 23:03:19'),
(16, 'Yema quemada roja', 'Postre artesanal de yema quemada.', 550.00, 4, 'yema-quemada-roja.avif', 1, '2026-08-17 17:34:48', '2026-08-17 17:34:48'),
(17, 'Empanadas', 'Empanadas caseras elaboradas artesanalmente.', 130.00, 2, 'empanadas.avif', 1, '2026-08-17 17:34:48', '2026-08-17 23:03:14'),
(18, 'Arrollado de carne', 'Arrollado artesanal relleno de carne.', 650.00, 2, 'arrollado-carne.avif', 0, '2026-08-17 17:34:48', '2026-08-17 23:15:05'),
(19, 'Arrollado de pollo', 'Arrollado artesanal relleno de pollo.', 650.00, 2, 'arrollado-pollo.avif', 0, '2026-08-17 17:34:48', '2026-08-17 23:15:01'),
(20, 'Arrollado', 'Arrollado artesanal de Don Diego.', 650.00, 2, 'arrollado.avif', 1, '2026-08-17 17:34:48', '2026-08-17 17:34:48'),
(21, 'Sándwiches', 'Sándwiches artesanales preparados por Don Diego.', 180.00, 2, 'sandwiches.avif', 1, '2026-08-17 17:34:48', '2026-08-17 23:03:06'),
(22, 'Pan casero', 'Pan artesanal recién horneado.', 180.00, 5, 'producto_6a8397d2024dd0.57171791.jpg', 1, '2026-08-17 17:34:48', '2026-08-19 10:52:09');

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
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre_completo`, `nombre_comercio`, `email`, `telefono`, `direccion`, `password`, `rol`, `fecha_registro`) VALUES
(1, 'Facundo Leites', NULL, 'fac4nd6@gmail.com', NULL, NULL, '$2y$10$irfcPZofXadQA0.q9.U76uZXp.LbIwmNNp2LWUMwcVndGYTWl0r.G', 'admin', '2026-08-17 12:43:07'),
(2, 'hoola', NULL, 'hola@gmail.com', NULL, NULL, '$2y$10$94.JpJaGHGIrI6ZxktaoSuBi1YZnj19I2wHWQMdnBxdOPy/U1i1Mi', 'cliente', '2026-08-19 10:16:33'),
(3, 'a', NULL, 'e@gmail.com', NULL, NULL, '$2y$10$Fn5IljyMwC4JxlFIRZM8X.yr6V5DZ/YSInu/QNNXaLjcDTFn941xG', 'cliente', '2026-08-19 10:30:18'),
(4, 'j', NULL, 'juan@gmail.com', NULL, NULL, '$2y$10$R//bmgMRFmAIHwnws4cA5.r3n0dqgnEpP45vFLRq2ZLrKITNWWWk6', 'cliente', '2026-08-19 10:31:19');

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
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `pedido_detalles`
--
ALTER TABLE `pedido_detalles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
