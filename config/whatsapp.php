<?php

/**
 * Número de WhatsApp del comercio en formato internacional.
 * Reemplazar el placeholder por el número real, sin +, espacios ni símbolos.
 */
define('WHATSAPP_NUMERO', '59892155738');

function crearUrlWhatsApp(string $mensaje): ?string
{
    $numero = WHATSAPP_NUMERO;
    if ($numero === 'WHATSAPP_NUMERO_AQUI' || !preg_match('/^[0-9]+$/', $numero)) {
        return null;
    }

    return 'https://wa.me/' . $numero . '?' . http_build_query(['text' => $mensaje]);
}
