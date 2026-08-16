<?php

$pageCss = "login.css";

$error = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Por favor, completa todos los campos.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Ingresa un correo electrónico válido.';
    } else {
        /*
         * Aquí irá la lógica de autenticación con la base de datos.
         *
         * Ejemplo:
         * - Buscar el usuario por email.
         * - Verificar la contraseña con password_verify().
         * - Crear la sesión.
         * - Redirigir al usuario.
         */
    }
}

require '../layouts/head.php';


?>

<body>

    <main class="background-container">

        <section class="login-card" aria-labelledby="login-title">

            <header class="logo-container">
                <div class="logo-badge">
                    <img
                        src="../../public/img/logo.avif"
                        alt="Logo de Don Diego">
                </div>
            </header>
            <div class="login-content">

                <header class="form-header">
                    <h2 id="login-title" class="form-title">
                        Iniciar sesión
                    </h2>
                </header>

                <?php if (!empty($error)): ?>

                    <aside class="error-msg" role="alert">
                        <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                    </aside>

                <?php endif; ?>

                <form action="login.php" method="POST" class="login-form">

                    <fieldset>

                        <legend class="sr-only">
                            Datos de inicio de sesión
                        </legend>

                        <div class="input-group">

                            <label for="email">
                                Correo electrónico
                            </label>

                            <input
                                type="email"
                                id="email"
                                name="email"
                                placeholder="ejemplo@correo.com"
                                value="<?= htmlspecialchars($email ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                autocomplete="email"
                                required>

                        </div>

                        <div class="input-group">

                            <label for="password">
                                Contraseña
                            </label>

                            <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="Ingresa tu contraseña"
                                autocomplete="current-password"
                                required>

                        </div>

                    </fieldset>

                    <button type="submit" class="btn-submit">
                        Iniciar sesión
                    </button>

                </form>

                <footer class="form-footer">

                    <p>
                        ¿No tienes una cuenta?
                        <a href="register.php">
                            Regístrate
                        </a>
                    </p>

                </footer>

            </div>

        </section>

    </main>

</body>

</html>