<?php

$pageCss = "login.css";


require '../../controllers/UsuarioController.php';


if (session_status() === PHP_SESSION_NONE) {
    require_once '../../config/Session.php';
    iniciar_sesion_segura();
}


$usuarioController =
    new UsuarioController();


$error = '';

$email = '';


/* =========================================================
   PROCESAR LOGIN
========================================================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
) {

    /*
     * Verificar CSRF antes de procesar
     * las credenciales.
     */

    verificar_csrf();


    $email =
        trim(
            $_POST['email'] ?? ''
        );


    $password =
        $_POST['password'] ?? '';


    $resultado =
        $usuarioController->login(
            $email,
            $password
        );


    if (
        $resultado['success']
    ) {

        header(
            'Location: ' . url('/')
        );

        exit;
    }


    $error =
        $resultado['error'];
}


/* =========================================================
   HEAD
========================================================= */

require '../layouts/head.php';

?>

<body>

    <main class="background-container">

        <section
            class="login-card"
            aria-labelledby="login-title">


            <!-- =================================================
                 LOGO
            ================================================== -->

            <header class="logo-container">

                <div class="logo-badge">

                    <img
                        src="<?= url('/public/img/logo.avif') ?>"
                        alt="Logo de Don Diego">

                </div>

            </header>


            <div class="login-content">


                <!-- =================================================
                     TÍTULO
                ================================================== -->

                <header class="form-header">

                    <h2
                        id="login-title"
                        class="form-title">

                        Iniciar sesión

                    </h2>

                </header>


                <!-- =================================================
                     ERROR
                ================================================== -->

                <?php if (!empty($error)): ?>

                    <aside
                        class="error-msg"
                        role="alert">

                        <?= htmlspecialchars(
                            $error,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>

                    </aside>

                <?php endif; ?>


                <!-- =================================================
                     FORMULARIO
                ================================================== -->

                <form
                    action="<?= url('/login') ?>"
                    method="POST"
                    class="login-form"
                    id="loginForm">


                    <!-- CSRF -->

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= htmlspecialchars(
                            csrf_token(),
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>">


                    <fieldset>

                        <legend class="sr-only">
                            Datos de inicio de sesión
                        </legend>


                        <!-- =================================================
                             EMAIL
                        ================================================== -->

                        <div class="input-group">

                            <label for="email">
                                Correo electrónico
                            </label>

                            <input
                                type="email"
                                id="email"
                                name="email"
                                placeholder="ejemplo@correo.com"
                                value="<?= htmlspecialchars(
                                    $email,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                                autocomplete="email"
                                maxlength="150"
                                required>

                        </div>


                        <!-- =================================================
                             CONTRASEÑA
                        ================================================== -->

                        <div class="input-group password-group">

                            <label for="password">
                                Contraseña
                            </label>


                            <div class="password-wrapper">

                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    placeholder="Ingresa tu contraseña"
                                    autocomplete="current-password"
                                    required>


                                <button
                                    type="button"
                                    class="toggle-password"
                                    data-target="password"
                                    aria-label="Mostrar contraseña"
                                    aria-pressed="false">

                                    <i class="fa-solid fa-eye"></i>

                                </button>

                            </div>

                        </div>

                    </fieldset>


                    <!-- =================================================
                         BOTÓN
                    ================================================== -->

                    <button
                        type="submit"
                        class="btn-submit"
                        id="loginSubmit">

                        Iniciar sesión

                    </button>


                </form>


                <!-- =================================================
                     REGISTRO
                ================================================== -->

                <footer class="form-footer">

                    <p>

                        ¿No tienes una cuenta?

                        <a href="<?= url('/registro') ?>">
                            Regístrate
                        </a>

                    </p>

                </footer>


            </div>

        </section>

    </main>


    <!-- =========================================================
         JAVASCRIPT
    ========================================================== -->

    <script
        src="<?= url('/public/js/auth.js') ?>"
        defer>
    </script>

</body>

</html>