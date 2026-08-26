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

        <!-- LOGO -->

        <a
            href="/DonDiego-Panaderia-Pruebas/controllers/HomeController.php"
            class="logo"
        >

            <img
                src="/DonDiego-Panaderia-Pruebas/public/img/logo.avif"
                alt="Don Diego"
            >

        </a>

        <button
            type="button"
            class="menu-toggle"
            aria-label="Abrir menú"
            aria-controls="menuPrincipal"
            aria-expanded="false">
            <span></span>
            <span></span>
            <span></span>
        </button>


        <!-- MENÚ PRINCIPAL -->

        <div class="mobile-menu" id="menuPrincipal">

            <div class="nav-links">

            <a
                href="/DonDiego-Panaderia-Pruebas/controllers/HomeController.php"
            >
                Hogar
            </a>


            <a
                href="/DonDiego-Panaderia-Pruebas/controllers/CatalogoController.php"
            >
                Productos
            </a>


            <a
                href="/DonDiego-Panaderia-Pruebas/views/contacto.php"
            >
                Contacto
            </a>

            </div>


        <!-- ACCIONES -->

            <div class="nav-actions">


            <!-- CUENTA -->

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


            <!-- CARRITO -->

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


            <!-- MIS PEDIDOS -->

            <?php if (isset($_SESSION['usuario_id'])): ?>

                <a
                    href="/DonDiego-Panaderia-Pruebas/controllers/PedidoController.php?accion=listar"
                    class="nav-button"
                >
                    Mis pedidos
                </a>

            <?php endif; ?>


            <!-- PEDIDOS ADMIN / EMPLEADO -->

            <?php if (
                isset($_SESSION['usuario_rol']) &&
                in_array(
                    $_SESSION['usuario_rol'],
                    ['admin', 'empleado'],
                    true
                )
            ): ?>

                <a
                    href="/DonDiego-Panaderia-Pruebas/controllers/PedidoController.php?accion=admin"
                    class="nav-button"
                >

                    <i class="fa-solid fa-box"></i>

                    Pedidos

                </a>

            <?php endif; ?>


            <!-- ADMIN -->

            <?php if (
                isset($_SESSION['usuario_rol']) &&
                $_SESSION['usuario_rol'] === 'admin'
            ): ?>

                <a
                    href="/DonDiego-Panaderia-Pruebas/controllers/ProductoController.php?accion=listar"
                    class="nav-button"
                >

                    Admin

                </a>

            <?php endif; ?>


            </div>

        </div>

    </nav>

</header>