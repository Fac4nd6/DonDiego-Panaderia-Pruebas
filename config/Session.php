<?php

function iniciar_sesion_segura()
{
    if (session_status() !== PHP_SESSION_NONE) {
        return;
    }

    $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

    ini_set('session.use_strict_mode', '1');
    session_set_cookie_params([
        'httponly' => true,
        'secure' => $https,
        'samesite' => 'Lax'
    ]);

    session_start();
}
