<?php
declare(strict_types=1);
namespace App\Core;

use InvalidArgumentException;
use PDO;

/** Punto único de invocación de Procedimientos Almacenados mediante PDO */
final class Procedimientos
{
    /** Prepara y ejecuta CALL sp_nombre(?, ?, ...) con parámetros enlazados. */
    private static function ejecutar(PDO $db, string $nombre, array $parametros): \PDOStatement
    {
        // El nombre del procedimiento no es un parámetro SQL: restringir su forma.
        if (!preg_match('/\Asp_[a-z_]+\z/D', $nombre)) {
            throw new InvalidArgumentException('Procedimiento no permitido.');
        }
        $marcas = implode(',', array_fill(0, count($parametros), '?'));
        $consulta = $db->prepare('CALL ' . $nombre . '(' . $marcas . ')');
        foreach (array_values($parametros) as $posicion => $valor) {
            $tipo = match (true) {
                $valor === null => PDO::PARAM_NULL,
                is_int($valor), is_bool($valor) => PDO::PARAM_INT,
                default => PDO::PARAM_STR,
            };
            $consulta->bindValue($posicion + 1, is_bool($valor) ? (int) $valor : $valor, $tipo);
        }
        $consulta->execute();
        return $consulta;
    }

    /** Filas del primer resultado del procedimiento. */
    public static function filas(PDO $db, string $nombre, array $parametros = []): array
    {
        $consulta = self::ejecutar($db, $nombre, $parametros);
        try {
            return $consulta->fetchAll(PDO::FETCH_ASSOC);
        } finally {
            // Cierra resultados del procedimiento antes de emitir otro CALL en la conexión.
            $consulta->closeCursor();
        }
    }

    /**
     * Todos los resultados de un procedimiento que ejecuta varios SELECT seguidos.
     * Devuelve [[filas del 1.er SELECT], [filas del 2.º SELECT], ...].
     */
    public static function conjuntos(PDO $db, string $nombre, array $parametros = []): array
    {
        $consulta = self::ejecutar($db, $nombre, $parametros);
        $resultados = [];
        try {
            do {
                // MySQL agrega al final un resultado de estado sin columnas: se omite.
                if ($consulta->columnCount() > 0) {
                    $resultados[] = $consulta->fetchAll(PDO::FETCH_ASSOC);
                }
            } while ($consulta->nextRowset());
        } finally {
            $consulta->closeCursor();
        }
        return $resultados;
    }

    public static function uno(PDO $db, string $nombre, array $parametros = []): ?array
    {
        return self::filas($db, $nombre, $parametros)[0] ?? null;
    }

    /** Id devuelto por los procedimientos *_guardar (SELECT ... AS id). */
    public static function id(PDO $db, string $nombre, array $parametros = []): int
    {
        return (int) (self::uno($db, $nombre, $parametros)['id'] ?? 0);
    }
}
