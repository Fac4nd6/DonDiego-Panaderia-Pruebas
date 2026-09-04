<?php

$pageCss = "login.css";

require '../layouts/head.php';

?>

<body>

    <main class="background-container">

        <section
            class="login-card"
            aria-labelledby="verification-title">

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
                        id="verification-title"
                        class="form-title">
                        Revisá tu correo
                    </h2>

                </header>


                <div
                    style="
                        text-align:center;
                        line-height:1.6;
                    ">

                    <p>
                        Tu cuenta fue creada correctamente.
                    </p>

                    <p>
                        Te enviamos un correo electrónico
                        con un enlace para verificar tu cuenta.
                    </p>

                    <p>
                        Revisá también la carpeta de
                        <strong>spam</strong> o
                        <strong>correo no deseado</strong>.
                    </p>


                    <p
                        style="
                            margin-top:25px;
                            font-size:14px;
                            color:#777;
                        ">
                        El enlace de verificación es válido
                        durante 24 horas.
                    </p>


                    <!-- REENVIAR VERIFICACIÓN -->
                    <p
                        style="
                            margin-top:30px;
                        ">

                        ¿No recibiste el correo?

                        <a href="<?= url('/reenviar-verificacion') ?>"
                            class="verification-resend">
                            Reenviar verificación
                        </a>

                    </p>


                    <!-- VOLVER AL LOGIN -->
                    <p
                        style="
                            margin-top:20px;
                        ">

                        <a href="<?= url('/login') ?>"
                            class="verification-resend">
                            Volver a iniciar sesión
                        </a>

                    </p>

                </div>

            </div>

        </section>

    </main>

</body>

</html>