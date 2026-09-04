<?php

/*
|--------------------------------------------------------------------------
| PROTECCIÓN CSRF
|--------------------------------------------------------------------------
| Genera y verifica un token único para la sesión.
|--------------------------------------------------------------------------
*/


// ASEGURAR SESIÓN
require_once __DIR__ . '/Session.php';
iniciar_sesion_segura();


// OBTENER / CREAR TOKEN
function csrf_token()
{
    if (
        empty($_SESSION['csrf_token']) ||
        !is_string($_SESSION['csrf_token'])
    ) {

        $_SESSION['csrf_token'] =
            bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}


// VERIFICAR TOKEN
function verificar_csrf()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

        http_response_code(405);

        exit(
            'Método no permitido.'
        );
    }


    $token =
        $_POST['csrf_token'] ?? '';


    if (
        empty($token) ||
        empty($_SESSION['csrf_token']) ||
        !is_string($token) ||
        !hash_equals(
            $_SESSION['csrf_token'],
            $token
        )
    ) {

        http_response_code(403);

        exit(
            'Solicitud no válida. '
            . 'El formulario puede haber expirado. '
            . 'Volvé a cargar la página e intentá nuevamente.'
        );
    }
}