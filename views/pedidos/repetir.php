<?php

$pageCss = "detalle-pedido.css";

require __DIR__ . '/../layouts/head.php';
require __DIR__ . '/../layouts/header.php';

?>

<body class="pagina-pedido-detalle">

    <main class="detalle-pedido-container">

        <header class="detalle-pedido-header">
            <span class="detalle-etiqueta">REPETIR PEDIDO</span>
            <h1>Revisá los productos disponibles</h1>
            <p>No se agregó nada al carrito todavía. Confirmá para agregar lo disponible.</p>
        </header>

        <section class="detalle-pedido-contenido">
            <div class="detalle-pedido-card">
                <?php if (!empty($itemsAAgregar)): ?>
                    <h2>Se agregarían al carrito</h2>
                    <div class="detalle-productos">
                        <?php foreach ($itemsAAgregar as $item): ?>
                            <?php $precio = Producto::obtenerPresentaciones($item['producto'])[$item['unidad_venta']]; ?>
                            <div class="detalle-producto">
                                <div class="detalle-producto-info">
                                    <?php if (!empty($item['producto']['imagen'])): ?>
                                        <img
                                            src="<?= url('/public/img/' . htmlspecialchars($item['producto']['imagen'], ENT_QUOTES, 'UTF-8')) ?>"
                                            alt="<?= htmlspecialchars($item['producto']['nombre'], ENT_QUOTES, 'UTF-8') ?>">
                                    <?php endif; ?>
                                    <div>
                                        <strong><?= htmlspecialchars($item['producto']['nombre'], ENT_QUOTES, 'UTF-8') ?></strong>
                                        <span>
                                            <?= htmlspecialchars(Producto::formatearCantidad($item['cantidad'], $item['unidad_venta']), ENT_QUOTES, 'UTF-8') ?>
                                            <?= htmlspecialchars(Producto::nombrePresentacion($item['unidad_venta'], $item['cantidad']), ENT_QUOTES, 'UTF-8') ?>
                                            × $<?= number_format($precio, 2, ',', '.') ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($productosNoDisponibles)): ?>
                    <h2>No disponibles</h2>
                    <div class="detalle-productos">
                        <?php foreach ($productosNoDisponibles as $item): ?>
                            <div class="detalle-producto">
                                <div class="detalle-producto-info">
                                    <strong><?= htmlspecialchars($item['nombre'], ENT_QUOTES, 'UTF-8') ?></strong>
                                    <span><?= htmlspecialchars($item['motivo'], ENT_QUOTES, 'UTF-8') ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="detalle-acciones">
                    <a
                        href="<?= url('/pedidos/detalle?id=' . (int) $pedidoRepetidoId) ?>"
                        class="btn-volver-pedidos">
                        Volver al pedido
                    </a>

                    <?php if (!empty($itemsAAgregar)): ?>
                        <form method="POST" action="<?= url('/pedidos') ?>">
                            <input type="hidden" name="accion" value="repetir_confirmado">
                            <input type="hidden" name="pedido_id" value="<?= (int) $pedidoRepetidoId ?>">
                            <input type="hidden" name="revision" value="<?= htmlspecialchars($revisionActual, ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                            <button type="submit" class="btn-seguir-comprando">
                                Agregar disponibles al carrito
                            </button>
                        </form>
                    <?php else: ?>
                        <a href="<?= url('/carrito') ?>" class="btn-seguir-comprando">
                            Ir al carrito
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </section>

    </main>

    <?php require __DIR__ . '/../layouts/footer.php'; ?>

</body>

</html>
