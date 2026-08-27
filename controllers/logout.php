<?php

require_once __DIR__ . '/../config/Session.php';
iniciar_sesion_segura();
require_once __DIR__ . '/../config/Csrf.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	http_response_code(405);
	exit('Método no permitido.');
}

verificar_csrf();

require_once __DIR__ . '/UsuarioController.php';

$usuarioController = new UsuarioController();

$usuarioController->logout();