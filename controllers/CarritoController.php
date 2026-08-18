<?php

session_start();


// =========================================================
// COMPROBAR SESIÓN DE USUARIO
// =========================================================

if (!isset($_SESSION['usuario_id'])) {

    header(
        'Location: /DonDiego-Panaderia-Pruebas/views/usuarios/login.php'
    );

    exit;
}


// =========================================================
// MODELOS
// =========================================================

require_once '../config/Database.php';
require_once '../models/Producto.php';
require_once '../models/Carrito.php';


$productoModel = new Producto($conn);

$carritoModel = new Carrito();


// =========================================================
// INICIALIZAR CARRITO
// =========================================================

$carritoModel->inicializar();


// =========================================================
// OBTENER ACCIÓN
// =========================================================

$accion = $_POST['accion'] ?? $_GET['accion'] ?? 'ver';


// =========================================================
// VER CARRITO
// =========================================================

if ($accion === 'ver') {

    $carrito = $carritoModel->obtener();

    $total = $carritoModel->calcularTotal();

    $cantidadProductos =
        $carritoModel->cantidadProductos();


    require '../views/carrito/index.php';

    exit;
}


// =========================================================
// AGREGAR PRODUCTO
// =========================================================

if ($accion === 'agregar') {

    $productoId =
        (int) ($_POST['producto_id'] ?? 0);

    $cantidad =
        (int) ($_POST['cantidad'] ?? 1);


    // =====================================================
    // VALIDAR ID
    // =====================================================

    if ($productoId <= 0) {

        exit('Producto no válido.');
    }


    // =====================================================
    // VALIDAR CANTIDAD
    // =====================================================

    if ($cantidad <= 0) {

        $cantidad = 1;
    }

    if ($cantidad > 99) {

        $cantidad = 99;
    }


    // =====================================================
    // OBTENER PRODUCTO
    // =====================================================

    $producto =
        $productoModel->obtenerPorId($productoId);


    if (!$producto) {

        exit('Producto no encontrado.');
    }


    // =====================================================
    // COMPROBAR PRODUCTO ACTIVO
    // =====================================================

    if ((int) $producto['activo'] !== 1) {

        exit('Este producto no está disponible.');
    }


    // =====================================================
    // AGREGAR AL CARRITO
    // =====================================================

    $carritoModel->agregar(
        $producto,
        $cantidad
    );


    // =====================================================
    // VOLVER AL CARRITO
    // =====================================================

    header(
        'Location: /DonDiego-Panaderia-Pruebas/controllers/CarritoController.php?accion=ver'
    );

    exit;
}


// =========================================================
// ACTUALIZAR CANTIDAD
// =========================================================

if ($accion === 'actualizar') {

    $productoId =
        (int) ($_POST['producto_id'] ?? 0);

    $cantidad =
        (int) ($_POST['cantidad'] ?? 0);


    // -----------------------------------------------------
    // VALIDAR
    // -----------------------------------------------------

    if ($productoId <= 0) {

        exit('Producto no válido.');
    }


    // -----------------------------------------------------
    // ACTUALIZAR
    // -----------------------------------------------------

    if (
        !$carritoModel->actualizarCantidad(
            $productoId,
            $cantidad
        )
    ) {

        exit('Producto no encontrado en el carrito.');
    }


    // -----------------------------------------------------
    // VOLVER
    // -----------------------------------------------------

    header(
        'Location: CarritoController.php?accion=ver'
    );

    exit;
}


// =========================================================
// ELIMINAR PRODUCTO
// =========================================================

if ($accion === 'eliminar') {

    $productoId =
        (int) ($_POST['producto_id'] ?? 0);


    if ($productoId <= 0) {

        exit('Producto no válido.');
    }


    $carritoModel->eliminar($productoId);


    // -----------------------------------------------------
    // VOLVER
    // -----------------------------------------------------

    header(
        'Location: CarritoController.php?accion=ver'
    );

    exit;
}


// =========================================================
// VACIAR CARRITO
// =========================================================

if ($accion === 'vaciar') {

    $carritoModel->vaciar();


    header(
        'Location: CarritoController.php?accion=ver'
    );

    exit;
}


// =========================================================
// ACCIÓN NO EXISTENTE
// =========================================================

http_response_code(404);

echo 'Acción de carrito no encontrada.';
