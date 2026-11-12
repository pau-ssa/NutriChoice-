<?php
declare(strict_types=1);
namespace App\Core;

use PDO;

/** Conexión PDO única por solicitud a la base `nutrichoice`. */
final class Database
{
    private static ?PDO $conexion = null;

    public static function conectar(): PDO
    {
        $cfg = require dirname(__DIR__, 2) . '/config/database.php';
        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            $cfg['host'], $cfg['port'], $cfg['dbname']);
        return new PDO($dsn, $cfg['user'], $cfg['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }

    /** Reutiliza la misma conexión para todos los modelos de la solicitud. */
    public static function obtener(): PDO
    {
        return self::$conexion ??= self::conectar();
    }
}