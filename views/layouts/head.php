<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Don Diego</title>


    <!-- =====================================================
         CSRF TOKEN
    ====================================================== -->

    <?php

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

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


    <!-- =====================================================
         GOOGLE FONTS
    ====================================================== -->

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Sansita+Swashed:wght@300..900&display=swap"
        rel="stylesheet">


    <!-- =====================================================
         CSS GENERAL
    ====================================================== -->

    <link
        rel="stylesheet"
        href="/DonDiego-Panaderia-Pruebas/public/css/variable.css">

    <link
        rel="stylesheet"
        href="/DonDiego-Panaderia-Pruebas/public/css/style.css">


    <!-- =====================================================
         CSS HEADER
    ====================================================== -->

    <link
        rel="stylesheet"
        href="/DonDiego-Panaderia-Pruebas/public/css/header.css">


    <!-- =====================================================
         CSS ESPECÍFICO DE LA PÁGINA
    ====================================================== -->

    <?php if (isset($pageCss)): ?>

        <?php

        if (!is_array($pageCss)) {
            $pageCss = [$pageCss];
        }

        ?>

        <?php foreach ($pageCss as $css): ?>

            <link
                rel="stylesheet"
                href="/DonDiego-Panaderia-Pruebas/public/css/<?= htmlspecialchars($css) ?>">

        <?php endforeach; ?>

    <?php endif; ?>


    <!-- =====================================================
         CSS FOOTER
    ====================================================== -->

    <link
        rel="stylesheet"
        href="/DonDiego-Panaderia-Pruebas/public/css/footer.css">


    <!-- =====================================================
         FONT AWESOME
    ====================================================== -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

</head>