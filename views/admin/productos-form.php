<?php

$pageCss = "admin-productos-form.css";

require __DIR__ . '/../layouts/head.php';

$editando = !empty($producto);

?>

<body class="pagina-admin">

    <?php require __DIR__ . '/../layouts/header.php'; ?>


    <main class="admin-form-container">


        <!-- =====================================================
             ENCABEZADO
        ====================================================== -->

        <section class="admin-form-header">

            <div>

                <span class="admin-etiqueta">
                    DON DIEGO
                </span>

                <h1>
                    <?= $editando ? 'Editar producto' : 'Agregar producto' ?>
                </h1>

                <p>
                    <?= $editando
                        ? 'Modificá la información del producto.'
                        : 'Agregá un nuevo producto al catálogo.'
                    ?>
                </p>

            </div>


            <a
                href="<?= url('/admin/productos') ?>"
                class="btn-volver">

                <i class="fa-solid fa-arrow-left"></i>

                Volver

            </a>

        </section>



        <!-- =====================================================
             FORMULARIO
        ====================================================== -->

        <section class="admin-form-card">

            <form
                action="<?= url('/admin/productos') ?>"
                method="POST"
                enctype="multipart/form-data"
                class="producto-form">


                <!-- =================================================
                     ACCIÓN
                ================================================== -->

                <input
                    type="hidden"
                    name="accion"
                    value="<?= $editando ? 'actualizar' : 'guardar' ?>">

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">


                <?php if ($editando): ?>

                    <input
                        type="hidden"
                        name="id"
                        value="<?= htmlspecialchars($producto['id']) ?>">

                <?php endif; ?>



                <!-- =================================================
                     INFORMACIÓN PRINCIPAL
                ================================================== -->

                <div class="form-seccion">

                    <div class="form-seccion-header">

                        <h2>
                            Información del producto
                        </h2>

                        <p>
                            Completá los datos principales.
                        </p>

                    </div>



                    <!-- =================================================
                         NOMBRE
                    ================================================== -->

                    <div class="form-grupo">

                        <label for="nombre">
                            Nombre
                        </label>

                        <input
                            type="text"
                            id="nombre"
                            name="nombre"
                            placeholder="Ej. Bizcochos de grasa"
                            value="<?= htmlspecialchars($producto['nombre'] ?? '') ?>"
                            required>

                    </div>



                    <!-- =================================================
                         DESCRIPCIÓN
                    ================================================== -->

                    <div class="form-grupo">

                        <label for="descripcion">
                            Descripción
                        </label>

                        <textarea
                            id="descripcion"
                            name="descripcion"
                            rows="4"
                            placeholder="Describí brevemente el producto..."><?= htmlspecialchars($producto['descripcion'] ?? '') ?></textarea>

                    </div>



                    <!-- =================================================
                         CATEGORÍA
                    ================================================== -->

                    <div class="form-grupo">

                        <label for="categoria">
                            Categoría
                        </label>

                        <select
                            id="categoria"
                            name="categoria_id"
                            required>

                            <option value="">
                                Seleccioná una categoría
                            </option>


                            <?php foreach ($categorias as $categoria): ?>

                                <option
                                    value="<?= $categoria['id'] ?>"
                                    <?= (($producto['categoria_id'] ?? '') == $categoria['id'])
                                        ? 'selected'
                                        : ''
                                    ?>>

                                    <?= htmlspecialchars($categoria['nombre']) ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>



                    <!-- =================================================
                         PRECIO
                    ================================================== -->

                    <div class="form-grupo">

                        <label for="precio">
                            Precio
                        </label>


                        <div class="input-precio">

                            <span>
                                $
                            </span>


                            <input
                                type="number"
                                id="precio"
                                name="precio"
                                min="0"
                                step="1"
                                placeholder="0"
                                value="<?= htmlspecialchars($producto['precio'] ?? '') ?>"
                                required>

                        </div>

                    </div>

                </div>


                <div class="form-seccion">

                    <div class="form-seccion-header">
                        <h2>Stock</h2>
                        <p>Indicá la cantidad disponible del producto.</p>
                    </div>

                    <div class="form-grupo">
                        <label for="stock">Cantidad disponible</label>
                        <input
                            type="number"
                            id="stock"
                            name="stock"
                            min="0"
                            step="1"
                            value="<?= htmlspecialchars($producto['stock'] ?? '100') ?>"
                            required>
                    </div>

                    <div class="form-grupo">
                        <label for="unidad_venta">Se vende por</label>
                        <select id="unidad_venta" name="unidad_venta" required>
                            <?php $unidadSeleccionada = $producto['unidad_venta'] ?? 'unidad'; ?>
                            <option value="unidad" <?= $unidadSeleccionada === 'unidad' ? 'selected' : '' ?>>Unidad</option>
                            <option value="docena" <?= $unidadSeleccionada === 'docena' ? 'selected' : '' ?>>Docena</option>
                            <option value="media docena" <?= $unidadSeleccionada === 'media docena' ? 'selected' : '' ?>>Media docena</option>
                            <option value="kilogramo" <?= $unidadSeleccionada === 'kilogramo' ? 'selected' : '' ?>>Kilogramo</option>
                        </select>
                    </div>

                </div>



                <!-- =================================================
                     IMAGEN
                ================================================== -->

                <div class="form-seccion">

                    <div class="form-seccion-header">

                        <h2>
                            Imagen
                        </h2>

                        <p>
                            Agregá una imagen para mostrar el producto.
                        </p>

                    </div>



                    <?php if ($editando && !empty($producto['imagen'])): ?>

                        <div class="imagen-actual">

                            <img
                                src="<?= url('/public/img/' . htmlspecialchars($producto['imagen'])) ?>"
                                alt="<?= htmlspecialchars($producto['nombre']) ?>">


                            <div>

                                <strong>
                                    Imagen actual
                                </strong>

                                <span>
                                    Podés reemplazarla seleccionando una nueva.
                                </span>

                            </div>

                        </div>

                    <?php endif; ?>



                    <div class="form-grupo">

                        <label for="imagen">

                            <?= $editando
                                ? 'Nueva imagen'
                                : 'Imagen del producto'
                            ?>

                        </label>


                        <input
                            type="file"
                            id="imagen"
                            name="imagen"
                            accept="image/jpeg,image/png,image/webp"
                            <?= $editando ? '' : 'required' ?>>


                        <small>
                            Formatos permitidos: JPG, PNG o WEBP.
                        </small>

                    </div>

                </div>



                <!-- =================================================
                     ESTADO
                ================================================== -->

                <?php if ($editando): ?>

                    <div class="form-seccion">

                        <div class="form-seccion-header">

                            <h2>
                                Estado
                            </h2>

                            <p>
                                Elegí si el producto estará visible en el catálogo.
                            </p>

                        </div>


                        <div class="form-grupo">

                            <label for="activo">
                                Estado del producto
                            </label>


                            <select
                                id="activo"
                                name="activo">

                                <option
                                    value="1"
                                    <?= ($producto['activo'] ?? 1) == 1
                                        ? 'selected'
                                        : ''
                                    ?>>
                                    Activo
                                </option>


                                <option
                                    value="0"
                                    <?= ($producto['activo'] ?? 1) == 0
                                        ? 'selected'
                                        : ''
                                    ?>>
                                    Inactivo
                                </option>

                            </select>

                        </div>

                    </div>

                <?php endif; ?>



                <!-- =================================================
                     BOTONES
                ================================================== -->

                <div class="form-acciones">


                    <a
                        href="<?= url('/admin/productos') ?>"
                        class="btn-cancelar">

                        Cancelar

                    </a>


                    <button
                        type="submit"
                        class="btn-guardar">

                        <i class="fa-solid fa-check"></i>

                        <?= $editando
                            ? 'Guardar cambios'
                            : 'Agregar producto'
                        ?>

                    </button>

                </div>

            </form>



            <!-- =====================================================
                 ELIMINAR PRODUCTO
            ====================================================== -->

            <?php if ($editando): ?>

                <div class="form-eliminar">

                    <div>

                        <strong>
                            Eliminar producto
                        </strong>

                        <p>
                            Esta acción eliminará permanentemente el producto.
                        </p>

                    </div>


                    <form
                        action="<?= url('/admin/productos') ?>"
                        method="POST"
                        onsubmit="return confirm(
                            '¿Seguro que querés eliminar este producto? Esta acción no se puede deshacer.'
                        );"
                    >

                        <input
                            type="hidden"
                            name="accion"
                            value="eliminar"
                        >


                        <input
                            type="hidden"
                            name="id"
                            value="<?= htmlspecialchars($producto['id']) ?>"
                        >

                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>"
                        >


                        <button
                            type="submit"
                            class="btn-eliminar-definitivo"
                        >

                            <i class="fa-solid fa-trash"></i>

                            Eliminar producto

                        </button>

                    </form>

                </div>

            <?php endif; ?>


        </section>

    </main>


</body>

</html>