<?php require_once __DIR__ . '/../../config/Url.php'; ?>
<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Don Diego</title>

    <link rel="icon" href="<?= url('/public/img/logo-don2.png') ?>" type="image/x-icon">


    <!-- CSRF TOKEN -->
    <?php

    require_once __DIR__ . '/../../config/Session.php';
    iniciar_sesion_segura();

    if (empty($_SESSION['csrf_token'])) {

        $_SESSION['csrf_token'] =
            bin2hex(random_bytes(32));
    }

    ?>

    <meta
        name="csrf-token"
        content="<?= htmlspecialchars(
            $_SESSION['csrf_token'],
            ENT_QUOTES,
            'UTF-8'
        ) ?>">


    <!-- GOOGLE FONTS -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Sansita+Swashed:wght@300..900&display=swap"
        rel="stylesheet">


    <!-- CSS GENERAL -->
    <link
        rel="stylesheet"
        href="<?= url('/public/css/variable.css') ?>">

    <link
        rel="stylesheet"
        href="<?= url('/public/css/style.css') ?>">


    <!-- CSS HEADER -->
    <link
        rel="stylesheet"
        href="<?= url('/public/css/header.css') ?>">


    <!-- CSS ESPECÍFICO DE LA PÁGINA -->
    <?php if (isset($pageCss)): ?>

        <?php

        if (!is_array($pageCss)) {
            $pageCss = [$pageCss];
        }

        ?>

        <?php foreach ($pageCss as $css): ?>

            <link
                rel="stylesheet"
                href="<?= url('/public/css/' . htmlspecialchars($css)) ?>?v=<?= filemtime(__DIR__ . '/../../public/css/' . $css) ?>">

        <?php endforeach; ?>

    <?php endif; ?>


    <!-- CSS FOOTER -->
    <link
        rel="stylesheet"
        href="<?= url('/public/css/footer.css') ?>">

    <link
        rel="stylesheet"
        href="<?= url('/public/css/responsive.css') ?>">


    <!-- FONT AWESOME -->
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <script
        src="<?= url('/public/js/mobile.js') ?>"
        defer></script>

    <script>window.APP_BASE_URL = <?= json_encode(url('')) ?>;</script>

</head>