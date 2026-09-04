<?php

// Configuración de ejemplo para PHPMailer con SMTP de Brevo.
// La contraseña debe ser una clave SMTP, no la API Key.
define('BREVO_SMTP_HOST', 'smtp-relay.brevo.com');
define('BREVO_SMTP_PORT', 587);
define('BREVO_SMTP_USERNAME', 'tu-login-smtp');
define('BREVO_SMTP_PASSWORD', 'tu-clave-smtp');
define('BREVO_SENDER_EMAIL', 'tu-correo-verificado@ejemplo.com');
define('BREVO_SENDER_NAME', 'Don Diego');
