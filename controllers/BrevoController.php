<?php

// Mantiene una interfaz compatible para enviar correos transaccionales.
require_once __DIR__ . '/../config/EmailService.php';

class BrevoController
{
    public static function enviarCorreo(
        $destinatario,
        $nombreDestinatario,
        $asunto,
        $html
    ) {
        return enviarCorreoTransaccional(
            $destinatario,
            $nombreDestinatario,
            $asunto,
            $html
        );
    }
}
