<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$cantidadCarrito = 0;

if (isset($_SESSION['carrito'])) {

    foreach ($_SESSION['carrito'] as $item) {

        $cantidadCarrito += (int) $item['cantidad'];
    }
}

?>

<header class="header">

    <nav class="navbar">


        <!-- =====================================================
             LOGO
        ====================================================== -->

        <a
            href="/DonDiego-Panaderia-Pruebas/views/home/index.php"
            class="logo"
        >

            <img
                src="/DonDiego-Panaderia-Pruebas/public/img/logo.avif"
                alt="Don Diego"
            >

        </a>


        <!-- =====================================================
             MENÚ
        ====================================================== -->

        <div class="nav-links">

            <a
                href="/DonDiego-Panaderia-Pruebas/views/home/index.php"
            >
                Hogar
            </a>

            <a
                href="/DonDiego-Panaderia-Pruebas/views/servicios.php"
            >
                Servicios
            </a>

            <a
                href="/DonDiego-Panaderia-Pruebas/views/blog.php"
            >
                Blog
            </a>

            <a
                href="/DonDiego-Panaderia-Pruebas/views/contacto.php"
            >
                Contacto
            </a>

        </div>


        <!-- =====================================================
             ACCIONES
        ====================================================== -->

        <div class="nav-actions">


            <?php if (isset($_SESSION['usuario_id'])): ?>

                <a
                    href="/DonDiego-Panaderia-Pruebas/views/usuarios/cuenta.php"
                    class="login-button"
                >

                    <i class="fa-solid fa-user"></i>

                    Mi cuenta

                </a>

            <?php else: ?>

                <a
                    href="/DonDiego-Panaderia-Pruebas/views/usuarios/login.php"
                    class="login-button"
                >
                    Iniciar sesión
                </a>

            <?php endif; ?>


            <!-- =================================================
                 CARRITO
            ================================================== -->

            <a
                href="/DonDiego-Panaderia-Pruebas/controllers/CarritoController.php?accion=ver"
                class="cart-button"
                aria-label="Carrito"
            >

                <i class="fa-solid fa-cart-shopping"></i>

                <span class="cart-count">
                    <?= $cantidadCarrito ?>
                </span>

            </a>


            <!-- =================================================
                 PEDIR AHORA
            ================================================== -->

            <a
                href="/DonDiego-Panaderia-Pruebas/controllers/CatalogoController.php"
                class="nav-button"
            >
                Pedir ahora
            </a>


        </div>

    </nav>

</header>