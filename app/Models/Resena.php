<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\Database;
use App\Core\Procedimientos;
use PDO;

/** Calificaciones y comentarios sobre productos, recetas y restaurantes. */
final class Resena
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::obtener();
    }

    public function de(string $tipo, int $refId): array
    {
        return Procedimientos::filas($this->db, 'sp_resena_consultar', [$tipo, $refId]);
    }

    /** Promedio y total calculados a partir de las reseñas. */
    public static function resumen(array $resenas): array
    {
        $total = count($resenas);
        return ['total' => $total,
            'promedio' => $total ? round(array_sum(array_column($resenas, 'puntaje')) / $total, 1) : 0.0];
    }

    public function crear(array $d): int
    {
        return Procedimientos::id($this->db, 'sp_resena_guardar', [$d['tipo'], (int) $d['ref_id'], (int) $d['usuario_id'],
            (int) $d['puntaje'], $d['comentario']]);
    }
}
