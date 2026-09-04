<?php

// Valida el código y permite establecer una nueva contraseña.
$pageCss = 'login.css';

require '../../config/Database.php';
require '../../config/Csrf.php';
require '../../config/Url.php';

if (session_status() === PHP_SESSION_NONE) {
    require_once '../../config/Session.php';
    iniciar_sesion_segura();
}

$error = '';
$mensaje = isset($_GET['enviado'])
    ? 'Revisá tu correo e ingresá el código recibido.'
    : '';
$email = $_SESSION['recuperacion_email'] ?? '';
$codigo = trim($_POST['codigo'] ?? '');
$usuarioVerificadoId = (int) ($_SESSION['recuperacion_verificada_id'] ?? 0);

function obtenerUsuarioPorCodigo($conn, $email, $codigo)
{
    if (!preg_match('/^\d{6}$/', $codigo)) {
        return null;
    }

    $stmt = $conn->prepare(
        'SELECT id, codigo_recuperacion FROM usuarios
         WHERE email = ?
         AND codigo_recuperacion_expira IS NOT NULL
         AND codigo_recuperacion_expira >= NOW()
         AND email_verificado = 1
         LIMIT 1'
    );

    if (!$stmt) {
        return null;
    }

    $stmt->bind_param('s', $email);
    $stmt->execute();
    $usuario = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return $usuario
        && is_string($usuario['codigo_recuperacion'] ?? null)
        && password_verify($codigo, $usuario['codigo_recuperacion'])
        ? $usuario
        : null;
}

function obtenerUsuarioVerificado($conn, $id, $email)
{
    $stmt = $conn->prepare(
        'SELECT id FROM usuarios
         WHERE id = ?
         AND email = ?
         AND codigo_recuperacion_expira IS NOT NULL
         AND codigo_recuperacion_expira >= NOW()
         AND email_verificado = 1
         LIMIT 1'
    );

    if (!$stmt) {
        return null;
    }

    $stmt->bind_param('is', $id, $email);
    $stmt->execute();
    $usuario = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return $usuario;
}

$usuario = $usuarioVerificadoId > 0
    ? obtenerUsuarioVerificado($conn, $usuarioVerificadoId, $email)
    : null;
$mostrarCambio = $usuario !== null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificar_csrf();

    if (($_POST['accion'] ?? '') === 'verificar_codigo') {
        $usuario = obtenerUsuarioPorCodigo($conn, $email, $codigo);

        if (!$usuario) {
            $error = 'El código no es válido o ya expiró.';
        } else {
            $_SESSION['recuperacion_verificada_id'] = (int) $usuario['id'];
            header('Location: ' . url('/restablecer-contrasena?verificado=1'));
            exit;
        }
    } else {
        $password = $_POST['password'] ?? '';
        $confirmacion = $_POST['password_confirmacion'] ?? '';
        $usuario = obtenerUsuarioVerificado($conn, $usuarioVerificadoId, $email);

        if (!$usuario) {
            unset($_SESSION['recuperacion_verificada_id']);
            $mostrarCambio = false;
            $error = 'El código no es válido o ya expiró. Solicitá uno nuevo.';
        } elseif (!$mostrarCambio) {
            $error = 'Primero tenés que validar el código recibido.';
        } elseif (strlen($password) < 8) {
            $error = 'La contraseña debe tener al menos 8 caracteres.';
        } elseif (strlen($password) > 72) {
            $error = 'La contraseña no puede superar los 72 caracteres.';
        } elseif (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
            $error = 'La contraseña debe contener una mayúscula, una minúscula y un número.';
        } elseif ($password !== $confirmacion) {
            $error = 'Las contraseñas no coinciden.';
        } else {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare(
                'UPDATE usuarios SET password = ?, codigo_recuperacion = NULL, codigo_recuperacion_expira = NULL
                 WHERE id = ?'
            );

            if (!$stmt) {
                $error = 'Ocurrió un error. Intentá nuevamente más tarde.';
            } else {
                $stmt->bind_param('si', $passwordHash, $usuarioVerificadoId);
                $actualizado = $stmt->execute();
                $stmt->close();

                if ($actualizado) {
                    unset($_SESSION['recuperacion_email'], $_SESSION['recuperacion_verificada_id']);
                    header('Location: ' . url('/login?restablecida=1'));
                    exit;
                }

                $error = 'Ocurrió un error. Intentá nuevamente más tarde.';
            }
        }
    }
}

require '../layouts/head.php';
?>

<body>
    <main class="background-container">
        <section class="login-card" aria-labelledby="reset-title">
            <header class="logo-container">
                <div class="logo-badge">
                    <img src="<?= url('/public/img/logo.avif') ?>" alt="Logo de Don Diego">
                </div>
            </header>

            <div class="login-content">
                <header class="form-header">
                    <h2 id="reset-title" class="form-title">
                        <?= $mostrarCambio ? 'Nueva contraseña' : 'Código de recuperación' ?>
                    </h2>
                </header>

                <?php if ($mensaje): ?>
                    <aside class="success-msg" role="status"><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></aside>
                <?php endif; ?>

                <?php if ($error): ?>
                    <aside class="error-msg" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></aside>
                <?php endif; ?>

                <?php if ($email && !$mostrarCambio): ?>
                    <form action="<?= url('/restablecer-contrasena') ?>" method="POST" class="login-form">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="accion" value="verificar_codigo">

                        <div class="input-group">
                            <label for="codigo">Código de recuperación</label>
                            <input type="text" id="codigo" name="codigo" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code" value="<?= htmlspecialchars($codigo, ENT_QUOTES, 'UTF-8') ?>" required>
                        </div>

                        <button type="submit" class="btn-submit">Verificar código</button>
                    </form>
                <?php elseif ($mostrarCambio): ?>
                    <form action="<?= url('/restablecer-contrasena') ?>" method="POST" class="login-form">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">

                        <div class="input-group">
                            <label for="password">Nueva contraseña</label>
                            <div class="password-wrapper">
                                <input type="password" id="password" name="password" minlength="8" maxlength="72" autocomplete="new-password" required>
                                <button type="button" class="toggle-password" data-target="password" aria-label="Mostrar contraseña" aria-pressed="false">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="input-group">
                            <label for="password_confirmacion">Confirmar contraseña</label>
                            <div class="password-wrapper">
                                <input type="password" id="password_confirmacion" name="password_confirmacion" minlength="8" maxlength="72" autocomplete="new-password" required>
                                <button type="button" class="toggle-password" data-target="password_confirmacion" aria-label="Mostrar contraseña" aria-pressed="false">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="btn-submit">Guardar contraseña</button>
                    </form>
                <?php else: ?>
                    <aside class="error-msg" role="alert">Solicitá primero un código de recuperación.</aside>
                <?php endif; ?>

                <footer class="form-footer">
                    <p><a href="<?= url('/login') ?>">Volver a iniciar sesión</a></p>
                </footer>
            </div>
        </section>
    </main>

    <script src="<?= url('/public/js/auth.js') ?>"></script>
</body>
</html>
