<?php

$pageCss = 'contacto.css';

require_once __DIR__ . '/../config/Session.php';
iniciar_sesion_segura();
require_once __DIR__ . '/../config/Csrf.php';

$error = '';
$mensaje = '';
$nombre = '';
$email = '';
$texto = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificar_csrf();

    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $texto = trim($_POST['mensaje'] ?? '');

    if ($nombre === '' || $email === '' || $texto === '') {
        $error = 'Completá todos los campos.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Ingresá un correo electrónico válido.';
    } elseif (strlen($nombre) > 150 || strlen($email) > 150 || strlen($texto) > 2000) {
        $error = 'Uno de los campos supera la longitud permitida.';
    } else {
        $mensaje = 'El formulario fue validado correctamente. El envío de mensajes estará disponible próximamente.';
        $nombre = '';
        $email = '';
        $texto = '';
    }
}

require __DIR__ . '/layouts/head.php';
?>

<body class="pagina-contacto">

    <?php require __DIR__ . '/layouts/header.php'; ?>

    <main class="contacto-container">
        <header class="contacto-header">
            <h1>Contacto</h1>
            <p>Estamos para atender tus consultas sobre Don Diego Panadería.</p>
        </header>

        <section class="contacto-contenido">
            <div class="contacto-informacion">
                <h2>Don Diego Panadería</h2>
                <p>Teléfono: 473 49 924</p>
                <p>WhatsApp: 095 005 706</p>
                <p>Dirección: Uruguay 1794</p>
            </div>

            <section class="contacto-formulario" aria-labelledby="titulo-formulario-contacto">
                <h2 id="titulo-formulario-contacto">Escribinos</h2>

                <?php if ($error !== ''): ?>
                    <div class="contacto-mensaje error" role="alert">
                        <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>

                <?php if ($mensaje !== ''): ?>
                    <div class="contacto-mensaje success" role="status">
                        <?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">

                    <label for="nombre">Nombre</label>
                    <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') ?>" maxlength="150" required>

                    <label for="email">Correo electrónico</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>" maxlength="150" required>

                    <label for="mensaje">Mensaje</label>
                    <textarea id="mensaje" name="mensaje" rows="6" maxlength="2000" required><?= htmlspecialchars($texto, ENT_QUOTES, 'UTF-8') ?></textarea>

                    <button type="submit">Enviar</button>
                </form>
            </section>
        </section>
    </main>

    <?php require __DIR__ . '/layouts/footer.php'; ?>

</body>

</html>
