<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\Database;
use App\Core\Procedimientos;
use PDO;

/** Supermercados, tiendas y restaurantes. */
final class Establecimiento
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::obtener();
    }

    public const PROVINCIAS = ['San José', 'Alajuela', 'Cartago', 'Heredia', 'Guanacaste', 'Puntarenas', 'Limón'];

    private static function normalizar(array $f): array
    {
        $f['restricciones'] = lista_csv($f['restricciones']);
        $f['opciones'] = $f['opciones'] === '' ? [] : explode("\n", $f['opciones']);
        $f['info_nutricional'] = (bool) $f['info_nutricional'];
        $f['publicado'] = (bool) $f['publicado'];
        return $f;
    }

    /** Parámetros de sp_establecimiento_consultar: id, tipo, texto, provincia, tipo_comida, seguridad, info, requeridas. */
    private function consultar(int $id, string $tipo, array $f = []): array
    {
        return array_map([self::class, 'normalizar'], Procedimientos::filas($this->db, 'sp_establecimiento_consultar', [
            $id, $tipo, $f['q'] ?? '', $f['provincia'] ?? '', $f['tipo_comida'] ?? '', $f['seguridad'] ?? '',
            !empty($f['info_nutricional']) ? 1 : 0, implode(',', $f['restricciones'] ?? []),
        ]));
    }

    /** $tipo: null todos | 'comercio' (supermercados y tiendas) | 'restaurante' */
    public function listar(?string $tipo = null): array
    {
        return $this->consultar(0, $tipo ?? '');
    }

    /** Lugares de compra como id => nombre. */
    public function comercios(): array
    {
        return array_column($this->listar('comercio'), 'nombre', 'id');
    }

    /** Restaurantes que cumplen los filtros; deben atender TODAS las restricciones pedidas. */
    public function buscarRestaurantes(array $f): array
    {
        return $this->consultar(0, 'restaurante', $f);
    }

    public function tiposComida(): array
    {
        $tipos = array_unique(array_filter(array_column($this->listar('restaurante'), 'tipo_comida')));
        sort($tipos);
        return $tipos;
    }

    public function obtener(int $id): ?array
    {
        return $this->consultar($id, '')[0] ?? null;
    }

    /** $publicado = false guarda una propuesta de la comunidad, oculta hasta su aprobación. */
    public function guardar(int $id, array $d, bool $publicado = true): int
    {
        return Procedimientos::id($this->db, 'sp_establecimiento_guardar', [
            $id, $d['nombre'], $d['tipo'], $d['provincia'], $d['direccion'], $d['telefono'] ?? '',
            $d['tipo_comida'] ?? '', $d['seguridad'] ?? '', !empty($d['info_nutricional']) ? 1 : 0,
            $d['horario'] ?? '', $d['descripcion'] ?? '', implode(',', $d['restricciones'] ?? []),
            implode("\n", $d['opciones'] ?? []), $publicado ? 1 : 0,
        ]);
    }

    /** Borra el registro. Lanza PDOException 23000 si está en uso (llave foránea RESTRICT). */
    public function eliminar(int $id): void
    {
        Procedimientos::uno($this->db, 'sp_eliminar', ['establecimientos', $id]);
    }
}
