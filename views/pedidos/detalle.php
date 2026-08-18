<?php

$pageCss = "detalle-pedido.css";

require __DIR__ . '/../layouts/head.php';
require __DIR__ . '/../layouts/header.php';

?>

<body class="pagina-pedido-detalle">

    <main class="detalle-pedido-container">

        <!-- =====================================================
             ENCABEZADO
        ====================================================== -->

        <header class="detalle-pedido-header">

            <span class="detalle-etiqueta">
                PEDIDO CONFIRMADO
            </span>

            <h1>
                Pedido #<?= (int) $pedido['id'] ?>
            </h1>

            <p>
                Tu pedido fue registrado correctamente.
            </p>

        </header>


        <section class="detalle-pedido-contenido">


            <!-- =================================================
                 INFORMACIÓN DEL PEDIDO
            ================================================== -->

            <div class="detalle-pedido-card">

                <h2>
                    Información del pedido
                </h2>


                <div class="detalle-info-grid">


                    <!-- ESTADO -->

                    <div class="detalle-info-item">

                        <span>
                            Estado
                        </span>

                        <strong class="detalle-estado estado-<?= htmlspecialchars(
                            $pedido['estado'],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>">

                            <?= ucfirst(
                                str_replace(
                                    '_',
                                    ' ',
                                    $pedido['estado']
                                )
                            ) ?>

                        </strong>

                    </div>


                    <!-- FECHA DEL PEDIDO -->

                    <div class="detalle-info-item">

                        <span>
                            Fecha del pedido
                        </span>

                        <strong>

                            <?= date(
                                'd/m/Y H:i',
                                strtotime(
                                    $pedido['fecha_pedido']
                                )
                            ) ?>

                        </strong>

                    </div>


                    <!-- FECHA DE RECEPCIÓN -->

                    <div class="detalle-info-item">

                        <span>
                            Fecha de recepción
                        </span>

                        <strong>

                            <?= date(
                                'd/m/Y',
                                strtotime(
                                    $pedido['fecha_recepcion']
                                )
                            ) ?>

                        </strong>

                    </div>


                    <!-- HORARIO -->

                    <div class="detalle-info-item">

                        <span>
                            Horario
                        </span>

                        <strong>

                            <?= htmlspecialchars(
                                $pedido['franja_horaria'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>

                        </strong>

                    </div>


                </div>


                <!-- =================================================
                     DIRECCIÓN
                ================================================== -->

                <div class="detalle-direccion">

                    <span>
                        Dirección de entrega
                    </span>

                    <strong>

                        <?= htmlspecialchars(
                            $pedido['direccion_entrega'],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>

                    </strong>

                </div>

            </div>


            <!-- =================================================
                 PRODUCTOS
            ================================================== -->

            <div class="detalle-pedido-card">

                <h2>
                    Productos
                </h2>


                <div class="detalle-productos">

                    <?php if (!empty($detalles)): ?>

                        <?php foreach ($detalles as $item): ?>

                            <div class="detalle-producto">


                                <div class="detalle-producto-info">

                                    <?php if (!empty($item['imagen'])): ?>

                                        <img
                                            src="/DonDiego-Panaderia-Pruebas/public/img/productos/<?= htmlspecialchars(
                                                $item['imagen'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>"
                                            alt="<?= htmlspecialchars(
                                                $item['nombre'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>">

                                    <?php endif; ?>


                                    <div>

                                        <strong>

                                            <?= htmlspecialchars(
                                                $item['nombre'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>

                                        </strong>

                                        <span>

                                            <?= (int) $item['cantidad'] ?>

                                            ×

                                            $<?= number_format(
                                                $item['precio_unitario'],
                                                0,
                                                ',',
                                                '.'
                                            ) ?>

                                        </span>

                                    </div>

                                </div>


                                <strong class="detalle-subtotal">

                                    $<?= number_format(
                                        $item['subtotal'],
                                        0,
                                        ',',
                                        '.'
                                    ) ?>

                                </strong>


                            </div>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <p>
                            No se encontraron productos en este pedido.
                        </p>

                    <?php endif; ?>

                </div>


                <!-- =================================================
                     TOTAL
                ================================================== -->

                <div class="detalle-total">

                    <span>
                        Total
                    </span>

                    <strong>

                        $<?= number_format(
                            $pedido['total'],
                            0,
                            ',',
                            '.'
                        ) ?>

                    </strong>

                </div>

            </div>


            <!-- =================================================
                 BOTONES
            ================================================== -->

            <div class="detalle-acciones">

                <a
                    href="/DonDiego-Panaderia-Pruebas/controllers/PedidoController.php?accion=listar"
                    class="btn-volver-pedidos">

                    ← Mis pedidos

                </a>


                <a
                    href="/DonDiego-Panaderia-Pruebas/controllers/ProductoController.php?accion=listar"
                    class="btn-seguir-comprando">

                    Seguir comprando

                </a>

            </div>


        </section>

    </main>


    <?php require __DIR__ . '/../layouts/footer.php'; ?>

</body>

</html>

