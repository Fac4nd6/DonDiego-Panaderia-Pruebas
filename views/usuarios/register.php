<?php

$pageCss = "login.css";

$error = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($nombre) || empty($email) || empty($password)) {

        $error = 'Por favor, completa todos los campos.';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = 'Ingresa un correo electrónico válido.';

    } elseif (strlen($password) < 8) {

        $error = 'La contraseña debe tener al menos 8 caracteres.';

    } else {

        /*
         * Aquí irá la lógica para registrar al usuario.
         *
         * - Comprobar si el correo ya existe.
         * - Encriptar la contraseña con password_hash().
         * - Guardar nombre, email y contraseña en la base de datos.
         * - Redirigir al login.
         */
    }
}

require '../layouts/head.php';

?>

<body>

    <main class="background-container">

        <section class="login-card" aria-labelledby="register-title">

            <!-- LOGO -->
            <header class="logo-container">

                <div class="logo-badge">

                    <img
                        src="../../public/img/logo.avif"
                        alt="Logo de Don Diego">

                </div>

            </header>


            <div class="login-content">

                <!-- TÍTULO -->
                <header class="form-header">

                    <h2 id="register-title" class="form-title">
                        Crear cuenta
                    </h2>

                </header>


                <!-- MENSAJE DE ERROR -->
                <?php if (!empty($error)): ?>

                    <aside class="error-msg" role="alert">

                        <?= htmlspecialchars(
                            $error,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>

                    </aside>

                <?php endif; ?>


                <!-- FORMULARIO -->
                <form
                    action="register.php"
                    method="POST"
                    class="login-form">

                    <fieldset>

                        <legend class="sr-only">
                            Datos de registro
                        </legend>


                        <!-- NOMBRE COMPLETO -->
                        <div class="input-group">

                            <label for="nombre">
                                Nombre completo
                            </label>

                            <input
                                type="text"
                                id="nombre"
                                name="nombre"
                                placeholder="Ingresa tu nombre completo"
                                value="<?= htmlspecialchars(
                                    $nombre ?? '',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                                autocomplete="name"
                                required>

                        </div>


                        <!-- CORREO -->
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
                                    $email ?? '',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                                autocomplete="email"
                                required>

                        </div>


                        <!-- CONTRASEÑA -->
                        <div class="input-group">

                            <label for="password">
                                Contraseña
                            </label>

                            <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="Ingresa tu contraseña"
                                autocomplete="new-password"
                                required>

                        </div>

                    </fieldset>


                    <!-- BOTÓN -->
                    <button
                        type="submit"
                        class="btn-submit">

                        Crear cuenta

                    </button>

                </form>


                <!-- FOOTER -->
                <footer class="form-footer">

                    <p>

                        ¿Ya tienes una cuenta?

                        <a href="login.php">
                            Inicia sesión
                        </a>

                    </p>

                </footer>

            </div>

        </section>

    </main>

</body>

</html>