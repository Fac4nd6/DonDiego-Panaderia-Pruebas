<?php

$pageCss = "home.css";

require '../layouts/head.php';
require '../layouts/header.php';

?>

<body>
   <?php 
   
   require '../layouts/header.php';
   
   ?>

    <main>

        <!-- Hero principal de la página -->

        <section class="hero">
            <div class="hero-content">
                <h1>El sabor de lo recién hecho</h1>
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
            <hr class="separador">
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
            
        </section>
                <a href="catalogo.php" class="catalogo-button">
                    Ver catálogo completo
                </a>
        
       

        <!-- Productos recomendados para el usuario -->

        <section class="recommended">
            <h2>Recomendado para vos</h2>
            <hr class="separador">

            <div class="productos-grid">
                <article class="producto-card">
                    <img src="../../public/img/torta-chocolate.avif" alt="Torta de chocolate">
                    <div class="producto-info">
                        <h3>Torta de chocolate</h3>
                        <p>$450</p>
                    </div>
                </article>

                <article class="producto-card">
                    <img src="../../public/img/alfajores.avif" alt="Alfajor de chocolate">
                    <div class="producto-info">
                        <h3>Alfajor de chocolate</h3>
                        <p>$100</p>
                    </div>
                </article>
                
                <article class="producto-card">
                    <img src="../../public/img/brownie.avif" alt="Alfajor de chocolate">
                    <div class="producto-info">
                        <h3>Brownie Chocolate</h3>
                        <p>$250</p>
                    </div>
                </article>
                
            </div>
        </section>

         <section class="conocenos">
            
            <div class="informacion">
                <div class="info-texto">
                    <h2>Animate y visitanos</h2>
                    <p>Uruguay 1796</p>
                    <p>de 6:00 a 20:00 hs</p>
                    <P>+598 95 005 706</p>
                    <P>473 49 924</p>
                 </div>
                <iframe class="mapa" src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d6811.739034260684!2d-57.9604178!3d-31.3901612!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x95addd560a248351%3A0x31070367860ed798!2sPanader%C3%ADa%20y%20Confiter%C3%ADa%20Don%20Diego%20(Centro)!5e0!3m2!1ses-419!2suy!4v1786501316195!5m2!1ses-419!2suy" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="strict-origin-when-cross-origin"></iframe>
                <img class="local" src="../../public/img/dondiego-local.jpg" alt="Productos de Don Diego">    
                </div>
            
        </section>

    </main>
<?php require '../layouts/footer.php'; ?>

</body>

</html>