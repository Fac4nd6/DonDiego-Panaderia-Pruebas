<?php

$pageCss = "carrito.css";

require __DIR__ . '/../layouts/head.php';
require __DIR__ . '/../layouts/header.php';

?>

<body class="pagina-carrito">

    <main class="carrito-container">

        <?php if (!empty($mensajeRepetir)): ?>
            <div class="account-message success" role="status">
                <?= htmlspecialchars($mensajeRepetir, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <header class="carrito-header">

            <h1>
                Tu carrito
            </h1>

            <p>
                Revisá tus productos antes de continuar.
            </p>

        </header>


        <?php if (empty($carrito)): ?>

            <!-- =====================================================
                 CARRITO VACÍO
            ====================================================== -->

            <section class="carrito-vacio">

                <div class="carrito-vacio-icono">
                    🛒
                </div>

                <h2>
                    Tu carrito está vacío
                </h2>

                <p>
                    Todavía no agregaste ningún producto.
                </p>

                <a
                    href="<?= url('/productos') ?>"
                    class="btn-volver">
                    Ver catálogo
                </a>

            </section>


        <?php else: ?>


            <!-- =================================================
                 CONTENIDO DEL CARRITO
            ================================================== -->

            <section class="carrito-contenido">


                <!-- =================================================
                     PRODUCTOS
                ================================================== -->

                <div class="carrito-productos">


                    <?php foreach ($carrito as $item): ?>

                        <article class="carrito-item">


                            <!-- IMAGEN -->

                            <div class="carrito-item-imagen">

                                <?php if (!empty($item['imagen'])): ?>

                                    <img
                                        src="<?= url('/public/img/' . htmlspecialchars(
                                                                                        $item['imagen'],
                                                                                        ENT_QUOTES,
                                                                                        'UTF-8'
                                                                                    )) ?>"
                                        alt="<?= htmlspecialchars(
                                                    $item['nombre'],
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>">

                                <?php else: ?>

                                    <img
                                        src="<?= url('/public/img/logo.avif') ?>"
                                        alt="Don Diego">

                                <?php endif; ?>

                            </div>


                            <!-- =================================================
                                 INFORMACIÓN
                            ================================================== -->

                            <div class="carrito-item-info">

                                <h2>
                                    <?= htmlspecialchars(
                                        $item['nombre'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </h2>


                                <p class="item-precio">

                                    $<?= number_format(
                                            $item['precio'],
                                            0,
                                            ',',
                                            '.'
                                        ) ?>

                                    por unidad

                                </p>


                                <!-- =================================================
                                     ACTUALIZAR CANTIDAD
                                ================================================== -->

                                <form
                                    method="POST"
                                    action="<?= url('/carrito') ?>"
                                    class="cantidad-form">

                                    <input
                                        type="hidden"
                                        name="csrf_token"
                                        value="<?= htmlspecialchars(
                                                    csrf_token(),
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>">

                                    <input
                                        type="hidden"
                                        name="accion"
                                        value="actualizar">

                                    <input
                                        type="hidden"
                                        name="producto_id"
                                        value="<?= (int) $item['id'] ?>">

                                    <label
                                        for="cantidad-<?= (int) $item['id'] ?>">
                                        Cantidad
                                    </label>

                                    <input
                                        type="number"
                                        id="cantidad-<?= (int) $item['id'] ?>"
                                        name="cantidad"
                                        value="<?= (int) $item['cantidad'] ?>"
                                        min="1"
                                        max="99">

                                    <button
                                        type="submit"
                                        class="btn-actualizar">
                                        Actualizar
                                    </button>

                                </form>


                                <!-- =================================================
                                     ELIMINAR
                                ================================================== -->

                                <form
                                    method="POST"
                                    action="<?= url('/carrito') ?>">


                                    <input
                                        type="hidden"
                                        name="csrf_token"
                                        value="<?= htmlspecialchars(
                                                    csrf_token(),
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>">


                                    <input
                                        type="hidden"
                                        name="accion"
                                        value="eliminar">

                                    <input
                                        type="hidden"
                                        name="producto_id"
                                        value="<?= (int) $item['id'] ?>">

                                    <button
                                        type="submit"
                                        class="btn-eliminar">
                                        Eliminar
                                    </button>

                                </form>

                            </div>


                            <!-- =================================================
                                 SUBTOTAL
                            ================================================== -->

                            <div class="carrito-item-subtotal">

                                <span>
                                    Subtotal
                                </span>

                                <strong>

                                    $<?= number_format(
                                            $item['precio'] * $item['cantidad'],
                                            0,
                                            ',',
                                            '.'
                                        ) ?>

                                </strong>

                            </div>


                        </article>

                    <?php endforeach; ?>


                </div>


                <!-- =================================================
                     RESUMEN
                ================================================== -->

                <aside class="carrito-resumen">

                    <h2>
                        Resumen del pedido
                    </h2>


                    <div class="resumen-linea">

                        <span>
                            Productos
                        </span>

                        <span>
                            <?= (int) $cantidadProductos ?>
                        </span>

                    </div>


                    <div class="resumen-linea total">

                        <span>
                            Total
                        </span>

                        <strong>

                            $<?= number_format(
                                    $total,
                                    0,
                                    ',',
                                    '.'
                                ) ?>

                        </strong>

                    </div>


                    <!-- CONTINUAR PEDIDO -->

                    <a
                        href="<?= url('/pedidos/crear') ?>"
                        class="btn-continuar">
                        Continuar con el pedido
                    </a>


                    <!-- SEGUIR COMPRANDO -->

                    <a
                        href="<?= url('/productos') ?>"
                        class="btn-seguir-comprando">
                        ← Seguir comprando
                    </a>

                </aside>


            </section>


        <?php endif; ?>


    </main>


    <?php require __DIR__ . '/../layouts/footer.php'; ?>

</body>

</html>