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
                href="ProductoController.php?accion=listar"
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
                action="ProductoController.php"
                method="POST"
                enctype="multipart/form-data"
                class="producto-form"
            >

                <!-- ACCIÓN -->

                <input
                    type="hidden"
                    name="accion"
                    value="<?= $editando ? 'actualizar' : 'guardar' ?>"
                >


                <?php if ($editando): ?>

                    <input
                        type="hidden"
                        name="id"
                        value="<?= htmlspecialchars($producto['id']) ?>"
                    >

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


                    <!-- NOMBRE -->

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
                            required
                        >

                    </div>


                    <!-- DESCRIPCIÓN -->

                    <div class="form-grupo">

                        <label for="descripcion">
                            Descripción
                        </label>

                        <textarea
                            id="descripcion"
                            name="descripcion"
                            rows="4"
                            placeholder="Describí brevemente el producto..."
                        ><?= htmlspecialchars($producto['descripcion'] ?? '') ?></textarea>

                    </div>


                    <!-- CATEGORÍA -->

                    <div class="form-grupo">

                        <label for="categoria">
                            Categoría
                        </label>

                        <select
                            id="categoria"
                            name="categoria"
                            required
                        >

                            <option value="">
                                Seleccioná una categoría
                            </option>

                            <option
                                value="Panadería"
                                <?= (($producto['categoria'] ?? '') === 'Panadería') ? 'selected' : '' ?>
                            >
                                Panadería
                            </option>

                            <option
                                value="Pastelería"
                                <?= (($producto['categoria'] ?? '') === 'Pastelería') ? 'selected' : '' ?>
                            >
                                Pastelería
                            </option>

                            <option
                                value="Bollería"
                                <?= (($producto['categoria'] ?? '') === 'Bollería') ? 'selected' : '' ?>
                            >
                                Bollería
                            </option>

                            <option
                                value="Tortas"
                                <?= (($producto['categoria'] ?? '') === 'Tortas') ? 'selected' : '' ?>
                            >
                                Tortas
                            </option>

                            <option
                                value="Otros"
                                <?= (($producto['categoria'] ?? '') === 'Otros') ? 'selected' : '' ?>
                            >
                                Otros
                            </option>

                        </select>

                    </div>


                    <!-- PRECIO -->

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
                                required
                            >

                        </div>

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
                                src="/DonDiego-Panaderia-Pruebas/public/img/<?= htmlspecialchars($producto['imagen']) ?>"
                                alt="<?= htmlspecialchars($producto['nombre']) ?>"
                            >

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
                            <?= $editando ? 'Nueva imagen' : 'Imagen del producto' ?>
                        </label>

                        <input
                            type="file"
                            id="imagen"
                            name="imagen"
                            accept="image/*"
                            <?= $editando ? '' : 'required' ?>
                        >

                        <small>
                            Formatos recomendados: JPG, PNG o WEBP.
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
                                name="activo"
                            >

                                <option
                                    value="1"
                                    <?= ($producto['activo'] ?? 1) == 1 ? 'selected' : '' ?>
                                >
                                    Activo
                                </option>

                                <option
                                    value="0"
                                    <?= ($producto['activo'] ?? 1) == 0 ? 'selected' : '' ?>
                                >
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
                        href="ProductoController.php?accion=listar"
                        class="btn-cancelar"
                    >
                        Cancelar
                    </a>


                    <button
                        type="submit"
                        class="btn-guardar"
                    >

                        <i class="fa-solid fa-check"></i>

                        <?= $editando
                            ? 'Guardar cambios'
                            : 'Agregar producto'
                        ?>

                    </button>

                </div>

            </form>

        </section>

    </main>


</body>

</html>