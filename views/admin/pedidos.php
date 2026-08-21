<?php

$pageCss = "admin-pedidos.css";

require __DIR__ . '/../layouts/head.php';
require __DIR__ . '/../layouts/header.php';

?>

<body class="pagina-admin-pedidos">

    <main class="admin-pedidos-container">

        <header class="admin-pedidos-header">

            <div>

                <span class="admin-etiqueta">
                    ADMINISTRACIÓN
                </span>

                <h1>
                    Gestión de pedidos
                </h1>

                <p>
                    Consultá y gestioná los pedidos realizados
                    por los comercios.
                </p>

            </div>

        </header>


        <?php if (empty($pedidos)): ?>

            <section class="admin-pedidos-vacio">

                <div class="admin-pedidos-icono">
                    📦
                </div>

                <h2>
                    No hay pedidos
                </h2>

                <p>
                    Todavía no se realizaron pedidos.
                </p>

            </section>


        <?php else: ?>


            <section class="admin-pedidos-tabla">

                <div class="tabla-scroll">

                    <table>

                        <thead>

                            <tr>

                                <th>
                                    Pedido
                                </th>

                                <th>
                                    Cliente
                                </th>

                                <th>
                                    Entrega
                                </th>

                                <th>
                                    Fecha
                                </th>

                                <th>
                                    Horario
                                </th>

                                <th>
                                    Total
                                </th>

                                <th>
                                    Estado
                                </th>

                                <th>
                                    Cambiar estado
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            <?php foreach ($pedidos as $pedido): ?>

                                <tr>

                                    <!-- PEDIDO -->

                                    <td>

                                        <strong class="pedido-numero">

                                            #<?= (int) $pedido['id'] ?>

                                        </strong>

                                    </td>


                                    <!-- CLIENTE -->

                                    <td>

                                        <div class="cliente">

                                            <strong>

                                                <?= htmlspecialchars(
                                                    $pedido['nombre_completo'],
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>

                                            </strong>

                                            <span>

                                                <?= htmlspecialchars(
                                                    $pedido['email'],
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>

                                            </span>

                                            <?php if (!empty($pedido['telefono'])): ?>

                                                <span>

                                                    📞
                                                    <?= htmlspecialchars(
                                                        $pedido['telefono'],
                                                        ENT_QUOTES,
                                                        'UTF-8'
                                                    ) ?>

                                                </span>

                                            <?php endif; ?>

                                        </div>

                                    </td>


                                    <!-- ENTREGA -->

                                    <td>

                                        <div class="pedido-entrega">

                                            <strong>
                                                📍 Dirección
                                            </strong>

                                            <span>

                                                <?= htmlspecialchars(
                                                    $pedido['direccion_entrega'],
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>

                                            </span>

                                        </div>

                                    </td>


                                    <!-- FECHA -->

                                    <td>

                                        <?= date(
                                            'd/m/Y H:i',
                                            strtotime(
                                                $pedido['fecha_pedido']
                                            )
                                        ) ?>

                                    </td>


                                    <!-- HORARIO -->

                                    <td>

                                        <div class="pedido-horario">

                                            <strong>

                                                <?= date(
                                                    'd/m/Y',
                                                    strtotime(
                                                        $pedido['fecha_recepcion']
                                                    )
                                                ) ?>

                                            </strong>

                                            <span>

                                                <?= htmlspecialchars(
                                                    $pedido['franja_horaria'],
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>

                                            </span>

                                        </div>

                                    </td>


                                    <!-- TOTAL -->

                                    <td>

                                        <strong class="pedido-total">

                                            $<?= number_format(
                                                $pedido['total'],
                                                0,
                                                ',',
                                                '.'
                                            ) ?>

                                        </strong>

                                    </td>


                                    <!-- ESTADO -->

                                    <td>

                                        <span
                                            class="estado estado-<?= htmlspecialchars(
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

                                    </td>


                                    <!-- CAMBIAR ESTADO -->

                                    <td>

                                        <form
                                            method="POST"
                                            action="/DonDiego-Panaderia-Pruebas/controllers/PedidoController.php"
                                            class="form-estado"
                                        >

                                            <input
                                                type="hidden"
                                                name="accion"
                                                value="actualizar_estado"
                                            >

                                            <input
                                                type="hidden"
                                                name="pedido_id"
                                                value="<?= (int) $pedido['id'] ?>"
                                            >


                                            <select
                                                name="estado"
                                                required
                                            >

                                                <option
                                                    value="pendiente"
                                                    <?= $pedido['estado'] === 'pendiente'
                                                        ? 'selected'
                                                        : '' ?>
                                                >
                                                    Pendiente
                                                </option>

                                                <option
                                                    value="confirmado"
                                                    <?= $pedido['estado'] === 'confirmado'
                                                        ? 'selected'
                                                        : '' ?>
                                                >
                                                    Confirmado
                                                </option>

                                                <option
                                                    value="en_preparacion"
                                                    <?= $pedido['estado'] === 'en_preparacion'
                                                        ? 'selected'
                                                        : '' ?>
                                                >
                                                    En preparación
                                                </option>

                                                <option
                                                    value="listo"
                                                    <?= $pedido['estado'] === 'listo'
                                                        ? 'selected'
                                                        : '' ?>
                                                >
                                                    Listo
                                                </option>

                                                <option
                                                    value="entregado"
                                                    <?= $pedido['estado'] === 'entregado'
                                                        ? 'selected'
                                                        : '' ?>
                                                >
                                                    Entregado
                                                </option>

                                                <option
                                                    value="cancelado"
                                                    <?= $pedido['estado'] === 'cancelado'
                                                        ? 'selected'
                                                        : '' ?>
                                                >
                                                    Cancelado
                                                </option>

                                            </select>


                                            <button
                                                type="submit"
                                            >
                                                Guardar
                                            </button>

                                        </form>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            </section>

        <?php endif; ?>


    </main>


    <?php require __DIR__ . '/../layouts/footer.php'; ?>

</body>

</html>