<?php

$pageTitle = 'Política de pedidos | Don Diego';
$pageCss = 'politicas.css';

require __DIR__ . '/../layouts/head.php';

?>

<body class="pagina-politica">

    <?php require __DIR__ . '/../layouts/header.php'; ?>

    <main class="politica-container">
        <header class="politica-header">
            <h1>Política de pedidos</h1>
            <p>Información sobre el proceso y los estados de un pedido.</p>
        </header>

        <article class="politica-contenido">
            <section class="politica-seccion">
                <h2>Cómo realizar un pedido</h2>
                <p>El usuario selecciona productos del catálogo, los agrega al carrito, revisa las cantidades y completa los datos solicitados para la recepción. Al enviarlo, el pedido queda pendiente y debe ser confirmado por Don Diego antes de considerarse aceptado.</p>
                <p>La disponibilidad final depende de Don Diego. Si un producto no tiene stock o no puede prepararse, el pedido puede requerir una modificación o ser cancelado, según lo que se coordine con el usuario.</p>
            </section>

            <section class="politica-seccion">
                <h2>Estados del pedido</h2>
                <p>El pedido puede pasar por los siguientes estados: pendiente, confirmado, en preparación, listo, entregado o cancelado. El estado informa en qué etapa se encuentra la gestión y puede cambiar cuando el comercio lo actualiza.</p>
            </section>

            <section class="politica-seccion">
                <h2>Repetir, modificar o cancelar</h2>
                <p>La opción “Repetir pedido” permite volver a cargar en el carrito los productos de un pedido anterior. Antes de enviarlo, el usuario debe revisar cantidades, precios y disponibilidad, ya que pueden haber cambiado.</p>
                <p>Las modificaciones y cancelaciones dependen del estado del pedido y de la coordinación con Don Diego. Además, pueden existir límites para mantener pedidos pendientes; cuando se alcanza uno, será necesario esperar a que un pedido avance o sea cancelado antes de realizar otro.</p>
            </section>
        </article>
    </main>

    <?php require __DIR__ . '/../layouts/footer.php'; ?>

</body>

</html>