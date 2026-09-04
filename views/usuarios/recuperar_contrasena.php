<?php

// Genera y envía el código de recuperación por correo.
$pageCss = 'login.css';

require '../../config/Database.php';
require '../../config/Csrf.php';
require '../../config/Brevo.php';
require '../../config/Url.php';

if (session_status() === PHP_SESSION_NONE) {
    require_once '../../config/Session.php';
    iniciar_sesion_segura();
}

$mensaje = '';
$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificar_csrf();

    unset($_SESSION['recuperacion_verificada_id']);
    $email = strtolower(trim($_POST['email'] ?? ''));

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Ingresá un correo electrónico válido.';
    } else {
        $stmt = $conn->prepare(
            'SELECT id, nombre_completo FROM usuarios
             WHERE email = ? AND email_verificado = 1 LIMIT 1'
        );

        if (!$stmt) {
            $error = 'Ocurrió un error. Intentá nuevamente más tarde.';
        } else {
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $usuario = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($usuario) {
                $codigo = (string) random_int(100000, 999999);
                $codigoHash = password_hash($codigo, PASSWORD_DEFAULT);
                $expira = date('Y-m-d H:i:s', time() + 900);

                $stmt = $conn->prepare(
                    'UPDATE usuarios SET codigo_recuperacion = ?, codigo_recuperacion_expira = ?
                     WHERE id = ?'
                );

                if ($stmt) {
                    $stmt->bind_param('ssi', $codigoHash, $expira, $usuario['id']);
                    $guardado = $stmt->execute();
                    $stmt->close();
                } else {
                    $guardado = false;
                }

                if ($guardado) {
                    $nombreUsuario = htmlspecialchars($usuario['nombre_completo'], ENT_QUOTES, 'UTF-8');
                    $contenido = '
                        <div style="margin:0;padding:32px 16px;background-color:#f7efe4;font-family:Arial,sans-serif;color:#2b2b2b;">
                            <div style="max-width:520px;margin:0 auto;background-color:#fffdf7;border:1px solid #eadccc;border-radius:12px;overflow:hidden;">
                                <div style="padding:24px;text-align:center;background-color:#b31b21;color:#ffffff;">
                                    <h1 style="margin:0;font-size:24px;line-height:1.3;">Don Diego</h1>
                                    <p style="margin:6px 0 0;font-size:13px;">Panadería y Confitería</p>
                                </div>
                                <div style="padding:32px 28px;text-align:center;">
                                    <h2 style="margin:0 0 18px;color:#2b2b2b;font-size:21px;">Recuperación de contraseña</h2>
                                    <p style="margin:0 0 12px;font-size:15px;line-height:1.6;text-align:left;">Hola, ' . $nombreUsuario . '.</p>
                                    <p style="margin:0 0 22px;font-size:15px;line-height:1.6;text-align:left;">Recibimos una solicitud para cambiar tu contraseña. Ingresá este código en la página:</p>
                                    <div style="display:inline-block;padding:14px 24px;border:2px solid #b31b21;border-radius:8px;background-color:#fff5f2;color:#b31b21;font-size:30px;font-weight:bold;letter-spacing:8px;">' . $codigo . '</div>
                                    <p style="margin:22px 0 0;font-size:13px;line-height:1.6;color:#6f6259;">Este código será válido durante 15 minutos.</p>
                                </div>
                                <div style="padding:16px 28px;border-top:1px solid #eadccc;background-color:#fffaf0;color:#6f6259;font-size:12px;line-height:1.5;text-align:center;">Si no solicitaste este cambio, podés ignorar este correo.</div>
                            </div>
                        </div>';

                    $resultadoCorreo = enviarCorreoBrevo(
                        $email,
                        $usuario['nombre_completo'],
                        'Restablecé tu contraseña - Don Diego',
                        $contenido
                    );

                    if (!$resultadoCorreo['success']) {
                        $error = 'No se pudo enviar el correo. Intentá nuevamente más tarde.';
                    } else {
                        $_SESSION['recuperacion_email'] = $email;
                        header('Location: ' . url('/restablecer-contrasena?enviado=1'));
                        exit;
                    }
                } else {
                    $error = 'Ocurrió un error. Intentá nuevamente más tarde.';
                }
            } else {
                $mensaje = 'Si existe una cuenta verificada con ese correo, recibirás un código para restablecer tu contraseña.';
            }
        }
    }
}

require '../layouts/head.php';
?>

<body>
    <main class="background-container">
        <section class="login-card" aria-labelledby="recovery-title">
            <header class="logo-container">
                <div class="logo-badge">
                    <img src="<?= url('/public/img/logo.avif') ?>" alt="Logo de Don Diego">
                </div>
            </header>

            <div class="login-content">
                <header class="form-header">
                    <h2 id="recovery-title" class="form-title">Recuperar contraseña</h2>
                </header>

                <p>Ingresá tu correo y te enviaremos un código para crear una nueva contraseña.</p>

                <?php if ($error): ?>
                    <aside class="error-msg" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></aside>
                <?php endif; ?>

                <?php if ($mensaje): ?>
                    <aside class="success-msg" role="status"><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></aside>
                <?php endif; ?>

                <form action="<?= url('/recuperar-contrasena') ?>" method="POST" class="login-form">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">

                    <div class="input-group">
                        <label for="email">Correo electrónico</label>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>" autocomplete="email" required>
                    </div>

                    <button type="submit" class="btn-submit">Enviar enlace</button>
                </form>

                <footer class="form-footer">
                    <p><a href="<?= url('/login') ?>">Volver a iniciar sesión</a></p>
                </footer>
            </div>
        </section>
    </main>
</body>
</html>
