
<?php

require_once __DIR__ . '/../../config/Session.php';

iniciar_sesion_segura();

$pageCss = ["admin-productos.css"];

require __DIR__ . '/../layouts/head.php';
require __DIR__ . '/../layouts/header.php';


// =========================================================
// CSRF
// =========================================================

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$csrfToken = $_SESSION['csrf_token'];

?>

<body class="pagina-admin">


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
                Asignar rol a un usuario
            </h1>

            <p>
                Buscá un usuario mediante su correo electrónico
                para modificar su rol.
            </p>

        </div>


        <div class="admin-header-botones">

            <a
                href="/DonDiego-Panaderia-Pruebas/controllers/ProductoController.php?accion=listar"
                class="btn-agregar"
            >
                ← Volver a productos
            </a>

        </div>

    </section>


    <!-- =====================================================
         BUSCAR USUARIO
    ====================================================== -->

    <section class="admin-resumen roles-busqueda">

        <div class="resumen-card">

            <h2>
                Buscar usuario
            </h2>

            <form
                method="POST"
                action="/DonDiego-Panaderia-Pruebas/controllers/ProductoController.php"
            >

                <input
                    type="hidden"
                    name="accion"
                    value="buscar_usuario_rol"
                >

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= htmlspecialchars(
                        $csrfToken,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                >

                <div>

                    <label for="correo">
                        Correo electrónico
                    </label>

                    <input
                        type="email"
                        id="correo"
                        name="correo"
                        placeholder="ejemplo@correo.com"
                        autocomplete="email"
                        required
                    >

                </div>

                <button
                    type="submit"
                    class="btn-agregar"
                >
                    Buscar usuario
                </button>

            </form>

        </div>

    </section>


    <!-- =====================================================
         USUARIO ENCONTRADO
    ====================================================== -->

    <?php if (isset($usuario) && is_array($usuario)): ?>

        <section class="admin-resumen roles-usuario">

            <div class="resumen-card">

                <h2>
                    Usuario encontrado
                </h2>

                <p>
                    <strong>Nombre:</strong>

                    <?= htmlspecialchars(
                        $usuario['nombre_completo'] ?? '',
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                </p>

                <p>
                    <strong>Correo:</strong>

                    <?= htmlspecialchars(
                        $usuario['email'] ?? '',
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                </p>

                <p>
                    <strong>Rol actual:</strong>

                    <?= htmlspecialchars(
                        $usuario['rol'] ?? '',
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                </p>


                <form
                    method="POST"
                    action="/DonDiego-Panaderia-Pruebas/controllers/ProductoController.php"
                >

                    <input
                        type="hidden"
                        name="accion"
                        value="actualizar_rol"
                    >

                    <input
                        type="hidden"
                        name="usuario_id"
                        value="<?= (int) $usuario['id'] ?>"
                    >

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= htmlspecialchars(
                            $csrfToken,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                    >

                    <div>

                        <label for="rol">
                            Nuevo rol
                        </label>

                        <select
                            name="rol"
                            id="rol"
                            required
                        >

                            <option value="cliente"
                                <?= ($usuario['rol'] ?? '') === 'cliente'
                                    ? 'selected'
                                    : '' ?>>
                                Cliente
                            </option>

                            <option value="empleado"
                                <?= ($usuario['rol'] ?? '') === 'empleado'
                                    ? 'selected'
                                    : '' ?>>
                                Empleado
                            </option>

                            <option value="admin"
                                <?= ($usuario['rol'] ?? '') === 'admin'
                                    ? 'selected'
                                    : '' ?>>
                                Administrador
                            </option>

                        </select>

                    </div>

                    <button
                        type="submit"
                        class="btn-agregar"
                        onclick="return confirm('¿Seguro que querés cambiar el rol de este usuario?');"
                    >
                        Guardar nuevo rol
                    </button>

                </form>

            </div>

        </section>

    <?php endif; ?>


</main>


<?php require __DIR__ . '/../layouts/footer.php'; ?>


</body>

</html>
