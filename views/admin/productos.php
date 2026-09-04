
<?php

// Muestra el listado administrativo de productos.
$pageCss = "admin-productos.css";

require __DIR__ . '/../layouts/head.php';

?>

<body class="pagina-admin">

    <?php require __DIR__ . '/../layouts/header.php'; ?>

    <main class="admin-productos-container">

        <section class="admin-header">

            <div>

                <span class="admin-etiqueta">
                    DON DIEGO
                </span>

                <h1>
                    Productos
                </h1>

                <p>
                    Administrá los productos del catálogo.
                </p>

            </div>

            <div class="admin-header-botones">

                <a
                    href="<?= url('/admin/productos/crear') ?>"
                    class="btn-agregar">
                    + Agregar producto
                </a>

                <a
                    href="<?= url('/admin/pedidos') ?>"
                    class="btn-agregar">
                    + Ver pedidos
                </a>

                <a
                    href="<?= url('/admin/clientes') ?>"
                    class="btn-agregar">
                    Clientes
                </a>

                <a
                    href="<?= url('/admin/roles') ?>"
                    class="btn-agregar">
                    + Asignar roles
                </a>

            </div>

        </section>


        <section class="admin-resumen">

            <div class="resumen-card">

                <span class="resumen-numero">
                    <?= count($productos) ?>
                </span>

                <span class="resumen-texto">
                    Productos activos
                </span>

            </div>

        </section>


        <section class="admin-productos">

            <?php if (empty($productos)): ?>

                <div class="productos-vacio">

                    <div class="productos-vacio-icono">
                        📦
                    </div>

                    <h2>
                        No hay productos
                    </h2>

                    <p>
                        Todavía no agregaste ningún producto al catálogo.
                    </p>

                    <a
                        href="<?= url('/admin/productos/crear') ?>"
                        class="btn-agregar">
                        + Agregar primer producto
                    </a>

                </div>

            <?php else: ?>

                <div class="tabla-contenedor">

                    <table class="tabla-productos">

                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Categoría</th>
                                <th>Precio</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php foreach ($productos as $producto): ?>

                                <tr>

                                    <td>

                                        <div class="producto-admin">

                                            <?php if (!empty($producto['imagen'])): ?>

                                                <img
                                                    src="<?= url('/public/img/' . htmlspecialchars($producto['imagen'])) ?>"
                                                    alt="<?= htmlspecialchars($producto['nombre']) ?>"
                                                    class="producto-admin-imagen">

                                            <?php else: ?>

                                                <div class="producto-sin-imagen">
                                                    📷
                                                </div>

                                            <?php endif; ?>


                                            <div class="producto-admin-info">

                                                <strong>
                                                    <?= htmlspecialchars($producto['nombre']) ?>
                                                </strong>

                                                <?php if (!empty($producto['descripcion'])): ?>

                                                    <span>
                                                        <?= htmlspecialchars($producto['descripcion']) ?>
                                                    </span>

                                                <?php endif; ?>

                                            </div>

                                        </div>

                                    </td>


                                    <td>

                                        <span class="categoria-producto">
                                            <?= htmlspecialchars($producto['categoria']) ?>
                                        </span>

                                    </td>


                                    <td>

                                        <strong class="precio-producto">
                                            $<?= number_format(
                                                    $producto['precio'],
                                                    0,
                                                    ',',
                                                    '.'
                                                ) ?>
                                        </strong>

                                    </td>


                                    <td>

                                        <?php if ($producto['activo'] == 1): ?>

                                            <span class="estado activo">
                                                Activo
                                            </span>

                                        <?php else: ?>

                                            <span class="estado inactivo">
                                                Inactivo
                                            </span>

                                        <?php endif; ?>

                                    </td>


                                    <td>

                                        <div class="acciones-producto">

                                            <a
                                                href="<?= url('/admin/productos/editar?id=' . (int) $producto['id']) ?>"
                                                class="btn-editar"
                                                title="Editar producto">
                                                ✏️
                                            </a>


                                            <?php if ($producto['activo'] == 1): ?>

                                                <form
                                                    method="POST"
                                                    action="<?= url('/admin/productos') ?>"
                                                    class="form-accion-producto"
                                                    onsubmit="return confirm('¿Seguro que querés desactivar este producto?');">
                                                    <input type="hidden" name="accion" value="desactivar">
                                                    <input type="hidden" name="id" value="<?= (int) $producto['id'] ?>">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                                                    <button
                                                        type="submit"
                                                        class="btn-eliminar"
                                                        title="Desactivar producto">
                                                    🗑️
                                                    </button>
                                                </form>

                                            <?php else: ?>

                                                <form
                                                    method="POST"
                                                    action="<?= url('/admin/productos') ?>"
                                                    class="form-accion-producto"
                                                    onsubmit="return confirm('¿Querés volver a activar este producto?');">
                                                    <input type="hidden" name="accion" value="activar">
                                                    <input type="hidden" name="id" value="<?= (int) $producto['id'] ?>">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                                                    <button
                                                        type="submit"
                                                        class="btn-activar"
                                                        title="Activar producto">
                                                    🔄
                                                    </button>
                                                </form>

                                            <?php endif; ?>
                                        </div>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            <?php endif; ?>

        </section>

    </main>

</body>

</html>
