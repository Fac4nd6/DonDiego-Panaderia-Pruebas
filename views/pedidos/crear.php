<?php

$pageCss = "crear.css";

require __DIR__ . '/../layouts/head.php';
require __DIR__ . '/../layouts/header.php';

?>

<body class="pagina-pedido">

    <main class="pedido-container">


        <!-- =====================================================
             ENCABEZADO
        ====================================================== -->

        <header class="pedido-header">

            <h1>
                Finalizar pedido
            </h1>

            <p>
                Completá los datos para confirmar tu pedido.
            </p>

        </header>


        <section class="pedido-contenido">


            <!-- =================================================
                 FORMULARIO
            ================================================== -->

            <div class="pedido-formulario">

                <form
                    method="POST"
                    action="/DonDiego-Panaderia-Pruebas/controllers/PedidoController.php">


                    <input
                        type="hidden"
                        name="accion"
                        value="crear">


                    <!-- =================================================
                         DATOS DE ENTREGA
                    ================================================== -->

                    <section class="pedido-seccion">

                        <h2>
                            Datos de entrega
                        </h2>


                        <!-- =================================================
                             DEPARTAMENTO
                        ================================================== -->

                        <div class="pedido-input">

                            <label for="departamento">

                                Departamento

                            </label>

                            <select
                                id="departamento"
                                name="departamento"
                                required>

                                <option
                                    value="Salto"
                                    selected>

                                    Salto

                                </option>

                            </select>

                            <small>
                                Actualmente realizamos entregas únicamente
                                dentro del departamento de Salto.
                            </small>

                        </div>


                        <!-- =================================================
                             CALLE
                        ================================================== -->

                        <div class="pedido-input">

                            <label for="calle">

                                Calle

                            </label>

                            <input
                                type="text"
                                id="calle"
                                name="calle"
                                placeholder="Ej: Artigas"
                                maxlength="100"
                                required>

                        </div>


                        <!-- =================================================
                             NÚMERO
                        ================================================== -->

                        <div class="pedido-input">

                            <label for="numero_puerta">

                                Número de puerta

                            </label>

                            <input
                                type="text"
                                id="numero_puerta"
                                name="numero_puerta"
                                placeholder="Ej: 1234"
                                maxlength="10"
                                required>

                        </div>


                        <!-- =================================================
                             APARTAMENTO / REFERENCIA
                        ================================================== -->

                        <div class="pedido-input">

                            <label for="referencia">

                                Apartamento / referencia

                                <span>
                                    (opcional)
                                </span>

                            </label>

                            <input
                                type="text"
                                id="referencia"
                                name="referencia"
                                placeholder="Ej: Apto 2, casa azul, esquina..."
                                maxlength="150">

                        </div>


                        <!-- =================================================
                             FECHA
                        ================================================== -->

                        <div class="pedido-input">

                            <label for="fecha_recepcion">

                                Fecha de recepción

                            </label>


                            <?php

                            $fechaMinima =
                                date('Y-m-d');


                            $fechaMaxima =
                                date(
                                    'Y-m-d',
                                    strtotime('+30 days')
                                );

                            ?>


                            <input
                                type="date"
                                id="fecha_recepcion"
                                name="fecha_recepcion"
                                min="<?= $fechaMinima ?>"
                                max="<?= $fechaMaxima ?>"
                                required>


                            <small>
                                Podés realizar pedidos desde hoy
                                hasta 30 días en adelante.
                            </small>

                        </div>


                        <!-- =================================================
                             FRANJA HORARIA
                        ================================================== -->

                        <div class="pedido-input">

                            <label for="franja_horaria">

                                Franja horaria

                            </label>


                            <select
                                id="franja_horaria"
                                name="franja_horaria"
                                required>

                                <option value="">
                                    Seleccioná un horario
                                </option>

                                <option value="08:00 - 10:00">
                                    08:00 - 10:00
                                </option>

                                <option value="10:00 - 12:00">
                                    10:00 - 12:00
                                </option>

                                <option value="12:00 - 14:00">
                                    12:00 - 14:00
                                </option>

                                <option value="14:00 - 16:00">
                                    14:00 - 16:00
                                </option>

                                <option value="16:00 - 18:00">
                                    16:00 - 18:00
                                </option>

                                <option value="18:00 - 20:00">
                                    18:00 - 20:00
                                </option>

                            </select>

                        </div>


                    </section>


                    <!-- =================================================
                         MÉTODO DE PAGO
                    ================================================== -->

                    <section class="pedido-seccion">

                        <h2>
                            Método de pago
                        </h2>


                        <div class="metodos-pago">


                            <!-- EFECTIVO -->

                            <label class="metodo-pago">

                                <input
                                    type="radio"
                                    name="metodo_pago"
                                    value="efectivo"
                                    required>


                                <div>

                                    <strong>
                                        Efectivo
                                    </strong>

                                    <span>
                                        Pagás al recibir tu pedido.
                                    </span>

                                </div>

                            </label>


                            <!-- MERCADO PAGO -->

                            <label class="metodo-pago">

                                <input
                                    type="radio"
                                    name="metodo_pago"
                                    value="mercado_pago">


                                <div>

                                    <strong>
                                        Mercado Pago
                                    </strong>

                                    <span>
                                        Pago online.
                                    </span>

                                </div>

                            </label>


                        </div>


                    </section>


                    <!-- =================================================
                         BOTONES
                    ================================================== -->

                    <div class="pedido-acciones">


                        <a
                            href="/DonDiego-Panaderia-Pruebas/controllers/CarritoController.php?accion=ver"
                            class="btn-volver-carrito">

                            ← Volver al carrito

                        </a>


                        <button
                            type="submit"
                            class="btn-confirmar-pedido">

                            Confirmar pedido

                            <i class="fa-solid fa-check"></i>

                        </button>


                    </div>


                </form>

            </div>


            <!-- =================================================
                 RESUMEN DEL PEDIDO
            ================================================== -->

            <aside class="pedido-resumen">

                <h2>
                    Resumen del pedido
                </h2>


                <?php if (!empty($carrito)): ?>


                    <div class="pedido-productos">


                        <?php foreach ($carrito as $item): ?>


                            <div class="pedido-producto">


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
                                            $item['precio'],
                                            0,
                                            ',',
                                            '.'
                                        ) ?>

                                    </span>

                                </div>


                                <strong>

                                    $<?= number_format(
                                        $item['precio']
                                        * $item['cantidad'],
                                        0,
                                        ',',
                                        '.'
                                    ) ?>

                                </strong>


                            </div>


                        <?php endforeach; ?>


                    </div>


                    <!-- =================================================
                         TOTAL
                    ================================================== -->

                    <div class="pedido-total">

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


                <?php else: ?>


                    <p>
                        Tu carrito está vacío.
                    </p>


                <?php endif; ?>


            </aside>


        </section>


    </main>


    <?php require __DIR__ . '/../layouts/footer.php'; ?>


</body>

</html>