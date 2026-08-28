<?php

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
                    href="/DonDiego-Panaderia-Pruebas/controllers/ProductoController.php?accion=crear"
                    class="btn-agregar">
                    + Agregar producto
                </a>

                <a
                    href="/DonDiego-Panaderia-Pruebas/controllers/PedidoController.php?accion=admin"
                    class="btn-agregar">
                    + Ver pedidos
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
                        href="/DonDiego-Panaderia-Pruebas/controllers/ProductoController.php?accion=crear"
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
                                                    src="/DonDiego-Panaderia-Pruebas/public/img/<?= htmlspecialchars($producto['imagen']) ?>"
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
                                                href="/DonDiego-Panaderia-Pruebas/controllers/ProductoController.php?accion=editar&id=<?= $producto['id'] ?>"
                                                class="btn-editar"
                                                title="Editar producto">
                                                ✏️
                                            </a>


                                            <?php if ($producto['activo'] == 1): ?>

                                                <a
                                                    href="/DonDiego-Panaderia-Pruebas/controllers/ProductoController.php?accion=desactivar&id=<?= $producto['id'] ?>"
                                                    class="btn-eliminar"
                                                    title="Desactivar producto"
                                                    onclick="return confirm('¿Seguro que querés desactivar este producto?');">
                                                    🗑️
                                                </a>

                                            <?php else: ?>

                                                <a
                                                    href="/DonDiego-Panaderia-Pruebas/controllers/ProductoController.php?accion=activar&id=<?= $producto['id'] ?>"
                                                    class="btn-activar"
                                                    title="Activar producto"
                                                    onclick="return confirm('¿Querés volver a activar este producto?');">
                                                    🔄
                                                </a>

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