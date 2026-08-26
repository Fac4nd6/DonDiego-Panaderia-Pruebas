<?php

class MercadoPagoService
{
    private string $accessToken;
    private string $baseUrl;


    /* =========================================================
       CONSTRUCTOR
    ========================================================= */

    public function __construct()
    {
        $config = require __DIR__ . '/../config/mercadopago.php';

        $this->accessToken =
            trim($config['access_token'] ?? '');

        $this->baseUrl =
            rtrim(
                $config['base_url'] ?? '',
                '/'
            );
    }


    /* =========================================================
       VALIDAR ACCESS TOKEN
    ========================================================= */

    private function validarAccessToken(): void
    {
        if (
            empty($this->accessToken) ||
            $this->accessToken === 'TU_ACCESS_TOKEN'
        ) {
            throw new Exception(
                'Mercado Pago todavía no tiene configurado el Access Token.'
            );
        }
    }


    /* =========================================================
       CREAR ORDEN
    ========================================================= */

    public function crearOrden(
        $pedido,
        $productos
    ) {

        $this->validarAccessToken();


        if (
            !is_array($pedido) ||
            empty($pedido['id'])
        ) {
            throw new Exception(
                'Los datos del pedido no son válidos.'
            );
        }


        /* -----------------------------------------------------
           PRODUCTOS
        ----------------------------------------------------- */

        $items = [];

        foreach ($productos as $producto) {

            $cantidad =
                (int) ($producto['cantidad'] ?? 0);

            $precio =
                (float) ($producto['precio_unitario']
                ?? $producto['precio']
                ?? 0);

            $nombre =
                trim(
                    (string) ($producto['nombre'] ?? '')
                );


            if ($cantidad <= 0) {

                throw new Exception(
                    'Uno de los productos tiene una cantidad inválida.'
                );
            }


            if ($precio < 0) {

                throw new Exception(
                    'Uno de los productos tiene un precio inválido.'
                );
            }


            if ($nombre === '') {

                throw new Exception(
                    'Uno de los productos no tiene nombre.'
                );
            }


            $subtotal =
                round(
                    $precio * $cantidad,
                    2
                );


            $items[] = [

                'title' =>
                    $nombre,

                'quantity' =>
                    $cantidad,

                'unit_price' =>
                    number_format(
                        $precio,
                        2,
                        '.',
                        ''
                    ),

                'unit_measure' =>
                    'unit',

                'total_amount' =>
                    number_format(
                        $subtotal,
                        2,
                        '.',
                        ''
                    )
            ];
        }


        if (empty($items)) {

            throw new Exception(
                'No hay productos para crear el pago.'
            );
        }


        /* -----------------------------------------------------
           TOTAL
        ----------------------------------------------------- */

        $total =
            round(
                (float) $pedido['total'],
                2
            );


        if ($total <= 0) {

            throw new Exception(
                'El total del pedido no es válido.'
            );
        }


        /* -----------------------------------------------------
           DATOS DE LA ORDEN
        ----------------------------------------------------- */

        $data = [

            'type' =>
                'online',

            'processing_mode' =>
                'manual',

            'total_amount' =>
                number_format(
                    $total,
                    2,
                    '.',
                    ''
                ),

            'external_reference' =>
                (string) $pedido['id'],

            'items' =>
                $items,

            'notification_url' =>
                $this->baseUrl
                . '/controllers/MercadoPagoController.php'
        ];


        /* -----------------------------------------------------
           JSON
        ----------------------------------------------------- */

        $json =
            json_encode(
                $data,
                JSON_UNESCAPED_UNICODE |
                JSON_UNESCAPED_SLASHES
            );


        if ($json === false) {

            throw new Exception(
                'No se pudieron preparar los datos del pago.'
            );
        }


        /* -----------------------------------------------------
           ID DE IDEMPOTENCIA
        ----------------------------------------------------- */

        $idempotencyKey =
            bin2hex(
                random_bytes(16)
            );


        /* -----------------------------------------------------
           CONEXIÓN
        ----------------------------------------------------- */

        $ch =
            curl_init(
                'https://api.mercadopago.com/v1/orders'
            );


        if ($ch === false) {

            throw new Exception(
                'No se pudo iniciar la conexión con Mercado Pago.'
            );
        }


        curl_setopt_array(
            $ch,
            [

                CURLOPT_RETURNTRANSFER =>
                    true,

                CURLOPT_POST =>
                    true,

                CURLOPT_HTTPHEADER => [

                    'Content-Type: application/json',

                    'Authorization: Bearer '
                    . $this->accessToken,

                    'X-Idempotency-Key: '
                    . $idempotencyKey

                ],

                CURLOPT_POSTFIELDS =>
                    $json,

                CURLOPT_CONNECTTIMEOUT =>
                    10,

                CURLOPT_TIMEOUT =>
                    30

            ]
        );


        $response =
            curl_exec($ch);


        $curlError =
            curl_error($ch);


        $httpCode =
            curl_getinfo(
                $ch,
                CURLINFO_HTTP_CODE
            );


        curl_close($ch);


        /* -----------------------------------------------------
           ERROR DE CONEXIÓN
        ----------------------------------------------------- */

        if ($response === false) {

            throw new Exception(
                'No se pudo conectar con Mercado Pago: '
                . $curlError
            );
        }


        /* -----------------------------------------------------
           RESPUESTA
        ----------------------------------------------------- */

        $resultado =
            json_decode(
                $response,
                true
            );


        if (!is_array($resultado)) {

            throw new Exception(
                'Mercado Pago devolvió una respuesta inválida.'
            );
        }


        /* -----------------------------------------------------
           ERROR HTTP
        ----------------------------------------------------- */

        if (
            $httpCode < 200 ||
            $httpCode >= 300
        ) {

            $mensaje =
                $resultado['message']
                ?? $resultado['error']
                ?? 'Error desconocido';


            throw new Exception(
                'Mercado Pago rechazó la solicitud: '
                . $mensaje
            );
        }


        /* -----------------------------------------------------
           ID DE ORDEN
        ----------------------------------------------------- */

        if (
            empty($resultado['id'])
        ) {

            throw new Exception(
                'Mercado Pago creó la respuesta, '
                . 'pero no devolvió el ID de la orden.'
            );
        }


        /* -----------------------------------------------------
           CHECKOUT
        ----------------------------------------------------- */

        if (
            empty(
                $resultado['checkout_url']
            )
        ) {

            throw new Exception(
                'Mercado Pago creó la orden, '
                . 'pero no devolvió una checkout_url.'
            );
        }


        return $resultado;
    }


    /* =========================================================
       CONSULTAR ORDEN
    ========================================================= */

    public function consultarOperacion(
        $operacionId
    ) {

        $this->validarAccessToken();


        $operacionId =
            trim(
                (string) $operacionId
            );


        if ($operacionId === '') {

            throw new Exception(
                'El ID de la operación no es válido.'
            );
        }


        $url =
            'https://api.mercadopago.com/v1/orders/'
            . rawurlencode(
                $operacionId
            );


        $ch =
            curl_init($url);


        if ($ch === false) {

            throw new Exception(
                'No se pudo iniciar la conexión con Mercado Pago.'
            );
        }


        curl_setopt_array(
            $ch,
            [

                CURLOPT_RETURNTRANSFER =>
                    true,

                CURLOPT_HTTPGET =>
                    true,

                CURLOPT_HTTPHEADER => [

                    'Content-Type: application/json',

                    'Authorization: Bearer '
                    . $this->accessToken

                ],

                CURLOPT_CONNECTTIMEOUT =>
                    10,

                CURLOPT_TIMEOUT =>
                    30

            ]
        );


        $response =
            curl_exec($ch);


        $curlError =
            curl_error($ch);


        $httpCode =
            curl_getinfo(
                $ch,
                CURLINFO_HTTP_CODE
            );


        curl_close($ch);


        if ($response === false) {

            throw new Exception(
                'No se pudo consultar Mercado Pago: '
                . $curlError
            );
        }


        $resultado =
            json_decode(
                $response,
                true
            );


        if (!is_array($resultado)) {

            throw new Exception(
                'Mercado Pago devolvió una respuesta inválida.'
            );
        }


        if (
            $httpCode < 200 ||
            $httpCode >= 300
        ) {

            $mensaje =
                $resultado['message']
                ?? $resultado['error']
                ?? 'Error desconocido';


            throw new Exception(
                'Mercado Pago rechazó la consulta: '
                . $mensaje
            );
        }


        return $resultado;
    }
}