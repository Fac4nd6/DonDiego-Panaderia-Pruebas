<?php

$pageCss = ["catalogo.css", "detalle.css", "pedido-encargo.css"];
require __DIR__ . '/../layouts/head.php';
require __DIR__ . '/../layouts/header.php';

$productos = $productos ?? [];
$fechaEntregaMinima = date('Y-m-d', strtotime('+7 days'));
?>

<body class="pagina-catalogo pagina-pedido-encargo">
    <main class="catalogo-container pedido-encargo-container">
        <section class="catalogo-header">
            <span class="catalogo-etiqueta">DON DIEGO</span>
            <h1>Pedido por encargo</h1>
            <p>Solicitá cantidades mayores a las normales y coordiná la entrega con el equipo.</p>
        </section>

        <section class="catalogo-herramientas pedido-encargo-nav">
            <a href="<?= url('/productos') ?>" class="pedido-encargo-link">← Volver al catálogo</a>
        </section>

        <section class="pedido-encargo-formulario">
                <form method="POST" action="<?= url('/pedidos/encargo') ?>" class="pedido-encargo-form">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="accion" value="encargo">
                    <script src="<?= url('/public/js/encargo.js') ?>" defer></script>

                    <?php if (($_GET['error'] ?? '') === 'fecha_entrega'): ?>
                        <p class="encargo-mensaje-error" role="alert">La fecha solicitada debe ser al menos una semana desde hoy. Te avisaremos la fecha confirmada según el tiempo de producción.</p>
                    <?php endif; ?>

                    <div class="detalle-pedido-card encargo-seccion">
                        <h2>Datos del cliente</h2>
                        <div class="detalle-info-grid">
                            <div class="detalle-info-item">
                                <label for="nombre_cliente">Nombre del cliente / comercio</label>
                                <input type="text" id="nombre_cliente" name="nombre_cliente" required maxlength="150">
                            </div>
                            <div class="detalle-info-item">
                                <label for="telefono">Teléfono obligatorio</label>
                                <input type="tel" id="telefono" name="telefono" required maxlength="30" placeholder="Ej: 099 123 456">
                            </div>
                            <div class="detalle-info-item">
                                <label for="sucursal">Sucursal / punto de contacto</label>
                                <input type="text" id="sucursal" name="sucursal" maxlength="150" placeholder="Opcional">
                            </div>
                            <div class="detalle-info-item">
                                <label for="fecha_entrega">Fecha deseada de entrega</label>
                                <input type="date" id="fecha_entrega" name="fecha_entrega" min="<?= htmlspecialchars($fechaEntregaMinima, ENT_QUOTES, 'UTF-8') ?>">
                                <small class="encargo-ayuda-fecha">Solicitala con al menos una semana de anticipación. Te avisaremos la fecha confirmada, porque depende del tiempo de producción.</small>
                            </div>
                        </div>
                    </div>

                    <div class="detalle-pedido-card encargo-seccion">
                        <div class="encargo-productos-titulo">
                            <h2>Producto solicitado</h2>
                            <p>Elegí un producto, su modalidad y la cantidad que necesitás.</p>
                        </div>
                        <?php if (empty($productos)): ?>
                            <p class="encargo-sin-productos">No hay productos disponibles para encargo.</p>
                        <?php else: ?>
                            <div class="encargo-productos-lista" data-encargo-productos>
                                <div class="encargo-producto-fila">
                                    <label>
                                        Producto
                                        <div class="encargo-producto-selector">
                                            <select name="producto_id[]" data-encargo-producto required>
                                                <option value="">Seleccioná un producto</option>
                                                <?php foreach ($productos as $producto): ?>
                                                    <?php $presentaciones = Producto::obtenerPresentaciones($producto); ?>
                                                    <option
                                                        value="<?= (int) $producto['id'] ?>"
                                                        data-imagen="<?= htmlspecialchars(url('/public/img/' . rawurlencode($producto['imagen'] ?? 'logo.avif')), ENT_QUOTES, 'UTF-8') ?>"
                                                        data-presentaciones="<?= htmlspecialchars(json_encode($presentaciones, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP), ENT_QUOTES, 'UTF-8') ?>">
                                                        <?= htmlspecialchars($producto['nombre'], ENT_QUOTES, 'UTF-8') ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <span class="encargo-producto-preview" data-producto-preview aria-live="polite">
                                                <img src="<?= url('/public/img/logo.avif') ?>" alt="" data-producto-preview-imagen>
                                                <span data-producto-preview-nombre>Elegí para ver el producto</span>
                                            </span>
                                        </div>
                                    </label>
                                    <label>
                                        Modalidad
                                        <select name="unidad_venta[]" data-encargo-modalidad required disabled>
                                            <option value="">Primero elegí un producto</option>
                                        </select>
                                    </label>
                                    <label>
                                        Cantidad
                                        <input type="number" name="cantidad[]" min="1" step="1" placeholder="Ej: 150" required>
                                    </label>
                                    <button type="button" class="encargo-quitar-producto" data-quitar-producto aria-label="Quitar producto" title="Quitar producto" hidden>&times;</button>
                                </div>
                            </div>
                            <template id="encargo-producto-template">
                                <div class="encargo-producto-fila">
                                    <label>
                                        Producto
                                        <div class="encargo-producto-selector">
                                            <select name="producto_id[]" data-encargo-producto required>
                                                <option value="">Seleccioná un producto</option>
                                                <?php foreach ($productos as $producto): ?>
                                                    <?php $presentaciones = Producto::obtenerPresentaciones($producto); ?>
                                                    <option
                                                        value="<?= (int) $producto['id'] ?>"
                                                        data-imagen="<?= htmlspecialchars(url('/public/img/' . rawurlencode($producto['imagen'] ?? 'logo.avif')), ENT_QUOTES, 'UTF-8') ?>"
                                                        data-presentaciones="<?= htmlspecialchars(json_encode($presentaciones, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP), ENT_QUOTES, 'UTF-8') ?>">
                                                        <?= htmlspecialchars($producto['nombre'], ENT_QUOTES, 'UTF-8') ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <span class="encargo-producto-preview" data-producto-preview aria-live="polite">
                                                <img src="<?= url('/public/img/logo.avif') ?>" alt="" data-producto-preview-imagen>
                                                <span data-producto-preview-nombre>Elegí para ver el producto</span>
                                            </span>
                                        </div>
                                    </label>
                                    <label>
                                        Modalidad
                                        <select name="unidad_venta[]" data-encargo-modalidad required disabled>
                                            <option value="">Primero elegí un producto</option>
                                        </select>
                                    </label>
                                    <label>
                                        Cantidad
                                        <input type="number" name="cantidad[]" min="1" step="1" placeholder="Ej: 150" required>
                                    </label>
                                    <button type="button" class="encargo-quitar-producto" data-quitar-producto aria-label="Quitar producto" title="Quitar producto">&times;</button>
                                </div>
                            </template>
                            <button type="button" class="encargo-agregar-producto" data-agregar-producto>
                                <span aria-hidden="true">+</span> Agregar otro producto
                            </button>
                        <?php endif; ?>
                    </div>

                    <div class="detalle-pedido-card encargo-seccion">
                        <h2>Observaciones</h2>
                        <div class="detalle-info-grid">
                            <div class="detalle-info-item encargo-observaciones">
                                <label for="observaciones">Observaciones adicionales</label>
                                <textarea id="observaciones" name="observaciones" rows="5" maxlength="1000" placeholder="Ej: 400 sandwiches, 15 kg de pan, fecha prioritaria, etc."></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="detalle-acciones encargo-acciones">
                        <button type="submit" class="btn-agregar">Enviar pedido por encargo</button>
                    </div>
                </form>
        </section>
    </main>
</body>
</html>
