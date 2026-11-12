<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\Database;
use App\Core\Procedimientos;
use PDO;

/** Catálogo local de productos de supermercado. Valores nutricionales por porción. */
final class Producto
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::obtener();
    }

    private const DECIMALES = ['precio', 'porcion_g', 'calorias', 'carbohidratos', 'azucares', 'fibra', 'proteina', 'grasa', 'sodio'];
    private const SIN_FILTROS = ['q' => '', 'categoria' => 0, 'marca' => '', 'tienda' => 0, 'precio_max' => null,
        'restricciones' => [], 'carb_max' => null, 'azucar_max' => null, 'orden' => 'nombre'];

    private static function normalizar(array $f): array
    {
        $f = con_decimales($f, self::DECIMALES);
        $f['contiene'] = lista_csv($f['contiene']);
        $f['trazas'] = lista_csv($f['trazas']);
        $f['tiendas'] = array_map('intval', lista_csv($f['tiendas']));
        $f['publicado'] = (bool) $f['publicado'];
        return $f;
    }

    private function consultar(int $id, array $f): array
    {
        return array_map([self::class, 'normalizar'], Procedimientos::filas($this->db, 'sp_producto_consultar', [
            $id, $f['q'], $f['categoria'], $f['marca'], $f['tienda'], decimal_o_nulo($f['precio_max']),
            implode(',', $f['restricciones']), decimal_o_nulo($f['carb_max']),
            decimal_o_nulo($f['azucar_max']), $f['orden'],
        ]));
    }

    /**
     * Búsqueda filtrada en MySQL: q, categoria, marca, tienda, precio_max,
     * restricciones (claves a excluir), carb_max y azucar_max (g por porción), orden.
     */
    public function buscar(array $f): array
    {
        return $this->consultar(0, $f + self::SIN_FILTROS);
    }

    public function listar(): array
    {
        return $this->buscar([]);
    }

    public function recientes(int $cantidad = 4): array
    {
        return array_slice($this->buscar(['orden' => 'recientes']), 0, $cantidad);
    }

    public function marcas(): array
    {
        $marcas = array_unique(array_column($this->listar(), 'marca'));
        sort($marcas);
        return $marcas;
    }

    public function obtener(int $id): ?array
    {
        return $this->consultar($id, self::SIN_FILTROS)[0] ?? null;
    }

    /** $publicado = false guarda una propuesta de la comunidad, oculta hasta su aprobación. */
    public function guardar(int $id, array $d, bool $publicado = true): int
    {
        return Procedimientos::id($this->db, 'sp_producto_guardar', [
            $id, $d['nombre'], $d['marca'], (int) $d['categoria_id'], (string) $d['precio'], (string) $d['porcion_g'],
            (string) $d['calorias'], (string) $d['carbohidratos'], (string) $d['azucares'], (string) $d['fibra'],
            (string) $d['proteina'], (string) $d['grasa'], (string) $d['sodio'], $d['ingredientes'],
            implode(',', $d['contiene']), implode(',', $d['trazas']), implode(',', $d['tiendas']), $publicado ? 1 : 0,
        ]);
    }

    /** Borra el registro. Lanza PDOException 23000 si está en uso (llave foránea RESTRICT). */
    public function eliminar(int $id): void
    {
        Procedimientos::uno($this->db, 'sp_eliminar', ['productos', $id]);
    }

    // ---------- Etiquetas visuales calculadas a partir de los datos ----------
    // Umbrales de referencia por 100 g/ml.
    public const AZUCAR_BAJO_100G = 5.0;
    public const CARBOS_BAJO_100G = 10.0;
    public const FIBRA_ALTA_100G = 6.0;

    /** Valor por 100 g a partir del valor por porción. */
    public static function por100(float|int $valor, float|int $porcion): float
    {
        return $porcion > 0 ? round($valor * 100 / $porcion, 1) : 0.0;
    }

    /** @return list<array{texto:string,clase:string}> "Sin gluten", "Bajo en azúcar"... */
    public static function etiquetas(array $p): array
    {
        $e = [];
        $libreDe = fn(string $c) => !in_array($c, $p['contiene'], true) && !in_array($c, $p['trazas'], true);
        if ($libreDe('gluten')) $e[] = ['texto' => 'Sin gluten', 'clase' => ''];
        if ($libreDe('lactosa')) $e[] = ['texto' => 'Sin lactosa', 'clase' => ''];
        if ((float) $p['azucares'] == 0.0) {
            $e[] = ['texto' => 'Sin azúcar', 'clase' => 'etiqueta--nutricional'];
        } elseif (self::por100($p['azucares'], $p['porcion_g']) <= self::AZUCAR_BAJO_100G) {
            $e[] = ['texto' => 'Bajo en azúcar', 'clase' => 'etiqueta--nutricional'];
        }
        if (self::por100($p['carbohidratos'], $p['porcion_g']) <= self::CARBOS_BAJO_100G) {
            $e[] = ['texto' => 'Bajo en carbohidratos', 'clase' => 'etiqueta--nutricional'];
        }
        if (self::por100($p['fibra'], $p['porcion_g']) >= self::FIBRA_ALTA_100G) {
            $e[] = ['texto' => 'Alto en fibra', 'clase' => 'etiqueta--nutricional'];
        }
        return $e;
    }
}
