<?php

$pageCss = "cuenta.css";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/* =========================================================
   PROTEGER LA PÁGINA
========================================================= */

if (!isset($_SESSION['usuario_id'])) {

    header('Location: login.php');
    exit;

}


/* =========================================================
   DATOS DEL USUARIO
========================================================= */

$nombre = $_SESSION['usuario_nombre'] ?? 'Usuario';
$email = $_SESSION['usuario_email'] ?? '';
$rol = $_SESSION['usuario_rol'] ?? 'usuario';


require '../layouts/head.php';

?>

<body>

    <main class="account-page">

        <section class="account-card">

            <!-- ENCABEZADO -->

            <header class="account-header">

                <div class="account-icon">
                    <i class="fa-solid fa-user"></i>
                </div>

                <div>

                    <h1>
                        Mi cuenta
                    </h1>

                    <p>
                        Bienvenido, <?= htmlspecialchars(
                            $nombre,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?> 👋
                    </p>

                </div>

            </header>


            <!-- DATOS -->

            <section class="account-section">

                <h2>
                    Mis datos
                </h2>


                <div class="account-info">

                    <div class="info-item">

                        <span class="info-label">
                            Nombre
                        </span>

                        <span class="info-value">
                            <?= htmlspecialchars(
                                $nombre,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </span>

                    </div>


                    <div class="info-item">

                        <span class="info-label">
                            Correo electrónico
                        </span>

                        <span class="info-value">
                            <?= htmlspecialchars(
                                $email,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </span>

                    </div>


                    <div class="info-item">

                        <span class="info-label">
                            Tipo de cuenta
                        </span>

                        <span class="info-value">
                            <?= htmlspecialchars(
                                $rol,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </span>

                    </div>

                </div>

            </section>


            <!-- OPCIONES -->

            <section class="account-options">

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

                    <i class="fa-solid fa-chevron-right arrow"></i>

                </a>


                <a
                    href="#"
                    class="account-option"
                >

                    <i class="fa-solid fa-box"></i>

                    <div>

                        <strong>
                            Mis pedidos
                        </strong>

                        <span>
                            Próximamente podrás ver tus pedidos
                        </span>

                    </div>

                    <i class="fa-solid fa-chevron-right arrow"></i>

                </a>

            </section>


            <!-- CERRAR SESIÓN -->

            <footer class="account-footer">

                <a
                    href="/DonDiego-Panaderia-Pruebas/controllers/logout.php"
                    class="logout-button"
                >

                    <i class="fa-solid fa-right-from-bracket"></i>

                    Cerrar sesión

                </a>

            </footer>

        </section>

    </main>

</body>

</html>