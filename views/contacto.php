<?php

$pageCss = 'contacto.css';

require_once __DIR__ . '/../config/Session.php';
iniciar_sesion_segura();
require_once __DIR__ . '/../config/Csrf.php';
require_once __DIR__ . '/../config/whatsapp.php';

$error = '';
$nombre = '';
$asunto = '';
$texto = '';
$mensajeInicial = 'Hola Don Diego, quisiera realizar una consulta.';
$whatsappUrl = crearUrlWhatsApp($mensajeInicial);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificar_csrf();

    $nombre = trim($_POST['nombre'] ?? '');
    $asunto = trim($_POST['asunto'] ?? '');
    $texto = trim($_POST['mensaje'] ?? '');

    if ($nombre === '' || $asunto === '' || $texto === '') {
        $error = 'Completá todos los campos.';
    } elseif (strlen($nombre) > 150 || strlen($asunto) > 150 || strlen($texto) > 2000) {
        $error = 'Uno de los campos supera la longitud permitida.';
    } else {
        // El mensaje solo se genera después de validar los campos y no se almacena.
        $mensajeWhatsApp = "Hola Don Diego.\n\n"
            . 'Nombre: ' . $nombre . "\n"
            . 'Asunto: ' . $asunto . "\n\n"
            . "Mensaje:\n"
            . $texto;
        $whatsappUrl = crearUrlWhatsApp($mensajeWhatsApp);

        if ($whatsappUrl !== null) {
            header('Location: ' . $whatsappUrl);
            exit;
        }

        $error = 'No se pudo preparar el enlace de WhatsApp.';
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
                <p>Podés consultarnos por productos, pedidos, disponibilidad, entregas y cualquier otra consulta relacionada con la panadería.</p>

                <a
                    href="<?= htmlspecialchars((string) $whatsappUrl, ENT_QUOTES, 'UTF-8') ?>"
                    class="contacto-whatsapp"
                    target="_blank"
                    rel="noopener"
                >
                    <i class="fa-brands fa-whatsapp"></i>
                    Contactanos por WhatsApp
                </a>
            </div>

            <section class="contacto-formulario" aria-labelledby="titulo-formulario-contacto">
                <h2 id="titulo-formulario-contacto">Escribinos</h2>

                <?php if ($error !== ''): ?>
                    <div class="contacto-mensaje error" role="alert">
                        <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>

                <form method="POST" target="_blank">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">

                    <label for="nombre">Nombre</label>
                    <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') ?>" maxlength="150" required>

                    <label for="asunto">Motivo o asunto</label>
                    <input type="text" id="asunto" name="asunto" value="<?= htmlspecialchars($asunto, ENT_QUOTES, 'UTF-8') ?>" maxlength="150" required>

                    <label for="mensaje">Mensaje</label>
                    <textarea id="mensaje" name="mensaje" rows="6" maxlength="2000" required><?= htmlspecialchars($texto, ENT_QUOTES, 'UTF-8') ?></textarea>

                    <button type="submit">
                        <i class="fa-brands fa-whatsapp"></i>
                        Enviar por WhatsApp
                    </button>
                </form>
            </section>
        </section>
    </main>

    <?php require __DIR__ . '/layouts/footer.php'; ?>

</body>

</html>
