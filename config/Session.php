<?php

// Configura una sesión segura para el usuario.
require_once __DIR__ . '/Url.php';

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

function usuarioPuede($permiso, $rol = null)
{
    $rolActual = $rol ?? ($_SESSION['usuario_rol'] ?? null);
    if ($rolActual === null) {
        return false;
    }

    $permisosPorRol = [
        'admin' => [
            'ver_pedidos',
            'ver_produccion',
            'gestionar_pedidos',
            'modificar_productos',
            'modificar_precios',
            'modificar_stock',
            'configurar_modalidades',
            'gestionar_produccion',
            'ver_usuarios'
        ],
        'empleado' => [
            'ver_pedidos',
            'ver_produccion',
            'gestionar_pedidos',
            'gestionar_produccion'
        ],
        'cliente' => []
    ];

    return in_array($permiso, $permisosPorRol[$rolActual] ?? [], true);
}

