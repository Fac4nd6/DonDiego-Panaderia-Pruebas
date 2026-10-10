<?php

$pageTitle = 'Política de entregas | Don Diego';
$pageCss = 'politicas.css';

require __DIR__ . '/../layouts/head.php';

?>

<body class="pagina-politica">

    <?php require __DIR__ . '/../layouts/header.php'; ?>

    <main class="politica-container">
        <header class="politica-header">
            <h1>Política de entregas</h1>
            <p>Información general sobre la coordinación de la entrega.</p>
        </header>

        <article class="politica-contenido">
            <section class="politica-seccion">
                <h2>Zona de entrega</h2>
                <p>Actualmente, el sistema permite solicitar entregas dentro del departamento de Salto. La dirección ingresada debe corresponder a esa zona para poder continuar con el pedido.</p>
            </section>

            <section class="politica-seccion">
                <h2>Coordinación</h2>
                <p>Durante el pedido, el usuario proporciona la dirección y los datos necesarios para la recepción. La entrega se coordina a partir de esa información y de la confirmación de Don Diego.</p>
                <p>La disponibilidad final de la entrega depende de Don Diego. Los horarios, costos y demás condiciones que no estén definidos en el sistema se comunicarán o acordarán cuando corresponda.</p>
            </section>

            <section class="politica-seccion">
                <h2>Datos de recepción</h2>
                <p>El usuario debe revisar que la dirección, el número de puerta y las referencias ingresadas sean correctos. Si necesita modificar un dato o coordinar una situación particular, deberá hacerlo antes de que el pedido sea gestionado, sujeto a la confirmación del comercio.</p>
            </section>
        </article>
    </main>

    <?php require __DIR__ . '/../layouts/footer.php'; ?>

</body>

</html>