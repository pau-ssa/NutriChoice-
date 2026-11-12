<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\Database;
use App\Core\Procedimientos;
use PDO;

/** Lista maestra de ingredientes con las restricciones que incumplen. */
final class Ingrediente
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::obtener();
    }

    private static function normalizar(array $f): array
    {
        $f['contiene'] = lista_csv($f['contiene']);
        $f['disponible_cr'] = (bool) $f['disponible_cr'];
        return $f;
    }

    public function listar(): array
    {
        return array_map([self::class, 'normalizar'], Procedimientos::filas($this->db, 'sp_ingrediente_consultar', [0]));
    }

    public function porId(): array
    {
        return array_column($this->listar(), null, 'id');
    }

    public function obtener(int $id): ?array
    {
        $f = Procedimientos::uno($this->db, 'sp_ingrediente_consultar', [$id]);
        return $f ? self::normalizar($f) : null;
    }

    public function guardar(int $id, array $d): int
    {
        return Procedimientos::id($this->db, 'sp_ingrediente_guardar', [$id, $d['nombre'], $d['disponible_cr'] ? 1 : 0, implode(',', $d['contiene'])]);
    }

    /** Borra el registro. Lanza PDOException 23000 si está en uso (llave foránea RESTRICT). */
    public function eliminar(int $id): void
    {
        Procedimientos::uno($this->db, 'sp_eliminar', ['ingredientes', $id]);
    }

    // ---------- Primeros pasos: sustituciones comunes de ingredientes ----------

    public function sustituciones(string $grupo = ''): array
    {
        return array_map(fn($s) => $s + ['restricciones' => lista_csv($s['aplica_a'])],
            Procedimientos::filas($this->db, 'sp_sustitucion_consultar', [$grupo]));
    }

    public function gruposSustitucion(): array
    {
        return array_values(array_unique(array_column($this->sustituciones(), 'grupo')));
    }
}
