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
                <img src="/DonDiego-Panaderia-Pruebas/public/img/hero.jpg" alt="Productos de Don Diego">
            </div>
        </section>

        <!-- Catálogo de productos destacados -->

        <section class="productos">
            <h2>Productos destacados</h2>
            <!-- Productos -->
        </section>

        <section class="sobre-nosotros">
            <h2>Sobre nosotros</h2>
            <!-- Información -->
        </section>

        <!-- Productos recomendados para el usuario -->

        <section class="recommended">
            <h2>Recomendado para vos</h2>

            <div class="products">
                <article class="product">
                    <img src="/DonDiego-Panaderia-Pruebas/public/img/producto1.jpg" alt="Torta de chocolate">
                    <h3>Torta de chocolate</h3>
                    <p>$450</p>
                    <button>Agregar al carrito</button>
                </article>

                <article class="product">
                    <img src="/DonDiego-Panaderia-Pruebas/public/img/producto2.jpg" alt="Alfajor de chocolate">
                    <h3>Alfajor de chocolate</h3>
                    <p>$100</p>
                    <button>Agregar al carrito</button>
                </article>
            </div>
        </section>

    </main>

    <footer>
        <!-- Footer -->
    </footer>

</body>

</html>