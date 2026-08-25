<?php

/*
|--------------------------------------------------------------------------
| PROTECCIÓN CSRF
|--------------------------------------------------------------------------
| Genera y verifica un token único para la sesión.
|--------------------------------------------------------------------------
*/


// =========================================================
// OBTENER / CREAR TOKEN
// =========================================================

function csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {

        $_SESSION['csrf_token'] =
            bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}


// =========================================================
// VERIFICAR TOKEN
// =========================================================

function verificar_csrf()
{
    $token =
        $_POST['csrf_token'] ?? '';

    if (
        empty($token) ||
        empty($_SESSION['csrf_token']) ||
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