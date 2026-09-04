<?php

require_once __DIR__ . '/../../config/Session.php';
iniciar_sesion_segura();

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
            href="<?= url('/') ?>"
            class="logo"
        >

            <img
                src="<?= url('/public/img/logo.avif') ?>"
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
                href="<?= url('/') ?>"
            >
                Hogar
            </a>


            <a
                href="<?= url('/productos') ?>"
            >
                Productos
            </a>


            <a
                href="<?= url('/contacto') ?>"
            >
                Contacto
            </a>

            </div>


        <!-- ACCIONES -->

            <div class="nav-actions">


            <!-- CUENTA -->

            <?php if (isset($_SESSION['usuario_id'])): ?>

                <a
                    href="<?= url('/cuenta') ?>"
                    class="login-button"
                >

                    <i class="fa-solid fa-user"></i>

                    Mi cuenta

                </a>

            <?php else: ?>

                <a
                    href="<?= url('/login') ?>"
                    class="login-button"
                >

                    Iniciar sesión

                </a>

            <?php endif; ?>


            <!-- CARRITO -->

            <a
                href="<?= url('/carrito') ?>"
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
                    href="<?= url('/pedidos') ?>"
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
                    href="<?= url('/admin/pedidos') ?>"
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
                    href="<?= url('/admin/productos') ?>"
                    class="nav-button"
                >

                    Admin

                </a>

            <?php endif; ?>


            </div>

        </div>

    </nav>

</header>