<?php

class BrevoController
{
    public static function enviarCorreo(
        $destinatario,
        $nombreDestinatario,
        $asunto,
        $html
    ) {

        $config = require __DIR__ . '/../config/Brevo.php';

        $apiKey = $config['api_key'];

        $senderName = $config['sender_name'];

        $senderEmail = $config['sender_email'];


        $datos = [

            'sender' => [
                'name' => $senderName,
                'email' => $senderEmail
            ],

            'to' => [
                [
                    'email' => $destinatario,
                    'name' => $nombreDestinatario
                ]
            ],

            'subject' => $asunto,

            'htmlContent' => $html
        ];


        $ch = curl_init(
            'https://api.brevo.com/v3/smtp/email'
        );


        curl_setopt_array(
            $ch,
            [

                CURLOPT_POST => true,

                CURLOPT_RETURNTRANSFER => true,

                CURLOPT_HTTPHEADER => [

                    'accept: application/json',

                    'api-key: ' . $apiKey,

                    'content-type: application/json'

                ],

                CURLOPT_POSTFIELDS =>
                    json_encode($datos),

                CURLOPT_TIMEOUT => 15

            ]
        );


        $respuesta = curl_exec($ch);


        $httpCode =
            curl_getinfo(
                $ch,
                CURLINFO_HTTP_CODE
            );


        $errorCurl =
            curl_error($ch);


        curl_close($ch);


        if (
            $respuesta === false ||
            !empty($errorCurl)
        ) {

            return [
                'success' => false,
                'error' =>
                    'Error de conexión con Brevo.'
            ];
        }


        if (
            $httpCode >= 200 &&
            $httpCode < 300
        ) {

            return [
                'success' => true
            ];
        }


        return [
            'success' => false,
            'error' =>
                'Brevo rechazó el envío.',
            'response' => $respuesta,
            'http_code' => $httpCode
        ];
    }
}