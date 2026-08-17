<?php

require_once '../config/Database.php';
require_once '../models/Producto.php';

$productoModel = new Producto($conn);

$accion = $_POST['accion'] ?? $_GET['accion'] ?? 'listar';


// =========================================================
// LISTAR
// =========================================================

if ($accion === 'listar') {

    $productos = $productoModel->obtenerTodos();

    require '../views/admin/productos.php';

    exit;
}


// =========================================================
// CREAR
// =========================================================

if ($accion === 'crear') {

    $categorias = $productoModel->obtenerCategorias();

    require '../views/admin/productos-form.php';

    exit;
}


// =========================================================
// GUARDAR
// =========================================================

if ($accion === 'guardar') {

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ProductoController.php?accion=listar');
        exit;
    }


    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $precio = $_POST['precio'] ?? '';
    $categoriaId = (int) ($_POST['categoria_id'] ?? 0);


    // -----------------------------------------------------
    // VALIDAR DATOS
    // -----------------------------------------------------

    if (
        $nombre === '' ||
        $precio === '' ||
        $categoriaId <= 0
    ) {
        exit('Faltan datos obligatorios.');
    }


    // -----------------------------------------------------
    // COMPROBAR IMAGEN
    // -----------------------------------------------------

    if (!isset($_FILES['imagen'])) {
        exit('No se recibió ninguna imagen.');
    }


    if ($_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {

        exit(
            'Error al subir imagen. Código: '
            . $_FILES['imagen']['error']
        );
    }


    $archivo = $_FILES['imagen'];


    // -----------------------------------------------------
    // CARPETA
    // -----------------------------------------------------

    $carpeta = __DIR__ . '/../public/img/';


    if (!is_dir($carpeta)) {

        if (!mkdir($carpeta, 0777, true)) {
            exit('No se pudo crear la carpeta public/img.');
        }
    }


    if (!is_writable($carpeta)) {
        exit('La carpeta public/img no tiene permisos de escritura.');
    }


    // -----------------------------------------------------
    // VALIDAR IMAGEN
    // -----------------------------------------------------

    $tipo = mime_content_type($archivo['tmp_name']);

    $tiposPermitidos = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp'
    ];


    if (!isset($tiposPermitidos[$tipo])) {

        exit(
            'Formato de imagen no permitido. Tipo detectado: '
            . htmlspecialchars($tipo)
        );
    }


    // -----------------------------------------------------
    // NOMBRE DE IMAGEN
    // -----------------------------------------------------

    $extension = $tiposPermitidos[$tipo];

    $nombreImagen = uniqid('producto_', true) . '.' . $extension;

    $rutaDestino = $carpeta . $nombreImagen;


    // -----------------------------------------------------
    // GUARDAR IMAGEN
    // -----------------------------------------------------

    if (!move_uploaded_file(
        $archivo['tmp_name'],
        $rutaDestino
    )) {

        exit(
            'No se pudo guardar la imagen.<br><br>' .
            'Ruta: ' . htmlspecialchars($rutaDestino)
        );
    }


    // -----------------------------------------------------
    // GUARDAR PRODUCTO
    // -----------------------------------------------------

    $resultado = $productoModel->crear(
        $nombre,
        $descripcion,
        $precio,
        $categoriaId,
        $nombreImagen
    );


    if (!$resultado) {

        if (file_exists($rutaDestino)) {
            unlink($rutaDestino);
        }

        exit('No se pudo guardar el producto en la base de datos.');
    }


    header('Location: ProductoController.php?accion=listar');

    exit;
}


// =========================================================
// EDITAR
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


    $categorias = $productoModel->obtenerCategorias();

    require '../views/admin/productos-form.php';

    exit;
}


// =========================================================
// ACTUALIZAR
// =========================================================

if ($accion === 'actualizar') {

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ProductoController.php?accion=listar');
        exit;
    }


    $id = (int) ($_POST['id'] ?? 0);

    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $precio = $_POST['precio'] ?? '';
    $categoriaId = (int) ($_POST['categoria_id'] ?? 0);
    $activo = (int) ($_POST['activo'] ?? 1);


    if (
        $id <= 0 ||
        $nombre === '' ||
        $precio === '' ||
        $categoriaId <= 0
    ) {
        exit('Datos inválidos.');
    }


    $productoActual = $productoModel->obtenerPorId($id);

    if (!$productoActual) {
        exit('Producto no encontrado.');
    }


    // Mantener imagen actual
    $imagen = $productoActual['imagen'];


    // -----------------------------------------------------
    // NUEVA IMAGEN
    // -----------------------------------------------------

    if (
        isset($_FILES['imagen']) &&
        $_FILES['imagen']['error'] === UPLOAD_ERR_OK
    ) {

        $archivo = $_FILES['imagen'];

        $tipo = mime_content_type($archivo['tmp_name']);

        $tiposPermitidos = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp'
        ];


        if (!isset($tiposPermitidos[$tipo])) {
            exit('Formato de imagen no permitido.');
        }


        $carpeta = __DIR__ . '/../public/img/';


        if (!is_dir($carpeta)) {

            if (!mkdir($carpeta, 0777, true)) {
                exit('No se pudo crear la carpeta public/img.');
            }
        }


        if (!is_writable($carpeta)) {
            exit('La carpeta public/img no tiene permisos de escritura.');
        }


        $extension = $tiposPermitidos[$tipo];

        $nuevaImagen =
            uniqid('producto_', true)
            . '.'
            . $extension;


        $rutaDestino = $carpeta . $nuevaImagen;


        if (!move_uploaded_file(
            $archivo['tmp_name'],
            $rutaDestino
        )) {
            exit('No se pudo guardar la nueva imagen.');
        }


        // Eliminar imagen anterior
        if (
            !empty($productoActual['imagen']) &&
            file_exists($carpeta . $productoActual['imagen'])
        ) {
            unlink($carpeta . $productoActual['imagen']);
        }


        $imagen = $nuevaImagen;
    }


    // -----------------------------------------------------
    // ACTUALIZAR BD
    // -----------------------------------------------------

    $resultado = $productoModel->actualizar(
        $id,
        $nombre,
        $descripcion,
        $precio,
        $categoriaId,
        $imagen,
        $activo
    );


    if (!$resultado) {
        exit('No se pudo actualizar el producto.');
    }


    header('Location: ProductoController.php?accion=listar');

    exit;
}


// =========================================================
// DESACTIVAR
// =========================================================

if ($accion === 'desactivar') {

    $id = (int) ($_GET['id'] ?? 0);

    if ($id <= 0) {
        exit('Producto no válido.');
    }


    if (!$productoModel->desactivar($id)) {
        exit('No se pudo desactivar el producto.');
    }


    header('Location: ProductoController.php?accion=listar');

    exit;
}


// =========================================================
// ACTIVAR
// =========================================================

if ($accion === 'activar') {

    $id = (int) ($_GET['id'] ?? 0);

    if ($id <= 0) {
        exit('Producto no válido.');
    }


    if (!$productoModel->activar($id)) {
        exit('No se pudo activar el producto.');
    }


    header('Location: ProductoController.php?accion=listar');

    exit;
}

// =========================================================
// ELIMINAR PRODUCTO DEFINITIVAMENTE
// =========================================================

if ($accion === 'eliminar') {

    // Aceptar ID tanto por GET como por POST
    $id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

    if ($id <= 0) {
        exit('Producto no válido.');
    }


    // -----------------------------------------------------
    // OBTENER PRODUCTO
    // -----------------------------------------------------

    $producto = $productoModel->obtenerPorId($id);

    if (!$producto) {
        exit('Producto no encontrado.');
    }


    // -----------------------------------------------------
    // ELIMINAR DE LA BASE DE DATOS
    // -----------------------------------------------------

    if (!$productoModel->eliminar($id)) {
        exit('No se pudo eliminar el producto.');
    }


    // -----------------------------------------------------
    // ELIMINAR IMAGEN
    // -----------------------------------------------------

    if (!empty($producto['imagen'])) {

        $rutaImagen = __DIR__ . '/../public/img/' . $producto['imagen'];

        if (file_exists($rutaImagen)) {
            unlink($rutaImagen);
        }
    }


    // -----------------------------------------------------
    // VOLVER A PRODUCTOS
    // -----------------------------------------------------

    header('Location: ProductoController.php?accion=listar');

    exit;
}
// =========================================================
// ACCIÓN NO EXISTENTE
// =========================================================

http_response_code(404);

echo 'Acción no encontrada.';