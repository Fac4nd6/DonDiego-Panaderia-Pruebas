<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<header class="header">
    <nav class="navbar">

        <!-- Logo -->
        <a
            href="/DonDiego-Panaderia-Pruebas/views/home/index.php"
            class="logo"
        >
            <img
                src="/DonDiego-Panaderia-Pruebas/public/img/logo.avif"
                alt="Don Diego"
            >
        </a>


        <!-- Menú -->
        <div class="nav-links">

            <a href="/DonDiego-Panaderia-Pruebas/views/home/index.php">
                Hogar
            </a>

            <a href="/DonDiego-Panaderia-Pruebas/views/servicios.php">
                Servicios
            </a>

            <a href="/DonDiego-Panaderia-Pruebas/views/blog.php">
                Blog
            </a>

            <a href="/DonDiego-Panaderia-Pruebas/views/contacto.php">
                Contacto
            </a>

        </div>


        <!-- Acciones -->
        <div class="nav-actions">

            <?php if (isset($_SESSION['usuario_id'])): ?>

                <!-- Mi cuenta -->
                <a
                    href="/DonDiego-Panaderia-Pruebas/views/usuarios/cuenta.php"
                    class="login-button"
                >
                    <i class="fa-solid fa-user"></i>
                    Mi cuenta
                </a>

            <?php else: ?>

                <!-- Iniciar sesión -->
                <a
                    href="/DonDiego-Panaderia-Pruebas/views/usuarios/login.php"
                    class="login-button"
                >
                    Iniciar sesión
                </a>

            <?php endif; ?>


            <!-- Carrito -->
            <a
                href="/DonDiego-Panaderia-Pruebas/views/carrito/index.php"
                class="cart-button"
                aria-label="Carrito"
            >
                <i class="fa-solid fa-cart-shopping"></i>

                <span class="cart-count">
                    0
                </span>
            </a>


            <!-- Pedir ahora -->
            <a
                href="/DonDiego-Panaderia-Pruebas/views/servicios.php"
                class="nav-button"
            >
                Pedir ahora
            </a>

        </div>

    </nav>
</header>