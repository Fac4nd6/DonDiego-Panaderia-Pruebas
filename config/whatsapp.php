<?php

/**
 * Número de WhatsApp del comercio en formato internacional.
 * Reemplazar el placeholder por el número real, sin +, espacios ni símbolos.
 */
define('WHATSAPP_NUMERO', '59892155738');

function crearMensajeWhatsApp(array $pedido, array $detalles): string
{
    $lineas = [
        'Hola Don Diego,',
        '',
        'Realicé el pedido #' . (int) ($pedido['id'] ?? 0) . '.',
        'Cliente: ' . trim((string) ($pedido['nombre_completo'] ?? '')),
        '',
        'Pedido:'
    ];

    foreach ($detalles as $detalle) {
        $cantidad = (int) ($detalle['cantidad'] ?? 0);
        $nombre = trim((string) ($detalle['nombre'] ?? 'Producto'));
        $precio = number_format((float) ($detalle['precio_unitario'] ?? 0), 2, ',', '.');
        $subtotal = number_format((float) ($detalle['subtotal'] ?? 0), 2, ',', '.');
        $lineas[] = '- ' . $cantidad . 'x ' . $nombre . ' - $' . $precio . ' c/u - subtotal: $' . $subtotal;
    }

    $fecha = date('d/m/Y', strtotime((string) ($pedido['fecha_recepcion'] ?? '')));
    $lineas[] = '';
    $lineas[] = 'Total: $' . number_format((float) ($pedido['total'] ?? 0), 2, ',', '.');
    $lineas[] = 'Fecha de recepción: ' . $fecha;
    $lineas[] = 'Franja horaria: ' . trim((string) ($pedido['franja_horaria'] ?? ''));
    $lineas[] = 'Dirección: ' . trim((string) ($pedido['direccion_entrega'] ?? ''));
    $lineas[] = 'Método de pago: Efectivo';

    return implode("\n", $lineas);
}

function crearUrlWhatsApp(string $mensaje): ?string
{
    $numero = WHATSAPP_NUMERO;
    if ($numero === 'WHATSAPP_NUMERO_AQUI' || !preg_match('/^[0-9]+$/', $numero)) {
        return null;
    }

    return 'https://wa.me/' . $numero . '?' . http_build_query(['text' => $mensaje]);
}
