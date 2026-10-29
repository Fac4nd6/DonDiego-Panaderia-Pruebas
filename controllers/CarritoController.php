<?php

require_once __DIR__ . '/../config/Session.php';
iniciar_sesion_segura();


// COMPROBAR SESIÓN DE USUARIO
if (!isset($_SESSION['usuario_id'])) {

    header(
        'Location: ' . url('/login')
    );

    exit;
}


// CSRF
require_once __DIR__ . '/../config/Csrf.php';


// MODELOS
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Producto.php';
require_once __DIR__ . '/../models/Carrito.php';


$productoModel = new Producto($conn);

$carritoModel = new Carrito();

$mensajeRepetir = $_SESSION['mensaje_repetir'] ?? '';
unset($_SESSION['mensaje_repetir']);


// INICIALIZAR CARRITO
$carritoModel->inicializar();


// OBTENER ACCIÓN
$accion =
    $_POST['accion']
    ?? $_GET['accion']
    ?? 'ver';


// VER CARRITO
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


// AGREGAR PRODUCTO
if ($accion === 'agregar') {


    // SOLO POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

        http_response_code(405);

        exit(
            'Método no permitido.'
        );
    }


    // CSRF
    verificar_csrf();


    $productoId =
        (int) ($_POST['producto_id'] ?? 0);

    $cantidadEntrada = $_POST['cantidad'] ?? null;

    $unidadVenta = trim($_POST['unidad_venta'] ?? '');


    if ($productoId <= 0) {

        exit(
            'Producto no válido.'
        );
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

    $presentaciones = Producto::obtenerPresentaciones($producto);
    if (!array_key_exists($unidadVenta, $presentaciones)) {
        http_response_code(422);
        exit('La presentación seleccionada no está disponible.');
    }

    $cantidad = Producto::normalizarCantidad($cantidadEntrada, $unidadVenta);
    if ($cantidad === false) {
        http_response_code(422);
        exit('La cantidad solicitada no es válida.');
    }

    $stocksPorModalidad = Producto::obtenerStocks($producto);
    $consumoSolicitado = Producto::consumoInventario($unidadVenta, $cantidad);
    $stock = (float) ($stocksPorModalidad[$consumoSolicitado['base']] ?? 0);

    $cantidadReservada = 0;
    foreach ($carritoModel->obtener() as $item) {
        if ((int) ($item['id'] ?? 0) !== $productoId) {
            continue;
        }

        $consumoExistente = Producto::consumoInventario(
            $item['unidad_venta'] ?? 'unidad',
            $item['cantidad'] ?? 0
        );
        if ($consumoExistente['base'] === $consumoSolicitado['base']) {
            $cantidadReservada += $consumoExistente['cantidad'];
        }
    }

    if ($cantidadReservada + $consumoSolicitado['cantidad'] > $stock) {
        http_response_code(409);
        exit('No hay stock suficiente para ' . Producto::nombrePresentacion($consumoSolicitado['base']) . '.');
    }

    $carritoModel->agregar(
        $producto,
        $cantidad,
        $unidadVenta
    );


    header(
        'Location: ' . url('/carrito')
    );

    exit;
}


// ACTUALIZAR CANTIDAD
if ($accion === 'actualizar') {


    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

        http_response_code(405);

        exit(
            'Método no permitido.'
        );
    }


    verificar_csrf();


    $productoId = (int) ($_POST['producto_id'] ?? 0);
    $lineaId = trim($_POST['linea_id'] ?? '');

    $cantidadEntrada = $_POST['cantidad'] ?? null;


    $carrito = $carritoModel->obtener();
    if (
        $productoId <= 0 ||
        !isset($carrito[$lineaId]) ||
        (int) ($carrito[$lineaId]['id'] ?? 0) !== $productoId
    ) {

        exit(
            'Producto no válido.'
        );
    }


    $producto = $productoModel->obtenerPorId($productoId);

    if (!$producto || (int) $producto['activo'] !== 1) {
        http_response_code(409);
        exit('Este producto ya no está disponible.');
    }

    $unidadVenta = (string) ($carrito[$lineaId]['unidad_venta'] ?? '');
    $stocksPorModalidad = Producto::obtenerStocks($producto);
    if (!array_key_exists($unidadVenta, Producto::obtenerPresentaciones($producto))) {
        http_response_code(409);
        exit('La modalidad de este producto ya no está disponible.');
    }

    $cantidad = Producto::normalizarCantidad($cantidadEntrada, $unidadVenta);
    if ($cantidad === false) {
        http_response_code(422);
        exit('La cantidad solicitada no es válida.');
    }

    $consumoSolicitado = Producto::consumoInventario($unidadVenta, $cantidad);
    $stock = (float) ($stocksPorModalidad[$consumoSolicitado['base']] ?? 0);
    $cantidadOtrasPresentaciones = 0;
    foreach ($carrito as $clave => $item) {
        if (
            $clave !== $lineaId &&
            (int) ($item['id'] ?? 0) === $productoId
        ) {
            $consumoExistente = Producto::consumoInventario(
                $item['unidad_venta'] ?? 'unidad',
                $item['cantidad'] ?? 0
            );
            if ($consumoExistente['base'] === $consumoSolicitado['base']) {
                $cantidadOtrasPresentaciones += $consumoExistente['cantidad'];
            }
        }
    }
    if ($consumoSolicitado['cantidad'] + $cantidadOtrasPresentaciones > $stock) {
        http_response_code(409);
        exit('La cantidad supera el stock disponible para esta modalidad.');
    }

    if (!$carritoModel->actualizarCantidad($lineaId, $cantidad)) {

        exit(
            'Producto no encontrado en el carrito.'
        );
    }


    header(
        'Location: ' . url('/carrito')
    );

    exit;
}


// ELIMINAR PRODUCTO
if ($accion === 'eliminar') {


    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

        http_response_code(405);

        exit(
            'Método no permitido.'
        );
    }


    verificar_csrf();


    $lineaId = trim($_POST['linea_id'] ?? '');


    if ($lineaId === '') {

        exit(
            'Producto no válido.'
        );
    }


    $carritoModel->eliminar($lineaId);


    header(
        'Location: ' . url('/carrito')
    );

    exit;
}


// VACIAR CARRITO
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


// ACCIÓN NO EXISTENTE
http_response_code(404);

echo 'Acción de carrito no encontrada.';