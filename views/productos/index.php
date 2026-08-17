<?php

$pageCss = "catalogo.css";


require '../../config/Database.php';
require '../layouts/header.php';
require '../layouts/head.php';


/*
|--------------------------------------------------------------------------
| CATÁLOGO
|--------------------------------------------------------------------------
| Por ahora los productos están cargados manualmente.
| Más adelante estos datos pueden venir desde la base de datos.
|--------------------------------------------------------------------------
*/

$productos = [

    // =========================================================
    // DULCES
    // =========================================================

    [
        'id' => 1,
        'nombre' => 'Alfajor de chocolate',
        'descripcion' => 'Alfajor artesanal cubierto de chocolate.',
        'precio' => 120,
        'categoria' => 'dulces',
        'imagen' => '../../public/img/alfajores.avif'
    ],

    [
        'id' => 2,
        'nombre' => 'Cookies con chips',
        'descripcion' => 'Cookies caseras con chips de chocolate.',
        'precio' => 150,
        'categoria' => 'dulces',
        'imagen' => 'public/img/productos/cookies.jpg'
    ],

    [
        'id' => 3,
        'nombre' => 'Brownie',
        'descripcion' => 'Brownie de chocolate artesanal.',
        'precio' => 180,
        'categoria' => 'dulces',
        'imagen' => 'public/img/productos/brownie.jpg'
    ],

    // =========================================================
    // TORTAS
    // =========================================================

    [
        'id' => 4,
        'nombre' => 'Torta de chocolate',
        'descripcion' => 'Torta húmeda de chocolate con cobertura.',
        'precio' => 950,
        'categoria' => 'tortas',
        'imagen' => 'public/img/productos/torta-chocolate.jpg'
    ],

    [
        'id' => 5,
        'nombre' => 'Torta de dulce de leche',
        'descripcion' => 'Torta artesanal rellena con dulce de leche.',
        'precio' => 1100,
        'categoria' => 'tortas',
        'imagen' => 'public/img/productos/torta-dulce-leche.jpg'
    ],

    // =========================================================
    // REPOSTERÍA
    // =========================================================

    [
        'id' => 6,
        'nombre' => 'Tarta de frutilla',
        'descripcion' => 'Tarta artesanal con crema y frutillas.',
        'precio' => 750,
        'categoria' => 'reposteria',
        'imagen' => 'public/img/productos/tarta-frutilla.jpg'
    ],

    [
        'id' => 7,
        'nombre' => 'Cheesecake',
        'descripcion' => 'Cheesecake artesanal con cobertura de frutos rojos.',
        'precio' => 850,
        'categoria' => 'reposteria',
        'imagen' => 'public/img/productos/cheesecake.jpg'
    ],

    // =========================================================
    // SALADOS
    // =========================================================

    [
        'id' => 8,
        'nombre' => 'Empanada de carne',
        'descripcion' => 'Empanada casera rellena de carne.',
        'precio' => 130,
        'categoria' => 'salados',
        'imagen' => 'public/img/productos/empanada-carne.jpg'
    ],

    [
        'id' => 9,
        'nombre' => 'Empanada de jamón y queso',
        'descripcion' => 'Empanada casera de jamón y queso.',
        'precio' => 130,
        'categoria' => 'salados',
        'imagen' => 'public/img/productos/empanada-jamon.jpg'
    ],

    // =========================================================
    // PANADERÍA
    // =========================================================

    [
        'id' => 10,
        'nombre' => 'Pan casero',
        'descripcion' => 'Pan artesanal recién horneado.',
        'precio' => 180,
        'categoria' => 'panaderia',
        'imagen' => 'public/img/productos/pan.jpg'
    ],

    [
        'id' => 11,
        'nombre' => 'Medialunas',
        'descripcion' => 'Medialunas artesanales.',
        'precio' => 90,
        'categoria' => 'panaderia',
        'imagen' => 'public/img/productos/medialunas.jpg'
    ],

    // =========================================================
    // ROSTICERÍA
    // =========================================================

    [
        'id' => 12,
        'nombre' => 'Pollo al horno',
        'descripcion' => 'Pollo al horno preparado de forma artesanal.',
        'precio' => 850,
        'categoria' => 'rostiseria',
        'imagen' => 'public/img/productos/pollo.jpg'
    ]

];


// =========================================================
// FILTROS
// =========================================================

$busqueda = $_GET['busqueda'] ?? '';

$categoriaSeleccionada = $_GET['categoria'] ?? 'todos';


// =========================================================
// FILTRAR PRODUCTOS
// =========================================================

$productosFiltrados = array_filter(
    $productos,
    function ($producto) use ($busqueda, $categoriaSeleccionada) {

        $coincideBusqueda =
            $busqueda === '' ||
            stripos($producto['nombre'], $busqueda) !== false ||
            stripos($producto['descripcion'], $busqueda) !== false;

        $coincideCategoria =
            $categoriaSeleccionada === 'todos' ||
            $producto['categoria'] === $categoriaSeleccionada;

        return $coincideBusqueda && $coincideCategoria;
    }
);


// =========================================================
// PAGINACIÓN
// =========================================================

$productosPorPagina = 8;

$totalProductos = count($productosFiltrados);

$totalPaginas = max(
    1,
    ceil($totalProductos / $productosPorPagina)
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
    $productosFiltrados,
    $inicio,
    $productosPorPagina
);


// =========================================================
// FUNCIÓN PARA GENERAR LINKS
// =========================================================

function linkCatalogo($pagina)
{
    global $busqueda, $categoriaSeleccionada;

    return '?pagina=' . $pagina .
        '&busqueda=' . urlencode($busqueda) .
        '&categoria=' . urlencode($categoriaSeleccionada);
}

?>

<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Catálogo | Don Diego</title>

    <link
        rel="stylesheet"
        href="public/css/catalogo.css"
    >

</head>


<body class="pagina-catalogo">


<?php include 'views/layouts/header.php'; ?>


<main class="catalogo-container">


    <!-- =====================================================
         ENCABEZADO
    ====================================================== -->

    <section class="catalogo-header">

        <span class="catalogo-etiqueta">
            DON DIEGO
        </span>

        <h1>
            Nuestro catálogo
        </h1>

        <p>
            Descubrí nuestros productos artesanales.
        </p>

    </section>



    <!-- =====================================================
         BUSCADOR
    ====================================================== -->

    <section class="catalogo-herramientas">

        <form
            method="GET"
            class="catalogo-buscador"
        >

            <input
                type="text"
                name="busqueda"
                placeholder="Buscar un producto..."
                value="<?= htmlspecialchars($busqueda) ?>"
            >

            <input
                type="hidden"
                name="categoria"
                value="<?= htmlspecialchars($categoriaSeleccionada) ?>"
            >

            <button type="submit">
                Buscar
            </button>

        </form>

    </section>



    <!-- =====================================================
         CATEGORÍAS
    ====================================================== -->

    <nav class="catalogo-categorias">

        <a
            href="?categoria=todos"
            class="<?= $categoriaSeleccionada === 'todos' ? 'activo' : '' ?>"
        >
            Todos
        </a>

        <a
            href="?categoria=dulces"
            class="<?= $categoriaSeleccionada === 'dulces' ? 'activo' : '' ?>"
        >
            Dulces
        </a>

        <a
            href="?categoria=salados"
            class="<?= $categoriaSeleccionada === 'salados' ? 'activo' : '' ?>"
        >
            Salados
        </a>

        <a
            href="?categoria=tortas"
            class="<?= $categoriaSeleccionada === 'tortas' ? 'activo' : '' ?>"
        >
            Tortas
        </a>

        <a
            href="?categoria=reposteria"
            class="<?= $categoriaSeleccionada === 'reposteria' ? 'activo' : '' ?>"
        >
            Repostería
        </a>

        <a
            href="?categoria=panaderia"
            class="<?= $categoriaSeleccionada === 'panaderia' ? 'activo' : '' ?>"
        >
            Panadería
        </a>

        <a
            href="?categoria=rostiseria"
            class="<?= $categoriaSeleccionada === 'rostiseria' ? 'activo' : '' ?>"
        >
            Rostisería
        </a>

    </nav>



    <!-- =====================================================
         RESULTADOS
    ====================================================== -->

    <section class="catalogo-resultados">

        <div class="catalogo-resultados-header">

            <h2>

                <?php if ($categoriaSeleccionada === 'todos'): ?>

                    Todos los productos

                <?php else: ?>

                    <?= ucfirst($categoriaSeleccionada) ?>

                <?php endif; ?>

            </h2>

            <span>
                <?= $totalProductos ?> productos
            </span>

        </div>



        <?php if (empty($productosPagina)): ?>

            <!-- =================================================
                 SIN RESULTADOS
            ================================================== -->

            <div class="catalogo-vacio">

                <div class="catalogo-vacio-icono">
                    🔎
                </div>

                <h2>
                    No encontramos productos
                </h2>

                <p>
                    Probá buscando otro producto o categoría.
                </p>

                <a href="catalogo.php">
                    Ver todos los productos
                </a>

            </div>


        <?php else: ?>


            <!-- =================================================
                 PRODUCTOS
            ================================================== -->

            <div class="productos-grid">

                <?php foreach ($productosPagina as $producto): ?>

                    <article class="producto-card">

                        <div class="producto-imagen">

                            <img
                                src="<?= htmlspecialchars($producto['imagen']) ?>"
                                alt="<?= htmlspecialchars($producto['nombre']) ?>"
                            >

                            <span class="producto-categoria">
                                <?= ucfirst($producto['categoria']) ?>
                            </span>

                        </div>


                        <div class="producto-info">

                            <h3>
                                <?= htmlspecialchars($producto['nombre']) ?>
                            </h3>

                            <p>
                                <?= htmlspecialchars($producto['descripcion']) ?>
                            </p>


                            <div class="producto-footer">

                                <strong>
                                    $<?= number_format(
                                        $producto['precio'],
                                        0,
                                        ',',
                                        '.'
                                    ) ?>
                                </strong>


                                <a
                                    href="detalle.php?id=<?= $producto['id'] ?>"
                                    class="producto-boton"
                                >
                                    Ver producto
                                </a>

                            </div>

                        </div>

                    </article>

                <?php endforeach; ?>

            </div>


        <?php endif; ?>

    </section>



    <!-- =====================================================
         PAGINACIÓN
    ====================================================== -->

    <?php if ($totalPaginas > 1): ?>

        <nav class="catalogo-paginacion">

            <?php if ($paginaActual > 1): ?>

                <a
                    href="<?= linkCatalogo($paginaActual - 1) ?>"
                    class="pagina-anterior"
                >
                    ←
                </a>

            <?php endif; ?>


            <?php for (
                $i = 1;
                $i <= $totalPaginas;
                $i++
            ): ?>

                <a
                    href="<?= linkCatalogo($i) ?>"
                    class="<?= $i === $paginaActual ? 'activo' : '' ?>"
                >
                    <?= $i ?>
                </a>

            <?php endfor; ?>


            <?php if ($paginaActual < $totalPaginas): ?>

                <a
                    href="<?= linkCatalogo($paginaActual + 1) ?>"
                    class="pagina-siguiente"
                >
                    →
                </a>

            <?php endif; ?>

        </nav>

    <?php endif; ?>


</main>


<?php include 'views/layouts/footer.php'; ?>


</body>

</html>