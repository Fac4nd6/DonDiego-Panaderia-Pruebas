<?php

// Muestra una página de error reutilizable.
$pageCss = 'error.css';
$codigoError = (int) ($codigoError ?? 404);
$tituloError = $tituloError ?? 'Página no encontrada';
$descripcionError = $descripcionError ?? 'No pudimos encontrar lo que estabas buscando.';
$urlVolver = $urlVolver ?? url('/');
$textoVolver = $textoVolver ?? 'Volver al inicio';

require __DIR__ . '/../layouts/head.php';
require __DIR__ . '/../layouts/header.php';

?>

<body class="pagina-error">

    <main class="error-container">

        <section class="error-card" aria-labelledby="titulo-error">

            <span class="error-codigo"><?= $codigoError ?></span>

            <h1 id="titulo-error">
                <?= htmlspecialchars($tituloError, ENT_QUOTES, 'UTF-8') ?>
            </h1>

            <p>
                <?= htmlspecialchars($descripcionError, ENT_QUOTES, 'UTF-8') ?>
            </p>

            <a
                href="<?= htmlspecialchars($urlVolver, ENT_QUOTES, 'UTF-8') ?>"
                class="error-boton"
            >
                <i class="fa-solid fa-arrow-left"></i>
                <?= htmlspecialchars($textoVolver, ENT_QUOTES, 'UTF-8') ?>
            </a>

        </section>

    </main>

    <?php require __DIR__ . '/../layouts/footer.php'; ?>

</body>

</html>