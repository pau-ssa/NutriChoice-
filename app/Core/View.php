<?php
declare(strict_types=1);
namespace App\Core;

final class View
{
    public static function render(string $__plantilla, array $__vars = [], string $__titulo = 'Inicio'): void
    {
        if (!preg_match('~\A[a-z0-9_/-]+\z~', $__plantilla)) {
            throw new \InvalidArgumentException('Vista inválida.');
        }
        $__base = dirname(__DIR__) . '/Views/';
        $__ruta = $__base . $__plantilla . '.php';
        if (!is_file($__ruta)) {
            throw new \LogicException('Vista no implementada: ' . $__plantilla);
        }
        // Sección activa del menú: por defecto, la carpeta de la vista.
        $seccion = $__vars['seccion'] ?? explode('/', $__plantilla)[0];
        $titulo = $__titulo;
        unset($__vars['seccion'], $__vars['titulo']);
        extract($__vars, EXTR_SKIP);
        require $__base . 'layouts/header.php';
        require $__ruta;
        require $__base . 'layouts/footer.php';
    }
}