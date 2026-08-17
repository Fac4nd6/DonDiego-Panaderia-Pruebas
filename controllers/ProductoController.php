

<?php

require_once '../config/Database.php';
require_once '../models/Producto.php';

$productoModel = new Producto($conn);

$accion = $_GET['accion'] ?? 'listar';


// =========================================================
// LISTAR PRODUCTOS
// =========================================================

if ($accion === 'listar') {

    $productos = $productoModel->obtenerTodos();

    require '../views/admin/productos.php';

    exit;
}


// =========================================================
// MOSTRAR FORMULARIO PARA CREAR
// =========================================================

if ($accion === 'crear') {

    require '../views/admin/productos-form.php';

    exit;
}


// =========================================================
// GUARDAR PRODUCTO
// =========================================================

if ($accion === 'guardar') {

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ProductoController.php?accion=listar');
        exit;
    }

    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $precio = $_POST['precio'] ?? 0;
    $categoriaId = $_POST['categoria_id'] ?? 0;
    $imagen = trim($_POST['imagen'] ?? '');

    if (
        $nombre === '' ||
        $precio === '' ||
        $categoriaId === ''
    ) {

        exit('Faltan datos obligatorios.');

    }

    $productoModel->crear(
        $nombre,
        $descripcion,
        $precio,
        $categoriaId,
        $imagen
    );

    header('Location: ProductoController.php?accion=listar');

    exit;
}


// =========================================================
// MOSTRAR FORMULARIO PARA EDITAR
// =========================================================

if ($accion === 'editar') {

    $id = (int) ($_GET['id'] ?? 0);

    if ($id <= 0) {
        exit('Producto no válido.');
    }

    $producto = $productoModel->obtenerPorId($id);

    if (!$producto) {
        exit('Producto no encontrado.');
    }

    require '../views/admin/productos-form.php';

    exit;
}


// =========================================================
// ACTUALIZAR PRODUCTO
// =========================================================

if ($accion === 'actualizar') {

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ProductoController.php?accion=listar');
        exit;
    }

    $id = (int) ($_POST['id'] ?? 0);

    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $precio = $_POST['precio'] ?? 0;
    $categoriaId = $_POST['categoria_id'] ?? 0;
    $imagen = trim($_POST['imagen'] ?? '');

    if (
        $id <= 0 ||
        $nombre === '' ||
        $precio === '' ||
        $categoriaId === ''
    ) {

        exit('Datos inválidos.');

    }

    $productoModel->actualizar(
        $id,
        $nombre,
        $descripcion,
        $precio,
        $categoriaId,
        $imagen
    );

    header('Location: ProductoController.php?accion=listar');

    exit;
}


// =========================================================
// DESACTIVAR PRODUCTO
// =========================================================

if ($accion === 'desactivar') {

    $id = (int) ($_GET['id'] ?? 0);

    if ($id <= 0) {
        exit('Producto no válido.');
    }

    $productoModel->desactivar($id);

    header('Location: ProductoController.php?accion=listar');

    exit;
}


// =========================================================
// ACCIÓN NO EXISTENTE
// =========================================================

http_response_code(404);

echo 'Acción no encontrada.';