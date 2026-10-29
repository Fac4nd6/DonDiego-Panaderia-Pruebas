<?php

$configuracionPrivada = __DIR__ . '/Brevo.php';
if (is_file($configuracionPrivada)) {
    require_once $configuracionPrivada;
}
require_once __DIR__ . '/../vendor/phpmailer/Exception.php';
require_once __DIR__ . '/../vendor/phpmailer/PHPMailer.php';
require_once __DIR__ . '/../vendor/phpmailer/SMTP.php';

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

function emailConfigValue(string $name, $default = '')
{
    $value = getenv($name);
    if ($value !== false && $value !== '') {
        return $value;
    }

    return defined($name) ? constant($name) : $default;
}

function enviarCorreoTransaccional(
    string $destinatario,
    string $nombreDestinatario,
    string $asunto,
    string $contenidoHTML,
    bool $liberarSesionDuranteEnvio = true
): array {
    if (!filter_var($destinatario, FILTER_VALIDATE_EMAIL)) {
        return [
            'success' => false,
            'temporary' => false,
            'error' => 'La dirección de correo no es válida.'
        ];
    }

    $apiKey = trim((string) emailConfigValue('BREVO_API_KEY'));
    $transport = strtolower(trim((string) emailConfigValue(
        'BREVO_MAIL_TRANSPORT',
        $apiKey !== '' ? 'api' : 'smtp'
    )));

    $sesionActiva = $liberarSesionDuranteEnvio && session_status() === PHP_SESSION_ACTIVE;
    if ($sesionActiva) {
        session_write_close();
    }

    try {
        if ($transport === 'api') {
            return enviarCorreoBrevoApi(
                $apiKey,
                $destinatario,
                $nombreDestinatario,
                $asunto,
                $contenidoHTML
            );
        }

        if ($transport === 'smtp') {
            return enviarCorreoBrevoSmtp(
                $destinatario,
                $nombreDestinatario,
                $asunto,
                $contenidoHTML
            );
        }

        return [
            'success' => false,
            'temporary' => false,
            'error' => 'El servicio de correo no está configurado.'
        ];
    } finally {
        if ($sesionActiva && session_status() === PHP_SESSION_NONE && !session_start()) {
            error_log('Unable to reopen PHP session after email transport.');
        }
    }
}

function enviarCorreoBrevoApi(
    string $apiKey,
    string $destinatario,
    string $nombreDestinatario,
    string $asunto,
    string $contenidoHTML
): array {
    $remitente = trim((string) emailConfigValue('BREVO_SENDER_EMAIL'));
    $nombreRemitente = trim((string) emailConfigValue('BREVO_SENDER_NAME', 'Don Diego'));

    if ($apiKey === '' || !filter_var($remitente, FILTER_VALIDATE_EMAIL)) {
        return [
            'success' => false,
            'temporary' => false,
            'error' => 'El servicio de correo no está configurado.'
        ];
    }

    if (!function_exists('curl_init')) {
        error_log('Brevo API transport unavailable: PHP cURL extension is missing.');

        return [
            'success' => false,
            'temporary' => false,
            'error' => 'El servicio de correo no está disponible.'
        ];
    }

    $payload = json_encode([
        'sender' => [
            'email' => $remitente,
            'name' => $nombreRemitente
        ],
        'to' => [[
            'email' => $destinatario,
            'name' => $nombreDestinatario
        ]],
        'subject' => $asunto,
        'htmlContent' => $contenidoHTML
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    if ($payload === false) {
        return [
            'success' => false,
            'temporary' => false,
            'error' => 'No se pudo preparar el correo.'
        ];
    }

    $curl = curl_init('https://api.brevo.com/v3/smtp/email');
    curl_setopt_array($curl, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_HTTPHEADER => [
            'accept: application/json',
            'api-key: ' . $apiKey,
            'content-type: application/json'
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 4,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2
    ]);

    $respuesta = curl_exec($curl);
    $estadoHttp = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $errorCurl = curl_errno($curl);
    curl_close($curl);

    if ($respuesta === false) {
        error_log('Brevo API transport error: cURL errno ' . $errorCurl . '.');

        return [
            'success' => false,
            'temporary' => true,
            'error' => 'No se pudo enviar el correo. Intentá nuevamente más tarde.'
        ];
    }

    if ($estadoHttp === 201 || $estadoHttp === 202) {
        return ['success' => true, 'temporary' => false];
    }

    $temporal = in_array($estadoHttp, [408, 425, 429], true) || $estadoHttp >= 500;
    $respuestaBrevo = json_decode($respuesta, true);
    $codigoBrevo = is_array($respuestaBrevo) ? ($respuestaBrevo['code'] ?? '') : '';
    if (!is_string($codigoBrevo) || !preg_match('/\A[a-zA-Z0-9_.-]{1,64}\z/', $codigoBrevo)) {
        $codigoBrevo = '';
    }
    $detalleBrevo = $codigoBrevo !== '' ? ' code=' . $codigoBrevo : '';
    error_log('Brevo API rejected email: HTTP ' . $estadoHttp . $detalleBrevo . '.');

    return [
        'success' => false,
        'temporary' => $temporal,
        'error' => 'No se pudo enviar el correo. Intentá nuevamente más tarde.'
    ];
}

function enviarCorreoBrevoSmtp(
    string $destinatario,
    string $nombreDestinatario,
    string $asunto,
    string $contenidoHTML
): array {
    $host = trim((string) emailConfigValue('BREVO_SMTP_HOST'));
    $usuario = trim((string) emailConfigValue('BREVO_SMTP_USERNAME'));
    $clave = (string) emailConfigValue('BREVO_SMTP_PASSWORD');
    $remitente = trim((string) emailConfigValue('BREVO_SENDER_EMAIL'));
    $nombreRemitente = trim((string) emailConfigValue('BREVO_SENDER_NAME', 'Don Diego'));
    $puerto = (int) emailConfigValue('BREVO_SMTP_PORT', 587);

    if (
        $host === '' ||
        $usuario === '' ||
        $clave === '' ||
        !filter_var($remitente, FILTER_VALIDATE_EMAIL)
    ) {
        return [
            'success' => false,
            'temporary' => false,
            'error' => 'El servicio de correo no está configurado.'
        ];
    }

    $mailer = new PHPMailer(true);

    try {
        $mailer->isSMTP();
        $mailer->Host = $host;
        $mailer->SMTPAuth = true;
        $mailer->Username = $usuario;
        $mailer->Password = $clave;
        $mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mailer->Port = $puerto;
        $mailer->Timeout = 10;
        $mailer->SMTPDebug = 0;
        $mailer->CharSet = 'UTF-8';
        $mailer->setFrom($remitente, $nombreRemitente);
        $mailer->addAddress($destinatario, $nombreDestinatario);
        $mailer->isHTML(true);
        $mailer->Subject = $asunto;
        $mailer->Body = $contenidoHTML;
        $mailer->send();

        return ['success' => true, 'temporary' => false];
    } catch (Exception $exception) {
        error_log('Transactional SMTP send failed.');

        return [
            'success' => false,
            'temporary' => null,
            'error' => 'No se pudo enviar el correo. Intentá nuevamente más tarde.'
        ];
    }
}