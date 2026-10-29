<?php

function solicitudSuperaLimite(
    mysqli $conexion,
    string $categoria,
    string $sujeto,
    int $maximo,
    int $ventanaSegundos
): bool {
    if (
        !preg_match('/^[a-z0-9_]{1,40}$/', $categoria) ||
        $sujeto === '' ||
        $maximo < 1 ||
        $ventanaSegundos < 1
    ) {
        return true;
    }

    $hashSujeto = hash('sha256', $sujeto);

    try {
        if (mt_rand(1, 100) === 1) {
            $conexion->query(
                'DELETE FROM rate_limit_buckets WHERE expires_at < UTC_TIMESTAMP() LIMIT 200'
            );
        }

        $stmt = $conexion->prepare(
            'INSERT INTO rate_limit_buckets
                (bucket, subject_hash, window_started_at, hits, expires_at)
             VALUES (?, ?, UTC_TIMESTAMP(), 1, DATE_ADD(UTC_TIMESTAMP(), INTERVAL ? SECOND))
             ON DUPLICATE KEY UPDATE
                hits = IF(
                    window_started_at <= DATE_SUB(UTC_TIMESTAMP(), INTERVAL ? SECOND),
                    1,
                    hits + 1
                ),
                window_started_at = IF(
                    window_started_at <= DATE_SUB(UTC_TIMESTAMP(), INTERVAL ? SECOND),
                    UTC_TIMESTAMP(),
                    window_started_at
                ),
                expires_at = DATE_ADD(UTC_TIMESTAMP(), INTERVAL ? SECOND)'
        );

        if (!$stmt) {
            throw new RuntimeException('Rate limit storage unavailable.');
        }

        $stmt->bind_param(
            'ssiiii',
            $categoria,
            $hashSujeto,
            $ventanaSegundos,
            $ventanaSegundos,
            $ventanaSegundos,
            $ventanaSegundos
        );

        if (!$stmt->execute()) {
            $stmt->close();
            throw new RuntimeException('Rate limit storage unavailable.');
        }
        $stmt->close();

        $stmt = $conexion->prepare(
            'SELECT hits FROM rate_limit_buckets WHERE bucket = ? AND subject_hash = ? LIMIT 1'
        );

        if (!$stmt) {
            throw new RuntimeException('Rate limit storage unavailable.');
        }

        $stmt->bind_param('ss', $categoria, $hashSujeto);
        if (!$stmt->execute()) {
            $stmt->close();
            throw new RuntimeException('Rate limit storage unavailable.');
        }

        $resultado = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return !$resultado || (int) $resultado['hits'] > $maximo;
    } catch (Throwable $exception) {
        error_log('Shared rate limit storage unavailable.');

        return true;
    }
}