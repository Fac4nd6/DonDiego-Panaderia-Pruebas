<?php

$pageCss = "cuenta.css";


/* =========================================================
   SESIÓN
========================================================= */

if (session_status() === PHP_SESSION_NONE) {
    require_once '../../config/Session.php';
    iniciar_sesion_segura();
}

require_once '../../config/Csrf.php';


/* =========================================================
   PROTEGER PÁGINA
========================================================= */

if (!isset($_SESSION['usuario_id'])) {

    header('Location: login.php');

    exit;
}


/* =========================================================
   MODELOS
========================================================= */

require '../../config/Database.php';
require '../../models/Usuario.php';


$usuarioModel =
    new Usuario($conn);


/* =========================================================
   OBTENER USUARIO
========================================================= */

$usuario =
    $usuarioModel->obtenerPorId(
        $_SESSION['usuario_id']
    );


if (!$usuario) {

    session_destroy();

    header('Location: login.php');

    exit;
}


/* =========================================================
   VARIABLES
========================================================= */

$nombre =
    $usuario['nombre_completo'] ?? '';

$email =
    $usuario['email'] ?? '';

$nombreComercio =
    $usuario['nombre_comercio'] ?? '';

$telefono =
    $usuario['telefono'] ?? '';

$direccion =
    $usuario['direccion'] ?? '';

$rol =
    $usuario['rol'] ?? 'cliente';


/* =========================================================
   MENSAJES
========================================================= */

$error = '';

$mensaje = '';


/* =========================================================
   ACTUALIZAR DATOS
========================================================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verificar_csrf();

    $nombre =
        trim($_POST['nombre'] ?? '');

    $nombreComercio =
        trim($_POST['nombre_comercio'] ?? '');

    $telefono =
        trim($_POST['telefono'] ?? '');

    $direccion =
        trim($_POST['direccion'] ?? '');


    /* =====================================================
       VALIDAR
    ===================================================== */

    if (empty($nombre)) {

        $error =
            'El nombre completo es obligatorio.';

    } elseif (empty($nombreComercio)) {

        $error =
            'El nombre del comercio es obligatorio.';

    } elseif (empty($telefono)) {

        $error =
            'El teléfono es obligatorio.';

    } elseif (empty($direccion)) {

        $error =
            'La dirección es obligatoria.';

    } else {


        /* =================================================
           ACTUALIZAR BD
        ================================================= */

        $resultado =
            $usuarioModel->actualizarDatos(
                $_SESSION['usuario_id'],
                $nombre,
                $nombreComercio,
                $telefono,
                $direccion
            );


        if ($resultado) {

            /* =============================================
               ACTUALIZAR SESIÓN
            ============================================= */

            $_SESSION['usuario_nombre'] =
                $nombre;


            $mensaje =
                'Tus datos fueron actualizados correctamente.';

        } else {

            $error =
                'No se pudieron guardar los datos.';
        }
    }
}


/* =========================================================
   HEAD
========================================================= */

require '../layouts/head.php';

?>


<body>


    <main class="account-page">


        <section class="account-card">


            <!-- =================================================
                 ENCABEZADO
            ================================================== -->

            <header class="account-header">


                <div class="account-icon">

                    <i class="fa-solid fa-user"></i>

                </div>


                <div>

                    <h1>
                        Mi cuenta
                    </h1>

                    <p>

                        Bienvenido,

                        <?= htmlspecialchars(
                            $nombre,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>

                        👋

                    </p>

                </div>


            </header>


            <!-- =================================================
                 MENSAJE DE ERROR
            ================================================== -->

            <?php if (!empty($error)): ?>

                <div
                    class="account-message error"
                    role="alert"
                >

                    <?= htmlspecialchars(
                        $error,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>

                </div>

            <?php endif; ?>


            <!-- =================================================
                 MENSAJE DE ÉXITO
            ================================================== -->

            <?php if (!empty($mensaje)): ?>

                <div
                    class="account-message success"
                    role="status"
                >

                    <?= htmlspecialchars(
                        $mensaje,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>

                </div>

            <?php endif; ?>


            <!-- =================================================
                 MIS DATOS
            ================================================== -->

            <section class="account-section">


                <h2>
                    Mis datos
                </h2>


                <form
                    method="POST"
                    class="account-form"
                >

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                            Nombre completo

                        </label>

                        <input
                            type="text"
                            id="nombre"
                            name="nombre"
                            value="<?= htmlspecialchars(
                                $nombre,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            required
                        >

                    </div>


                    <!-- EMAIL -->

                    <div class="account-input">

                        <label for="email">

                            Correo electrónico

                        </label>

                        <input
                            type="email"
                            id="email"
                            value="<?= htmlspecialchars(
                                $email,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            disabled
                        >

                        <small>
                            El correo electrónico no se puede modificar.
                        </small>

                    </div>


                    <!-- COMERCIO -->

                    <div class="account-input">

                        <label for="nombre_comercio">

                            Nombre del comercio

                        </label>

                        <input
                            type="text"
                            id="nombre_comercio"
                            name="nombre_comercio"
                            placeholder="Ej: Almacén Don Pedro"
                            value="<?= htmlspecialchars(
                                $nombreComercio,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            required
                        >

                    </div>


                    <!-- TELÉFONO -->

                    <div class="account-input">

                        <label for="telefono">

                            Teléfono

                        </label>

                        <input
                            type="tel"
                            id="telefono"
                            name="telefono"
                            placeholder="Ej: 099 123 456"
                            value="<?= htmlspecialchars(
                                $telefono,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            required
                        >

                    </div>


                    <!-- DIRECCIÓN -->

                    <div class="account-input">

                        <label for="direccion">

                            Dirección del comercio

                        </label>

                        <input
                            type="text"
                            id="direccion"
                            name="direccion"
                            placeholder="Ej: Artigas 1234"
                            value="<?= htmlspecialchars(
                                $direccion,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            required
                        >

                    </div>


                    <!-- ROL -->

                    <div class="account-input">

                        <label>

                            Tipo de cuenta

                        </label>

                        <input
                            type="text"
                            value="<?= htmlspecialchars(
                                $rol,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            disabled
                        >

                    </div>


                    <!-- GUARDAR -->

                    <button
                        type="submit"
                        class="account-save-button"
                    >

                        <i class="fa-solid fa-floppy-disk"></i>

                        Guardar cambios

                    </button>


                </form>


            </section>


            <!-- =================================================
                 OPCIONES
            ================================================== -->

            <section class="account-options">


                <!-- CARRITO -->

                <a
                    href="/DonDiego-Panaderia-Pruebas/views/carrito/index.php"
                    class="account-option"
                >

                    <i class="fa-solid fa-cart-shopping"></i>


                    <div>

                        <strong>
                            Mi carrito
                        </strong>

                        <span>
                            Ver los productos que agregaste
                        </span>

                    </div>


                    <i
                        class="fa-solid fa-chevron-right arrow"
                    ></i>

                </a>


                <!-- PEDIDOS -->

                <a
                    href="/DonDiego-Panaderia-Pruebas/controllers/PedidoController.php?accion=listar"
                    class="account-option"
                >

                    <i class="fa-solid fa-box"></i>


                    <div>

                        <strong>
                            Mis pedidos
                        </strong>

                        <span>
                            Consultar tus pedidos anteriores
                        </span>

                    </div>


                    <i
                        class="fa-solid fa-chevron-right arrow"
                    ></i>

                </a>


            </section>


            <!-- =================================================
                 CERRAR SESIÓN
            ================================================== -->

            <footer class="account-footer">


                <form method="POST" action="/DonDiego-Panaderia-Pruebas/controllers/logout.php">

                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">

                    <button type="submit" class="logout-button">

                    <i
                        class="fa-solid fa-right-from-bracket"
                    ></i>

                        Cerrar sesión

                    </button>

                </form>


            </footer>


        </section>


    </main>


</body>

</html>