<?php

$pageCss = "admin-productos.css";

require __DIR__ . '/../layouts/head.php';

?>

<body class="pagina-admin">

    <?php require __DIR__ . '/../layouts/header.php'; ?>

    <main class="admin-productos-container">
        <!-- =====================================================
         ENCABEZADO
    ====================================================== -->

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

            <a
                href="../../controllers/ProductoController.php?accion=crear"
                class="btn-agregar">
                + Agregar producto
            </a>

        </section>


        <!-- =====================================================
      RESUMEN
    ====================================================== -->

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


        <!-- =====================================================
         PRODUCTOS
    ====================================================== -->

        <section class="admin-productos">

            <?php if (empty($productos)): ?>

                <!-- SIN PRODUCTOS -->

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
                        href="../../controllers/ProductoController.php?accion=crear"
                        class="btn-agregar">
                        + Agregar primer producto
                    </a>

                </div>


            <?php else: ?>


                <!-- =================================================
                 TABLA
            ================================================== -->

                <div class="tabla-contenedor">

                    <table class="tabla-productos">

                        <thead>

                            <tr>

                                <th>
                                    Producto
                                </th>

                                <th>
                                    Categoría
                                </th>

                                <th>
                                    Precio
                                </th>

                                <th>
                                    Estado
                                </th>

                                <th>
                                    Acciones
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            <?php foreach ($productos as $producto): ?>

                                <tr>

                                    <!-- PRODUCTO -->

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
                                                    <?= htmlspecialchars(
                                                        $producto['nombre']
                                                    ) ?>
                                                </strong>

                                                <?php if (!empty($producto['descripcion'])): ?>

                                                    <span>
                                                        <?= htmlspecialchars(
                                                            $producto['descripcion']
                                                        ) ?>
                                                    </span>

                                                <?php endif; ?>

                                            </div>

                                        </div>

                                    </td>


                                    <!-- CATEGORÍA -->

                                    <td>

                                        <span class="categoria-producto">

                                            <?= htmlspecialchars(
                                                $producto['categoria']
                                            ) ?>

                                        </span>

                                    </td>


                                    <!-- PRECIO -->

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


                                    <!-- ESTADO -->

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


                                    <!-- ACCIONES -->

                                    <td>

                                        <div class="acciones-producto">

                                            <!-- EDITAR -->

                                            <a
                                                href="../../controllers/ProductoController.php?accion=editar&id=<?= $producto['id'] ?>"
                                                class="btn-editar"
                                                title="Editar producto">
                                                ✏️
                                            </a>


                                            <!-- DESACTIVAR -->

                                            <?php if ($producto['activo'] == 1): ?>

                                                <a
                                                    href="../../controllers/ProductoController.php?accion=desactivar&id=<?= $producto['id'] ?>"
                                                    class="btn-eliminar"
                                                    title="Desactivar producto"
                                                    onclick="return confirm('¿Seguro que querés desactivar este producto?');">
                                                    🗑️
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