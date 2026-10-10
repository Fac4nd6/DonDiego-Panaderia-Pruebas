<?php

$pageCss = (!empty($esAdmin) || !empty($esEmpleado))
    ? ["detalle-pedido.css", "produccion.css"]
    : "detalle-pedido.css";

require __DIR__ . '/../layouts/head.php';

?>

<body class="pagina-pedido-detalle">

    <?php require __DIR__ . '/../layouts/header.php'; ?>

    <main class="detalle-pedido-container">


        <!-- ENCABEZADO -->
        <header class="detalle-pedido-header">

            <span class="detalle-etiqueta">

                <?php if (!empty($esEmpleado)): ?>

                    GESTIÓN DE PEDIDOS

                <?php elseif (!empty($esAdmin)): ?>

                    ADMINISTRACIÓN

                <?php else: ?>

                    PEDIDO REGISTRADO

                <?php endif; ?>

            </span>


            <h1>

                <?= ($pedido['tipo'] ?? 'normal') === 'encargo' ? 'Pedido por encargo' : 'Pedido normal' ?> #<?= (int) $pedido['id'] ?>

            </h1>


            <p>

                <?php if (!empty($esEmpleado)): ?>

                    Detalle del pedido para gestión y entrega.

                <?php elseif (!empty($esAdmin)): ?>

                    Detalle completo del pedido realizado por el cliente.

                <?php else: ?>

                    Tu pedido fue registrado correctamente.

                <?php endif; ?>

            </p>

        </header>



        <section class="detalle-pedido-contenido">


            <!-- INFORMACIÓN DEL CLIENTE -->
            <?php if (!empty($esAdmin) || !empty($esEmpleado)): ?>

                <div class="detalle-pedido-card">

                    <h2>
                        Información del cliente
                    </h2>


                    <div class="detalle-info-grid">


                        <div class="detalle-info-item">

                            <span>
                                Nombre
                            </span>

                            <strong>

                                <?= htmlspecialchars(
                                    $pedido['nombre_completo'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>

                            </strong>

                        </div>


                        <?php if (!empty($pedido['nombre_comercio'])): ?>

                            <div class="detalle-info-item">

                                <span>
                                    Comercio
                                </span>

                                <strong>

                                    <?= htmlspecialchars(
                                        $pedido['nombre_comercio'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                </strong>

                            </div>

                        <?php endif; ?>


                        <div class="detalle-info-item">

                            <span>
                                Email
                            </span>

                            <strong>

                                <?= htmlspecialchars(
                                    $pedido['email'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>

                            </strong>

                        </div>


                        <?php if (!empty($pedido['telefono'])): ?>

                            <div class="detalle-info-item">

                                <span>
                                    Teléfono
                                </span>

                                <strong>

                                    <?= htmlspecialchars(
                                        $pedido['telefono'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                </strong>

                            </div>

                        <?php endif; ?>


                    </div>

                </div>

            <?php endif; ?>



            <!-- INFORMACIÓN DEL PEDIDO -->
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

                        <strong
                            class="detalle-estado estado-<?= htmlspecialchars(
                                                                $pedido['estado'],
                                                                ENT_QUOTES,
                                                                'UTF-8'
                                                            ) ?>">

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



                <!-- DIRECCIÓN -->
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



            <!-- PRODUCTOS -->
            <div class="detalle-pedido-card">

                <h2>
                    Productos
                </h2>


                <?php if (!empty($esAdmin) || !empty($esEmpleado)): ?>
                    <form method="POST" action="<?= url('/admin/pedidos') ?>" class="detalle-productos-form">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="accion" value="enviar_a_produccion">
                        <input type="hidden" name="pedido_id" value="<?= (int) $pedido['id'] ?>">
                <?php endif; ?>

                <div class="detalle-productos">

                    <?php if (!empty($detalles)): ?>

                        <?php foreach ($detalles as $item): ?>

                            <div class="detalle-producto">

                                <?php if (!empty($esAdmin) || !empty($esEmpleado)): ?>
                                    <div class="detalle-producto-check">
                                        <label>
                                            <input
                                                type="checkbox"
                                                name="detalle_ids[]"
                                                value="<?= (int) $item['id'] ?>"
                                                <?= !empty($item['seleccionada']) ? 'checked' : '' ?>>
                                            Seleccionar para producción
                                        </label>
                                    </div>
                                <?php endif; ?>


                                <div class="detalle-producto-info">

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

                                            <?= htmlspecialchars(Producto::formatearCantidad($item['cantidad'], $item['unidad_venta'] ?? 'unidad'), ENT_QUOTES, 'UTF-8') ?>

                                            <?= htmlspecialchars(
                                                Producto::nombrePresentacion($item['unidad_venta'] ?? 'unidad', $item['cantidad']),
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>

                                            ×

                                            $<?= number_format(
                                                    $item['precio_unitario'],
                                                    2,
                                                    ',',
                                                    '.'
                                                ) ?>

                                        </span>

                                        <?php if (!empty($esAdmin) || !empty($esEmpleado)): ?>
                                            <div class="detalle-producto-meta">
                                                <label>
                                                    Sector
                                                    <input
                                                        type="text"
                                                        name="sector[<?= (int) $item['id'] ?>]"
                                                        value="<?= htmlspecialchars($item['sector'] ?? 'Por definir', ENT_QUOTES, 'UTF-8') ?>"
                                                        maxlength="100"
                                                        placeholder="Por definir">
                                                </label>

                                                <label>
                                                    Sucursal
                                                    <input
                                                        type="text"
                                                        name="sucursal[<?= (int) $item['id'] ?>]"
                                                        value="<?= htmlspecialchars($item['sucursal'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                        placeholder="Sin sucursal">
                                                </label>
                                            </div>
                                        <?php endif; ?>

                                    </div>

                                </div>


                                <strong class="detalle-subtotal">

                                    $<?= number_format(
                                            $item['subtotal'],
                                            2,
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

                <?php if (!empty($esAdmin) || !empty($esEmpleado)): ?>
                        <div class="detalle-acciones detalle-acciones-produccion">
                            <button type="submit" class="btn-agregar">Enviar seleccionados a producción</button>
                        </div>
                    </form>
                <?php endif; ?>



                <!-- TOTAL -->
                <div class="detalle-total">

                    <span>
                        Total
                    </span>

                    <strong>

                        $<?= number_format(
                                $pedido['total'],
                                2,
                                ',',
                                '.'
                            ) ?>

                    </strong>

                </div>

            </div>



            <!-- BOTONES -->
            <div class="detalle-acciones">


                <?php if (!empty($esAdmin) || !empty($esEmpleado)): ?>

                    <a
                        href="<?= url('/admin/pedidos') ?>"
                        class="btn-volver-pedidos">

                        ← Volver a pedidos

                    </a>


                    <div class="detalle-acciones-derecha">

                        <a
                            href="<?= url('/admin/pedidos') ?>"
                            class="btn-seguir-comprando">

                            Gestión de pedidos

                        </a>

                    </div>


                <?php else: ?>


                    <a
                        href="<?= url('/pedidos') ?>"
                        class="btn-volver-pedidos">

                        ← Mis pedidos

                    </a>


                    <div class="detalle-acciones-derecha">

                        <form method="POST" action="<?= url('/pedidos') ?>">
                            <input type="hidden" name="accion" value="repetir">
                            <input type="hidden" name="pedido_id" value="<?= (int) $pedido['id'] ?>">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                            <button type="submit" class="btn-seguir-comprando">Repetir pedido</button>
                        </form>


                        <?php if ($pedido['estado'] === 'pendiente'): ?>

                            <form
                                method="POST"
                                action="<?= url('/pedidos') ?>"
                                class="form-cancelar-pedido"
                                onsubmit="return confirm('¿Estás seguro de que querés cancelar este pedido?');">

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
                                    value="cancelar">

                                <input
                                    type="hidden"
                                    name="pedido_id"
                                    value="<?= (int) $pedido['id'] ?>">

                                <button
                                    type="submit"
                                    class="btn-cancelar-pedido">

                                    <i class="fa-solid fa-xmark"></i>

                                    Cancelar pedido

                                </button>

                            </form>

                        <?php endif; ?>


                        <a
                            href="<?= url('/productos') ?>"
                            class="btn-seguir-comprando">

                            Seguir comprando

                        </a>

                    </div>

                <?php endif; ?>


            </div>


        </section>

    </main>


    <?php require __DIR__ . '/../layouts/footer.php'; ?>

</body>

</html>