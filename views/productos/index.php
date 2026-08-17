<?php

$pageCss = "catalogo.css";

require '../../config/Database.php';
require '../../models/Producto.php';
require '../layouts/header.php';
require '../layouts/head.php';


// =========================================================
// PRODUCTOS DESDE LA BASE DE DATOS
// =========================================================

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

    $productos = $productoModel->buscar($busqueda);

} else {

    $productos = $productoModel->obtenerTodos();

}


// =========================================================
// FILTRAR CATEGORÍA
// =========================================================
// La base de datos devuelve nombres como:
// "Dulces", "Tortas", "Repostería", etc.
//
// Convertimos el nombre a un formato simple para
// compararlo con la URL (?categoria=dulces).
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
        href="../../public/css/catalogo.css"
    >

</head>


<body class="pagina-catalogo">


<main class="catalogo-container">


    <!-- =====================================================
         DECORACIÓN DEL CATÁLOGO
    ====================================================== -->


    <!-- MIGAS -->

    <div class="miga miga-1" aria-hidden="true"></div>
    <div class="miga miga-2" aria-hidden="true"></div>
    <div class="miga miga-3" aria-hidden="true"></div>
    <div class="miga miga-4" aria-hidden="true"></div>
    <div class="miga miga-5" aria-hidden="true"></div>
    <div class="miga miga-6" aria-hidden="true"></div>
    <div class="miga miga-7" aria-hidden="true"></div>
    <div class="miga miga-8" aria-hidden="true"></div>
    <div class="miga miga-9" aria-hidden="true"></div>
    <div class="miga miga-10" aria-hidden="true"></div>


    <!-- HOJAS -->

    <div class="hoja hoja-1" aria-hidden="true"></div>
    <div class="hoja hoja-2" aria-hidden="true"></div>
    <div class="hoja hoja-3" aria-hidden="true"></div>
    <div class="hoja hoja-4" aria-hidden="true"></div>
    <div class="hoja hoja-5" aria-hidden="true"></div>
    <div class="hoja hoja-6" aria-hidden="true"></div>


    <!-- MARIPOSA -->

    <div class="mariposa" aria-hidden="true">

        <span class="ala ala-izquierda"></span>

        <span class="cuerpo-mariposa"></span>

        <span class="ala ala-derecha"></span>

    </div>


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
            href="?categoria=salados"
            class="<?= $categoriaSeleccionada === 'salados' ? 'activo' : '' ?>"
        >
            Salados
        </a>

    </nav>


    <!-- =====================================================
         RESULTADOS
    ====================================================== -->

    <section class="catalogo-resultados">


        <div class="catalogo-resultados-header">

            <h2>

                <?= htmlspecialchars(
                    nombreCategoria($categoriaSeleccionada)
                ) ?>

            </h2>


            <span>
                <?= $totalProductos ?> productos
            </span>

        </div>


        <!-- =================================================
             SIN RESULTADOS
        ================================================== -->

        <?php if (empty($productosPagina)): ?>


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
                 GRID DE PRODUCTOS
            ================================================== -->

            <div class="productos-grid">


                <?php foreach ($productosPagina as $producto): ?>


                    <article class="producto-card">


                        <!-- IMAGEN -->

                        <div class="producto-imagen">


                            <?php if (!empty($producto['imagen'])): ?>

                                <img
                                    src="../../public/img/<?= htmlspecialchars($producto['imagen']) ?>"
                                    alt="<?= htmlspecialchars($producto['nombre']) ?>"
                                >

                            <?php else: ?>

                                <img
                                    src="../../public/img/logo.avif"
                                    alt="Don Diego"
                                >

                            <?php endif; ?>


                            <!-- CATEGORÍA -->

                            <span class="producto-categoria">

                                <?= htmlspecialchars(
                                    $producto['categoria']
                                ) ?>

                            </span>


                        </div>


                        <!-- INFORMACIÓN -->

                        <div class="producto-info">


                            <h3>

                                <?= htmlspecialchars(
                                    $producto['nombre']
                                ) ?>

                            </h3>


                            <p>

                                <?= htmlspecialchars(
                                    $producto['descripcion']
                                ) ?>

                            </p>


                            <!-- FOOTER PRODUCTO -->

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


            <!-- ANTERIOR -->

            <?php if ($paginaActual > 1): ?>

                <a
                    href="<?= linkCatalogo($paginaActual - 1) ?>"
                    class="pagina-anterior"
                    aria-label="Página anterior"
                >
                    ←
                </a>

            <?php endif; ?>


            <!-- NÚMEROS -->

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


            <!-- SIGUIENTE -->

            <?php if ($paginaActual < $totalPaginas): ?>

                <a
                    href="<?= linkCatalogo($paginaActual + 1) ?>"
                    class="pagina-siguiente"
                    aria-label="Página siguiente"
                >
                    →
                </a>

            <?php endif; ?>


        </nav>


    <?php endif; ?>


</main>


<?php include '../layouts/footer.php'; ?>


</body>

</html>