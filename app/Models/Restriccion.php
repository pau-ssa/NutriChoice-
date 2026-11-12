<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\Database;
use App\Core\Procedimientos;
use PDO;

/** Características alimentarias: "sin gluten", "sin lactosa", etc. */
final class Restriccion
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::obtener();
    }

    private static ?array $cache = null;

    public function listar(): array
    {
        return self::$cache ??= Procedimientos::filas($this->db, 'sp_restriccion_consultar', [0]);
    }

    /** Mapa clave => nombre, p. ej. ['gluten' => 'Sin gluten']. */
    public function nombres(): array
    {
        return array_column($this->listar(), 'nombre', 'clave');
    }

    /** Devuelve solo las claves válidas de la lista recibida. */
    public function filtrarValidas(array $claves): array
    {
        return array_values(array_intersect(array_keys($this->nombres()), $claves));
    }

    public function obtener(int $id): ?array
    {
        return Procedimientos::uno($this->db, 'sp_restriccion_consultar', [$id]);
    }

    public function guardar(int $id, array $d): int
    {
        self::$cache = null;
        return Procedimientos::id($this->db, 'sp_restriccion_guardar', [$id, $d['clave'], $d['nombre'], $d['descripcion']]);
    }

    /** Borra el registro. Lanza PDOException 23000 si está en uso (llave foránea RESTRICT). */
    public function eliminar(int $id): void
    {
        Procedimientos::uno($this->db, 'sp_eliminar', ['restricciones', $id]);
    }

    // ---------- Primeros pasos: guía de transición por condición ----------

    /** ['celiaquia' => ['titulo', 'resumen', 'secciones' => [['titulo', 'puntos' => [...]]]]] */
    public function guias(): array
    {
        $guias = [];
        foreach (Procedimientos::filas($this->db, 'sp_guia_consultar') as $f) {
            $guias[$f['clave']]['titulo'] = $f['guia_titulo'];
            $guias[$f['clave']]['resumen'] = $f['resumen'];
            $guias[$f['clave']]['secciones'][$f['seccion_id']]['titulo'] = $f['seccion_titulo'];
            $guias[$f['clave']]['secciones'][$f['seccion_id']]['puntos'][] = $f['texto'];
        }
        foreach ($guias as $clave => $g) $guias[$clave]['secciones'] = array_values($g['secciones']);
        return $guias;
    }

    public function guia(string $clave): ?array
    {
        return $this->guias()[$clave] ?? null;
    }
}
