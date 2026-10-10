<?php
$modoImpresion = $modoImpresion ?? false;
$tanda = $tanda ?? [];
$detallesTanda = $detallesTanda ?? [];
$cambiosTanda = $cambiosTanda ?? [];
$metadatosDisponibles = $metadatosDisponibles ?? false;
$sectorDocumento = $sectorDocumento ?? '';
$destinosTanda = is_array($tanda['destinos'] ?? null) ? $tanda['destinos'] : [];
$tituloTanda = trim((string) ($tanda['titulo'] ?? '')) ?: 'Tanda #' . (int) ($tanda['id'] ?? 0) . ' - ' . date('d/m/Y H:i', strtotime($tanda['fecha_creacion'] ?? 'now'));
$porSector = [];
$tieneSucursalPedido = false;
$tieneObservacionPedido = false;
foreach ($detallesTanda as $detalle) {
    $sector = trim((string) $detalle['sector']) ?: 'Por definir';
    $porSector[$sector][] = $detalle;
    $tieneSucursalPedido = $tieneSucursalPedido || trim((string) ($detalle['sucursal_pedido'] ?? '')) !== '';
    $tieneObservacionPedido = $tieneObservacionPedido || trim((string) ($detalle['observaciones_pedido'] ?? '')) !== '';
}
$pageCss = ['admin-pedidos.css', 'planificacion.css'];
require __DIR__ . '/../layouts/head.php';
if (!$modoImpresion) {
    require __DIR__ . '/../layouts/header.php';
}
$e = static fn ($valor) => htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
?>

<body class="pagina-admin-pedidos pagina-planificacion <?= $modoImpresion ? 'planificacion-impresion' : '' ?>">
    <main class="admin-pedidos-container planificacion-documento-vista">
        <?php if (!$modoImpresion): ?>
            <header class="admin-pedidos-header">
                <div><span class="admin-etiqueta">DOCUMENTO DE PRODUCCIÓN</span><h1><?= $e($tituloTanda) ?></h1><p>Tanda #<?= (int) $tanda['id'] ?> · Creada <?= $e(date('d/m/Y H:i', strtotime($tanda['fecha_creacion']))) ?> por <?= $e($tanda['responsable']) ?></p></div>
                <div class="admin-header-acciones">
                    <a class="btn-agregar" href="<?= url('/admin/tandas/imprimir?id=' . (int) $tanda['id']) ?>">Imprimir / guardar PDF</a>
                    <a class="btn-agregar btn-volver-admin" href="<?= url('/admin/tandas') ?>">Historial</a>
                </div>
            </header>
        <?php else: ?>
            <header class="planificacion-impresion-acciones"><a href="<?= url('/admin/tandas/detalle?id=' . (int) $tanda['id']) ?>">Volver a la tanda</a><button type="button" data-print>Imprimir / guardar PDF</button></header>
        <?php endif; ?>

        <header class="planificacion-documento-cabecera">
            <div class="planificacion-documento-marca">
                <img src="<?= url('/public/img/logo.avif') ?>" alt="Don Diego Panadería" width="68" height="68">
                <div><span class="produccion-documento-etiqueta">Orden de producción<?= $sectorDocumento !== '' ? ' · ' . $e($sectorDocumento) : '' ?></span><h2><?= $e($tituloTanda) ?></h2><p class="planificacion-numero-tanda">Tanda #<?= (int) $tanda['id'] ?></p></div>
            </div>
            <dl>
                <div><dt>Creada</dt><dd><?= $e(date('d/m/Y H:i', strtotime($tanda['fecha_creacion']))) ?></dd></div>
                <div><dt>Responsable</dt><dd><?= $e($tanda['responsable']) ?></dd></div>
            </dl>
        </header>

        <?php if ($destinosTanda !== [] || trim((string) ($tanda['observaciones'] ?? '')) !== ''): ?>
            <section class="planificacion-metadatos-documento">
                <?php if ($destinosTanda !== []): ?>
                    <div class="planificacion-documento-destinos"><h3>Destinos de esta tanda</h3><ul><?php foreach ($destinosTanda as $destino): ?><li><?= $e($destino) ?></li><?php endforeach; ?></ul></div>
                <?php endif; ?>
                <?php if (trim((string) ($tanda['observaciones'] ?? '')) !== ''): ?><div class="planificacion-documento-observaciones"><h3>Indicaciones generales de producción</h3><p><?= nl2br($e($tanda['observaciones'])) ?></p></div><?php endif; ?>
            </section>
        <?php endif; ?>

        <?php if (!$modoImpresion): ?>
            <?php if (($_GET['actualizada'] ?? '') === '1'): ?><div class="planificacion-aviso planificacion-exito" role="status">Datos de la tanda actualizados. El cambio quedó registrado en el historial.</div><?php endif; ?>
            <?php if (in_array($_GET['error'] ?? '', ['metadatos', 'esquema'], true)): ?><div class="planificacion-aviso planificacion-error" role="alert"><?= ($_GET['error'] ?? '') === 'esquema' ? 'Aplicá la migración incremental para editar los metadatos.' : 'No se pudieron actualizar los datos de la tanda.' ?></div><?php endif; ?>
            <?php if (usuarioPuede('gestionar_produccion')): ?>
                <?php if ($metadatosDisponibles): ?>
                    <details class="planificacion-editar-metadatos">
                        <summary>Editar título, destinos u observaciones</summary>
                        <form method="POST" action="<?= url('/admin/tandas/detalle') ?>" class="planificacion-form-metadatos">
                            <input type="hidden" name="csrf_token" value="<?= $e(csrf_token()) ?>">
                            <input type="hidden" name="accion" value="actualizar_metadatos">
                            <input type="hidden" name="tanda_id" value="<?= (int) $tanda['id'] ?>">
                            <div class="planificacion-campo-metadato"><label for="editar-titulo">Título <span>(opcional)</span></label><input id="editar-titulo" type="text" name="titulo" maxlength="150" value="<?= $e($tanda['titulo'] ?? '') ?>" placeholder="<?= $e($tituloTanda) ?>"></div>
                            <fieldset class="planificacion-destinos" data-destinations>
                                <legend>Destinos o sucursales <span>(opcional)</span></legend>
                                <div class="planificacion-destinos-lista" data-destination-list>
                                    <?php foreach ($destinosTanda as $destino): ?><div class="planificacion-destino-fila"><label class="solo-lectores">Destino</label><input type="text" name="destinos[]" maxlength="150" value="<?= $e($destino) ?>" placeholder="Ej.: Sucursal Centro"><button type="button" data-remove-destination aria-label="Quitar destino">Quitar</button></div><?php endforeach; ?>
                                    <?php if ($destinosTanda === []): ?><div class="planificacion-destino-fila"><label class="solo-lectores">Destino</label><input type="text" name="destinos[]" maxlength="150" value="" placeholder="Ej.: Sucursal Centro"><button type="button" data-remove-destination aria-label="Quitar destino">Quitar</button></div><?php endif; ?>
                                </div>
                                <button type="button" class="planificacion-agregar-destino" data-add-destination>Agregar destino</button>
                            </fieldset>
                            <div class="planificacion-campo-metadato"><label for="editar-observaciones">Observaciones <span>(opcional)</span></label><textarea id="editar-observaciones" name="observaciones" rows="4" maxlength="3000"><?= $e($tanda['observaciones'] ?? '') ?></textarea></div>
                            <button type="submit" class="btn-agregar">Guardar cambios</button>
                        </form>
                    </details>
                <?php else: ?>
                    <div class="planificacion-aviso planificacion-error" role="alert">Para editar metadatos, ejecutá únicamente el bloque <code>ALTER TABLE tandas_produccion</code> al final de <code>database/don_diego.sql</code>.</div>
                <?php endif; ?>
            <?php endif; ?>
            <?php if ($cambiosTanda !== []): ?>
                <details class="planificacion-auditoria">
                    <summary>Historial de cambios de la tanda (<?= count($cambiosTanda) ?>)</summary>
                    <?php foreach ($cambiosTanda as $cambio): ?>
                        <article><h3><?= $e(date('d/m/Y H:i', strtotime($cambio['fecha_cambio']))) ?> · <?= $e($cambio['responsable']) ?></h3>
                            <p><strong>Título:</strong> <?= $e($cambio['titulo_anterior'] ?: 'Automático') ?> → <?= $e($cambio['titulo_nuevo'] ?: 'Automático') ?></p>
                            <p><strong>Destinos:</strong> <?= $e(implode(', ', $cambio['destinos_anteriores']) ?: 'Ninguno') ?> → <?= $e(implode(', ', $cambio['destinos_nuevos']) ?: 'Ninguno') ?></p>
                            <p><strong>Observaciones:</strong> <?= $e($cambio['observaciones_anteriores'] ?: 'Ninguna') ?> → <?= $e($cambio['observaciones_nuevas'] ?: 'Ninguna') ?></p>
                        </article>
                    <?php endforeach; ?>
                </details>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($detallesTanda === []): ?>
            <div class="planificacion-aviso planificacion-error">Esta tanda no tiene líneas guardadas.</div>
        <?php endif; ?>

        <?php foreach ($porSector as $sector => $lineas): ?>
            <section class="planificacion-sector">
                <header>
                    <div><span class="produccion-documento-etiqueta">Sector de producción</span><h2><?= $e($sector) ?></h2></div>
                    <span class="planificacion-sector-conteo"><?= count($lineas) ?> <?= count($lineas) === 1 ? 'línea' : 'líneas' ?></span>
                    <?php if (!$modoImpresion): ?><a href="<?= url('/admin/tandas/imprimir?id=' . (int) $tanda['id'] . '&sector=' . rawurlencode($sector)) ?>">Imprimir sector / guardar PDF</a><?php endif; ?>
                </header>
                <div class="planificacion-tabla-contenedor">
                    <table class="planificacion-tabla planificacion-tabla-documento">
                        <thead><tr><th scope="col" class="col-producto">Producto</th><th scope="col" class="col-cantidad">Cantidad a preparar</th><th scope="col" class="col-origen">Pedido</th><?php if ($tieneSucursalPedido): ?><th scope="col" class="col-sucursal">Sucursal del pedido</th><?php endif; ?><?php if ($tieneObservacionPedido): ?><th scope="col" class="col-observacion">Observación del pedido</th><?php endif; ?><th scope="col" class="col-listo">Listo</th></tr></thead>
                        <tbody>
                            <?php $pedidosConNotaImpresos = []; ?>
                            <?php foreach ($lineas as $linea): ?>
                                <?php
                                $observacionPedido = trim((string) ($linea['observaciones_pedido'] ?? ''));
                                $mostrarObservacion = $observacionPedido !== '' && ($pedidosConNotaImpresos[(int) $linea['pedido_id']] ?? null) !== $observacionPedido;
                                if ($mostrarObservacion) {
                                    $pedidosConNotaImpresos[(int) $linea['pedido_id']] = $observacionPedido;
                                }
                                ?>
                                <tr>
                                    <td data-label="Producto"><strong><?= $e($linea['producto_nombre']) ?></strong></td>
                                    <td data-label="Cantidad" class="planificacion-cantidad-celda"><strong><?= $e(Producto::formatearCantidad($linea['cantidad'], $linea['unidad_venta'])) ?></strong><span><?= $e(Producto::nombrePresentacion($linea['unidad_venta'], $linea['cantidad'])) ?></span></td>
                                    <td data-label="Pedido">#<?= (int) $linea['pedido_id'] ?></td>
                                    <?php if ($tieneSucursalPedido): ?><td data-label="Sucursal del pedido"><?php if (trim((string) ($linea['sucursal_pedido'] ?? '')) !== ''): ?><?= $e($linea['sucursal_pedido']) ?><?php endif; ?></td><?php endif; ?>
                                    <?php if ($tieneObservacionPedido): ?><td data-label="Observación del pedido"><?php if ($mostrarObservacion): ?><span class="planificacion-nota-pedido">Pedido #<?= (int) $linea['pedido_id'] ?>:</span> <?= nl2br($e($observacionPedido)) ?><?php endif; ?></td><?php endif; ?>
                                    <td data-label="Listo"><span class="planificacion-casilla-listo" aria-label="Pendiente de marcar"></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        <?php endforeach; ?>
        <footer class="planificacion-documento-resumen">
            <strong><?= count($detallesTanda) ?> <?= count($detallesTanda) === 1 ? 'línea de producto' : 'líneas de productos' ?></strong>
            <span>Marque cada línea al completar la preparación.</span>
        </footer>
        <p class="planificacion-documento-pie">Don Diego Panadería · Tanda #<?= (int) $tanda['id'] ?> · <?= $e(date('d/m/Y H:i', strtotime($tanda['fecha_creacion']))) ?></p>
    </main>
    <script src="<?= url('/public/js/planificacion.js') ?>" defer></script>
</body>
</html>