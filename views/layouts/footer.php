<?php

require_once __DIR__ . '/../../config/whatsapp.php';
$footerWhatsAppUrl = crearUrlWhatsApp('Hola Don Diego, quisiera realizar una consulta.');

// CONEXIÓN A LA BASE DE DATOS
$novedades = [];

try {

    $footerDb = new mysqli(
        'localhost',
        'root',
        '',
        'don_diego'
    );

    if ($footerDb->connect_error) {

        throw new Exception(
            'Error de conexión: ' . $footerDb->connect_error
        );
    }

    $footerDb->set_charset('utf8mb4');


    // CARGAR MODELO
    require_once __DIR__ . '/../../models/Producto.php';


    // OBTENER NOVEDADES
    $productoModel = new Producto($footerDb);

    $novedades = $productoModel->obtenerNovedades(3);


} catch (Throwable $e) {

    error_log(
        'Error en novedades del footer: '
        . $e->getMessage()
    );

    $novedades = [];

}

?>


<footer class="footer">


    <!-- PARTE SUPERIOR -->
    <div class="footer-top">


        <!-- LOGO -->

        <div class="footer-logo">

            <img
                src="<?= url('/public/img/logo-don2.png') ?>"
                alt="Don Diego Panadería y Confitería"
            >

        </div>


        <!-- REDES -->

        <div class="footer-social">

            <span>
                Nuestras redes
            </span>


            <div class="social-links">


                <!-- FACEBOOK -->

                <a
                    href="https://www.facebook.com/dondiego.uy/?locale=es_LA"
                    target="_blank"
                    rel="noopener noreferrer"
                    aria-label="Facebook"
                >

                    <i class="fa-brands fa-facebook-f"></i>

                    <span>
                        Facebook
                    </span>

                </a>


                <!-- WHATSAPP -->

                <?php if ($footerWhatsAppUrl !== null): ?>

                    <a
                        href="<?= htmlspecialchars($footerWhatsAppUrl, ENT_QUOTES, 'UTF-8') ?>"
                        target="_blank"
                        rel="noopener noreferrer"
                        aria-label="WhatsApp"
                    >

                        <i class="fa-brands fa-whatsapp"></i>

                        <span>
                            WhatsApp
                        </span>

                    </a>

                <?php endif; ?>


                <!-- INSTAGRAM -->

                <a
                    href="https://www.instagram.com/dondiego.uy/?hl=es"
                    target="_blank"
                    rel="noopener noreferrer"
                    aria-label="Instagram"
                >

                    <i class="fa-brands fa-instagram"></i>

                    <span>
                        Instagram
                    </span>

                </a>


            </div>

        </div>

    </div>


    <hr>


    <!-- CONTENIDO -->
    <div class="footer-content">


        <!-- SOBRE NOSOTROS -->
        <div class="footer-column">

            <h3>
                Sobre nosotros
            </h3>

            <p>
                473 49 924
            </p>

            <p>
                Uruguay 1794
            </p>

            <p>
                Departamento Salto
            </p>

        </div>


        <!-- EXPLORAR -->
        <div class="footer-column">

            <h3>
                Explorar
            </h3>

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


        <!-- NOVEDADES -->
        <div class="footer-column">

            <h3>
                Novedades
            </h3>


            <?php if (count($novedades) > 0): ?>


                <div class="footer-news">


                    <?php foreach ($novedades as $producto): ?>


                        <article class="news-item">


                            <!-- IMAGEN -->

                            <?php if (!empty($producto['imagen'])): ?>

                                <img
                                    src="<?= url('/public/img/' . htmlspecialchars(
                                        $producto['imagen'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    )) ?>"
                                    alt="<?= htmlspecialchars(
                                        $producto['nombre'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                >

                            <?php endif; ?>


                            <!-- INFORMACIÓN -->

                            <div>

                                <small>

                                    <?php

                                    if (
                                        !empty($producto['created_at'])
                                        &&
                                        strtotime($producto['created_at']) !== false
                                    ) {

                                        echo date(
                                            'd/m/Y',
                                            strtotime(
                                                $producto['created_at']
                                            )
                                        );

                                    } else {

                                        echo 'Nuevo';

                                    }

                                    ?>

                                </small>


                                <p>

                                    <?= htmlspecialchars(
                                        $producto['nombre'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                </p>

                            </div>


                        </article>


                    <?php endforeach; ?>


                </div>


            <?php else: ?>


                <p class="footer-sin-novedades">

                    No hay novedades por el momento.

                </p>


            <?php endif; ?>


        </div>


    </div>


    <!-- COPYRIGHT -->
    <div class="footer-bottom">

        <p>
            © 2026 Don Diego, Todos Los Derechos Reservados
        </p>

    </div>


</footer>


<?php

// CERRAR CONEXIÓN DEL FOOTER
if (isset($footerDb) && $footerDb instanceof mysqli) {

    $footerDb->close();

}

?>