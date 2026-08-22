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
                href="/DonDiego-Panaderia-Pruebas/controllers/HomeController.php"
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


      <!-- =================================================
     ACCIONES
================================================== -->

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
         PEDIDOS
    ================================================== -->

    <a
        href="http://localhost/DonDiego-Panaderia-Pruebas/controllers/PedidoController.php?accion=listar"
        class="nav-button"
    >
        Mis pedidos
    </a>

    <!-- =================================================
         ADMIN
    ================================================== -->

    <?php if (
        isset($_SESSION['usuario_rol']) &&
        $_SESSION['usuario_rol'] === 'admin'
    ): ?>

        <a
            href="http://localhost/DonDiego-Panaderia-Pruebas/controllers/ProductoController.php?accion=listar"
            class="nav-button"
        >
            Admin
        </a>

    <?php endif; ?>



</div>

</header>