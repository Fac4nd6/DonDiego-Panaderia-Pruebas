
<?php

$pageCss = "login.css";


require '../../config/Database.php';


/* =========================================================
   OBTENER TOKEN
========================================================= */

$token =
    trim(
        $_GET['token'] ?? ''
    );


$verificado = false;

$error = '';

$mensaje = '';

$email = '';


/* =========================================================
   VALIDAR TOKEN
========================================================= */

if (
    empty($token)
) {

    $error =
        'El enlace de verificación no es válido.';

} elseif (
    !preg_match(
        '/^[a-f0-9]{64}$/',
        $token
    )
) {

    $error =
        'El enlace de verificación no es válido.';

} else {


    /* =====================================================
       BUSCAR USUARIO
    ===================================================== */

    $stmt =
        $conn->prepare(
            "
            SELECT
                id,
                nombre_completo,
                email,
                email_verificado,
                token_expira
            FROM usuarios
            WHERE token_verificacion = ?
            LIMIT 1
            "
        );


    if (!$stmt) {

        $error =
            'Ocurrió un error al verificar el correo.';

    } else {


        $stmt->bind_param(
            "s",
            $token
        );


        $stmt->execute();


        $result =
            $stmt->get_result();


        $usuario =
            $result->fetch_assoc();


        $stmt->close();


        /* =================================================
           TOKEN NO EXISTE
        ================================================== */

        if (!$usuario) {

            $error =
                'El enlace de verificación no existe o ya fue utilizado.';

        }


        /* =================================================
           YA ESTÁ VERIFICADO
        ================================================== */

        elseif (
            (int) $usuario['email_verificado'] === 1
        ) {

            $verificado = true;

            $mensaje =
                'Tu correo electrónico ya fue verificado anteriormente.';

        }


        /* =================================================
           TOKEN EXPIRADO
        ================================================== */

        elseif (
            empty($usuario['token_expira']) ||
            strtotime(
                $usuario['token_expira']
            ) < time()
        ) {

            $email =
                $usuario['email'];

            $error =
                'El enlace de verificación ha expirado.';

        }


        /* =================================================
           VERIFICAR EMAIL
        ================================================== */

        else {


            $stmt =
                $conn->prepare(
                    "
                    UPDATE usuarios
                    SET
                        email_verificado = 1,
                        token_verificacion = NULL,
                        token_expira = NULL,
                        ultimo_reenvio_verificacion = NULL,
                        intentos_reenvio_verificacion = 0
                    WHERE id = ?
                    LIMIT 1
                    "
                );


            if (!$stmt) {

                $error =
                    'No se pudo verificar el correo.';

            } else {


                $stmt->bind_param(
                    "i",
                    $usuario['id']
                );


                if (
                    $stmt->execute()
                ) {

                    $verificado = true;

                    $mensaje =
                        'Tu correo electrónico fue verificado correctamente.';

                } else {

                    $error =
                        'No se pudo completar la verificación.';
                }


                $stmt->close();
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
            aria-labelledby="verification-title"
        >


            <!-- =================================================
                 LOGO
            ================================================== -->

            <header class="logo-container">

                <div class="logo-badge">

                    <img
                        src="../../public/img/logo.avif"
                        alt="Logo de Don Diego"
                    >

                </div>

            </header>


            <div class="login-content">


                <!-- =================================================
                     VERIFICACIÓN CORRECTA
                ================================================== -->

                <?php if ($verificado): ?>


                    <header class="form-header">

                        <h2
                            id="verification-title"
                            class="form-title"
                        >
                            ¡Correo verificado!
                        </h2>

                    </header>


                    <div class="verification-message">

                        <p>

                            <?= htmlspecialchars(
                                $mensaje,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>

                        </p>


                        <p>

                            Ya podés iniciar sesión
                            en tu cuenta de Don Diego.

                        </p>

                    </div>


                    <a
                        href="login.php"
                        class="btn-submit"
                        style="
                            display:block;
                            text-align:center;
                            text-decoration:none;
                        "
                    >
                        Iniciar sesión
                    </a>


                <!-- =================================================
                     ERROR
                ================================================== -->

                <?php else: ?>


                    <header class="form-header">

                        <h2
                            id="verification-title"
                            class="form-title"
                        >
                            No se pudo verificar
                        </h2>

                    </header>


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


                    <?php if (!empty($email)): ?>


                        <div class="verification-message">

                            <p>
                                Podés solicitar un nuevo
                                enlace de verificación.
                            </p>

                        </div>


                        <a
                            href="reenviar_verificacion.php"
                            class="btn-submit"
                            style="
                                display:block;
                                text-align:center;
                                text-decoration:none;
                            "
                        >
                            Solicitar nuevo enlace
                        </a>


                    <?php else: ?>


                        <div class="verification-message">

                            <p>

                                Si el enlace expiró,
                                podés solicitar un nuevo
                                enlace de verificación.

                            </p>

                        </div>


                        <a
                            href="reenviar_verificacion.php"
                            class="btn-submit"
                            style="
                                display:block;
                                text-align:center;
                                text-decoration:none;
                            "
                        >
                            Solicitar nuevo enlace
                        </a>


                    <?php endif; ?>


                    <footer class="form-footer">

                        <p>

                            <a href="login.php">
                                Volver al inicio de sesión
                            </a>

                        </p>

                    </footer>


                <?php endif; ?>


            </div>

        </section>

    </main>

</body>

</html>
