<?php

$pageCss = "home.css";

require '../layouts/head.php';
require '../layouts/header.php';

?>

<body>
    <nav>
        <!-- Barra de navegación -->
    </nav>

    <main>

        <!-- Hero principal de la página -->

        <section class="hero">
            <div class="hero-content">
                <h1>Don Diego</h1>
                <p>Los mejores productos de panadería.</p>
                <a href="#" class="hero-button">Ver catálogo</a>
            </div>

            <div class="hero-image">
                <img src="../../public/img/dondiego-algorico.jpeg" alt="Productos de Don Diego">
            </div>
        </section>

        <!-- Catálogo de productos destacados -->

        <section class="productos">
            <h2>Productos</h2>

            <div class="productos-grid">

                <article class="producto-card">
                    <img src="/DonDiego-Panaderia-Pruebas/public/img/caja-alfajores.avif" alt="Caja de alfajorcitos">

                    <div class="producto-info">
                        <h3>Caja de Alfajorcitos</h3>
                        <p>$300</p>
                    </div>
                </article>

                <article class="producto-card">
                    <img src="/DonDiego-Panaderia-Pruebas/public/img/torta-crema.avif" alt="Torta de crema">

                    <div class="producto-info">
                        <h3>Torta de Crema</h3>
                        <p>$1200</p>
                    </div>
                </article>

                <article class="producto-card">
                    <img src="/DonDiego-Panaderia-Pruebas/public/img/postre-massini.avif" alt="Postre Massini">

                    <div class="producto-info">
                        <h3>Postre Massini</h3>
                        <p>$175</p>
                    </div>
                </article>

                <article class="producto-card">
                    <img src="/DonDiego-Panaderia-Pruebas/public/img/tronco-navidad.avif" alt="Tronco de Navidad">

                    <div class="producto-info">
                        <h3>Tronco de Navidad</h3>
                        <p>$300</p>
                    </div>
                </article>

                <article class="producto-card">
                    <img src="/DonDiego-Panaderia-Pruebas/public/img/brownie.avif" alt="Brownie">

                    <div class="producto-info">
                        <h3>Brownie</h3>
                        <p>$155</p>
                    </div>
                </article>

                <article class="producto-card">
                    <img src="/DonDiego-Panaderia-Pruebas/public/img/yema-quemada.avif" alt="Yema quemada">

                    <div class="producto-info">
                        <h3>Yema Quemada</h3>
                        <p>$400</p>
                    </div>
                </article>

            </div>

            <a href="catalogo.php" class="catalogo-button">
                Ver catálogo completo
            </a>
        </section>

        <section class="sobre-nosotros">
            <h2>Sobre nosotros</h2>
            <!-- Información -->
        </section>

        <!-- Productos recomendados para el usuario -->

        <section class="recommended">
            <h2>Recomendado para vos</h2>

            <div class="productos-grid">
                <article class="producto-card">
                    <img src="../../public/img/torta-chocolate.avif" alt="Torta de chocolate">
                    <h3>Torta de chocolate</h3>
                    <p>$450</p>

                </article>

                <article class="producto-card">
                    <img src="../../public/img/alfajores.avif" alt="Alfajor de chocolate">
                    <h3>Alfajor de chocolate</h3>
                    <p>$100</p>
                </article>
                
                <article class="producto-card">
                    <img src="../../public/img/brownie.avif" alt="Alfajor de chocolate">
                    <h3>Brownie Chocolate</h3>
                    <p>$250</p>
                    
                </article>
                
            </div>
        </section>

    </main>

    <footer>
        <!-- Footer -->
    </footer>

</body>

</html>