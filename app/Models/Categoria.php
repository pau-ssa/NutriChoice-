<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\Database;
use App\Core\Procedimientos;
use PDO;

/** Clasificación de productos y recetas. */
final class Categoria
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::obtener();
    }

    public function listar(?string $tipo = null): array
    {
        return Procedimientos::filas($this->db, 'sp_categoria_consultar', [0, $tipo ?? '']);
    }

    public function nombres(): array
    {
        return array_column($this->listar(), 'nombre', 'id');
    }

    public function obtener(int $id): ?array
    {
        return Procedimientos::uno($this->db, 'sp_categoria_consultar', [$id, '']);
    }

    public function guardar(int $id, array $d): int
    {
        return Procedimientos::id($this->db, 'sp_categoria_guardar', [$id, $d['nombre'], $d['tipo']]);
    }

    /** Borra el registro. Lanza PDOException 23000 si está en uso (llave foránea RESTRICT). */
    public function eliminar(int $id): void
    {
        Procedimientos::uno($this->db, 'sp_eliminar', ['categorias', $id]);
    }
}
