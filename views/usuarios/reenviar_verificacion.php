<?php

$pageCss = "login.css";

require '../../config/Database.php';
require '../../config/Csrf.php';
require '../../config/Brevo.php';

if (session_status() === PHP_SESSION_NONE) {
    require_once '../../config/Session.php';
    iniciar_sesion_segura();
}

$mensaje = '';
$error = '';

$email = '';


/* PROCESAR SOLICITUD */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    /* CSRF */
    verificar_csrf();


    /* EMAIL */
    $email =
        strtolower(
            trim(
                $_POST['email'] ?? ''
            )
        );


    /* VALIDAR EMAIL */
    if (
        !filter_var(
            $email,
            FILTER_VALIDATE_EMAIL
        )
    ) {

        $error =
            'Ingresá un correo electrónico válido.';

    } else {

        /* BUSCAR USUARIO */
        $stmt =
            $conn->prepare(
                "
                SELECT
                    id,
                    nombre_completo,
                    email_verificado,
                    ultimo_reenvio_verificacion,
                    intentos_reenvio_verificacion
                FROM usuarios
                WHERE email = ?
                LIMIT 1
                "
            );


        if (!$stmt) {

            $error =
                'Ocurrió un error. Intentá nuevamente más tarde.';

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


            /*
             * No revelamos si el correo existe.
        */
            if (!$usuario) {

                $mensaje =
                    'Si existe una cuenta pendiente de verificación, '
                    . 'recibirás un nuevo correo electrónico.';

            } elseif (
                (int) $usuario['email_verificado'] === 1
            ) {

                /*
                 * La cuenta ya está verificada.
            */
                $mensaje =
                    'Si existe una cuenta pendiente de verificación, '
                    . 'recibirás un nuevo correo electrónico.';

            } else {

                /* CONTROL DE REENVÍO */
                $ahora = time();

                $ultimoReenvio = null;

                if (
                    !empty(
                        $usuario['ultimo_reenvio_verificacion']
                    )
                ) {

                    $ultimoReenvio =
                        strtotime(
                            $usuario['ultimo_reenvio_verificacion']
                        );
                }


                /*
                 * Esperar 2 minutos entre reenvíos.
            */
                if (
                    $ultimoReenvio !== null &&
                    ($ahora - $ultimoReenvio) < 120
                ) {

                    $error =
                        'Esperá unos minutos antes de solicitar '
                        . 'otro correo de verificación.';

                } else {

                    /*
                     * Contador de reenvíos.
                */
                    $intentos =
                        (int)
                        $usuario['intentos_reenvio_verificacion'];


                    /*
                     * Si pasó una hora desde el último
                     * reenvío, reiniciamos el contador.
                */
                    if (
                        $ultimoReenvio === null ||
                        ($ahora - $ultimoReenvio) >= 3600
                    ) {

                        $intentos = 0;
                    }


                    /*
                     * Máximo 3 reenvíos por hora.
                */
                    if ($intentos >= 3) {

                        $error =
                            'Alcanzaste el límite de reenvíos. '
                            . 'Esperá una hora antes de intentarlo nuevamente.';

                    } else {

                        /* GENERAR NUEVO TOKEN */
                        $token =
                            bin2hex(
                                random_bytes(32)
                            );


                        /*
                         * Nuevo token válido durante 24 horas.
                    */
                        $tokenExpira =
                            date(
                                'Y-m-d H:i:s',
                                time() + 86400
                            );


                        /*
                         * Nuevo contador.
                    */
                        $nuevoIntento =
                            $intentos + 1;


                        /* GUARDAR TOKEN */
                        $stmt =
                            $conn->prepare(
                                "
                                UPDATE usuarios
                                SET
                                    token_verificacion = ?,
                                    token_expira = ?,
                                    ultimo_reenvio_verificacion = NOW(),
                                    intentos_reenvio_verificacion = ?
                                WHERE id = ?
                                LIMIT 1
                                "
                            );


                        if (!$stmt) {

                            $error =
                                'No se pudo generar el nuevo enlace.';

                        } else {

                            $stmt->bind_param(
                                "ssii",
                                $token,
                                $tokenExpira,
                                $nuevoIntento,
                                $usuario['id']
                            );


                            if (
                                !$stmt->execute()
                            ) {

                                $error =
                                    'No se pudo generar el nuevo enlace.';

                                $stmt->close();

                            } else {

                                $stmt->close();


                                /* ENLACE */
                                $enlaceVerificacion =
                                    url_absoluta('/verificar-email?token=' . urlencode($token));


                                /* CONTENIDO EMAIL */
                                $contenidoHTML = '

                                <!DOCTYPE html>

                                <html lang="es">

                                <head>

                                    <meta charset="UTF-8">

                                    <title>
                                        Verificar correo - Don Diego
                                    </title>

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
                                            Verificá tu correo
                                        </h2>

                                        <p>
                                            Hola '
                                            . htmlspecialchars(
                                                $usuario['nombre_completo'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            )
                                            . ',
                                        </p>

                                        <p>
                                            Recibimos una solicitud
                                            para volver a enviar
                                            tu enlace de verificación.
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
                                            Este enlace será válido
                                            durante 24 horas.
                                        </p>

                                        <p
                                            style="
                                                color:#999;
                                                font-size:12px;
                                            "
                                        >
                                            Si vos no solicitaste
                                            este correo, podés ignorarlo.
                                        </p>

                                    </div>

                                </body>

                                </html>
                                ';


                                /* ENVIAR CON BREVO */
                                $resultadoCorreo =
                                    enviarCorreoBrevo(
                                        $email,
                                        $usuario['nombre_completo'],
                                        'Nuevo enlace de verificación - Don Diego',
                                        $contenidoHTML
                                    );


                                /* RESULTADO */
                                if (
                                    !$resultadoCorreo['success']
                                ) {

                                    $error =
                                        'No se pudo enviar el correo. '
                                        . 'Intentá nuevamente más tarde.';

                                } else {

                                    /*
                                     * Regenerar CSRF.
                                */
                                    $_SESSION['csrf_token'] =
                                        bin2hex(
                                            random_bytes(32)
                                        );


                                    $mensaje =
                                        'Si existe una cuenta pendiente '
                                        . 'de verificación, recibirás '
                                        . 'un nuevo correo electrónico.';
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}


/* HEAD */
require '../layouts/head.php';

?>

<body>

    <main class="background-container">

        <section
            class="login-card"
            aria-labelledby="verification-title"
        >

            <!-- LOGO -->
            <header class="logo-container">

                <div class="logo-badge">

                    <img
                        src="<?= url('/public/img/logo.avif') ?>"
                        alt="Logo de Don Diego"
                    >

                </div>

            </header>


            <div class="login-content">

                <!-- TÍTULO -->
                <header class="form-header">

                    <h2
                        id="verification-title"
                        class="form-title"
                    >
                        Reenviar verificación
                    </h2>

                </header>


                <!-- MENSAJE -->
                <?php if (!empty($mensaje)): ?>

                    <div
                        class="verification-message"
                        role="status"
                    >

                        <p>

                            <?= htmlspecialchars(
                                $mensaje,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>

                        </p>

                    </div>

                <?php endif; ?>


                <!-- ERROR -->
                <?php if (!empty($error)): ?>

                    <aside
                        class="error-msg"
                        role="alert"
                    >

                        <?= htmlspecialchars(
                            $error,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>

                    </aside>

                <?php endif; ?>


                <!-- FORMULARIO -->
                <form
                                    action="<?= url('/reenviar-verificacion') ?>"
                    method="POST"
                    class="login-form"
                >

                    <!-- CSRF -->

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= htmlspecialchars(
                            csrf_token(),
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                    >


                    <fieldset>

                        <legend class="sr-only">
                            Solicitar nuevo correo
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
                                value="<?= htmlspecialchars(
                                    $email,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                                autocomplete="email"
                                maxlength="150"
                                required
                            >

                        </div>

                    </fieldset>


                    <button
                        type="submit"
                        class="btn-submit"
                    >
                        Reenviar correo
                    </button>

                </form>


                <!-- REGISTRO -->
                <div class="form-footer">

                    <p>

                        ¿Todavía no tenés una cuenta?

                        <a href="<?= url('/registro') ?>">
                            Crear cuenta
                        </a>

                    </p>

                    <p>

                        <a href="<?= url('/login') ?>">
                            Volver al inicio de sesión
                        </a>

                    </p>

                </div>

            </div>

        </section>

    </main>

</body>

</html>
