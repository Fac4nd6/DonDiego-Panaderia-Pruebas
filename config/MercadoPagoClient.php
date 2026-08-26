<?php

class MercadoPagoClient
{
    private string $accessToken;

    public function __construct()
    {
        $config = require __DIR__ . '/MercadoPago.php';

        $this->accessToken =
            $config['access_token'] ?? '';

        if (empty($this->accessToken)) {

            throw new Exception(
                'No se configuró el Access Token de Mercado Pago.'
            );
        }
    }


    /**
     * Realiza una petición a la API de Mercado Pago.
     */
    public function request(
        string $method,
        string $endpoint,
        ?array $data = null
    ): array {

        $url =
            'https://api.mercadopago.com'
            . $endpoint;


        $ch = curl_init($url);


        $headers = [

            'Authorization: Bearer '
                . $this->accessToken,

            'Content-Type: application/json',

            'Accept: application/json'

        ];


        curl_setopt_array($ch, [

            CURLOPT_RETURNTRANSFER => true,

            CURLOPT_CUSTOMREQUEST => strtoupper($method),

            CURLOPT_HTTPHEADER => $headers,

            CURLOPT_TIMEOUT => 30

        ]);


        if ($data !== null) {

            curl_setopt(
                $ch,
                CURLOPT_POSTFIELDS,
                json_encode($data)
            );
        }


        $response =
            curl_exec($ch);


        $httpCode =
            curl_getinfo(
                $ch,
                CURLINFO_HTTP_CODE
            );


        if ($response === false) {

            $error =
                curl_error($ch);

            curl_close($ch);

            throw new Exception(
                'Error de conexión con Mercado Pago: '
                . $error
            );
        }


        curl_close($ch);


        $decoded =
            json_decode(
                $response,
                true
            );


        if (!is_array($decoded)) {

            $decoded = [];
        }


        if ($httpCode < 200 || $httpCode >= 300) {

            throw new Exception(
                'Mercado Pago respondió con HTTP '
                . $httpCode
                . '.'
            );
        }


        return $decoded;
    }
}