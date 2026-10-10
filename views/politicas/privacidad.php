<?php

$pageTitle = 'Política de privacidad | Don Diego';
$pageCss = 'politicas.css';

require __DIR__ . '/../layouts/head.php';

?>

<body class="pagina-politica">

    <?php require __DIR__ . '/../layouts/header.php'; ?>

    <main class="politica-container">
        <header class="politica-header">
            <h1>Política de privacidad</h1>
            <p>Cómo se utiliza la información necesaria para brindar el servicio.</p>
        </header>

        <article class="politica-contenido">
            <section class="politica-seccion">
                <h2>Información que se solicita</h2>
                <p>La plataforma puede almacenar datos que el usuario proporciona al registrarse o realizar un pedido, como nombre, teléfono, dirección y la información necesaria para gestionar su cuenta y la entrega solicitada.</p>
            </section>

            <section class="politica-seccion">
                <h2>Para qué se utiliza</h2>
                <p>Estos datos se utilizan para administrar cuentas, recibir y gestionar pedidos, coordinar la entrega y mantener la comunicación relacionada con el servicio. No se describen aquí usos distintos de los que requiere el funcionamiento actual de la plataforma.</p>
            </section>

            <section class="politica-seccion">
                <h2>Protección y derechos</h2>
                <p>El sistema incorpora medidas generales de seguridad, como control de sesiones, protección de formularios y restricciones de acceso según el tipo de usuario. Aun así, el usuario debe proteger sus credenciales y evitar compartirlas.</p>
                <p>El usuario puede solicitar información sobre sus datos, pedir que se corrijan si son incorrectos o consultar su actualización y eliminación cuando corresponda. Las solicitudes se revisarán de acuerdo con la información disponible y las necesidades del servicio.</p>
            </section>
        </article>
    </main>

    <?php require __DIR__ . '/../layouts/footer.php'; ?>

</body>

</html>