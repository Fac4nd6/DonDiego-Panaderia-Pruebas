<?php

/*
|--------------------------------------------------------------------------
| MERCADO PAGO - WEBHOOK
|--------------------------------------------------------------------------
|
| Recibe las notificaciones de Mercado Pago.
|
| El webhook NO confía directamente en la notificación.
| Obtiene el ID de la operación y consulta Mercado Pago.
|
|--------------------------------------------------------------------------
*/


// =========================================================
// CONFIGURACIÓN
// =========================================================

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Pedido.php';
require_once __DIR__ . '/../service/MercadoPagoService.php';


// =========================================================
// RESPONDER JSON
// =========================================================

header(
    'Content-Type: application/json; charset=utf-8'
);


// =========================================================
// LEER NOTIFICACIÓN
// =========================================================

$input =
    file_get_contents(
        'php://input'
    );


$data =
    json_decode(
        $input,
        true
    );


// =========================================================
// VALIDAR JSON
// =========================================================

if (!is_array($data)) {

    http_response_code(400);

    echo json_encode([
        'ok' => false,
        'error' => 'JSON inválido.'
    ]);

    exit;
}


// =========================================================
// SERVICIOS
// =========================================================

$pedidoModel =
    new Pedido($conn);

$mercadoPago =
    new MercadoPagoService();


// =========================================================
// OBTENER ID DE OPERACIÓN
// =========================================================

$operacionId = null;


// ---------------------------------------------------------
// data.id
// ---------------------------------------------------------

if (
    isset($data['data']) &&
    is_array($data['data']) &&
    isset($data['data']['id'])
) {

    $operacionId =
        trim(
            (string) $data['data']['id']
        );
}


// ---------------------------------------------------------
// id DIRECTO
// ---------------------------------------------------------

if (
    !$operacionId &&
    isset($data['id'])
) {

    $operacionId =
        trim(
            (string) $data['id']
        );
}


// =========================================================
// SIN ID
// =========================================================

if (!$operacionId) {

    http_response_code(200);

    echo json_encode([
        'ok' => true,
        'message' =>
            'Notificación recibida sin ID de operación.'
    ]);

    exit;
}


// =========================================================
// CONSULTAR OPERACIÓN
// =========================================================

try {

    $operacion =
        $mercadoPago->consultarOperacion(
            $operacionId
        );

} catch (Throwable $e) {

    error_log(
        'Error consultando Mercado Pago: '
        . $e->getMessage()
    );

    http_response_code(500);

    echo json_encode([
        'ok' => false,
        'error' =>
            'No se pudo verificar la operación.'
    ]);

    exit;
}


// =========================================================
// OBTENER PEDIDO
// =========================================================

$pedidoId = 0;


// ---------------------------------------------------------
// EXTERNAL REFERENCE
// ---------------------------------------------------------

if (
    isset(
        $operacion['external_reference']
    )
) {

    $pedidoId =
        (int) $operacion['external_reference'];
}


// =========================================================
// VALIDAR PEDIDO
// =========================================================

if ($pedidoId <= 0) {

    http_response_code(200);

    echo json_encode([
        'ok' => true,
        'message' =>
            'Operación recibida sin pedido asociado.'
    ]);

    exit;
}


// =========================================================
// OBTENER PEDIDO
// =========================================================

$pedido =
    $pedidoModel->obtenerPorIdAdmin(
        $pedidoId
    );


if (!$pedido) {

    http_response_code(200);

    echo json_encode([
        'ok' => true,
        'message' =>
            'Pedido no encontrado.'
    ]);

    exit;
}


// =========================================================
// ESTADO DE LA ORDEN
// =========================================================

$estadoOperacion =
    strtolower(
        trim(
            (string) (
                $operacion['status']
                ?? ''
            )
        )
    );


// =========================================================
// BUSCAR PAYMENT ID Y MONTO
// =========================================================

$paymentId = null;

$montoOperacion = null;


// ---------------------------------------------------------
// transactions.payments
// ---------------------------------------------------------

if (
    isset($operacion['transactions']) &&
    is_array($operacion['transactions']) &&
    isset(
        $operacion['transactions']['payments']
    ) &&
    is_array(
        $operacion['transactions']['payments']
    )
) {

    foreach (
        $operacion['transactions']['payments']
        as $pago
    ) {

        if (!is_array($pago)) {
            continue;
        }


        /*
         * Guardamos el primer payment_id válido.
         */

        if (
            $paymentId === null &&
            isset($pago['id'])
        ) {

            $paymentId =
                trim(
                    (string) $pago['id']
                );
        }


        /*
         * Buscamos el monto.
         */

        if (
            $montoOperacion === null &&
            isset($pago['amount'])
        ) {

            $montoOperacion =
                round(
                    (float) $pago['amount'],
                    2
                );
        }
    }
}


// =========================================================
// SI NO ENCONTRAMOS PAYMENT ID
// =========================================================

if ($paymentId === null) {

    http_response_code(200);

    echo json_encode([
        'ok' => true,
        'message' =>
            'La operación todavía no tiene un pago asociado.'
    ]);

    exit;
}


// =========================================================
// VERIFICAR MONTO
// =========================================================

$montoPedido =
    round(
        (float) $pedido['total'],
        2
    );


if ($montoOperacion === null) {

    http_response_code(200);

    echo json_encode([
        'ok' => true,
        'message' =>
            'La operación todavía no tiene un monto verificable.'
    ]);

    exit;
}


if (
    abs(
        $montoOperacion - $montoPedido
    ) > 0.01
) {

    error_log(
        'Monto Mercado Pago no coincide. '
        . 'Pedido: '
        . $montoPedido
        . ' / Operación: '
        . $montoOperacion
    );


    http_response_code(200);

    echo json_encode([
        'ok' => true,
        'message' =>
            'El monto no coincide con el pedido.'
    ]);

    exit;
}


// =========================================================
// VERIFICAR ESTADO
// =========================================================

if (
    $estadoOperacion !== 'processed'
) {

    http_response_code(200);

    echo json_encode([
        'ok' => true,
        'message' =>
            'El pago todavía no está procesado.',
        'status' =>
            $estadoOperacion
    ]);

    exit;
}


// =========================================================
// GUARDAR PAYMENT ID + CONFIRMAR PEDIDO
// =========================================================

$resultado =
    $pedidoModel->marcarComoPagado(
        $pedidoId,
        $paymentId
    );


// =========================================================
// RESULTADO
// =========================================================

if (!$resultado) {

    /*
     * Puede ocurrir porque el pedido ya estaba confirmado.
     */

    http_response_code(200);

    echo json_encode([
        'ok' => true,
        'message' =>
            'El pedido ya estaba confirmado o no pudo actualizarse.'
    ]);

    exit;
}


// =========================================================
// ÉXITO
// =========================================================

http_response_code(200);

echo json_encode([
    'ok' => true,
    'message' =>
        'Pago verificado y pedido confirmado.',
    'pedido_id' =>
        $pedidoId,
    'payment_id' =>
        $paymentId
]);

exit;