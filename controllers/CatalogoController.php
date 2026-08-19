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
// OBTENER PRODUCTOS
// =========================================================

if ($busqueda !== '') {

    // buscar() ya devuelve solamente productos activos
    $productos = $productoModel->buscar($busqueda);

} else {

    // Solo productos activos
    $productos = $productoModel->obtenerActivos();

}


// =========================================================
// FILTRAR CATEGORÍA
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


if ($categoriaSeleccionada !== 'todos') {

    $productos = array_filter(
        $productos,
        function ($producto) use ($categoriaSeleccionada) {

            return categoriaSlug($producto['categoria'])
                === $categoriaSeleccionada;
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
    (int) ceil($totalProductos / $productosPorPagina)
);


$paginaActual = isset($_GET['pagina'])
    ? max(1, (int) $_GET['pagina'])
    : 1;


$paginaActual = min(
    $paginaActual,
    $totalPaginas
);


$inicio = ($paginaActual - 1) * $productosPorPagina;


$productosPagina = array_slice(
    $productos,
    $inicio,
    $productosPorPagina
);


// =========================================================
// FUNCIÓN PARA GENERAR LINKS
// =========================================================

function linkCatalogo($pagina)
{
    global $busqueda, $categoriaSeleccionada;

    return '?pagina=' . $pagina
        . '&busqueda=' . urlencode($busqueda)
        . '&categoria=' . urlencode($categoriaSeleccionada);
}


// =========================================================
// NOMBRE BONITO DE LA CATEGORÍA
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

    return $categorias[$categoria] ?? 'Productos';
}


// =========================================================
// MOSTRAR VISTA
// =========================================================

require_once __DIR__ . '/../views/productos/index.php';