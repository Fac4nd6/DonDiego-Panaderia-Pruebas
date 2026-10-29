<?php

// Configuración de ejemplo para PHPMailer con SMTP de Brevo.
define('BREVO_SMTP_HOST', 'smtp-relay.brevo.com');
define('BREVO_SMTP_PORT', 587);
define('BREVO_SMTP_USERNAME', 'tu-login-smtp');
define('BREVO_SMTP_PASSWORD', 'tu-clave-smtp');
define('BREVO_SENDER_EMAIL', 'tu-correo-verificado@ejemplo.com');
define('BREVO_SENDER_NAME', 'Don Diego');

// Configuración de ejemplo para PHPMailer con Api de Brevo.

define('BREVO_API_KEY', '');
define('BREVO_MAIL_TRANSPORT', 'api');
