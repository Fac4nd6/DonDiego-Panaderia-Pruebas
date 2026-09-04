<?php

/*
|--------------------------------------------------------------------------
| CONFIGURACIÓN DE BREVO - EJEMPLO
|--------------------------------------------------------------------------
| Este archivo es solamente una plantilla.
|
| Copiá este archivo como:
|
|     Brevo.php
|
| y completá los datos reales.
|
| IMPORTANTE:
| Brevo.php NO debe subirse a GitHub porque contiene
| credenciales privadas.
|--------------------------------------------------------------------------
*/


define(
    'BREVO_API_KEY',
    'TU_API_KEY_DE_BREVO'
);


define(
    'BREVO_SENDER_EMAIL',
    'tu-correo-verificado@ejemplo.com'
);


define(
    'BREVO_SENDER_NAME',
    'Don Diego'
);


/*
|--------------------------------------------------------------------------
| ENVIAR EMAIL
|--------------------------------------------------------------------------
*/

function enviarCorreoBrevo(
    $destinatario,
    $nombreDestinatario,
    $asunto,
    $contenidoHTML
) {

    $url =
        'https://api.brevo.com/v3/smtp/email';


    $datos = [

        'sender' => [

            'name' =>
                BREVO_SENDER_NAME,

            'email' =>
                BREVO_SENDER_EMAIL
        ],

        'to' => [

            [

                'email' =>
                    $destinatario,

                'name' =>
                    $nombreDestinatario
            ]

        ],

        'subject' =>
            $asunto,

        'htmlContent' =>
            $contenidoHTML
    ];


    $ch =
        curl_init($url);


    curl_setopt_array(
        $ch,
        [

            CURLOPT_POST =>
                true,

            CURLOPT_RETURNTRANSFER =>
                true,

            CURLOPT_HTTPHEADER => [

                'accept: application/json',

                'api-key: ' .
                    BREVO_API_KEY,

                'content-type: application/json'
            ],

            CURLOPT_POSTFIELDS =>
                json_encode($datos),

            CURLOPT_TIMEOUT =>
                15
        ]
    );


    $respuesta =
        curl_exec($ch);


    $codigoHTTP =
        curl_getinfo(
            $ch,
            CURLINFO_HTTP_CODE
        );


    $errorCurl =
        curl_error($ch);


    curl_close($ch);


    /*
    |--------------------------------------------------------------------------
    | ERROR DE CURL
    |--------------------------------------------------------------------------
    */

    if ($respuesta === false) {

        return [

            'success' =>
                false,

            'error' =>
                'No se pudo conectar con Brevo.',

            'response' =>
                $errorCurl,

            'http_code' =>
                $codigoHTTP
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | RESPUESTA DE BREVO
    |--------------------------------------------------------------------------
    */

    if (
        $codigoHTTP < 200 ||
        $codigoHTTP >= 300
    ) {

        return [

            'success' =>
                false,

            'error' =>
                'Brevo rechazó el envío.',

            'response' =>
                $respuesta,

            'http_code' =>
                $codigoHTTP
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | ENVÍO CORRECTO
    |--------------------------------------------------------------------------
    */

    return [

        'success' =>
            true,

        'response' =>
            $respuesta,

        'http_code' =>
            $codigoHTTP
    ];
}
