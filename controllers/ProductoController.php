<?php

// =========================================================
// SESIÓN
if (!isset($_SESSION['usuario_id'])) {

    header(
        'Location: /DonDiego-Panaderia-Pruebas/views/usuarios/login.php'
    );

    exit;
}


// =========================================================
// COMPROBAR QUE SEA ADMIN
// =========================================================

if (
    !isset($_SESSION['usuario_rol']) ||
    $_SESSION['usuario_rol'] !== 'admin'
) {

    http_response_code(403);
    $codigoError = 404;
    $tituloError = 'Página no encontrada';
    $descripcionError = 'No pudimos encontrar lo que estabas buscando.';
    $urlVolver = '/DonDiego-Panaderia-Pruebas/controllers/HomeController.php';
    $textoVolver = 'Volver al inicio';
    require __DIR__ . '/../views/errors/error.php';
    exit;
}


// =========================================================
// MODELOS
// =========================================================

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

        header(
            'Location: ProductoController.php?accion=listar'
        );

        exit;
    }

    verificar_csrf();


    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $precio = $_POST['precio'] ?? '';
    $categoriaId = (int) ($_POST['categoria_id'] ?? 0);
    $stock = filter_var($_POST['stock'] ?? null, FILTER_VALIDATE_INT);


    // -----------------------------------------------------
    // VALIDAR DATOS
    // -----------------------------------------------------

    if (
        $nombre === '' ||
        $precio === '' ||
        $categoriaId <= 0 ||
        $stock === false ||
        $stock < 0
    ) {

        exit(
            'Faltan datos obligatorios.'
        );
    }


    // -----------------------------------------------------
    // COMPROBAR IMAGEN
    // -----------------------------------------------------

    if (!isset($_FILES['imagen'])) {

        exit(
            'No se recibió ninguna imagen.'
        );
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

        if (!mkdir($carpeta, 0755, true)) {

            exit(
                'No se pudo crear la carpeta public/img.'
            );
        }
    }


    if (!is_writable($carpeta)) {

        exit(
            'La carpeta public/img no tiene permisos de escritura.'
        );
    }


    // -----------------------------------------------------
    // VALIDAR IMAGEN
    // -----------------------------------------------------

    $tipo = mime_content_type(
        $archivo['tmp_name']
    );

    if ((int) $archivo['size'] > 5 * 1024 * 1024) {
        exit('La imagen no puede superar los 5 MB.');
    }

    $dimensiones = @getimagesize($archivo['tmp_name']);
    if (!$dimensiones || $dimensiones[0] > 5000 || $dimensiones[1] > 5000) {
        exit('Las dimensiones de la imagen no son válidas.');
    }

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

    $nombreImagen =
        uniqid('producto_', true)
        . '.'
        . $extension;

    $rutaDestino =
        $carpeta . $nombreImagen;


    // -----------------------------------------------------
    // GUARDAR IMAGEN
    // -----------------------------------------------------

    if (
        !move_uploaded_file(
            $archivo['tmp_name'],
            $rutaDestino
        )
    ) {

        exit(
            'No se pudo guardar la imagen.<br><br>'
            . 'Ruta: '
            . htmlspecialchars($rutaDestino)
        );
    }


    // -----------------------------------------------------
    // GUARDAR PRODUCTO
    // -----------------------------------------------------

    $resultado =
        $productoModel->crear(
            $nombre,
            $descripcion,
            $precio,
            $categoriaId,
            $nombreImagen,
            $stock
        );


    if (!$resultado) {

        if (file_exists($rutaDestino)) {

            unlink($rutaDestino);
        }

        exit(
            'No se pudo guardar el producto en la base de datos.'
        );
    }


    header(
        'Location: ProductoController.php?accion=listar'
    );

    exit;
}


// =========================================================
// EDITAR
// =========================================================

if ($accion === 'editar') {

    $id =
        (int) ($_GET['id'] ?? 0);


    if ($id <= 0) {

        exit(
            'Producto no válido.'
        );
    }


    $producto =
        $productoModel->obtenerPorId($id);


    if (!$producto) {
        http_response_code(404);
        $codigoError = 404;
        $tituloError = 'Producto no encontrado';
        $descripcionError = 'El producto que buscás ya no está disponible.';
        $urlVolver = '/DonDiego-Panaderia-Pruebas/controllers/CatalogoController.php';
        $textoVolver = 'Volver al catálogo';
        require __DIR__ . '/../views/errors/error.php';
        exit;
    }


    $categorias =
        $productoModel->obtenerCategorias();


    require '../views/admin/productos-form.php';

    exit;
}


// =========================================================
// ACTUALIZAR
// =========================================================

if ($accion === 'actualizar') {

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

        header(
            'Location: ProductoController.php?accion=listar'
        );

        exit;
    }

    verificar_csrf();


    $id =
        (int) ($_POST['id'] ?? 0);

    $nombre =
        trim($_POST['nombre'] ?? '');

    $descripcion =
        trim($_POST['descripcion'] ?? '');

    $precio =
        $_POST['precio'] ?? '';

    $categoriaId =
        (int) ($_POST['categoria_id'] ?? 0);

    $activo =
        (int) ($_POST['activo'] ?? 1);

    $stock = filter_var($_POST['stock'] ?? null, FILTER_VALIDATE_INT);


    if (
        $id <= 0 ||
        $nombre === '' ||
        $precio === '' ||
        $categoriaId <= 0 ||
        $stock === false ||
        $stock < 0
    ) {

        exit(
            'Datos inválidos.'
        );
    }


    $productoActual =
        $productoModel->obtenerPorId($id);


    if (!$productoActual) {
        http_response_code(404);
        $codigoError = 404;
        $tituloError = 'Producto no encontrado';
        $descripcionError = 'El producto que buscás ya no está disponible.';
        $urlVolver = '/DonDiego-Panaderia-Pruebas/controllers/ProductoController.php?accion=listar';
        $textoVolver = 'Volver a productos';
        require __DIR__ . '/../views/errors/error.php';
        exit;
    }


    // -----------------------------------------------------
    // MANTENER IMAGEN ACTUAL
    // -----------------------------------------------------

    $imagen =
        $productoActual['imagen'];

    $carpeta =
        __DIR__ . '/../public/img/';


    // -----------------------------------------------------
    // NUEVA IMAGEN
    // -----------------------------------------------------

    if (
        isset($_FILES['imagen']) &&
        $_FILES['imagen']['error'] === UPLOAD_ERR_OK
    ) {

        $archivo =
            $_FILES['imagen'];


        $tipo =
            mime_content_type(
                $archivo['tmp_name']
            );

        if ((int) $archivo['size'] > 5 * 1024 * 1024) {
            exit('La imagen no puede superar los 5 MB.');
        }

        $dimensiones = @getimagesize($archivo['tmp_name']);
        if (!$dimensiones || $dimensiones[0] > 5000 || $dimensiones[1] > 5000) {
            exit('Las dimensiones de la imagen no son válidas.');
        }


        $tiposPermitidos = [

            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp'

        ];


        if (!isset($tiposPermitidos[$tipo])) {

            exit(
                'Formato de imagen no permitido.'
            );
        }


        if (!is_dir($carpeta)) {

            if (!mkdir($carpeta, 0755, true)) {

                exit(
                    'No se pudo crear la carpeta public/img.'
                );
            }
        }


        if (!is_writable($carpeta)) {

            exit(
                'La carpeta public/img no tiene permisos de escritura.'
            );
        }


        $extension =
            $tiposPermitidos[$tipo];


        $nuevaImagen =
            uniqid('producto_', true)
            . '.'
            . $extension;


        $rutaDestino =
            $carpeta . $nuevaImagen;


        if (
            !move_uploaded_file(
                $archivo['tmp_name'],
                $rutaDestino
            )
        ) {

            exit(
                'No se pudo guardar la nueva imagen.'
            );
        }


        $imagen =
            $nuevaImagen;
    }


    // -----------------------------------------------------
    // ACTUALIZAR BD
    // -----------------------------------------------------

    $resultado =
        $productoModel->actualizar(
            $id,
            $nombre,
            $descripcion,
            $precio,
            $categoriaId,
            $imagen,
            $activo,
            $stock
        );


    if (!$resultado) {

        if (isset($rutaDestino) && file_exists($rutaDestino)) {
            unlink($rutaDestino);
        }

        exit(
            'No se pudo actualizar el producto.'
        );
    }

    if (!empty($nuevaImagen) && !empty($productoActual['imagen'])) {
        $rutaAnterior = $carpeta . $productoActual['imagen'];
        if (file_exists($rutaAnterior)) {
            unlink($rutaAnterior);
        }
    }


    header(
        'Location: ProductoController.php?accion=listar'
    );

    exit;
}


// =========================================================
// DESACTIVAR
// =========================================================

if ($accion === 'desactivar') {

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        exit('Método no permitido.');
    }

    verificar_csrf();

    $id =
        (int) ($_POST['id'] ?? 0);


    if ($id <= 0) {

        exit(
            'Producto no válido.'
        );
    }


    if (
        !$productoModel->desactivar($id)
    ) {

        exit(
            'No se pudo desactivar el producto.'
        );
    }


    header(
        'Location: ProductoController.php?accion=listar'
    );

    exit;
}


// =========================================================
// ACTIVAR
// =========================================================

if ($accion === 'activar') {

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        exit('Método no permitido.');
    }

    verificar_csrf();

    $id =
        (int) ($_POST['id'] ?? 0);


    if ($id <= 0) {

        exit(
            'Producto no válido.'
        );
    }


    if (
        !$productoModel->activar($id)
    ) {

        exit(
            'No se pudo activar el producto.'
        );
    }


    header(
        'Location: ProductoController.php?accion=listar'
    );

    exit;
}


// =========================================================
// ELIMINAR PRODUCTO DEFINITIVAMENTE
// =========================================================

if ($accion === 'eliminar') {

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        exit('Método no permitido.');
    }

    verificar_csrf();

    // -----------------------------------------------------
    $id =
        (int) ($_POST['id'] ?? 0);


    if ($id <= 0) {

        exit(
            'Producto no válido.'
        );
    }


    // -----------------------------------------------------
    // OBTENER PRODUCTO
    // -----------------------------------------------------

    $producto =
        $productoModel->obtenerPorId($id);


    if (!$producto) {
        http_response_code(404);
        $codigoError = 404;
        $tituloError = 'Producto no encontrado';
        $descripcionError = 'El producto que buscás ya no está disponible.';
        $urlVolver = '/DonDiego-Panaderia-Pruebas/controllers/ProductoController.php?accion=listar';
        $textoVolver = 'Volver a productos';
        require __DIR__ . '/../views/errors/error.php';
        exit;
    }


    // -----------------------------------------------------
    // ELIMINAR DE LA BASE DE DATOS
    // -----------------------------------------------------

    if (
        !$productoModel->eliminar($id)
    ) {

        exit(
            'No se pudo eliminar el producto.'
        );
    }


    // -----------------------------------------------------
    // ELIMINAR IMAGEN
    // -----------------------------------------------------

    if (!empty($producto['imagen'])) {

        $rutaImagen =
            __DIR__
            . '/../public/img/'
            . $producto['imagen'];


        if (file_exists($rutaImagen)) {

            unlink($rutaImagen);
        }
    }


    // -----------------------------------------------------
    // VOLVER A PRODUCTOS
    // -----------------------------------------------------

    header(
        'Location: ProductoController.php?accion=listar'
    );

    exit;
}


// =========================================================
// ACCIÓN NO EXISTENTE
// =========================================================

http_response_code(404);

echo 'Acción no encontrada.';