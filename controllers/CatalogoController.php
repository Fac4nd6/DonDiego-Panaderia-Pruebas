<?php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Producto.php';

$productoModel = new Producto($conn);


// =========================================================
// FILTROS
// =========================================================

$busqueda = trim($_GET['busqueda'] ?? '');

$categoriaSeleccionada = $_GET['categoria'] ?? 'todos';


// =========================================================
// OBTENER TODOS LOS PRODUCTOS ACTIVOS
// =========================================================

$productos = $productoModel->obtenerActivos();


// =========================================================
// PRODUCTO A ABRIR AUTOMÁTICAMENTE
// =========================================================

$productoAbrir = null;

if (isset($_GET['producto'])) {

    $productoId = (int) $_GET['producto'];

    if ($productoId > 0) {

        foreach ($productos as $producto) {

            if ((int) $producto['id'] === $productoId) {

                $productoAbrir = $producto;

                break;
            }
        }

        if ($productoAbrir === null) {
            http_response_code(404);
            $codigoError = 404;
            $tituloError = 'Producto no encontrado';
            $descripcionError = 'El producto que buscás ya no está disponible.';
            $urlVolver = '/DonDiego-Panaderia-Pruebas/controllers/CatalogoController.php';
            $textoVolver = 'Volver al catálogo';
            require __DIR__ . '/../views/errors/error.php';
            exit;
        }
    }
}


// =========================================================
// BUSCAR
// =========================================================

if ($busqueda !== '') {

    $productos = array_filter(
        $productos,
        function ($producto) use ($busqueda) {

            return stripos(
                $producto['nombre'],
                $busqueda
            ) !== false
            ||
            stripos(
                $producto['descripcion'],
                $busqueda
            ) !== false;
        }
    );
}


// =========================================================
// FUNCIÓN CATEGORÍA
// =========================================================

function categoriaSlug($categoria)
{
    $categoria = strtolower($categoria);

    $categoria = str_replace(
        ['á', 'é', 'í', 'ó', 'ú', 'ñ'],
        ['a', 'e', 'i', 'o', 'u', 'n'],
        $categoria
    );

    return $categoria;
}


// =========================================================
// FILTRAR CATEGORÍA
// =========================================================

if ($categoriaSeleccionada !== 'todos') {

    $productos = array_filter(
        $productos,
        function ($producto) use ($categoriaSeleccionada) {

            return categoriaSlug(
                $producto['categoria']
            ) === $categoriaSeleccionada;
        }
    );
}


// =========================================================
// PAGINACIÓN
// =========================================================

$productosPorPagina = 8;

$totalProductos = count($productos);

$totalPaginas = max(
    1,
    (int) ceil(
        $totalProductos / $productosPorPagina
    )
);


$paginaActual = isset($_GET['pagina'])
    ? max(1, (int) $_GET['pagina'])
    : 1;


$paginaActual = min(
    $paginaActual,
    $totalPaginas
);


$inicio = (
    $paginaActual - 1
) * $productosPorPagina;


$productosPagina = array_slice(
    $productos,
    $inicio,
    $productosPorPagina
);


// =========================================================
// LINKS DE PAGINACIÓN
// =========================================================

function linkCatalogo($pagina)
{
    global $busqueda;
    global $categoriaSeleccionada;

    return '?pagina=' . $pagina
        . '&busqueda=' . urlencode($busqueda)
        . '&categoria=' . urlencode(
            $categoriaSeleccionada
        );
}


// =========================================================
// NOMBRE BONITO DE CATEGORÍA
// =========================================================

function nombreCategoria($categoria)
{
    $categorias = [

        'todos'      => 'Todos los productos',

        'dulces'     => 'Dulces',

        'tortas'     => 'Tortas',

        'reposteria' => 'Repostería',

        'panaderia'  => 'Panadería',

        'salados'    => 'Salados'

    ];

    return $categorias[$categoria]
        ?? 'Productos';
}


// =========================================================
// MOSTRAR VISTA
// =========================================================

require_once __DIR__ . '/../views/productos/index.php';