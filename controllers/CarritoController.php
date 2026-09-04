<?php

require_once __DIR__ . '/../config/Session.php';
iniciar_sesion_segura();


// =========================================================
// COMPROBAR SESIÓN DE USUARIO
// =========================================================

if (!isset($_SESSION['usuario_id'])) {

    header(
        'Location: ' . url('/login')
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

$mensajeRepetir = $_SESSION['mensaje_repetir'] ?? '';
unset($_SESSION['mensaje_repetir']);


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

    $cantidad = filter_var(
        $_POST['cantidad'] ?? null,
        FILTER_VALIDATE_INT
    );


    if ($productoId <= 0) {

        exit(
            'Producto no válido.'
        );
    }


    if ($cantidad === false || $cantidad <= 0 || $cantidad > 99) {
        http_response_code(422);
        exit('La cantidad solicitada no es válida.');
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

    $stock = (int) ($producto['stock'] ?? 0);

    if ($stock <= 0) {
        http_response_code(409);
        exit('Este producto está agotado.');
    }

    $cantidadActual = (int) ($carritoModel->obtener()[$productoId]['cantidad'] ?? 0);
    if ($cantidadActual + $cantidad > $stock) {
        http_response_code(409);
        exit('No hay stock suficiente. Disponible: ' . $stock . ' unidades.');
    }


    $carritoModel->agregar(
        $producto,
        $cantidad
    );


    header(
        'Location: ' . url('/carrito')
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

    $cantidad = filter_var(
        $_POST['cantidad'] ?? null,
        FILTER_VALIDATE_INT
    );


    if ($productoId <= 0 || $cantidad === false || $cantidad <= 0 || $cantidad > 99) {

        exit(
            'Producto no válido.'
        );
    }


    $producto = $productoModel->obtenerPorId($productoId);

    if (!$producto || (int) $producto['activo'] !== 1) {
        http_response_code(409);
        exit('Este producto ya no está disponible.');
    }

    $stock = (int) ($producto['stock'] ?? 0);
    if ($cantidad > $stock) {
        http_response_code(409);
        exit('La cantidad supera el stock disponible: ' . $stock . ' unidades.');
    }

    if (!$carritoModel->actualizarCantidad($productoId, $cantidad)) {

        exit(
            'Producto no encontrado en el carrito.'
        );
    }


    header(
        'Location: ' . url('/carrito')
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
        'Location: ' . url('/carrito')
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
        'Location: ' . url('/carrito')
    );

    exit;
}


// =========================================================
// ACCIÓN NO EXISTENTE
// =========================================================

http_response_code(404);

echo 'Acción de carrito no encontrada.';