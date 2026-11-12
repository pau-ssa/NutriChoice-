<?php
declare(strict_types=1);
namespace App\Core;

final class HttpException extends \RuntimeException
{
    public const TITULOS = [
        400 => 'Solicitud inválida',
        401 => 'Sesión no válida',
        403 => 'Acceso denegado',
        404 => 'Página no encontrada',
        405 => 'Método no permitido',
        409 => 'Operación en conflicto',
        422 => 'Datos inválidos',
    ];

    public function __construct(int $estado, string $mensaje)
    {
        parent::__construct($mensaje, $estado);
    }
}
