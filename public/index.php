<?php
declare(strict_types=1);

ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

// Front controller: todas las solicitudes pasan por este archivo.
ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.cookie_secure', (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? '1' : '0');
session_start();
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: same-origin');

spl_autoload_register(function (string $nombre): void {
    if (!str_starts_with($nombre, 'App\\')) return;
    $ruta = dirname(__DIR__) . '/app/' . str_replace('\\', '/', substr($nombre, 4)) . '.php';
    if (is_file($ruta)) require $ruta;
});
require dirname(__DIR__) . '/app/Core/helpers.php';

ob_start();
try {
    (new \App\Core\Router())->ejecutar();
} catch (\App\Core\HttpException $ex) {
    $codigo = $ex->getCode();
    $codigo = ($codigo >= 400 && $codigo <= 599) ? $codigo : 400;
    $encabezado = \App\Core\HttpException::TITULOS[$codigo] ?? 'Solicitud no completada';
    ob_clean();
    http_response_code($codigo);
    \App\Core\View::render('layouts/error', [
        'encabezado' => $encabezado,
        'mensaje' => $ex->getMessage(),
    ], $encabezado);
} catch (\PDOException $ex) {
    error_log((string) $ex);
    ob_clean();
    http_response_code(503);
    \App\Core\View::render('layouts/error', [
        'encabezado' => 'Servicio temporalmente no disponible',
        'mensaje' => 'No se pudo acceder a la base de datos. Intente de nuevo en unos minutos.',
    ], 'Error');
} catch (\Throwable $ex) {
    error_log((string) $ex);
    ob_clean();
    http_response_code(500);
    \App\Core\View::render('layouts/error', [
        'encabezado' => 'Error interno',
        'mensaje' => 'Ocurrió un error inesperado. Intente de nuevo más tarde.',
    ], 'Error');
}
