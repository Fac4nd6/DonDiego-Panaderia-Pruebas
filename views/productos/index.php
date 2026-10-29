<?php

$pageCss = [
    "catalogo.css",
    "detalle.css"
];

require __DIR__ . '/../layouts/head.php';
require __DIR__ . '/../layouts/header.php';

?>

<body class="pagina-catalogo">


    <main class="catalogo-container">


        <!-- DECORACIÓN DEL CATÁLOGO -->
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


        <!-- ENCABEZADO -->
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


        <!-- BUSCADOR -->
        <section class="catalogo-herramientas">

            <form
                method="GET"
                class="catalogo-buscador">

                <input
                    type="text"
                    name="busqueda"
                    placeholder="Buscar un producto..."
                    value="<?= htmlspecialchars($busqueda) ?>">

                <input
                    type="hidden"
                    name="categoria"
                    value="<?= htmlspecialchars($categoriaSeleccionada) ?>">

                <button type="submit">
                    Buscar
                </button>

            </form>

            <a href="<?= url('/pedidos/encargo') ?>" class="pedido-encargo-link">
                ¿Necesitás una cantidad mayor? <strong>Solicitar pedido por encargo</strong>
            </a>

        </section>


        <!-- CATEGORÍAS -->
        <nav class="catalogo-categorias">

            <a
                href="?categoria=todos"
                class="<?= $categoriaSeleccionada === 'todos' ? 'activo' : '' ?>">
                Todos
            </a>

            <a
                href="?categoria=dulces"
                class="<?= $categoriaSeleccionada === 'dulces' ? 'activo' : '' ?>">
                Dulces
            </a>

            <a
                href="?categoria=tortas"
                class="<?= $categoriaSeleccionada === 'tortas' ? 'activo' : '' ?>">
                Tortas
            </a>

            <a
                href="?categoria=reposteria"
                class="<?= $categoriaSeleccionada === 'reposteria' ? 'activo' : '' ?>">
                Repostería
            </a>

            <a
                href="?categoria=panaderia"
                class="<?= $categoriaSeleccionada === 'panaderia' ? 'activo' : '' ?>">
                Panadería
            </a>

            <a
                href="?categoria=salados"
                class="<?= $categoriaSeleccionada === 'salados' ? 'activo' : '' ?>">
                Salados
            </a>

        </nav>


        <!-- RESULTADOS -->
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


            <!-- SIN RESULTADOS -->
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

                    <a
                        href="<?= url('/productos') ?>">
                        Ver todos los productos
                    </a>

                </div>


            <?php else: ?>


                <!-- GRID DE PRODUCTOS -->
                <div class="productos-grid">


                    <?php foreach ($productosPagina as $producto): ?>


                        <?php

                        /*
                     * Ruta de imagen
                */
                        if (!empty($producto['imagen'])) {
                            $imagenProducto = url(
                                '/public/img/' . $producto['imagen']
                            );
                        } else {
                            $imagenProducto = url('/public/img/logo.avif');
                        }

                        ?>


                        <article class="producto-card">


                            <!-- IMAGEN -->

                            <div class="producto-imagen">


                                <img
                                    src="<?= htmlspecialchars($imagenProducto) ?>"
                                    alt="<?= htmlspecialchars($producto['nombre']) ?>">


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

                                    <span>
                                        <?= Producto::tieneStock($producto) ? 'Disponible' : 'Agotado' ?>
                                    </span>


                                    <!--
                                BOTÓN DETALLE

                                Ya no lleva a detalle.php.

                                Ahora abre el panel inferior.
                           -->
                                    <button
                                        type="button"
                                        class="producto-boton"
                                        onclick="abrirProducto(this)"

                                        data-id="<?= (int) $producto['id'] ?>"

                                        data-nombre="<?= htmlspecialchars(
                                                            $producto['nombre'],
                                                            ENT_QUOTES
                                                        ) ?>"

                                        data-descripcion="<?= htmlspecialchars(
                                                                $producto['descripcion'],
                                                                ENT_QUOTES
                                                            ) ?>"

                                        data-precio="<?= (float) $producto['precio'] ?>"

                                        data-stock="<?= Producto::stockCompatibilidad(Producto::obtenerStocks($producto)) ?>"

                                        data-unidad-venta="<?= htmlspecialchars(
                                                                $producto['unidad_venta'] ?? 'unidad',
                                                                ENT_QUOTES
                                                            ) ?>"

                                        data-presentaciones="<?= htmlspecialchars(
                                                                json_encode(Producto::obtenerPresentaciones($producto), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                                                                ENT_QUOTES,
                                                                'UTF-8'
                                                            ) ?>"

                                        data-stocks="<?= htmlspecialchars(
                                                        json_encode(Producto::obtenerStocks($producto), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                                                        ENT_QUOTES,
                                                        'UTF-8'
                                                    ) ?>"

                                        data-categoria="<?= htmlspecialchars(
                                                            $producto['categoria'],
                                                            ENT_QUOTES
                                                        ) ?>"

                                        data-imagen="<?= htmlspecialchars(
                                                            $imagenProducto,
                                                            ENT_QUOTES
                                                        ) ?>">

                                        <?= Producto::tieneStock($producto) ? 'Ver producto' : 'Agotado' ?>

                                    </button>


                                </div>


                            </div>


                        </article>


                    <?php endforeach; ?>


                </div>


            <?php endif; ?>


        </section>


        <!-- PAGINACIÓN -->
        <?php if ($totalPaginas > 1): ?>

            <nav class="catalogo-paginacion">


                <!-- ANTERIOR -->

                <?php if ($paginaActual > 1): ?>

                    <a
                        href="<?= htmlspecialchars(
                                    linkCatalogo($paginaActual - 1)
                                ) ?>"
                        class="pagina-anterior"
                        aria-label="Página anterior">
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
                        href="<?= htmlspecialchars(
                                    linkCatalogo($i)
                                ) ?>"
                        class="<?= $i === $paginaActual ? 'activo' : '' ?>">
                        <?= $i ?>
                    </a>

                <?php endfor; ?>


                <!-- SIGUIENTE -->

                <?php if ($paginaActual < $totalPaginas): ?>

                    <a
                        href="<?= htmlspecialchars(
                                    linkCatalogo($paginaActual + 1)
                                ) ?>"
                        class="pagina-siguiente"
                        aria-label="Página siguiente">
                        →
                    </a>

                <?php endif; ?>


            </nav>

        <?php endif; ?>


    </main>


    <!-- PANEL DE DETALLE DEL PRODUCTO -->
    <div
        class="producto-overlay"
        id="productoOverlay"
        aria-hidden="true">


        <!-- PANEL -->

        <section
            class="producto-panel"
            id="productoPanel"
            aria-labelledby="detalleNombre">


            <!-- BOTÓN CERRAR -->
            <button
                type="button"
                class="producto-cerrar"
                id="cerrarProducto"
                aria-label="Cerrar detalle del producto">
                ×
            </button>


            <!-- CONTENIDO -->
            <div class="producto-panel-contenido">


                <!-- IMAGEN -->

                <div class="producto-panel-imagen">

                    <img
                        id="detalleImagen"
                        src=""
                        alt="">

                </div>


                <!-- INFORMACIÓN -->

                <div class="producto-panel-info">


                    <!-- CATEGORÍA -->

                    <span
                        class="detalle-categoria"
                        id="detalleCategoria">
                    </span>


                    <!-- NOMBRE -->

                    <h2 id="detalleNombre">
                    </h2>


                    <!-- DESCRIPCIÓN -->

                    <p
                        class="detalle-descripcion"
                        id="detalleDescripcion">
                    </p>


                    <div id="detallePresentacionContenedor" class="detalle-presentacion" hidden>
                        <span>¿Cómo querés comprarlo?</span>
                        <div id="detallePresentacion" class="detalle-presentaciones" role="group" aria-label="Modalidad de compra"></div>
                    </div>

                    <!-- PRECIO -->
                    <strong
                        class="detalle-precio"
                        id="detallePrecio">
                    </strong>

                    <span id="detallePrecioUnidad" class="detalle-precio-unidad"></span>

                    <span
                        class="detalle-stock"
                        id="detalleStock">
                    </span>


                    <!-- CANTIDAD -->
                    <div class="detalle-cantidad">


                        <span>
                            Cantidad
                        </span>


                        <div class="cantidad-control">


                            <button
                                type="button"
                                id="cantidadMenos"
                                aria-label="Disminuir cantidad">
                                −
                            </button>


                            <input
                                type="number"
                                id="cantidadProducto"
                                value="1"
                                min="1"
                                max="99"
                                step="1"
                                aria-label="Cantidad">


                            <button
                                type="button"
                                id="cantidadMas"
                                aria-label="Aumentar cantidad">
                                +
                            </button>


                        </div>


                    </div>


                    <!-- AGREGAR AL CARRITO -->
                    <button
                        type="button"
                        class="detalle-carrito"
                        id="agregarCarrito"
                        data-autenticado="<?= isset($_SESSION['usuario_id']) ? '1' : '0' ?>">
                        Agregar al carrito
                    </button>


                </div>


            </div>


        </section>


    </div>


    <?php require __DIR__ . '/../layouts/footer.php'; ?>


    <!-- JAVASCRIPT DEL DETALLE -->
    <script src="<?= url('/public/js/catalogo.js') ?>"></script>

    <?php if ($productoAbrir): ?>

        <script>
            const productoDesdeHome = <?= json_encode(
                                            $productoAbrir,
                                            JSON_HEX_TAG |
                                                JSON_HEX_APOS |
                                                JSON_HEX_QUOT |
                                                JSON_HEX_AMP
                                        ) ?>;

            abrirProductoDesdeDatos(productoDesdeHome);
        </script>

    <?php endif; ?>

</body>

</html>