<?php

$pageCss = "detalle.css";

require '../../config/Database.php';
require '../layouts/header.php';
require '../layouts/head.php';


// =========================================================
// PRODUCTOS
// =========================================================

$productos = [

    1 => [
        'id' => 1,
        'nombre' => 'Alfajor de chocolate',
        'descripcion' => 'Alfajor artesanal cubierto de chocolate.',
        'precio' => 120,
        'categoria' => 'dulces',
        'imagen' => 'public/img/productos/alfajor.jpg'
    ],

    2 => [
        'id' => 2,
        'nombre' => 'Cookies con chips',
        'descripcion' => 'Cookies caseras con chips de chocolate.',
        'precio' => 150,
        'categoria' => 'dulces',
        'imagen' => 'public/img/productos/cookies.jpg'
    ],

    3 => [
        'id' => 3,
        'nombre' => 'Brownie',
        'descripcion' => 'Brownie de chocolate artesanal.',
        'precio' => 180,
        'categoria' => 'dulces',
        'imagen' => 'public/img/productos/brownie.jpg'
    ],

    4 => [
        'id' => 4,
        'nombre' => 'Torta de chocolate',
        'descripcion' => 'Torta húmeda de chocolate con cobertura.',
        'precio' => 950,
        'categoria' => 'tortas',
        'imagen' => 'public/img/productos/torta-chocolate.jpg'
    ],

    5 => [
        'id' => 5,
        'nombre' => 'Torta de dulce de leche',
        'descripcion' => 'Torta artesanal rellena con dulce de leche.',
        'precio' => 1100,
        'categoria' => 'tortas',
        'imagen' => 'public/img/productos/torta-dulce-leche.jpg'
    ],

    6 => [
        'id' => 6,
        'nombre' => 'Tarta de frutilla',
        'descripcion' => 'Tarta artesanal con crema y frutillas.',
        'precio' => 750,
        'categoria' => 'reposteria',
        'imagen' => 'public/img/productos/tarta-frutilla.jpg'
    ],

    7 => [
        'id' => 7,
        'nombre' => 'Cheesecake',
        'descripcion' => 'Cheesecake artesanal con cobertura de frutos rojos.',
        'precio' => 850,
        'categoria' => 'reposteria',
        'imagen' => 'public/img/productos/cheesecake.jpg'
    ],

    8 => [
        'id' => 8,
        'nombre' => 'Empanada de carne',
        'descripcion' => 'Empanada casera rellena de carne.',
        'precio' => 130,
        'categoria' => 'salados',
        'imagen' => 'public/img/productos/empanada-carne.jpg'
    ],

    9 => [
        'id' => 9,
        'nombre' => 'Empanada de jamón y queso',
        'descripcion' => 'Empanada casera de jamón y queso.',
        'precio' => 130,
        'categoria' => 'salados',
        'imagen' => 'public/img/productos/empanada-jamon.jpg'
    ],

    10 => [
        'id' => 10,
        'nombre' => 'Pan casero',
        'descripcion' => 'Pan artesanal recién horneado.',
        'precio' => 180,
        'categoria' => 'panaderia',
        'imagen' => 'public/img/productos/pan.jpg'
    ],

    11 => [
        'id' => 11,
        'nombre' => 'Medialunas',
        'descripcion' => 'Medialunas artesanales.',
        'precio' => 90,
        'categoria' => 'panaderia',
        'imagen' => 'public/img/productos/medialunas.jpg'
    ],

    12 => [
        'id' => 12,
        'nombre' => 'Pollo al horno',
        'descripcion' => 'Pollo al horno preparado de forma artesanal.',
        'precio' => 850,
        'categoria' => 'rostiseria',
        'imagen' => 'public/img/productos/pollo.jpg'
    ]

];


// =========================================================
// OBTENER ID
// =========================================================

$id = isset($_GET['id'])
    ? (int) $_GET['id']
    : 0;


// =========================================================
// BUSCAR PRODUCTO
// =========================================================

$producto = $productos[$id] ?? null;


// =========================================================
// SI NO EXISTE
// =========================================================

if (!$producto) {
    http_response_code(404);
}

?>

<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?= $producto
            ? htmlspecialchars($producto['nombre']) . ' | Don Diego'
            : 'Producto no encontrado | Don Diego'
        ?>
    </title>

    <link
        rel="stylesheet"
        href="public/css/detalles.css"
    >

</head>


<body class="pagina-detalles">


<?php include '../layouts/header.php'; ?>


<main class="detalles-container">


    <?php if (!$producto): ?>

        <!-- PRODUCTO NO ENCONTRADO -->

        <section class="producto-no-encontrado">

            <span>404</span>

            <h1>
                Producto no encontrado
            </h1>

            <p>
                El producto que estás buscando no existe.
            </p>

            <a href="index.php">
                Volver al catálogo
            </a>

        </section>


    <?php else: ?>


        <!-- =================================================
             DETALLE DEL PRODUCTO
        ================================================== -->

        <section class="detalle-producto">


            <!-- IMAGEN -->

            <div class="detalle-imagen">

                <img
                    src="<?= htmlspecialchars($producto['imagen']) ?>"
                    alt="<?= htmlspecialchars($producto['nombre']) ?>"
                >

            </div>


            <!-- INFORMACIÓN -->

            <div class="detalle-info">

                <span class="detalle-categoria">

                    <?= ucfirst(
                        htmlspecialchars($producto['categoria'])
                    ) ?>

                </span>


                <h1>

                    <?= htmlspecialchars(
                        $producto['nombre']
                    ) ?>

                </h1>


                <p class="detalle-descripcion">

                    <?= htmlspecialchars(
                        $producto['descripcion']
                    ) ?>

                </p>


                <div class="detalle-precio">

                    $<?= number_format(
                        $producto['precio'],
                        0,
                        ',',
                        '.'
                    ) ?>

                </div>


                <!-- CANTIDAD -->

                <div class="detalle-cantidad">

                    <label for="cantidad">
                        Cantidad
                    </label>

                    <div class="cantidad-control">

                        <button
                            type="button"
                            id="menos"
                        >
                            −
                        </button>

                        <input
                            type="number"
                            id="cantidad"
                            value="1"
                            min="1"
                        >

                        <button
                            type="button"
                            id="mas"
                        >
                            +
                        </button>

                    </div>

                </div>


                <!-- BOTÓN CARRITO -->

                <button
                    type="button"
                    class="detalle-carrito"
                    id="agregar-carrito"
                >
                    Agregar al carrito
                </button>


                <!-- VOLVER -->

                <a
                    href="index.php"
                    class="volver-catalogo"
                >
                    ← Volver al catálogo
                </a>

            </div>

        </section>


    <?php endif; ?>


</main>


<?php include '../layouts/footer.php'; ?>


</body>

</html>