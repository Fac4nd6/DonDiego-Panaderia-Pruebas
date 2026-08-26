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
// CONFIGURACIÓN CSRF
// =========================================================

require_once __DIR__ . '/../config/Csrf.php';


// =========================================================
// MODELOS
// =========================================================

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Pedido.php';
require_once __DIR__ . '/../models/Carrito.php';


// =========================================================
// SERVICIO MERCADO PAGO
// =========================================================

require_once __DIR__ . '/../service/MercadoPagoService.php';


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

        exit('No tenés permisos para acceder a esta sección.');
    }
}


// =========================================================
// PANEL DE PEDIDOS - ADMIN / EMPLEADO
// =========================================================

if ($accion === 'admin') {

    $pedidos =
        $pedidoModel->obtenerTodos();

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

        exit('Pedido no encontrado.');
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

        exit('El estado seleccionado no es válido.');
    }

    $resultado =
        $pedidoModel->actualizarEstado(
            $pedidoId,
            $estado
        );

    if (!$resultado) {

        exit('No se pudo actualizar el estado del pedido.');
    }

    header(
        'Location: /DonDiego-Panaderia-Pruebas/controllers/PedidoController.php?accion=admin'
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
    // VERIFICAR CSRF
    // -----------------------------------------------------

    verificar_csrf();


    // -----------------------------------------------------
    // OBTENER CARRITO
    // -----------------------------------------------------

    $carrito =
        $carritoModel->obtener();

    if (empty($carrito)) {

        exit('El carrito está vacío.');
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

        exit('Ya tenés 3 pedidos pendientes. '
            . 'Esperá a que sean confirmados o cancelá uno antes de realizar otro pedido.');
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


    // =====================================================
    // CALCULAR TOTAL
    // =====================================================

    $total =
        $carritoModel->calcularTotal();

    if ($total <= 0) {

        exit('El total del pedido no es válido.');
    }


    // =====================================================
    // CREAR PEDIDO
    // =====================================================

    $pedidoId =
        $pedidoModel->crearPedido(
            $usuarioId,
            $fechaRecepcion,
            $franjaHoraria,
            $direccionEntrega,
            $metodoPago,
            $total
        );

    if (!$pedidoId) {

        exit('No se pudo crear el pedido en la base de datos.');
    }


    // =====================================================
    // GUARDAR DETALLES
    // =====================================================

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

            exit('El pedido fue creado, pero ocurrió un error al guardar uno de los productos.');
        }
    }

    // =====================================================
    // MERCADO PAGO
    // =====================================================

    if ($metodoPago === 'mercado_pago') {

        try {

            /*
         * Volvemos a obtener los detalles desde la BD.
         *
         * Esto evita confiar únicamente en los datos
         * que llegaron desde el navegador.
         */

            $detalles =
                $pedidoModel->obtenerDetalles(
                    $pedidoId
                );


            if (empty($detalles)) {

                exit('No se pudieron obtener los productos del pedido.');
            }


            /*
         * Obtener los datos completos del pedido.
         */

            $pedido =
                $pedidoModel->obtenerPorIdAdmin(
                    $pedidoId
                );


            if (!$pedido) {

                exit('No se pudo obtener el pedido creado.');
            }


            /*
         * Crear servicio de Mercado Pago.
         */

            $mercadoPago =
                new MercadoPagoService();


            /*
         * Crear la orden en Mercado Pago.
         */

            $resultado =
                $mercadoPago->crearOrden(
                    $pedido,
                    $detalles
                );


            /*
         * Obtener ID de la orden creada.
         */

            $orderId =
                $resultado['id']
                ?? null;


            if (!$orderId) {

                exit('Mercado Pago no devolvió el ID de la orden.');
            }


            /*
         * Guardar el ID de la orden de Mercado Pago
         * en nuestra base de datos.
         */

            $guardado =
                $pedidoModel->guardarMercadoPagoOrderId(
                    $pedidoId,
                    $orderId
                );


            if (!$guardado) {

                exit('La orden de Mercado Pago fue creada, '
                    . 'pero no se pudo guardar su ID en la base de datos.');
            }


            /*
         * Obtener URL del checkout.
         */

            $checkoutUrl =
                $resultado['checkout_url']
                ?? null;


            if (!$checkoutUrl) {

                exit('Mercado Pago no devolvió una URL de pago.');
            }


            /*
         * Vaciar carrito solamente después de comprobar
         * que la orden fue creada correctamente.
         */

            $carritoModel->vaciar();


            /*
         * Enviar al cliente al checkout de Mercado Pago.
         */

            header(
                'Location: ' . $checkoutUrl
            );

            exit;
        } catch (Throwable $e) {

            /*
         * El pedido ya existe en nuestra BD,
         * pero el pago no pudo iniciarse.
         *
         * No lo marcamos como confirmado/pagado.
         */

            error_log(
                'Error Mercado Pago pedido '
                    . $pedidoId
                    . ': '
                    . $e->getMessage()
            );


            http_response_code(500);

            exit('El pedido fue creado, pero no se pudo iniciar '
                . 'el pago con Mercado Pago.');
        }
    }
    // =====================================================
    // EFECTIVO
    // =====================================================

    if ($metodoPago === 'efectivo') {

        $carritoModel->vaciar();


        header(
            'Location: /DonDiego-Panaderia-Pruebas/controllers/PedidoController.php?accion=ver&id='
                . $pedidoId
        );

        exit;
    }
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

        exit('Pedido no encontrado.');
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
