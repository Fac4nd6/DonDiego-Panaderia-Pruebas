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
// CSRF
// =========================================================

require_once __DIR__ . '/../config/Csrf.php';


// =========================================================
// MODELOS
// =========================================================

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Producto.php';
require_once __DIR__ . '/../models/Carrito.php';


$productoModel = new Producto($conn);

$carritoModel = new Carrito();


// =========================================================
// INICIALIZAR CARRITO
// =========================================================

$carritoModel->inicializar();


// =========================================================
// OBTENER ACCIÓN
// =========================================================

$accion =
    $_POST['accion']
    ?? $_GET['accion']
    ?? 'ver';


// =========================================================
// VER CARRITO
// =========================================================

if ($accion === 'ver') {

    $carrito =
        $carritoModel->obtener();

    $total =
        $carritoModel->calcularTotal();

    $cantidadProductos =
        $carritoModel->cantidadProductos();


    require __DIR__ . '/../views/carrito/index.php';

    exit;
}


// =========================================================
// AGREGAR PRODUCTO
// =========================================================

if ($accion === 'agregar') {


    // -----------------------------------------------------
    // SOLO POST
    // -----------------------------------------------------

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

        http_response_code(405);

        exit(
            'Método no permitido.'
        );
    }


    // -----------------------------------------------------
    // CSRF
    // -----------------------------------------------------

    verificar_csrf();


    $productoId =
        (int) ($_POST['producto_id'] ?? 0);

    $cantidad =
        (int) ($_POST['cantidad'] ?? 1);


    if ($productoId <= 0) {

        exit(
            'Producto no válido.'
        );
    }


    if ($cantidad <= 0) {

        $cantidad = 1;
    }


    if ($cantidad > 99) {

        $cantidad = 99;
    }


    $producto =
        $productoModel->obtenerPorId(
            $productoId
        );


    if (!$producto) {

        exit(
            'Producto no encontrado.'
        );
    }


    if ((int) $producto['activo'] !== 1) {

        exit(
            'Este producto no está disponible.'
        );
    }


    $carritoModel->agregar(
        $producto,
        $cantidad
    );


    header(
        'Location: /DonDiego-Panaderia-Pruebas/controllers/CarritoController.php?accion=ver'
    );

    exit;
}


// =========================================================
// ACTUALIZAR CANTIDAD
// =========================================================

if ($accion === 'actualizar') {


    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

        http_response_code(405);

        exit(
            'Método no permitido.'
        );
    }


    verificar_csrf();


    $productoId =
        (int) ($_POST['producto_id'] ?? 0);

    $cantidad =
        (int) ($_POST['cantidad'] ?? 0);


    if ($productoId <= 0) {

        exit(
            'Producto no válido.'
        );
    }


    if (
        !$carritoModel->actualizarCantidad(
            $productoId,
            $cantidad
        )
    ) {

        exit(
            'Producto no encontrado en el carrito.'
        );
    }


    header(
        'Location: /DonDiego-Panaderia-Pruebas/controllers/CarritoController.php?accion=ver'
    );

    exit;
}


// =========================================================
// ELIMINAR PRODUCTO
// =========================================================

if ($accion === 'eliminar') {


    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

        http_response_code(405);

        exit(
            'Método no permitido.'
        );
    }


    verificar_csrf();


    $productoId =
        (int) ($_POST['producto_id'] ?? 0);


    if ($productoId <= 0) {

        exit(
            'Producto no válido.'
        );
    }


    $carritoModel->eliminar(
        $productoId
    );


    header(
        'Location: /DonDiego-Panaderia-Pruebas/controllers/CarritoController.php?accion=ver'
    );

    exit;
}


// =========================================================
// VACIAR CARRITO
// =========================================================

if ($accion === 'vaciar') {


    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

        http_response_code(405);

        exit(
            'Método no permitido.'
        );
    }


    verificar_csrf();


    $carritoModel->vaciar();


    header(
        'Location: /DonDiego-Panaderia-Pruebas/controllers/CarritoController.php?accion=ver'
    );

    exit;
}


// =========================================================
// ACCIÓN NO EXISTENTE
// =========================================================

http_response_code(404);

echo 'Acción de carrito no encontrada.';