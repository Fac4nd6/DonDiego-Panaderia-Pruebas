<?php

if (!function_exists('url')) {
    function url($path = '')
    {
        $documentRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
        $projectRoot = realpath(__DIR__ . '/..');
        $basePath = '';

        if ($documentRoot && $projectRoot && strpos($projectRoot, $documentRoot) === 0) {
            $basePath = str_replace(DIRECTORY_SEPARATOR, '/', substr($projectRoot, strlen($documentRoot)));
        }

        $path = '/' . ltrim($path, '/');

        return rtrim($basePath, '/') . ($path === '/' ? '/' : $path);
    }
}

if (!function_exists('url_absoluta')) {
    function url_absoluta($path = '')
    {
        $protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

        return $protocolo . '://' . $host . url($path);
    }
}
