<?php

$pageTitle = 'Términos y condiciones | Don Diego';
$pageCss = 'politicas.css';

require __DIR__ . '/../layouts/head.php';

?>

<body class="pagina-politica">

    <?php require __DIR__ . '/../layouts/header.php'; ?>

    <main class="politica-container">
        <header class="politica-header">
            <h1>Términos y condiciones</h1>
            <p>Información general sobre el uso de la plataforma de Don Diego.</p>
        </header>

        <article class="politica-contenido">
            <section class="politica-seccion">
                <h2>Uso de la plataforma</h2>
                <p>La plataforma está destinada a clientes registrados que desean consultar productos y realizar pedidos a Don Diego Panadería. Para utilizar las funciones de cuenta, cada usuario debe proporcionar información verdadera y mantener sus datos actualizados.</p>
                <p>La cuenta es personal. El usuario debe cuidar sus credenciales y avisar si detecta un uso no autorizado. También debe utilizar el sitio de forma correcta, sin intentar alterar su funcionamiento ni acceder a información o funciones que no le correspondan.</p>
            </section>

            <section class="politica-seccion">
                <h2>Productos y pedidos</h2>
                <p>Los productos y sus precios pueden cambiar. La realización de un pedido no significa que haya sido aceptado automáticamente: queda pendiente hasta que Don Diego lo revise y confirme.</p>
                <p>Don Diego puede modificar o cancelar un pedido cuando corresponda, por ejemplo, si cambia la disponibilidad de un producto o si es necesario coordinar algún dato del pedido. Cualquier modificación relevante será comunicada por los medios disponibles en la plataforma.</p>
            </section>

            <section class="politica-seccion">
                <h2>Responsabilidad del usuario</h2>
                <p>Al utilizar la plataforma, el usuario se compromete a ingresar datos correctos, revisar la información antes de enviar un pedido y respetar las condiciones que se muestran durante el proceso. El uso indebido del sitio puede impedir el acceso a sus funciones.</p>
            </section>
        </article>
    </main>

    <?php require __DIR__ . '/../layouts/footer.php'; ?>

</body>

</html>