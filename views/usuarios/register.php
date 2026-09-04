<?php

$pageCss = "login.css";

require '../../config/Database.php';
require '../../config/Csrf.php';
require '../../config/Brevo.php';

if (session_status() === PHP_SESSION_NONE) {
    require_once '../../config/Session.php';
    iniciar_sesion_segura();
}

$error = '';

$nombre = '';

$email = '';


/* =========================================================
   PROCESAR REGISTRO
========================================================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    /* =====================================================
       CSRF
    ===================================================== */

    verificar_csrf();


    /* =====================================================
       DATOS
    ===================================================== */

    $nombre =
        trim(
            $_POST['nombre'] ?? ''
        );

    $email =
        strtolower(
            trim(
                $_POST['email'] ?? ''
            )
        );

    $password =
        $_POST['password'] ?? '';

    $passwordConfirmacion =
        $_POST['password_confirmacion'] ?? '';


    /* =====================================================
       VALIDAR NOMBRE
    ===================================================== */

    if (empty($nombre)) {

        $error =
            'El nombre completo es obligatorio.';
    } elseif (strlen($nombre) < 2) {

        $error =
            'El nombre debe tener al menos 2 caracteres.';
    } elseif (strlen($nombre) > 100) {

        $error =
            'El nombre es demasiado largo.';
    }


    /* =====================================================
       VALIDAR EMAIL
    ===================================================== */ elseif (
        !filter_var(
            $email,
            FILTER_VALIDATE_EMAIL
        )
    ) {

        $error =
            'Ingresa un correo electrónico válido.';
    }


    /* =====================================================
       VALIDAR CONTRASEÑA
    ===================================================== */ elseif (strlen($password) < 8) {

        $error =
            'La contraseña debe tener al menos 8 caracteres.';
    } elseif (strlen($password) > 72) {

        $error =
            'La contraseña no puede superar los 72 caracteres.';
    } elseif (!preg_match('/[A-Z]/', $password)) {

        $error =
            'La contraseña debe contener al menos una letra mayúscula.';
    } elseif (!preg_match('/[a-z]/', $password)) {

        $error =
            'La contraseña debe contener al menos una letra minúscula.';
    } elseif (!preg_match('/[0-9]/', $password)) {

        $error =
            'La contraseña debe contener al menos un número.';
    }


    /* =====================================================
       CONFIRMAR CONTRASEÑA
    ===================================================== */ elseif (
        $password !==
        $passwordConfirmacion
    ) {

        $error =
            'Las contraseñas no coinciden.';
    }


    /* =====================================================
       COMPROBAR EMAIL
    ===================================================== */ else {

        $stmt =
            $conn->prepare(
                "
                SELECT
                    id
                FROM usuarios
                WHERE email = ?
                LIMIT 1
                "
            );


        if (!$stmt) {

            $error =
                'Ocurrió un error al comprobar el correo.';
        } else {

            $stmt->bind_param(
                "s",
                $email
            );

            $stmt->execute();

            $result =
                $stmt->get_result();

            $usuario =
                $result->fetch_assoc();

            $stmt->close();


            /* =================================================
               EMAIL YA EXISTE
            ================================================== */

            if ($usuario) {

                $error =
                    'El correo electrónico ya está registrado.';
            } else {

                /* =============================================
                   HASH DE CONTRASEÑA
                ============================================== */

                $passwordHash =
                    password_hash(
                        $password,
                        PASSWORD_DEFAULT
                    );


                if ($passwordHash === false) {

                    $error =
                        'No se pudo proteger la contraseña.';
                } else {

                    /* =========================================
                       GENERAR TOKEN
                    ========================================== */

                    $token =
                        bin2hex(
                            random_bytes(32)
                        );


                    /*
                     * El token será válido durante 24 horas.
                     */

                    $tokenExpira =
                        date(
                            'Y-m-d H:i:s',
                            time() + 86400
                        );


                    /* =========================================
                       INSERTAR USUARIO
                    ========================================== */

                    $stmt =
                        $conn->prepare(
                            "
                            INSERT INTO usuarios
                            (
                                nombre_completo,
                                email,
                                password,
                                rol,
                                email_verificado,
                                token_verificacion,
                                token_expira
                            )
                            VALUES
                            (?, ?, ?, 'cliente', 0, ?, ?)
                            "
                        );


                    if (!$stmt) {

                        $error =
                            'Ocurrió un error al crear la cuenta.';
                    } else {

                        $stmt->bind_param(
                            "sssss",
                            $nombre,
                            $email,
                            $passwordHash,
                            $token,
                            $tokenExpira
                        );


                        /* =====================================
                           CREAR CUENTA
                        ====================================== */

                        if ($stmt->execute()) {

                            $stmt->close();


                            /* =================================
                               ENLACE DE VERIFICACIÓN
                            ================================== */

                            $enlaceVerificacion =
                                url_absoluta('/verificar-email?token=' . urlencode($token));


                            /* =================================
                               CONTENIDO DEL CORREO
                            ================================== */

                            $contenidoHTML = '

                            <!DOCTYPE html>

                            <html lang="es">

                            <head>

                                <meta charset="UTF-8">

                                <title>Verificar correo</title>

                            </head>

                            <body
                                style="
                                    margin:0;
                                    padding:0;
                                    background:#f5f5f5;
                                    font-family:Arial,sans-serif;
                                "
                            >

                                <div
                                    style="
                                        max-width:600px;
                                        margin:40px auto;
                                        background:#ffffff;
                                        padding:40px;
                                        border-radius:12px;
                                        text-align:center;
                                    "
                                >

                                    <h1
                                        style="
                                            color:#c62828;
                                        "
                                    >
                                        Don Diego
                                    </h1>

                                    <h2>
                                        ¡Bienvenido, '
                                . htmlspecialchars(
                                    $nombre,
                                    ENT_QUOTES,
                                    'UTF-8'
                                )
                                . '!
                                    </h2>

                                    <p>
                                        Gracias por registrarte.
                                    </p>

                                    <p>
                                        Para activar tu cuenta,
                                        verificá tu correo electrónico
                                        haciendo clic en el siguiente botón:
                                    </p>

                                    <p style="margin:30px 0;">

                                        <a
                                            href="'
                                . htmlspecialchars(
                                    $enlaceVerificacion,
                                    ENT_QUOTES,
                                    'UTF-8'
                                )
                                . '"
                                            style="
                                                display:inline-block;
                                                padding:14px 24px;
                                                background:#c62828;
                                                color:#ffffff;
                                                text-decoration:none;
                                                border-radius:8px;
                                                font-weight:bold;
                                            "
                                        >
                                            Verificar mi correo
                                        </a>

                                    </p>

                                    <p
                                        style="
                                            color:#777;
                                            font-size:14px;
                                        "
                                    >
                                        Este enlace será válido durante
                                        24 horas.
                                    </p>

                                    <p
                                        style="
                                            color:#999;
                                            font-size:12px;
                                        "
                                    >
                                        Si vos no creaste esta cuenta,
                                        podés ignorar este correo.
                                    </p>

                                </div>

                            </body>

                            </html>
                            ';


                            /* =================================
                               ENVIAR CORREO
                            ================================== */

                            $resultadoCorreo =
                                enviarCorreoBrevo(
                                    $email,
                                    $nombre,
                                    'Verificá tu correo electrónico - Don Diego',
                                    $contenidoHTML
                                );


                            /* =================================
                               SI BREVO FALLA
                            ================================== */

                            if (
                                !$resultadoCorreo['success']
                            ) {

                                /*
                                 * Eliminamos la cuenta para
                                 * no dejarla sin posibilidad
                                 * de verificación.
                                 */

                                $stmt =
                                    $conn->prepare(
                                        "
                                        DELETE FROM usuarios
                                        WHERE email = ?
                                        LIMIT 1
                                        "
                                    );


                                if ($stmt) {

                                    $stmt->bind_param(
                                        "s",
                                        $email
                                    );

                                    $stmt->execute();

                                    $stmt->close();
                                }


                                $error =
                                    'No se pudo enviar el correo de verificación. '
                                    . 'Intentá nuevamente más tarde.';
                            } else {

                                /* =============================
                                   REGENERAR CSRF
                                ============================== */

                                $_SESSION['csrf_token'] =
                                    bin2hex(
                                        random_bytes(32)
                                    );


                                /* =============================
                                   AVISO
                                ============================== */

                                header(
                                    'Location: verificar_aviso.php'
                                );

                                exit;
                            }
                        } else {

                            if ($conn->errno === 1062) {

                                $error =
                                    'El correo electrónico ya está registrado.';
                            } else {

                                $error =
                                    'Ocurrió un error al crear la cuenta.';
                            }

                            $stmt->close();
                        }
                    }
                }
            }
        }
    }
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
            aria-labelledby="register-title">

            <header class="logo-container">

                <div class="logo-badge">

                    <img
                        src="<?= url('/public/img/logo.avif') ?>"
                        alt="Logo de Don Diego">

                </div>

            </header>


            <div class="login-content">

                <header class="form-header">

                    <h2
                        id="register-title"
                        class="form-title">
                        Crear cuenta
                    </h2>

                </header>


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


                <form
                                    action="<?= url('/registro') ?>"
                    method="POST"
                    class="login-form"
                    id="registerForm">

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
                            Datos de registro
                        </legend>


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
                                            $nombre,
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>"
                                autocomplete="name"
                                minlength="2"
                                maxlength="100"
                                required>

                        </div>


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
                                    autocomplete="new-password"
                                    minlength="8"
                                    maxlength="72"
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


                            <small
                                id="passwordRequirements"
                                class="password-requirements">
                                Mínimo 8 caracteres, una mayúscula,
                                una minúscula y un número.
                            </small>

                        </div>


                        <div class="input-group password-group">

                            <label for="password_confirmacion">
                                Confirmar contraseña
                            </label>

                            <div class="password-wrapper">

                                <input
                                    type="password"
                                    id="password_confirmacion"
                                    name="password_confirmacion"
                                    placeholder="Repetí tu contraseña"
                                    autocomplete="new-password"
                                    minlength="8"
                                    maxlength="72"
                                    required>

                                <button
                                    type="button"
                                    class="toggle-password"
                                    data-target="password_confirmacion"
                                    aria-label="Mostrar contraseña"
                                    aria-pressed="false">

                                    <i class="fa-solid fa-eye"></i>

                                </button>

                            </div>


                            <small
                                id="passwordMatch"
                                class="password-match"></small>

                        </div>

                    </fieldset>


                    <button
                        type="submit"
                        class="btn-submit"
                        id="registerSubmit">
                        Crear cuenta
                    </button>

                </form>


                <footer class="form-footer">

                    <p>

                        ¿Ya tienes una cuenta?

                        <a href="<?= url('/login') ?>">
                            Inicia sesión
                        </a>

                    </p>

                </footer>

            </div>

        </section>

    </main>


    <script
        src="<?= url('/public/js/auth.js') ?>"
        defer></script>

</body>

</html>