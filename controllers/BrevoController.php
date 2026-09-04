<?php

// Mantiene una interfaz compatible para enviar correos con Brevo.
require_once __DIR__ . '/../config/Brevo.php';

class BrevoController
{
    public static function enviarCorreo(
        $destinatario,
        $nombreDestinatario,
        $asunto,
        $html
    ) {
        return enviarCorreoBrevo(
            $destinatario,
            $nombreDestinatario,
            $asunto,
            $html
        );
    }
}
