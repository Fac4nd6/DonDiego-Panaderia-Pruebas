<?php

$pageCss = ["admin-pedidos.css", "produccion.css"];
require __DIR__ . '/../layouts/head.php';
require __DIR__ . '/../layouts/header.php';

$produccion = $produccion ?? [];
$documentosProduccion = [];
foreach ($produccion as $item) {
    $sucursal = trim((string) ($item['sucursal'] ?? '')) ?: 'Sin sucursal';
    $sector = trim((string) ($item['sector'] ?? '')) ?: 'Por definir';
    $documentosProduccion[$sucursal][$sector][] = $item;
}
?>

<body class="pagina-admin-pedidos pagina-produccion">
    <main class="admin-pedidos-container">
        <header class="admin-pedidos-header">
            <div>
                <span class="admin-etiqueta">PRODUCCIÓN</span>
                <h1>Producción</h1>
                <p>Seguimiento de los productos enviados desde cada pedido.</p>
            </div>
            <div class="admin-header-acciones">
                <?php if (!empty($produccion)): ?>
                    <button type="button" class="btn-agregar produccion-imprimir" data-imprimir-produccion>Imprimir órdenes</button>
                <?php endif; ?>
                <a href="<?= url('/admin/pedidos') ?>" class="btn-agregar btn-volver-admin">Volver</a>
            </div>
        </header>

        <?php if (isset($_GET['actualizado'])): ?>
            <div class="produccion-aviso produccion-aviso-exito" role="status">Estado de producción actualizado.</div>
        <?php elseif (($_GET['error'] ?? '') === 'esquema' || empty($produccionConfigurada)): ?>
            <div class="produccion-aviso produccion-aviso-error" role="alert">
                La página abrió, pero la base de datos todavía no tiene todas las columnas para gestionar producción. Agregá a <strong>pedido_detalles</strong>: <code>seleccionada</code>, <code>estado_produccion</code>, <code>sector</code> y <code>sucursal</code>.
            </div>
        <?php elseif (($_GET['error'] ?? '') === 'estado'): ?>
            <div class="produccion-aviso produccion-aviso-error" role="alert">No se pudo actualizar el estado de producción.</div>
        <?php endif; ?>

        <section class="produccion-filtros" aria-label="Filtrar producción">
            <form method="GET" action="<?= url('/admin/produccion') ?>">
                <label for="estado_produccion">Estado</label>
                <select id="estado_produccion" name="estado_produccion">
                    <option value="" <?= ($filtroProduccion ?? '') === '' ? 'selected' : '' ?>>Todos</option>
                    <option value="pendiente" <?= ($filtroProduccion ?? '') === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                    <option value="en_produccion" <?= ($filtroProduccion ?? '') === 'en_produccion' ? 'selected' : '' ?>>En producción</option>
                    <option value="terminado" <?= ($filtroProduccion ?? '') === 'terminado' ? 'selected' : '' ?>>Terminado</option>
                </select>
                <button type="submit" class="btn-agregar">Filtrar</button>
                <a href="<?= url('/admin/produccion') ?>" class="produccion-limpiar">Limpiar</a>
            </form>
        </section>

        <?php if (empty($produccion)): ?>
            <section class="admin-pedidos-vacio">
                <div class="admin-pedidos-icono">📦</div>
                <h2>No hay productos para mostrar</h2>
                <p>Los productos enviados a producción aparecerán aquí, vinculados a su pedido de origen.</p>
            </section>
        <?php else: ?>
            <section class="produccion-documentos" aria-label="Órdenes de producción">
                <?php foreach ($documentosProduccion as $sucursal => $sectores): ?>
                    <?php foreach ($sectores as $sector => $itemsSector): ?>
                        <article class="produccion-documento">
                            <header class="produccion-documento-cabecera">
                                <div>
                                    <span class="produccion-documento-etiqueta">Orden de producción</span>
                                    <h2><?= htmlspecialchars($sector, ENT_QUOTES, 'UTF-8') ?></h2>
                                </div>
                                <dl>
                                    <div><dt>Sucursal</dt><dd><?= htmlspecialchars($sucursal, ENT_QUOTES, 'UTF-8') ?></dd></div>
                                    <div><dt>Emitida</dt><dd><?= date('d/m/Y') ?></dd></div>
                                </dl>
                            </header>

                            <div class="produccion-documento-lineas">
                                <?php foreach ($itemsSector as $item): ?>
                                    <?php
                                    $estadoItem = $item['estado_produccion'] ?? 'pendiente';
                                    $estadoSiguiente = $estadoItem === 'pendiente' ? 'en_produccion' : 'terminado';
                                    $tipoPedido = ($item['tipo_pedido'] ?? 'normal') === 'encargo' ? 'Encargo' : 'Pedido';
                                    ?>
                                    <section class="produccion-documento-linea">
                                        <div class="produccion-linea-producto">
                                            <strong class="produccion-cantidad"><?= htmlspecialchars(Producto::formatearCantidad($item['cantidad'] ?? 0, $item['unidad_venta'] ?? 'unidad'), ENT_QUOTES, 'UTF-8') ?></strong>
                                            <span class="produccion-multiplicador" aria-hidden="true">×</span>
                                            <div>
                                                <h3><?= htmlspecialchars($item['nombre'], ENT_QUOTES, 'UTF-8') ?></h3>
                                                <p><?= htmlspecialchars(Producto::nombrePresentacion($item['unidad_venta'] ?? 'unidad'), ENT_QUOTES, 'UTF-8') ?></p>
                                            </div>
                                        </div>
                                        <div class="produccion-linea-origen">
                                            <strong><?= $tipoPedido ?> #<?= (int) $item['pedido_id'] ?></strong>
                                            <span><?= htmlspecialchars($item['nombre_comercio'] ?: ($item['nombre_cliente'] ?? 'Cliente'), ENT_QUOTES, 'UTF-8') ?></span>
                                            <span>Entrega: <?= !empty($item['fecha_recepcion']) ? date('d/m/Y', strtotime($item['fecha_recepcion'])) : 'A coordinar' ?></span>
                                        </div>
                                        <div class="produccion-linea-estado">
                                            <span class="produccion-estado produccion-estado-<?= htmlspecialchars($estadoItem, ENT_QUOTES, 'UTF-8') ?>">
                                                <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $estadoItem)), ENT_QUOTES, 'UTF-8') ?>
                                            </span>
                                            <?php if ($estadoItem !== 'terminado'): ?>
                                                <form method="POST" action="<?= url('/admin/produccion') ?>" class="produccion-accion-form">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                                                    <input type="hidden" name="accion" value="actualizar_produccion">
                                                    <input type="hidden" name="detalle_id" value="<?= (int) $item['id'] ?>">
                                                    <input type="hidden" name="estado_produccion" value="<?= htmlspecialchars($estadoSiguiente, ENT_QUOTES, 'UTF-8') ?>">
                                                    <input type="hidden" name="filtro_produccion" value="<?= htmlspecialchars($filtroProduccion ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                                    <button type="submit" class="produccion-accion">
                                                        <?= $estadoItem === 'pendiente' ? 'Pasar a en producción' : 'Finalizar' ?>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </section>
                                <?php endforeach; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>
    </main>
    <script src="<?= url('/public/js/produccion.js') ?>" defer></script>
</body>
</html>
