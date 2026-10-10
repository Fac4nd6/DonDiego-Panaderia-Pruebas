<?php
$pageCss = ['admin-pedidos.css', 'planificacion.css'];
require __DIR__ . '/../layouts/head.php';
require __DIR__ . '/../layouts/header.php';
$pedidos = $pedidos ?? [];
$lineasPedido = $lineasPedido ?? [];
$revision = $revision ?? [];
$errores = $errores ?? [];
$metadatos = $metadatos ?? ['titulo' => '', 'destinos' => [], 'observaciones' => ''];
$modo = $modo ?? 'seleccionar';
$estadoFiltro = $estadoFiltro ?? 'pendiente';
$estadosComerciales = $estadosComerciales ?? ['pendiente', 'confirmado', 'en_preparacion', 'listo', 'entregado'];
$gruposProduccion = $gruposProduccion ?? [];
$gruposRevision = $gruposRevision ?? [];
$gruposPorSector = [];
foreach ($gruposProduccion as $grupo) {
    $gruposPorSector[$grupo['sector']][] = $grupo;
}
$gruposRevisionPorSector = [];
foreach ($gruposRevision as $grupo) {
    $gruposRevisionPorSector[$grupo['sector']][] = $grupo;
}
$pedidoIdsVista = array_values(array_unique(array_map(static fn ($linea) => (int) $linea['pedido_id'], $lineasPedido)));
$e = static fn ($valor) => htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
?>

<body class="pagina-admin-pedidos pagina-planificacion">
    <main class="admin-pedidos-container">
        <header class="admin-pedidos-header">
            <div>
                <span class="admin-etiqueta">PRODUCCIÓN</span>
                <h1>Planificación de producción</h1>
                <p>Elegí pedidos, asigná productos y revisá el total antes de crear la tanda.</p>
            </div>
            <div class="admin-header-acciones">
                <a href="<?= url('/admin/tandas') ?>" class="btn-agregar">Historial de tandas</a>
                <a href="<?= url('/admin/pedidos') ?>" class="btn-agregar btn-volver-admin">Volver a pedidos</a>
            </div>
        </header>

        <?php if (empty($esquemaDisponible)): ?>
            <div class="planificacion-aviso planificacion-error" role="alert">
                La base todavía no tiene las tablas de tandas. Consultá <code>database/don_diego.sql</code> para el esquema inicial.
            </div>
        <?php endif; ?>
        <?php if (!empty($esquemaDisponible) && empty($metadatosDisponibles)): ?>
            <div class="planificacion-aviso planificacion-error" role="alert">
                Para guardar títulos, destinos, observaciones y su historial de cambios, ejecutá únicamente el bloque <code>ALTER TABLE tandas_produccion</code> al final de <code>database/don_diego.sql</code>. No importes el dump completo sobre datos existentes.
            </div>
        <?php endif; ?>

        <?php foreach ($errores as $error): ?>
            <?php if ($error !== ''): ?>
                <div class="planificacion-aviso planificacion-error" role="alert"><?= $e($error) ?></div>
            <?php endif; ?>
        <?php endforeach; ?>

        <?php if (($_GET['creada'] ?? '') === '1'): ?>
            <div class="planificacion-aviso planificacion-exito" role="status">La tanda se creó correctamente.</div>
        <?php endif; ?>

        <?php if ($modo === 'revision' && !empty($revision['lineas'])): ?>
            <section class="planificacion-seccion">
                <div class="planificacion-seccion-titulo">
                    <div>
                        <span class="admin-etiqueta">PASO 4</span>
                        <h2>Revisar tanda</h2>
                        <p>Comprobá los pedidos, cantidades y datos generales antes de confirmar.</p>
                    </div>
                </div>
                <dl class="planificacion-resumen-metadatos">
                    <div><dt>Título</dt><dd><?= $e($metadatos['titulo'] !== '' ? $metadatos['titulo'] : 'Tanda automática') ?></dd></div>
                    <div><dt>Destinos</dt><dd><?= $metadatos['destinos'] !== [] ? $e(implode(', ', $metadatos['destinos'])) : 'Sin destinos indicados' ?></dd></div>
                    <?php if ($metadatos['observaciones'] !== ''): ?><div class="planificacion-resumen-observaciones"><dt>Observaciones</dt><dd><?= nl2br($e($metadatos['observaciones'])) ?></dd></div><?php endif; ?>
                </dl>
                <div class="planificacion-grupos-sectores planificacion-resumen-grupos">
                    <?php foreach ($gruposRevisionPorSector as $sector => $gruposSector): ?>
                        <section class="planificacion-grupo-sector">
                            <header><span class="admin-etiqueta">SECTOR DE PRODUCCIÓN</span><h3><?= $e($sector) ?></h3></header>
                            <div class="planificacion-grupos-productos">
                                <?php foreach ($gruposSector as $grupo): ?>
                                    <article class="planificacion-producto-grupo">
                                        <div class="planificacion-producto-grupo-cabecera">
                                            <div><h4><?= $e($grupo['producto']) ?></h4><p><?= $e(Producto::nombrePresentacion($grupo['unidad_venta'], $grupo['cantidad_para_tanda'])) ?></p></div>
                                            <strong class="planificacion-resumen-cantidad"><?= $e(Producto::formatearCantidad($grupo['cantidad_para_tanda'], $grupo['unidad_venta'])) ?> <?= $e(Producto::nombrePresentacion($grupo['unidad_venta'], $grupo['cantidad_para_tanda'])) ?></strong>
                                        </div>
                                        <div class="planificacion-origenes"><strong>Distribución por pedido</strong>
                                            <?php foreach ($grupo['origenes'] as $origen): ?>
                                                <div><span>Pedido #<?= (int) $origen['pedido_id'] ?> · <?= $e($origen['cliente']) ?></span><span><?= $e(Producto::formatearCantidad($origen['cantidad_seleccionada'], $grupo['unidad_venta'])) ?></span></div>
                                            <?php endforeach; ?>
                                        </div>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        </section>
                    <?php endforeach; ?>
                </div>
                <div class="planificacion-acciones">
                    <form method="POST" action="<?= url('/admin/planificacion') ?>">
                        <input type="hidden" name="csrf_token" value="<?= $e(csrf_token()) ?>">
                        <input type="hidden" name="accion" value="editar_metadatos">
                        <input type="hidden" name="estado_filtro" value="<?= $e($estadoFiltro) ?>">
                        <?php foreach ($pedidoIdsVista as $pedidoId): ?><input type="hidden" name="pedido_ids[]" value="<?= $pedidoId ?>"><?php endforeach; ?>
                        <?php foreach ($revision['lineas'] as $linea): ?>
                            <input type="hidden" name="lineas[<?= (int) $linea['detalle_id'] ?>][cantidad]" value="<?= $e(number_format((float) $linea['cantidad_seleccionada'], 1, '.', '')) ?>">
                            <input type="hidden" name="lineas[<?= (int) $linea['detalle_id'] ?>][modo]" value="<?= $e($linea['modo_asignacion']) ?>">
                            <input type="hidden" name="lineas[<?= (int) $linea['detalle_id'] ?>][pedido_id]" value="<?= (int) $linea['pedido_id'] ?>">
                            <input type="hidden" name="lineas[<?= (int) $linea['detalle_id'] ?>][sector]" value="<?= $e($linea['sector_seleccionado']) ?>">
                        <?php endforeach; ?>
                        <input type="hidden" name="titulo" value="<?= $e($metadatos['titulo']) ?>">
                        <?php foreach ($metadatos['destinos'] as $destino): ?><input type="hidden" name="destinos[]" value="<?= $e($destino) ?>"><?php endforeach; ?>
                        <input type="hidden" name="observaciones" value="<?= $e($metadatos['observaciones']) ?>">
                        <button type="submit" class="planificacion-secundario">Volver a editar los datos</button>
                    </form>
                    <?php if (usuarioPuede('gestionar_produccion')): ?>
                        <?php if (!empty($metadatosDisponibles)): ?>
                        <form method="POST" action="<?= url('/admin/planificacion') ?>">
                            <input type="hidden" name="csrf_token" value="<?= $e(csrf_token()) ?>">
                            <input type="hidden" name="accion" value="crear_tanda">
                            <input type="hidden" name="estado_filtro" value="<?= $e($estadoFiltro) ?>">
                            <input type="hidden" name="titulo" value="<?= $e($metadatos['titulo']) ?>">
                            <?php foreach ($metadatos['destinos'] as $destino): ?><input type="hidden" name="destinos[]" value="<?= $e($destino) ?>"><?php endforeach; ?>
                            <input type="hidden" name="observaciones" value="<?= $e($metadatos['observaciones']) ?>">
                            <?php foreach ($revision['lineas'] as $linea): ?>
                                <input type="hidden" name="lineas[<?= (int) $linea['detalle_id'] ?>][cantidad]" value="<?= $e(number_format((float) $linea['cantidad_seleccionada'], 1, '.', '')) ?>">
                                <input type="hidden" name="lineas[<?= (int) $linea['detalle_id'] ?>][modo]" value="<?= $e($linea['modo_asignacion']) ?>">
                                <input type="hidden" name="lineas[<?= (int) $linea['detalle_id'] ?>][pedido_id]" value="<?= (int) $linea['pedido_id'] ?>">
                                <input type="hidden" name="lineas[<?= (int) $linea['detalle_id'] ?>][sector]" value="<?= $e($linea['sector_seleccionado']) ?>">
                            <?php endforeach; ?>
                            <button class="btn-agregar" type="submit">Crear tanda</button>
                        </form>
                        <?php else: ?>
                            <button class="btn-agregar" type="button" disabled>Aplicá la migración para crear</button>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </section>
        <?php elseif ($modo === 'metadatos' && !empty($revision['lineas'])): ?>
            <section class="planificacion-seccion">
                <div class="planificacion-seccion-titulo">
                    <div><span class="admin-etiqueta">PASO 3</span><h2>Datos de la tanda</h2><p>Estos campos son opcionales y no cambian los pedidos comerciales.</p></div>
                </div>
                <form method="POST" action="<?= url('/admin/planificacion') ?>" class="planificacion-form-metadatos">
                    <input type="hidden" name="csrf_token" value="<?= $e(csrf_token()) ?>">
                    <input type="hidden" name="accion" value="confirmar_revision">
                    <input type="hidden" name="estado_filtro" value="<?= $e($estadoFiltro) ?>">
                    <?php foreach ($pedidoIdsVista as $pedidoId): ?><input type="hidden" name="pedido_ids[]" value="<?= $pedidoId ?>"><?php endforeach; ?>
                    <?php foreach ($revision['lineas'] as $linea): ?>
                        <input type="hidden" name="lineas[<?= (int) $linea['detalle_id'] ?>][cantidad]" value="<?= $e(number_format((float) $linea['cantidad_seleccionada'], 1, '.', '')) ?>">
                        <input type="hidden" name="lineas[<?= (int) $linea['detalle_id'] ?>][modo]" value="<?= $e($linea['modo_asignacion']) ?>">
                        <input type="hidden" name="lineas[<?= (int) $linea['detalle_id'] ?>][pedido_id]" value="<?= (int) $linea['pedido_id'] ?>">
                        <input type="hidden" name="lineas[<?= (int) $linea['detalle_id'] ?>][sector]" value="<?= $e($linea['sector_seleccionado']) ?>">
                    <?php endforeach; ?>
                    <div class="planificacion-campo-metadato">
                        <label for="titulo-tanda">Título <span>(opcional)</span></label>
                        <input id="titulo-tanda" type="text" name="titulo" maxlength="150" value="<?= $e($metadatos['titulo']) ?>" placeholder="Ej.: Producción de la mañana">
                        <small>Si lo dejás vacío, se asignará un título con el número y la fecha de la tanda.</small>
                    </div>
                    <fieldset class="planificacion-destinos" data-destinations>
                        <legend>Destinos o sucursales <span>(opcional)</span></legend>
                        <p>Agregá los destinos para esta tanda. Las sucursales de los pedidos son solo referencias y no se asignan automáticamente.</p>
                        <div class="planificacion-destinos-lista" data-destination-list>
                            <?php foreach ($metadatos['destinos'] as $destino): ?>
                                <div class="planificacion-destino-fila"><label class="solo-lectores">Destino</label><input type="text" name="destinos[]" maxlength="150" value="<?= $e($destino) ?>" placeholder="Ej.: Sucursal Centro"><button type="button" data-remove-destination aria-label="Quitar destino">Quitar</button></div>
                            <?php endforeach; ?>
                            <?php if ($metadatos['destinos'] === []): ?>
                                <div class="planificacion-destino-fila"><label class="solo-lectores">Destino</label><input type="text" name="destinos[]" maxlength="150" value="" placeholder="Ej.: Sucursal Centro"><button type="button" data-remove-destination aria-label="Quitar destino">Quitar</button></div>
                            <?php endif; ?>
                        </div>
                        <button type="button" class="planificacion-agregar-destino" data-add-destination>Agregar destino</button>
                    </fieldset>
                    <div class="planificacion-campo-metadato">
                        <label for="observaciones-tanda">Observaciones <span>(opcional)</span></label>
                        <textarea id="observaciones-tanda" name="observaciones" rows="4" maxlength="3000" placeholder="Prioridades o indicaciones para el personal"><?= $e($metadatos['observaciones']) ?></textarea>
                        <small>Máximo 3000 caracteres.</small>
                    </div>
                    <div class="planificacion-acciones planificacion-acciones-fin">
                        <a class="planificacion-secundario" href="<?= url('/admin/planificacion?' . http_build_query(['estado' => $estadoFiltro, 'pedido_ids' => $pedidoIdsVista])) ?>">Volver a productos y cantidades</a>
                        <button class="btn-agregar" type="submit">Revisar tanda</button>
                    </div>
                </form>
            </section>
        <?php else: ?>
            <section class="planificacion-seccion">
                <div class="planificacion-seccion-titulo">
                    <div>
                        <span class="admin-etiqueta">PASO 1</span>
                        <h2>Seleccionar pedidos</h2>
                        <p>Paso 1: seleccioná uno o varios pedidos. El estado se puede filtrar según el flujo comercial vigente.</p>
                    </div>
                </div>
                <form method="GET" action="<?= url('/admin/planificacion') ?>">
                    <div class="planificacion-filtro-estado">
                        <label for="estado-pedidos">Estado comercial</label>
                        <select id="estado-pedidos" name="estado">
                            <option value="" <?= $estadoFiltro === '' ? 'selected' : '' ?>>Todos excepto cancelados</option>
                            <?php foreach ($estadosComerciales as $estadoComercial): ?>
                                <option value="<?= $e($estadoComercial) ?>" <?= $estadoFiltro === $estadoComercial ? 'selected' : '' ?>><?= $e(ucfirst(str_replace('_', ' ', $estadoComercial))) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button class="btn-agregar" type="submit">Filtrar</button>
                    </div>
                <?php if (empty($pedidos)): ?>
                    <div class="admin-pedidos-vacio"><h2>No hay pedidos para mostrar con este filtro</h2><p>Probá otro estado comercial o revisá la gestión de pedidos.</p></div>
                <?php else: ?>
                        <div class="planificacion-tabla-contenedor">
                            <table class="planificacion-tabla">
                                <thead><tr><th>Incluir</th><th>Pedido</th><th>Fecha</th><th>Comercio / sucursal</th><th>Productos</th><th>Estado comercial</th><th>Estado de producción</th></tr></thead>
                                <tbody>
                                    <?php foreach ($pedidos as $pedido): ?>
                                        <tr>
                                            <td data-label="Incluir"><input type="checkbox" name="pedido_ids[]" value="<?= (int) $pedido['id'] ?>" aria-label="Incluir pedido #<?= (int) $pedido['id'] ?>" <?= in_array((int) $pedido['id'], $pedidoIdsVista, true) ? 'checked' : '' ?>></td>
                                            <td data-label="Pedido"><strong>#<?= (int) $pedido['id'] ?></strong></td>
                                            <td data-label="Fecha"><?= $e(date('d/m/Y', strtotime($pedido['fecha_pedido']))) ?><br><small>Entrega <?= $e(date('d/m/Y', strtotime($pedido['fecha_recepcion']))) ?></small></td>
                                            <td data-label="Comercio / sucursal"><?= $e($pedido['cliente']) ?><?= trim((string) $pedido['sucursal']) !== '' ? '<br><small>' . $e($pedido['sucursal']) . '</small>' : '' ?></td>
                                            <td data-label="Productos"><?= $e($pedido['productos'] ?: 'Sin productos') ?></td>
                                            <td data-label="Estado"><span class="planificacion-estado"><?= $e(ucfirst(str_replace('_', ' ', $pedido['estado']))) ?></span></td>
                                            <td data-label="Estado de producción"><span class="planificacion-estado-asignacion estado-asignacion-<?= $e($pedido['estado_asignacion']) ?>"><?= match ($pedido['estado_asignacion']) { 'completo' => 'Completo', 'parcial' => 'Parcial', 'sin_productos' => 'Sin productos', default => 'Sin asignar' } ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="planificacion-acciones planificacion-acciones-fin">
                            <button class="btn-agregar" type="submit">Continuar con los pedidos</button>
                        </div>
                <?php endif; ?>
                </form>
            </section>

            <?php if ($lineasPedido !== []): ?>
                <section class="planificacion-seccion">
                    <div class="planificacion-seccion-titulo">
                        <div>
                            <span class="admin-etiqueta">PASO 2</span>
                            <h2>Productos por preparar</h2>
                            <p>Los productos repetidos se agrupan solo cuando coinciden producto, presentación y sector. Las cantidades completas se calculan al confirmar.</p>
                        </div>
                    </div>
                    <form method="POST" action="<?= url('/admin/planificacion') ?>">
                        <input type="hidden" name="csrf_token" value="<?= $e(csrf_token()) ?>">
                        <input type="hidden" name="accion" value="revisar">
                        <input type="hidden" name="estado_filtro" value="<?= $e($estadoFiltro) ?>">
                        <?php foreach ($pedidoIdsVista as $pedidoId): ?>
                            <input type="hidden" name="pedido_ids[]" value="<?= $pedidoId ?>">
                        <?php endforeach; ?>
                        <?php if ($gruposPorSector === []): ?>
                            <div class="planificacion-aviso">Todos los productos de estos pedidos ya están asignados a producción.</div>
                        <?php else: ?>
                            <div class="planificacion-grupos-sectores">
                                <?php foreach ($gruposPorSector as $sector => $gruposSector): ?>
                                    <section class="planificacion-grupo-sector">
                                        <header><span class="admin-etiqueta">SECTOR DE PRODUCCIÓN</span><h3><?= $e($sector) ?></h3></header>
                                        <div class="planificacion-grupos-productos">
                                            <?php foreach ($gruposSector as $grupo): ?>
                                                <?php $sinDisponible = (float) $grupo['cantidad_disponible'] <= 0; ?>
                                                <article class="planificacion-producto-grupo <?= $sinDisponible ? 'grupo-completo' : '' ?>">
                                                    <div class="planificacion-producto-grupo-cabecera">
                                                        <div>
                                                            <h4><?= $e($grupo['producto']) ?></h4>
                                                            <p><?= $e(Producto::nombrePresentacion($grupo['unidad_venta'], $grupo['cantidad_disponible'])) ?></p>
                                                        </div>
                                                        <span class="planificacion-grupo-estado <?= $sinDisponible ? 'estado-asignacion-completo' : ($grupo['estado_asignacion'] === 'parcial' ? 'estado-asignacion-parcial' : 'estado-asignacion-sin_asignar') ?>">
                                                            <?= $sinDisponible ? 'Ya asignado' : ($grupo['estado_asignacion'] === 'parcial' ? 'Parcialmente asignado' : 'Pendiente') ?>
                                                        </span>
                                                    </div>
                                                    <div class="planificacion-producto-grupo-total">
                                                        <span>Cantidad pendiente de asignar</span>
                                                        <strong><?= $e(Producto::formatearCantidad($grupo['cantidad_disponible'], $grupo['unidad_venta'])) ?> <small><?= $e(Producto::nombrePresentacion($grupo['unidad_venta'], $grupo['cantidad_disponible'])) ?></small></strong>
                                                    </div>
                                                    <div class="planificacion-origenes">
                                                        <strong>Pedidos de origen</strong>
                                                        <?php foreach ($grupo['origenes'] as $origen): ?>
                                                            <div><span>#<?= (int) $origen['pedido_id'] ?> · <?= $e($origen['cliente']) ?></span><span><?= $e(Producto::formatearCantidad($origen['cantidad_disponible'], $grupo['unidad_venta'])) ?> pendiente</span></div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                    <?php if (!$sinDisponible): ?>
                                                        <details class="planificacion-ajuste-parcial">
                                                            <summary>Ajustar cantidades manualmente (opcional)</summary>
                                                            <p>Usá esta opción solo si hoy se va a preparar una parte. Dejá vacías las líneas que no se asignan.</p>
                                                            <?php foreach ($grupo['origenes'] as $origen): ?>
                                                                <?php if ((float) $origen['cantidad_disponible'] > 0): ?>
                                                                    <label class="planificacion-parcial-origen">
                                                                        <span>Pedido #<?= (int) $origen['pedido_id'] ?> · <?= $e($origen['cliente']) ?><small>Disponible: <?= $e(Producto::formatearCantidad($origen['cantidad_disponible'], $grupo['unidad_venta'])) ?></small></span>
                                                                        <input type="number" name="parciales[<?= (int) $origen['detalle_id'] ?>]" min="0" max="<?= $e(number_format((float) $origen['cantidad_disponible'], 1, '.', '')) ?>" step="0.1" value="" aria-label="Cantidad parcial del pedido #<?= (int) $origen['pedido_id'] ?>">
                                                                    </label>
                                                                <?php endif; ?>
                                                            <?php endforeach; ?>
                                                        </details>
                                                        <label class="planificacion-asignar-control">
                                                            <input type="checkbox" name="grupos_seleccionados[]" value="<?= $e($grupo['clave']) ?>">
                                                            <span>Asignar a producción</span>
                                                        </label>
                                                    <?php endif; ?>
                                                </article>
                                            <?php endforeach; ?>
                                        </div>
                                    </section>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        <div class="planificacion-acciones planificacion-acciones-fin">
                            <a class="planificacion-secundario" href="<?= url('/admin/planificacion') ?>">Cambiar pedidos</a>
                            <?php if ($gruposPorSector !== []): ?><button class="btn-agregar" type="submit">Revisar productos asignados</button><?php endif; ?>
                        </div>
                    </form>
                </section>
            <?php endif; ?>
        <?php endif; ?>
    </main>
</body>
</html>