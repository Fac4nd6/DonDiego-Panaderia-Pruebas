<?php

require_once __DIR__ . '/../config/Session.php';
iniciar_sesion_segura();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . url('/login'));
    exit;
}

require_once __DIR__ . '/../config/Csrf.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Produccion.php';
require_once __DIR__ . '/../models/Producto.php';

if (!usuarioPuede('ver_produccion')) {
    http_response_code(403);
    exit('No tenés permisos para consultar planificación de producción.');
}

$produccionModel = new Produccion($conn);
$accion = $_POST['accion'] ?? $_GET['accion'] ?? 'seleccionar';
$errores = [];
$esquemaDisponible = $produccionModel->esquemaDisponible();
$metadatosDisponibles = $produccionModel->metadatosDisponibles();
$estadosComerciales = $produccionModel->estadosComerciales();
$estadoFiltro = $produccionModel->normalizarFiltroEstado($_POST['estado_filtro'] ?? $_GET['estado'] ?? 'pendiente');

if (!$esquemaDisponible) {
    $modo = 'seleccionar';
    $pedidos = [];
    $lineasPedido = [];
    $revision = [];
    $metadatos = ['titulo' => '', 'destinos' => [], 'observaciones' => ''];
    $pageTitle = 'Planificación de producción';
    require __DIR__ . '/../views/admin/planificacion.php';
    exit;
}

if ($accion === 'actualizar_metadatos') {
    if (!usuarioPuede('gestionar_produccion')) {
        http_response_code(403);
        exit('No tenés permisos para editar los datos de una tanda.');
    }
    verificar_csrf();
    if (!$metadatosDisponibles) {
        header('Location: ' . url('/admin/tandas/detalle?id=' . (int) ($_POST['tanda_id'] ?? 0) . '&error=esquema'));
        exit;
    }

    $tandaId = (int) ($_POST['tanda_id'] ?? 0);
    if (!$produccionModel->actualizarMetadatos($tandaId, (int) $_SESSION['usuario_id'], $_POST)) {
        header('Location: ' . url('/admin/tandas/detalle?id=' . $tandaId . '&error=metadatos'));
        exit;
    }
    header('Location: ' . url('/admin/tandas/detalle?id=' . $tandaId . '&actualizada=1'));
    exit;
}

if ($accion === 'crear_tanda') {
    if (!usuarioPuede('gestionar_produccion')) {
        http_response_code(403);
        exit('No tenés permisos para crear tandas de producción.');
    }
    verificar_csrf();
    if (!$metadatosDisponibles) {
        $errores[] = 'Aplicá la migración incremental de metadatos antes de crear tandas.';
        $modo = 'seleccionar';
        $estadoFiltro = $produccionModel->normalizarFiltroEstado($_POST['estado_filtro'] ?? 'pendiente');
        $pedidos = $produccionModel->obtenerPedidos($estadoFiltro);
        $lineasPedido = [];
        $revision = [];
        $gruposProduccion = [];
        $metadatos = ['titulo' => '', 'destinos' => [], 'observaciones' => ''];
        $pageTitle = 'Planificación de producción';
        require __DIR__ . '/../views/admin/planificacion.php';
        exit;
    }

    $seleccion = $_POST['lineas'] ?? [];
    if (!is_array($seleccion)) {
        $seleccion = [];
    }
    $tandaId = $produccionModel->crearTanda($seleccion, (int) $_SESSION['usuario_id'], $_POST);
    if ($tandaId === false) {
        $errores[] = $produccionModel->obtenerUltimoError();
        $modo = 'seleccionar';
        $estadoFiltro = $produccionModel->normalizarFiltroEstado($_POST['estado_filtro'] ?? 'pendiente');
        $pedidos = $produccionModel->obtenerPedidos($estadoFiltro);
        $pedidoIds = array_values(array_unique(array_map(
            static fn ($linea) => (int) ($linea['pedido_id'] ?? 0),
            is_array($seleccion) ? $seleccion : []
        )));
        $lineasPedido = $produccionModel->obtenerLineasPedidos($pedidoIds, $estadoFiltro);
        $gruposProduccion = $produccionModel->agruparLineas($lineasPedido);
        $revision = [];
        $metadatos = [
            'titulo' => is_scalar($_POST['titulo'] ?? '') ? (string) ($_POST['titulo'] ?? '') : '',
            'destinos' => is_array($_POST['destinos'] ?? null) ? $_POST['destinos'] : [],
            'observaciones' => is_scalar($_POST['observaciones'] ?? '') ? (string) ($_POST['observaciones'] ?? '') : ''
        ];
        $pageTitle = 'Planificación de producción';
        require __DIR__ . '/../views/admin/planificacion.php';
        exit;
    }

    header('Location: ' . url('/admin/tandas/detalle?id=' . $tandaId . '&creada=1'));
    exit;
}

if ($accion === 'revisar') {
    if (!usuarioPuede('gestionar_produccion')) {
        http_response_code(403);
        exit('No tenés permisos para preparar una tanda de producción.');
    }
    verificar_csrf();

    $pedidoIds = $_POST['pedido_ids'] ?? [];
    if (!is_array($pedidoIds)) {
        $pedidoIds = [];
    }
    $pedidoIds = array_slice(array_values(array_unique(array_filter(array_map('intval', $pedidoIds), static fn ($id) => $id > 0))), 0, 50);
    $estadoFiltro = $produccionModel->normalizarFiltroEstado($_POST['estado_filtro'] ?? 'pendiente');
    $lineasCandidatas = $produccionModel->obtenerLineasPedidos($pedidoIds, $estadoFiltro);
    $clavesGrupo = $_POST['grupos_seleccionados'] ?? [];
    $clavesGrupo = is_array($clavesGrupo) ? $clavesGrupo : [];
    $parciales = $_POST['parciales'] ?? [];
    $parciales = is_array($parciales) ? $parciales : [];
    $seleccionPreparada = $produccionModel->prepararSeleccionGrupos($lineasCandidatas, $clavesGrupo, $parciales);
    $revision = $produccionModel->revisarSeleccion($seleccionPreparada['seleccion'], $pedidoIds, $estadoFiltro);
    $revision['errores'] = array_values(array_unique(array_merge($seleccionPreparada['errores'], $revision['errores'])));
    $errores = $revision['errores'];
    $modo = $errores === [] ? 'metadatos' : 'seleccionar';
    $metadatos = ['titulo' => '', 'destinos' => [], 'observaciones' => ''];
    $pedidos = $produccionModel->obtenerPedidos($estadoFiltro);
    $lineasPedido = $lineasCandidatas;
    $gruposProduccion = $produccionModel->agruparLineas($lineasPedido);
    $gruposRevision = $produccionModel->agruparLineas($revision['lineas']);
    $pageTitle = 'Datos de la tanda de producción';
    require __DIR__ . '/../views/admin/planificacion.php';
    exit;
}

if (in_array($accion, ['confirmar_revision', 'editar_metadatos'], true)) {
    if (!usuarioPuede('gestionar_produccion')) {
        http_response_code(403);
        exit('No tenés permisos para revisar una tanda de producción.');
    }
    verificar_csrf();
    $pedidoIds = $_POST['pedido_ids'] ?? [];
    $pedidoIds = is_array($pedidoIds)
        ? array_slice(array_values(array_unique(array_filter(array_map('intval', $pedidoIds), static fn ($id) => $id > 0))), 0, 50)
        : [];
    $seleccion = $_POST['lineas'] ?? [];
    $seleccion = is_array($seleccion) ? $seleccion : [];
    $estadoFiltro = $produccionModel->normalizarFiltroEstado($_POST['estado_filtro'] ?? 'pendiente');
    $revision = $produccionModel->revisarSeleccion($seleccion, $pedidoIds, $estadoFiltro);
    $metadatos = $produccionModel->validarMetadatos($_POST);
    if ($metadatos === false) {
        $metadatos = [
            'titulo' => is_scalar($_POST['titulo'] ?? '') ? (string) ($_POST['titulo'] ?? '') : '',
            'destinos' => is_array($_POST['destinos'] ?? null) ? $_POST['destinos'] : [],
            'observaciones' => is_scalar($_POST['observaciones'] ?? '') ? (string) ($_POST['observaciones'] ?? '') : ''
        ];
        $revision['errores'][] = 'El título, los destinos o las observaciones superan los límites permitidos.';
    }
    $errores = $revision['errores'];
    $modo = $errores === [] && $accion === 'confirmar_revision' ? 'revision' : 'metadatos';
    $pedidos = $produccionModel->obtenerPedidos($estadoFiltro);
    $lineasPedido = $produccionModel->obtenerLineasPedidos($pedidoIds, $estadoFiltro);
    $gruposProduccion = $produccionModel->agruparLineas($lineasPedido);
    $gruposRevision = $produccionModel->agruparLineas($revision['lineas']);
    $pageTitle = $modo === 'revision' ? 'Revisar tanda de producción' : 'Datos de la tanda de producción';
    require __DIR__ . '/../views/admin/planificacion.php';
    exit;
}

if ($accion === 'historial') {
    $tandas = $produccionModel->obtenerTandas();
    $pageTitle = 'Historial de producción';
    require __DIR__ . '/../views/admin/tandas.php';
    exit;
}

if (in_array($accion, ['detalle', 'imprimir'], true)) {
    $tandaId = (int) ($_GET['id'] ?? 0);
    $tanda = $tandaId > 0 ? $produccionModel->obtenerTanda($tandaId) : null;
    if (!$tanda) {
        http_response_code(404);
        exit('No se encontró la tanda solicitada.');
    }

    $detallesTanda = $produccionModel->obtenerDetallesTanda($tandaId);
    $cambiosTanda = $produccionModel->obtenerCambiosTanda($tandaId);
    $sectorFiltro = is_string($_GET['sector'] ?? null) ? trim($_GET['sector']) : '';
    if (strlen($sectorFiltro) > 100) {
        http_response_code(400);
        exit('El sector solicitado no es válido.');
    }
    if ($sectorFiltro !== '') {
        $detallesTanda = array_values(array_filter(
            $detallesTanda,
            static fn ($detalle) => (string) $detalle['sector'] === $sectorFiltro
        ));
        if ($detallesTanda === []) {
            http_response_code(404);
            exit('No se encontró ese sector en la tanda.');
        }
    }
    $modoImpresion = $accion === 'imprimir';
    $sectorDocumento = $sectorFiltro;
    $pageTitle = 'Tanda de producción #' . $tandaId;
    require __DIR__ . '/../views/admin/tanda-detalle.php';
    exit;
}

$modo = 'seleccionar';
$estadoFiltro = $produccionModel->normalizarFiltroEstado($_GET['estado'] ?? 'pendiente');
$pedidos = $produccionModel->obtenerPedidos($estadoFiltro);
$pedidoIds = $_GET['pedido_ids'] ?? [];
if (!is_array($pedidoIds)) {
    $pedidoIds = [];
}
$pedidoIds = array_slice(array_values(array_unique(array_filter(array_map('intval', $pedidoIds), static fn ($id) => $id > 0))), 0, 50);
$lineasPedido = $produccionModel->obtenerLineasPedidos($pedidoIds, $estadoFiltro);
$gruposProduccion = $produccionModel->agruparLineas($lineasPedido);
$gruposRevision = [];
$revision = [];
$metadatos = ['titulo' => '', 'destinos' => [], 'observaciones' => ''];
$pageTitle = 'Planificación de producción';
require __DIR__ . '/../views/admin/planificacion.php';