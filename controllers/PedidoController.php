<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

session_start();



// =========================================================
// COMPROBAR SESIÓN
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

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Pedido.php';
require_once __DIR__ . '/../models/Carrito.php';


// =========================================================
// INSTANCIAR MODELOS
// =========================================================

$pedidoModel = new Pedido($conn);

$carritoModel = new Carrito();

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
//
// Estas acciones pueden ser utilizadas por:
// - admin
// - empleado
//
// Los clientes NO pueden acceder.
//

if (
    $accion === 'admin' ||
    $accion === 'actualizar_estado' ||
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

        exit(
            'No tenés permisos para acceder a esta sección.'
        );
    }
}

// =========================================================
// PANEL DE PEDIDOS - ADMIN / EMPLEADO
// =========================================================

if ($accion === 'admin') {

    $pedidos =
        $pedidoModel->obtenerTodos();


    // -----------------------------------------------------
    // INDICAR EL ROL
    // -----------------------------------------------------

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

if ($accion === 'ver_admin') {

    // -----------------------------------------------------
    // OBTENER ID
    // -----------------------------------------------------

    $pedidoId =
        (int) ($_GET['id'] ?? 0);


    // -----------------------------------------------------
    // VALIDAR ID
    // -----------------------------------------------------

    if ($pedidoId <= 0) {

        exit(
            'Pedido no válido.'
        );
    }


    // -----------------------------------------------------
    // OBTENER PEDIDO
    // -----------------------------------------------------

    $pedido =
        $pedidoModel->obtenerPorIdAdmin(
            $pedidoId
        );


    if (!$pedido) {

        http_response_code(404);

        exit(
            'Pedido no encontrado.'
        );
    }


    // -----------------------------------------------------
    // OBTENER DETALLES
    // -----------------------------------------------------

    $detalles =
        $pedidoModel->obtenerDetalles(
            $pedidoId
        );


    // -----------------------------------------------------
    // INDICAR ROL
    // -----------------------------------------------------

    $esAdmin =
        $_SESSION['usuario_rol'] === 'admin';

    $esEmpleado =
        $_SESSION['usuario_rol'] === 'empleado';


    // -----------------------------------------------------
    // MOSTRAR DETALLE
    // -----------------------------------------------------

    require __DIR__ . '/../views/pedidos/detalle.php';

    exit;
}


// =========================================================
// ACTUALIZAR ESTADO DEL PEDIDO
// ADMIN / EMPLEADO
// =========================================================

if ($accion === 'actualizar_estado') {

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
    // OBTENER DATOS
    // -----------------------------------------------------

    $pedidoId =
        (int) ($_POST['pedido_id'] ?? 0);

    $estado =
        trim($_POST['estado'] ?? '');


    // -----------------------------------------------------
    // VALIDAR ID
    // -----------------------------------------------------

    if ($pedidoId <= 0) {

        exit(
            'Pedido no válido.'
        );
    }


    // -----------------------------------------------------
    // ESTADOS PERMITIDOS
    // -----------------------------------------------------

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

        exit(
            'El estado seleccionado no es válido.'
        );
    }


    // -----------------------------------------------------
    // ACTUALIZAR
    // -----------------------------------------------------

    $resultado =
        $pedidoModel->actualizarEstado(
            $pedidoId,
            $estado
        );


    if (!$resultado) {

        exit(
            'No se pudo actualizar el estado del pedido.'
        );
    }


    // -----------------------------------------------------
    // VOLVER AL PANEL
    // -----------------------------------------------------

    header(
        'Location: /DonDiego-Panaderia-Pruebas/controllers/PedidoController.php?accion=admin'
    );

    exit;
}


// =========================================================
// CANCELAR PEDIDO
// =========================================================

if ($accion === 'cancelar') {

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
    // OBTENER ID
    // -----------------------------------------------------

    $pedidoId =
        (int) ($_POST['pedido_id'] ?? 0);


    // -----------------------------------------------------
    // VALIDAR ID
    // -----------------------------------------------------

    if ($pedidoId <= 0) {

        exit(
            'Pedido no válido.'
        );
    }


    // -----------------------------------------------------
    // USUARIO
    // -----------------------------------------------------

    $usuarioId =
        (int) $_SESSION['usuario_id'];


    // -----------------------------------------------------
    // CANCELAR
    // -----------------------------------------------------

    $resultado =
        $pedidoModel->cancelarPedido(
            $pedidoId,
            $usuarioId
        );


    if (!$resultado) {

        exit(
            'No se pudo cancelar el pedido. '
            . 'Es posible que ya haya sido confirmado o procesado.'
        );
    }


    // -----------------------------------------------------
    // VOLVER AL DETALLE
    // -----------------------------------------------------

    header(
        'Location: /DonDiego-Panaderia-Pruebas/controllers/PedidoController.php?accion=ver&id='
        . $pedidoId
    );

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
                'Location: /DonDiego-Panaderia-Pruebas/controllers/CarritoController.php?accion=ver'
            );

            exit;
        }


        require __DIR__ . '/../views/pedidos/crear.php';

        exit;
    }


    // -----------------------------------------------------
    // OBTENER CARRITO
    // -----------------------------------------------------

    $carrito =
        $carritoModel->obtener();


    if (empty($carrito)) {

        exit(
            'El carrito está vacío.'
        );
    }


    // -----------------------------------------------------
    // USUARIO
    // -----------------------------------------------------

    $usuarioId =
        (int) $_SESSION['usuario_id'];


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


    // -----------------------------------------------------
    // VALIDAR DEPARTAMENTO
    // -----------------------------------------------------

    if ($departamento !== 'Salto') {

        exit(
            'Don Diego solamente realiza entregas dentro del departamento de Salto.'
        );
    }


    // -----------------------------------------------------
    // VALIDAR CALLE
    // -----------------------------------------------------

    if ($calle === '') {

        exit(
            'La calle es obligatoria.'
        );
    }


    if (strlen($calle) < 2) {

        exit(
            'La calle ingresada no es válida.'
        );
    }


    // -----------------------------------------------------
    // VALIDAR NÚMERO DE PUERTA
    // -----------------------------------------------------

    if ($numeroPuerta === '') {

        exit(
            'El número de puerta es obligatorio.'
        );
    }


    if (
        !preg_match(
            '/^[0-9]+[a-zA-Z]?(?:\s?(?:bis|BIS))?(?:-[a-zA-Z0-9]+)?$/',
            $numeroPuerta
        )
    ) {

        exit(
            'El número de puerta no es válido.'
        );
    }


    // -----------------------------------------------------
    // VALIDAR FECHA
    // -----------------------------------------------------

    if ($fechaRecepcion === '') {

        exit(
            'La fecha de recepción es obligatoria.'
        );
    }


    $hoy =
        date('Y-m-d');


    $fechaMaxima =
        date(
            'Y-m-d',
            strtotime('+30 days')
        );


    if ($fechaRecepcion < $hoy) {

        exit(
            'La fecha de recepción no puede ser anterior a hoy.'
        );
    }


    if ($fechaRecepcion > $fechaMaxima) {

        exit(
            'La fecha de recepción no puede superar los 30 días de anticipación.'
        );
    }


    // -----------------------------------------------------
    // COMPROBAR FECHA REAL
    // -----------------------------------------------------

    $fechaValida =
        DateTime::createFromFormat(
            'Y-m-d',
            $fechaRecepcion
        );


    if (
        !$fechaValida ||
        $fechaValida->format('Y-m-d') !== $fechaRecepcion
    ) {

        exit(
            'La fecha de recepción no es válida.'
        );
    }


    // -----------------------------------------------------
    // VALIDAR FRANJA HORARIA
    // -----------------------------------------------------

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

        exit(
            'La franja horaria seleccionada no es válida.'
        );
    }


    // -----------------------------------------------------
    // VALIDAR MÉTODO DE PAGO
    // -----------------------------------------------------

    $metodosPermitidos = [

        'efectivo',
        'mercado_pago'

    ];


    if (
        !in_array(
            $metodoPago,
            $metodosPermitidos,
            true
        )
    ) {

        exit(
            'El método de pago seleccionado no es válido.'
        );
    }


    // -----------------------------------------------------
    // CONSTRUIR DIRECCIÓN
    // -----------------------------------------------------

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


    // -----------------------------------------------------
    // VALIDAR LONGITUD
    // -----------------------------------------------------

    if (strlen($direccionEntrega) > 255) {

        exit(
            'La dirección de entrega es demasiado larga.'
        );
    }


    // -----------------------------------------------------
    // CALCULAR TOTAL
    // -----------------------------------------------------

    $total =
        $carritoModel->calcularTotal();


    if ($total <= 0) {

        exit(
            'El total del pedido no es válido.'
        );
    }


    // -----------------------------------------------------
    // CREAR PEDIDO
    // -----------------------------------------------------

    $pedidoId =
        $pedidoModel->crearPedido(
            $usuarioId,
            $fechaRecepcion,
            $franjaHoraria,
            $direccionEntrega,
            $total
        );


    if (!$pedidoId) {

        exit(
            'No se pudo crear el pedido en la base de datos.'
        );
    }


    // -----------------------------------------------------
    // GUARDAR DETALLES
    // -----------------------------------------------------

    foreach ($carrito as $item) {

        $productoId =
            (int) $item['id'];

        $cantidad =
            (int) $item['cantidad'];

        $precio =
            (float) $item['precio'];

        $subtotal =
            $precio * $cantidad;


        $resultado =
            $pedidoModel->agregarDetalle(
                $pedidoId,
                $productoId,
                $cantidad,
                $precio,
                $subtotal
            );


        if (!$resultado) {

            exit(
                'El pedido fue creado, pero ocurrió un error al guardar uno de los productos.'
            );
        }
    }


    // -----------------------------------------------------
    // VACIAR CARRITO
    // -----------------------------------------------------

    $carritoModel->vaciar();


    // -----------------------------------------------------
    // REDIRIGIR AL DETALLE
    // -----------------------------------------------------

    header(
        'Location: /DonDiego-Panaderia-Pruebas/controllers/PedidoController.php?accion=ver&id='
        . $pedidoId
    );

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

        exit(
            'Pedido no válido.'
        );
    }


    $usuarioId =
        (int) $_SESSION['usuario_id'];


    // -----------------------------------------------------
    // OBTENER PEDIDO
    // -----------------------------------------------------

    $pedido =
        $pedidoModel->obtenerPorId(
            $pedidoId,
            $usuarioId
        );


    if (!$pedido) {

        http_response_code(404);

        exit(
            'Pedido no encontrado.'
        );
    }


    // -----------------------------------------------------
    // OBTENER DETALLES
    // -----------------------------------------------------

    $detalles =
        $pedidoModel->obtenerDetalles(
            $pedidoId
        );


    // -----------------------------------------------------
    // INDICAR QUE NO ES ADMIN
    // -----------------------------------------------------

    $esAdmin = false;

    $esEmpleado = false;


    // -----------------------------------------------------
    // MOSTRAR DETALLE
    // -----------------------------------------------------

    require __DIR__ . '/../views/pedidos/detalle.php';

    exit;
}


// =========================================================
// ACCIÓN NO EXISTENTE
// =========================================================

http_response_code(404);

echo 'Acción de pedido no encontrada.';