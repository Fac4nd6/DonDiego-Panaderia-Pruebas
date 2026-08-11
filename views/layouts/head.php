<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Don Diego</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Sansita+Swashed:wght@300..900&display=swap"
        rel="stylesheet"
    >

    <!-- CSS general -->
    <link rel="stylesheet" href="/DonDiego-Panaderia-Pruebas/public/css/variable.css">
    <link rel="stylesheet" href="/DonDiego-Panaderia-Pruebas/public/css/style.css">

    <!-- CSS específico de la página -->
    <?php if (isset($pageCss)): ?>
        <link rel="stylesheet" href="/DonDiego-Panaderia-Pruebas/public/css/<?= $pageCss ?>">
    <?php endif; ?>

</head>