<?php

$pageCss = "carrito.css";

session_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {

    header('Location: /DonDiego-Panaderia-Pruebas/views/usuarios/login.php');
    exit;
}

require '../layouts/head.php';
require '../layouts/header.php';


/*
|--------------------------------------------------------------------------
| INICIALIZAR CARRITO
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['carrito'])) {

    $_SESSION['carrito'] = [];
}


/*
|--------------------------------------------------------------------------
| ACTUALIZAR CANTIDAD
|--------------------------------------------------------------------------
*/

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['actualizar'])
) {

    $productoId = filter_input(
        INPUT_POST,
        'producto_id',
        FILTER_VALIDATE_INT
    );

    $cantidad = filter_input(
        INPUT_POST,
        'cantidad',
        FILTER_VALIDATE_INT
    );


    if (
        $productoId !== false &&
        $productoId !== null &&
        $cantidad !== false &&
        $cantidad !== null
    ) {

        if ($cantidad <= 0) {

            unset($_SESSION['carrito'][$productoId]);
        } else {

            if (isset($_SESSION['carrito'][$productoId])) {

                $_SESSION['carrito'][$productoId]['cantidad'] = $cantidad;
            }
        }
    }


    header('Location: carrito.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| ELIMINAR PRODUCTO
|--------------------------------------------------------------------------
*/

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['eliminar'])
) {

    $productoId = filter_input(
        INPUT_POST,
        'producto_id',
        FILTER_VALIDATE_INT
    );


    if (
        $productoId !== false &&
        $productoId !== null
    ) {

        unset($_SESSION['carrito'][$productoId]);
    }


    header('Location: carrito.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| CALCULAR TOTAL
|--------------------------------------------------------------------------
*/

$total = 0;

$cantidadProductos = 0;


foreach ($_SESSION['carrito'] as $item) {

    $subtotal = $item['precio'] * $item['cantidad'];

    $total += $subtotal;

    $cantidadProductos += $item['cantidad'];
}

?>

<body class="pagina-carrito">

    <?php require '../layouts/header.php'; ?>


    <main class="carrito-container">


        <header class="carrito-header">

            <h1>Tu carrito</h1>

            <p>
                Revisá tus productos antes de continuar.
            </p>

        </header>


        <?php if (empty($_SESSION['carrito'])): ?>


            <!-- CARRITO VACÍO -->

            <section class="carrito-vacio">

                <div class="carrito-vacio-icono">
                    🛒
                </div>

                <h2>
                    Tu carrito está vacío
                </h2>

                <p>
                    Todavía no agregaste ningún producto.
                </p>

                <a
                    href="../productos/index.php"
                    class="btn-volver">

                    Ver catálogo

                </a>

            </section>


        <?php else: ?>


            <!-- CONTENIDO DEL CARRITO -->

            <section class="carrito-contenido">


                <!-- PRODUCTOS -->

                <div class="carrito-productos">


                    <?php foreach ($_SESSION['carrito'] as $item): ?>


                        <article class="carrito-item">


                            <div class="carrito-item-imagen">

                                <img
                                    src="<?= htmlspecialchars(
                                                $item['imagen'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>"
                                    alt="<?= htmlspecialchars(
                                                $item['nombre'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>">

                            </div>


                            <div class="carrito-item-info">

                                <h2>

                                    <?= htmlspecialchars(
                                        $item['nombre'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                </h2>


                                <p class="item-precio">

                                    $<?= number_format(
                                            $item['precio'],
                                            0,
                                            ',',
                                            '.'
                                        ) ?>

                                    por unidad

                                </p>


                                <!-- CANTIDAD -->

                                <form
                                    method="POST"
                                    action="carrito.php"
                                    class="cantidad-form">

                                    <input
                                        type="hidden"
                                        name="producto_id"
                                        value="<?= $item['id'] ?>">


                                    <label for="cantidad-<?= $item['id'] ?>">
                                        Cantidad
                                    </label>


                                    <input
                                        type="number"
                                        id="cantidad-<?= $item['id'] ?>"
                                        name="cantidad"
                                        value="<?= $item['cantidad'] ?>"
                                        min="1"
                                        max="99">


                                    <button
                                        type="submit"
                                        name="actualizar"
                                        class="btn-actualizar">

                                        Actualizar

                                    </button>

                                </form>


                                <!-- ELIMINAR -->

                                <form
                                    method="POST"
                                    action="carrito.php">

                                    <input
                                        type="hidden"
                                        name="producto_id"
                                        value="<?= $item['id'] ?>">


                                    <button
                                        type="submit"
                                        name="eliminar"
                                        class="btn-eliminar">

                                        Eliminar

                                    </button>

                                </form>

                            </div>


                            <!-- SUBTOTAL -->

                            <div class="carrito-item-subtotal">

                                <span>
                                    Subtotal
                                </span>

                                <strong>

                                    $<?= number_format(
                                            $item['precio'] * $item['cantidad'],
                                            0,
                                            ',',
                                            '.'
                                        ) ?>

                                </strong>

                            </div>


                        </article>


                    <?php endforeach; ?>


                </div>


                <!-- RESUMEN -->

                <aside class="carrito-resumen">


                    <h2>
                        Resumen del pedido
                    </h2>


                    <div class="resumen-linea">

                        <span>
                            Productos
                        </span>

                        <span>
                            <?= $cantidadProductos ?>
                        </span>

                    </div>


                    <div class="resumen-linea total">

                        <span>
                            Total
                        </span>

                        <strong>

                            $<?= number_format(
                                    $total,
                                    0,
                                    ',',
                                    '.'
                                ) ?>

                        </strong>

                    </div>


                    <!-- TODAVÍA NO CONFIRMA EL PEDIDO -->

                    <a
                        href="#"
                        class="btn-continuar">

                        Continuar con el pedido

                    </a>


                    <a
                        href="../productos/catalogo.php"
                        class="btn-seguir-comprando">

                        ← Seguir comprando

                    </a>


                </aside>


            </section>


        <?php endif; ?>


    </main>


    <?php require '../layouts/footer.php'; ?>


</body>

</html>