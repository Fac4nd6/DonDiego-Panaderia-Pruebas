<?php

$pageCss = "pedido-limite.css";

require __DIR__ . '/../layouts/head.php';

?>

<body>

    <main class="limite-container">

        <section class="limite-card">

            <div class="limite-icono">
                <i class="fa-solid fa-box"></i>
            </div>

            <h1>
                Límite de pedidos alcanzado
            </h1>

            <p>
                Ya tenés <strong>3 pedidos pendientes</strong>.
            </p>

            <p class="limite-descripcion">
                Esperá a que alguno de tus pedidos sea confirmado,
                entregado o cancelado antes de realizar uno nuevo.
            </p>

            <div class="limite-acciones">

                <a
                    href="/DonDiego-Panaderia-Pruebas/controllers/PedidoController.php?accion=listar"
                    class="btn-pedidos">

                    Ver mis pedidos

                    <i class="fa-solid fa-arrow-right"></i>

                </a>

                <a
                    href="/DonDiego-Panaderia-Pruebas/controllers/CarritoController.php?accion=ver"
                    class="btn-carrito">

                    Volver al carrito

                </a>

            </div>

        </section>

    </main>


    <?php require __DIR__ . '/../layouts/footer.php'; ?>

</body>

</html>