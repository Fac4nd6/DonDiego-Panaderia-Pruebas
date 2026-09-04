<?php

$pageCss = "home.css";

require __DIR__ . '/../layouts/head.php';
require __DIR__ . '/../layouts/header.php';

?>

<body>

    <main>

        <!-- =====================================================
             HERO
        ====================================================== -->

        <section class="hero">

            <div class="hero-content">

                <h1>El sabor de lo recién hecho</h1>

                <p>Los mejores productos de panadería.</p>

                <a
                    href="<?= url('/productos') ?>"
                    class="hero-button">
                    Ver catálogo
                </a>

            </div>

            <div class="hero-image">

                <img
                    src="<?= url('/public/img/dondiego-algorico.jpeg') ?>"
                    alt="Productos de Don Diego">

            </div>

        </section>


        <!-- =====================================================
             PRODUCTOS DESTACADOS
        ====================================================== -->

        <section class="productos">

            <h2>Productos</h2>

            <hr class="separador">

            <div class="productos-grid">

                <?php foreach ($productosDestacados as $producto): ?>

                    <a
                        href="<?= url('/productos?producto=' . (int) $producto['id']) ?>"
                        class="producto-card"
                    >

                        <img
                            src="<?= url('/public/img/' . htmlspecialchars($producto['imagen'])) ?>"
                            alt="<?= htmlspecialchars($producto['nombre']) ?>"
                        >

                        <div class="producto-info">

                            <h3>
                                <?= htmlspecialchars($producto['nombre']) ?>
                            </h3>

                            <p>
                                $<?= number_format(
                                    $producto['precio'],
                                    0,
                                    ',',
                                    '.'
                                ) ?>
                            </p>

                        </div>

                    </a>

                <?php endforeach; ?>

            </div>

        </section>


        <!-- =====================================================
             BOTÓN CATÁLOGO COMPLETO
        ====================================================== -->

        <a
            href="<?= url('/productos') ?>"
            class="catalogo-button">
            Ver catálogo completo
        </a>


        <!-- =====================================================
             PRODUCTOS RECOMENDADOS
        ====================================================== -->

        <section class="recommended">

            <h2>Recomendado para vos</h2>

            <hr class="separador">

            <div class="productos-grid">

                <?php foreach ($productosRecomendados as $producto): ?>

                    <a
                        href="<?= url('/productos?producto=' . (int) $producto['id']) ?>"
                        class="producto-card"
                    >

                        <img
                            src="<?= url('/public/img/' . htmlspecialchars($producto['imagen'])) ?>"
                            alt="<?= htmlspecialchars($producto['nombre']) ?>"
                        >

                        <div class="producto-info">

                            <h3>
                                <?= htmlspecialchars($producto['nombre']) ?>
                            </h3>

                            <p>
                                $<?= number_format(
                                    $producto['precio'],
                                    0,
                                    ',',
                                    '.'
                                ) ?>
                            </p>

                        </div>

                    </a>

                <?php endforeach; ?>

            </div>

        </section>


        <!-- =====================================================
             INFORMACIÓN DEL LOCAL
        ====================================================== -->

        <section class="conocenos">

            <div class="conocenos-contenido">

                <div class="info-texto">

                    <h2>Animate y<br>visitarnos</h2>

                    <p class="info-descripcion">
                        Vení a disfrutar de algo rico, recién hecho y preparado
                        con mucho cariño. Te esperamos en nuestro local.
                    </p>

                    <div class="info-datos">

                        <div class="info-dato">

                            <span>📍</span>

                            <p>Uruguay 1796</p>

                        </div>

                        <div class="info-dato">

                            <span>🕐</span>

                            <p>Todos los días · 6:00 a 20:00 hs</p>

                        </div>

                        <div class="info-dato">

                            <span>📞</span>

                            <p>095 005 706 · 473 49 924</p>

                        </div>

                    </div>

                    <a
                        href="https://www.google.com/maps/place/Panader%C3%ADa+y+Confiter%C3%ADa+Don+Diego+(Centro)/@-31.3889752,-57.9519957,17z/data=!4m6!3m5!1s0x95addd560a248351:0x31070367860ed798!8m2!3d-31.3889752!4d-57.9519957!16s%2Fg%2F11h2_b5sg3?hl=es-419&entry=ttu&g_ep=EgoyMDI2MDgxMi4wIKXMDSoASAFQAw%3D%3D"
                        class="conocenos-button"
                        target="_blank"
                        rel="noopener noreferrer">

                        Cómo llegar
                        <span>→</span>

                    </a>

                </div>


                <!-- MAPA -->

                <div class="mapa-container">

                    <iframe
                        title="Mapa de ubicación de Don Diego"
                        class="mapa"
                        src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d6811.739034260684!2d-57.9604178!3d-31.3901612!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x95addd560a248351%3A0x31070367860ed798!2sPanader%C3%ADa%20y%20Confiter%C3%ADa%20Don%20Diego%20(Centro)!5e0!3m2!1ses-419!2suy!4v1786501316195!5m2!1ses-419!2suy"
                        allowfullscreen=""
                        loading="lazy"
                        referrerpolicy="strict-origin-when-cross-origin">
                    </iframe>

                    <div class="mapa-label">
                        📍 Encontranos acá
                    </div>

                </div>

            </div>

        </section>

    </main>


    <?php require __DIR__ . '/../layouts/footer.php'; ?>

</body>

</html>