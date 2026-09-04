<?php

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');

require_once __DIR__ . '/../config/Session.php';
iniciar_sesion_segura();


// =========================================================
// COMPROBAR SESIÓN
// =========================================================

if (!isset($_SESSION['usuario_id'])) {

    header(
        'Location: ' . url('/login')
    );

    exit;
}


// =========================================================
// CONFIGURACIÓN CSRF
// =========================================================

require_once __DIR__ . '/../config/Csrf.php';


// =========================================================
// MODELOS
// =========================================================

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Pedido.php';
require_once __DIR__ . '/../models/Carrito.php';
require_once __DIR__ . '/../models/Producto.php';


require_once __DIR__ . '/../config/whatsapp.php';
require_once __DIR__ . '/../models/Usuario.php';


// =========================================================
// INSTANCIAR MODELOS
// =========================================================

$pedidoModel = new Pedido($conn);

$carritoModel = new Carrito();
$productoModel = new Producto($conn);

$carritoModel->inicializar();


// =========================================================
// OBTENER ACCIÓN
// =========================================================

$accion =
    $_POST['accion']
    ?? $_GET['accion']
    ?? 'listar';


// =========================================================
// COMPROBAR ROL DE ADMIN / EMPLEADO
// =========================================================

if (
    $accion === 'gestionar_pedidos' ||
    $accion === 'admin' ||
    $accion === 'actualizar_estado' ||
    $accion === 'detalle_admin' ||
    $accion === 'ver_admin'
) {

    if (
        !isset($_SESSION['usuario_rol']) ||
        !in_array(
            $_SESSION['usuario_rol'],
            ['admin', 'empleado'],
            true
        )
    ) {

        http_response_code(403);
        $codigoError = 404;
        $tituloError = 'Página no encontrada';
        $descripcionError = 'No pudimos encontrar lo que estabas buscando.';
        $urlVolver = url('/');
        $textoVolver = 'Volver al inicio';
        require __DIR__ . '/../views/errors/error.php';
        exit;
    }
}


// =========================================================
// PANEL DE PEDIDOS - ADMIN / EMPLEADO
// =========================================================

if ($accion === 'gestionar_pedidos' || $accion === 'admin') {

    $nombreCliente = trim($_GET['nombre_cliente'] ?? '');
    $fechaDesde = trim($_GET['fecha_desde'] ?? '');
    $fechaHasta = trim($_GET['fecha_hasta'] ?? '');
    $estado = trim($_GET['estado'] ?? '');

    $estadosPermitidos = [
        '',
        'pendiente',
        'confirmado',
        'en_preparacion',
        'listo',
        'entregado',
        'cancelado'
    ];

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaDesde)) {
        $fechaDesde = '';
    }

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaHasta)) {
        $fechaHasta = '';
    }

    if (!in_array($estado, $estadosPermitidos, true)) {
        $estado = '';
    }

    $pedidos =
        $pedidoModel->obtenerTodos(
            $nombreCliente,
            $fechaDesde,
            $fechaHasta,
            $estado
        );

    $esAdmin =
        $_SESSION['usuario_rol'] === 'admin';

    $esEmpleado =
        $_SESSION['usuario_rol'] === 'empleado';

    require __DIR__ . '/../views/admin/pedidos.php';

    exit;
}


// =========================================================
// VER PEDIDO - ADMIN / EMPLEADO
// =========================================================

if ($accion === 'detalle_admin' || $accion === 'ver_admin') {

    $pedidoId =
        (int) ($_GET['id'] ?? 0);

    if ($pedidoId <= 0) {

        exit('Pedido no válido.');
    }

    $pedido =
        $pedidoModel->obtenerPorIdAdmin(
            $pedidoId
        );

    if (!$pedido) {
        http_response_code(404);
        $codigoError = 404;
        $tituloError = 'Pedido no encontrado';
        $descripcionError = 'No pudimos encontrar el pedido solicitado.';
        $urlVolver = url('/pedidos');
        $textoVolver = 'Volver a mis pedidos';
        require __DIR__ . '/../views/errors/error.php';
        exit;
    }

    $detalles =
        $pedidoModel->obtenerDetalles(
            $pedidoId
        );

    $esAdmin =
        $_SESSION['usuario_rol'] === 'admin';

    $esEmpleado =
        $_SESSION['usuario_rol'] === 'empleado';

    require __DIR__ . '/../views/pedidos/detalle.php';

    exit;
}


// =========================================================
// ACTUALIZAR ESTADO DEL PEDIDO
// ADMIN / EMPLEADO
// =========================================================

if ($accion === 'actualizar_estado') {

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

        http_response_code(405);

        exit('Método no permitido.');
    }

    verificar_csrf();

    $pedidoId =
        (int) ($_POST['pedido_id'] ?? 0);

    $estado =
        trim($_POST['estado'] ?? '');

    if ($pedidoId <= 0) {

        exit('Pedido no válido.');
    }

    $pedidoActual = $pedidoModel->obtenerPorIdAdmin($pedidoId);
    if (!$pedidoActual) {
        http_response_code(404);
        $codigoError = 404;
        $tituloError = 'Pedido no encontrado';
        $descripcionError = 'No pudimos encontrar el pedido solicitado.';
        $urlVolver = url('/admin/pedidos');
        $textoVolver = 'Volver a pedidos';
        require __DIR__ . '/../views/errors/error.php';
        exit;
    }

    if ($pedidoActual['estado'] === $estado) {
        header('Location: ' . url('/admin/pedidos?error=estado_igual'));
        exit;
    }

    $estadosPermitidos = [

        'pendiente',
        'confirmado',
        'en_preparacion',
        'listo',
        'entregado',
        'cancelado'

    ];

    if (
        !in_array(
            $estado,
            $estadosPermitidos,
            true
        )
    ) {
        header('Location: ' . url('/admin/pedidos?error=estado_invalido'));
        exit;
    }

    $resultado =
        $pedidoModel->actualizarEstado(
            $pedidoId,
            $estado
        );

    if (!$resultado) {
        header('Location: ' . url('/admin/pedidos?error=transicion_invalida'));
        exit;
    }

    header(
        'Location: ' . url('/admin/pedidos')
    );

    exit;
}


// =========================================================
// CANCELAR PEDIDO
// =========================================================

if ($accion === 'cancelar') {

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

        http_response_code(405);

        exit('Método no permitido.');
    }

    verificar_csrf();

    $pedidoId =
        (int) ($_POST['pedido_id'] ?? 0);

    if ($pedidoId <= 0) {

        exit('Pedido no válido.');
    }

    $usuarioId =
        (int) $_SESSION['usuario_id'];

    $resultado =
        $pedidoModel->cancelarPedido(
            $pedidoId,
            $usuarioId
        );

    if (!$resultado) {

        exit('No se pudo cancelar el pedido. '
            . 'Es posible que ya haya sido confirmado o procesado.');
    }

    header(
        'Location: ' . url('/pedidos/detalle?id=' . $pedidoId)
    );

    exit;
}


if ($accion === 'repetir') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        exit('Método no permitido.');
    }

    verificar_csrf();

    $pedidoId = filter_var($_POST['pedido_id'] ?? null, FILTER_VALIDATE_INT);
    $usuarioId = (int) $_SESSION['usuario_id'];

    if ($pedidoId === false || $pedidoId <= 0) {
        exit('Pedido no válido.');
    }

    $detalles = $pedidoModel->obtenerDetallesDeUsuario($pedidoId, $usuarioId);
    if (empty($detalles)) {
        http_response_code(404);
        exit('El pedido no existe o no pertenece a tu cuenta.');
    }

    $agregados = [];
    $noAgregados = [];

    foreach ($detalles as $detalle) {
        $productoId = (int) $detalle['producto_id'];
        $cantidadOriginal = filter_var($detalle['cantidad'], FILTER_VALIDATE_INT);
        $producto = $productoModel->obtenerPorId($productoId);

        if (!$producto || (int) $producto['activo'] !== 1 || (int) $producto['stock'] <= 0) {
            $noAgregados[] = $producto
                ? $producto['nombre']
                : ('Producto #' . $productoId);
            continue;
        }

        $cantidadOriginal = max(0, (int) $cantidadOriginal);
        $cantidadActual = (int) ($carritoModel->obtener()[$productoId]['cantidad'] ?? 0);
        $cantidadDisponible = max(0, (int) $producto['stock'] - $cantidadActual);
        $cantidadAgregar = min($cantidadOriginal, $cantidadDisponible);

        if ($cantidadAgregar > 0) {
            $carritoModel->agregar($producto, $cantidadAgregar);
            $agregados[] = $producto['nombre'];
        }

        if ($cantidadAgregar < $cantidadOriginal) {
            $noAgregados[] = $producto['nombre'];
        }
    }

    $_SESSION['mensaje_repetir'] = empty($noAgregados)
        ? 'Los productos del pedido fueron agregados al carrito.'
        : 'Algunos productos no se agregaron por disponibilidad o stock: '
        . implode(', ', $noAgregados) . '.';

    header('Location: ' . url('/carrito'));
    exit;
}


// =========================================================
// CREAR PEDIDO
// =========================================================

if ($accion === 'crear') {


    // -----------------------------------------------------
    // MOSTRAR FORMULARIO
    // -----------------------------------------------------

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

        $carrito =
            $carritoModel->obtener();

        $total =
            $carritoModel->calcularTotal();

        if (empty($carrito)) {

            header(
                'Location: ' . url('/carrito')
            );

            exit;
        }

        require __DIR__ . '/../views/pedidos/crear.php';

        exit;
    }


    // -----------------------------------------------------
    // VERIFICAR CSRF
    // -----------------------------------------------------

    verificar_csrf();


    // -----------------------------------------------------
    // OBTENER CARRITO
    // -----------------------------------------------------

    $carrito =
        $carritoModel->obtener();

    if (empty($carrito)) {

        require __DIR__ . '/../controllers/CarritoController.php';
        exit;
    }


    // -----------------------------------------------------
    // USUARIO
    // -----------------------------------------------------

    $usuarioId =
        (int) $_SESSION['usuario_id'];


    // -----------------------------------------------------
    // COMPROBAR PEDIDOS PENDIENTES
    // -----------------------------------------------------

    $pedidosPendientes =
        $pedidoModel->cantidadPendientes(
            $usuarioId
        );



    if ($pedidosPendientes >= 3) {
        require __DIR__ . '/../views/pedidos/limite.php';
        exit;
    }


    // -----------------------------------------------------
    // DATOS DEL FORMULARIO
    // -----------------------------------------------------

    $departamento =
        trim($_POST['departamento'] ?? '');

    $calle =
        trim($_POST['calle'] ?? '');

    $numeroPuerta =
        trim($_POST['numero_puerta'] ?? '');

    $referencia =
        trim($_POST['referencia'] ?? '');

    $fechaRecepcion =
        trim($_POST['fecha_recepcion'] ?? '');

    $franjaHoraria =
        trim($_POST['franja_horaria'] ?? '');

    $metodoPago =
        trim($_POST['metodo_pago'] ?? '');


    // =====================================================
    // VALIDAR DEPARTAMENTO
    // =====================================================

    if ($departamento !== 'Salto') {

        exit('Don Diego solamente realiza entregas dentro del departamento de Salto.');
    }


    // =====================================================
    // VALIDAR CALLE
    // =====================================================

    if ($calle === '') {

        exit('La calle es obligatoria.');
    }

    if (strlen($calle) < 2) {

        exit('La calle ingresada no es válida.');
    }


    // =====================================================
    // VALIDAR NÚMERO DE PUERTA
    // =====================================================

    if ($numeroPuerta === '') {

        exit('El número de puerta es obligatorio.');
    }

    if (
        !preg_match(
            '/^[0-9]+[a-zA-Z]?(?:\s?(?:bis|BIS))?(?:-[a-zA-Z0-9]+)?$/',
            $numeroPuerta
        )
    ) {

        exit('El número de puerta no es válido.');
    }


    // =====================================================
    // VALIDAR FECHA
    // =====================================================

    if ($fechaRecepcion === '') {

        exit('La fecha de recepción es obligatoria.');
    }

    $hoy =
        date('Y-m-d');

    $fechaMaxima =
        date(
            'Y-m-d',
            strtotime('+30 days')
        );

    if ($fechaRecepcion < $hoy) {

        exit('La fecha de recepción no puede ser anterior a hoy.');
    }

    if ($fechaRecepcion > $fechaMaxima) {

        exit('La fecha de recepción no puede superar los 30 días de anticipación.');
    }


    // =====================================================
    // COMPROBAR FECHA REAL
    // =====================================================

    $fechaValida =
        DateTime::createFromFormat(
            'Y-m-d',
            $fechaRecepcion
        );

    if (
        !$fechaValida ||
        $fechaValida->format('Y-m-d') !== $fechaRecepcion
    ) {

        exit('La fecha de recepción no es válida.');
    }


    // =====================================================
    // VALIDAR FRANJA HORARIA
    // =====================================================

    $franjasPermitidas = [

        '08:00 - 10:00',
        '10:00 - 12:00',
        '12:00 - 14:00',
        '14:00 - 16:00',
        '16:00 - 18:00',
        '18:00 - 20:00'

    ];

    if (
        !in_array(
            $franjaHoraria,
            $franjasPermitidas,
            true
        )
    ) {

        exit('La franja horaria seleccionada no es válida.');
    }


    // =====================================================
    // VALIDAR MÉTODO DE PAGO
    // =====================================================

    $metodosPermitidos = ['efectivo'];

    if (
        !in_array(
            $metodoPago,
            $metodosPermitidos,
            true
        )
    ) {

        exit('El método de pago seleccionado no es válido.');
    }

    // =====================================================
    // CONSTRUIR DIRECCIÓN
    // =====================================================

    $direccionEntrega =
        $departamento
        . ', '
        . $calle
        . ' '
        . $numeroPuerta;

    if ($referencia !== '') {

        $direccionEntrega .=
            ', '
            . $referencia;
    }


    // =====================================================
    // VALIDAR LONGITUD
    // =====================================================

    if (strlen($direccionEntrega) > 255) {

        exit('La dirección de entrega es demasiado larga.');
    }


    $ids = [];
    $cantidades = [];
    foreach ($carrito as $item) {
        $productoId = filter_var($item['id'] ?? null, FILTER_VALIDATE_INT);
        $cantidad = filter_var($item['cantidad'] ?? null, FILTER_VALIDATE_INT);

        if ($productoId === false || $productoId <= 0 || $cantidad === false || $cantidad <= 0 || $cantidad > 99) {
            exit('La cantidad o el producto del carrito no son válidos.');
        }

        $ids[] = $productoId;
        $cantidades[$productoId] = $cantidad;
    }

    $productos = $pedidoModel->obtenerProductosActivos($ids);

    if (count($productos) !== count(array_unique($ids))) {
        exit('Uno de los productos ya no existe o dejó de estar disponible.');
    }

    $detalles = [];
    foreach ($ids as $productoId) {
        $detalles[$productoId] = [
            'producto_id' => $productoId,
            'cantidad' => $cantidades[$productoId],
            'precio_unitario' => (float) $productos[$productoId]['precio']
        ];
    }

    $pedidoId = $pedidoModel->crearPedidoConDetalles(
        $usuarioId,
        $fechaRecepcion,
        $franjaHoraria,
        $direccionEntrega,
        $metodoPago,
        array_values($detalles)
    );

    if (!$pedidoId) {
        $errorPedido = $pedidoModel->obtenerUltimoError();
        exit($errorPedido !== ''
            ? $errorPedido
            : 'No se pudo crear el pedido en la base de datos.');
    }

    $pedido = $pedidoModel->obtenerPorId($pedidoId, $usuarioId);
    $usuario = (new Usuario($conn))->obtenerPorId($usuarioId);
    if ($pedido && $usuario) {
        // El aviso usa datos del cliente consultados desde MySQL, no del formulario.
        $pedido['nombre_completo'] = $usuario['nombre_completo'];
    }
    $detallesGuardados = $pedidoModel->obtenerDetalles($pedidoId);
    $mensaje = crearMensajeWhatsApp($pedido ?: [], $detallesGuardados);
    $whatsappUrl = crearUrlWhatsApp($mensaje);

    $carritoModel->vaciar();

    if ($whatsappUrl === null) {
        exit('El pedido #' . $pedidoId . ' fue creado correctamente. Configurá el número de WhatsApp para contactar al comercio.');
    }

    $pedidosUrl = url('/pedidos');

    header('Content-Type: text/html; charset=UTF-8');
    echo '<!doctype html><html lang="es"><head><meta charset="UTF-8"><title>Pedido confirmado</title></head><body>';
    echo '<script>';
    echo 'const pedidosUrl = ' . json_encode($pedidosUrl, JSON_UNESCAPED_SLASHES) . ';';
    echo 'const whatsappUrl = ' . json_encode($whatsappUrl, JSON_UNESCAPED_SLASHES) . ';';
    echo 'localStorage.setItem("dondiego_pedido_confirmado", Date.now().toString());';
    echo 'if (window.opener && !window.opener.closed) { window.opener.location.href = pedidosUrl; }';
    echo 'window.location.replace(whatsappUrl);';
    echo '</script>';
    echo '<p>Pedido confirmado. Abriendo WhatsApp...</p></body></html>';
    exit;
}


// =========================================================
// LISTAR PEDIDOS DEL USUARIO
// =========================================================

if ($accion === 'listar') {

    $usuarioId =
        (int) $_SESSION['usuario_id'];

    $pedidos =
        $pedidoModel->obtenerPorUsuario(
            $usuarioId
        );

    require __DIR__ . '/../views/pedidos/index.php';

    exit;
}


// =========================================================
// VER PEDIDO - CLIENTE
// =========================================================

if ($accion === 'ver') {

    $pedidoId =
        (int) (
            $_GET['id'] ?? 0
        );

    if ($pedidoId <= 0) {

        exit('Pedido no válido.');
    }

    $usuarioId =
        (int) $_SESSION['usuario_id'];

    $pedido =
        $pedidoModel->obtenerPorId(
            $pedidoId,
            $usuarioId
        );

    if (!$pedido) {
        http_response_code(404);
        $codigoError = 404;
        $tituloError = 'Pedido no encontrado';
        $descripcionError = 'No pudimos encontrar el pedido solicitado.';
        $urlVolver = url('/pedidos');
        $textoVolver = 'Volver a mis pedidos';
        require __DIR__ . '/../views/errors/error.php';
        exit;
    }

    $detalles =
        $pedidoModel->obtenerDetalles(
            $pedidoId
        );

    $esAdmin = false;

    $esEmpleado = false;

    require __DIR__ . '/../views/pedidos/detalle.php';

    exit;
}


// =========================================================
// ACCIÓN NO EXISTENTE
// =========================================================

http_response_code(404);

echo 'Acción de pedido no encontrada.';
