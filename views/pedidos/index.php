<?php

$pageCss = "pedidos.css";

require __DIR__ . '/../layouts/head.php';
require __DIR__ . '/../layouts/header.php';

?>

<body class="pagina-pedidos">

    <main class="pedidos-container">

        <!-- =====================================================
             ENCABEZADO
        ====================================================== -->

        <header class="pedidos-header">

            <div>

                <h1>
                    Mis pedidos
                </h1>

                <p>
                    Consultá tus pedidos anteriores.
                </p>

            </div>

            <a
                href="<?= url('/productos') ?>"
                class="btn-volver-catalogo"
            >
                ← Seguir comprando
            </a>

        </header>


        <!-- =====================================================
             SIN PEDIDOS
        ====================================================== -->

        <?php if (empty($pedidos)): ?>

            <section class="pedidos-vacio">

                <div class="pedidos-vacio-icono">
                    📦
                </div>

                <h2>
                    Todavía no tenés pedidos
                </h2>

                <p>
                    Cuando realices tu primer pedido,
                    aparecerá aquí.
                </p>

                <a
                    href="<?= url('/productos') ?>"
                    class="btn-ver-catalogo"
                >
                    Ver catálogo
                </a>

            </section>


        <?php else: ?>


            <!-- =================================================
                 LISTA DE PEDIDOS
            ================================================== -->

            <section class="pedidos-lista">


                <?php foreach ($pedidos as $pedido): ?>

                    <article class="pedido-card">


                        <!-- =================================================
                             CABECERA DEL PEDIDO
                        ================================================== -->

                        <div class="pedido-card-header">

                            <div>

                                <span class="pedido-label">
                                    ID del pedido
                                </span>

                                <h2>
                                    #<?= (int) $pedido['id'] ?>
                                </h2>

                            </div>


                            <!-- ESTADO -->

                            <span
                                class="pedido-estado estado-<?= htmlspecialchars(
                                    $pedido['estado'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                            >

                                <?= htmlspecialchars(
                                    ucfirst(
                                        str_replace(
                                            '_',
                                            ' ',
                                            $pedido['estado']
                                        )
                                    ),
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>

                            </span>

                        </div>


                        <!-- =================================================
                             INFORMACIÓN
                        ================================================== -->

                        <div class="pedido-info">


                            <!-- FECHA DEL PEDIDO -->

                            <div class="pedido-info-item">

                                <span>
                                    Fecha del pedido
                                </span>

                                <strong>

                                    <?= date(
                                        'd/m/Y',
                                        strtotime(
                                            $pedido['fecha_pedido']
                                        )
                                    ) ?>

                                </strong>

                            </div>


                            <!-- FECHA RECEPCIÓN -->

                            <div class="pedido-info-item">

                                <span>
                                    Recepción
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


                            <!-- FRANJA HORARIA -->

                            <div class="pedido-info-item">

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


                            <!-- TOTAL -->

                            <div class="pedido-info-item">

                                <span>
                                    Total
                                </span>

                                <strong class="pedido-total">

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
                             DIRECCIÓN
                        ================================================== -->

                        <div class="pedido-direccion">

                            <i class="fa-solid fa-location-dot"></i>

                            <span>

                                <?= htmlspecialchars(
                                    $pedido['direccion_entrega'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>

                            </span>

                        </div>


                        <!-- =================================================
                             VER DETALLE
                        ================================================== -->

                        <div class="pedido-card-footer">

                            <form method="POST" action="<?= url('/pedidos') ?>">
                                <input type="hidden" name="accion" value="repetir">
                                <input type="hidden" name="pedido_id" value="<?= (int) $pedido['id'] ?>">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                                <button type="submit" class="btn-ver-pedido">Repetir pedido</button>
                            </form>

                            <a
                                href="<?= url('/pedidos/detalle?id=' . (int) $pedido['id']) ?>"
                                class="btn-ver-pedido"
                            >

                                Ver detalles

                                <i class="fa-solid fa-chevron-right"></i>

                            </a>

                        </div>


                    </article>

                <?php endforeach; ?>


            </section>


        <?php endif; ?>


    </main>


    <?php require __DIR__ . '/../layouts/footer.php'; ?>

    

</body>

</html>