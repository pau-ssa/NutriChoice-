<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\Database;
use App\Core\Procedimientos;
use PDO;

/**
 * Recetas con ingredientes del catálogo. MySQL calcula los valores por porción y los alérgenos.
 * sp_receta_consultar con un id devuelve 4 resultados: receta, ingredientes, pasos y sustituciones.
 */
final class Receta
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::obtener();
    }

    private const NUTRIENTES = ['calorias', 'carbohidratos', 'azucares', 'fibra', 'proteina', 'grasa'];
    private const SIN_FILTROS = ['q' => '', 'categoria' => 0, 'tiempo_max' => 0,
        'restricciones' => [], 'carb_max' => null, 'azucar_max' => null];

    private static function normalizar(array $f): array
    {
        $f = con_decimales($f, self::NUTRIENTES);
        foreach (self::NUTRIENTES as $n) {
            $f['porcion'][$n] = (float) $f['porcion_' . $n];
            unset($f['porcion_' . $n]);
        }
        $f['contiene'] = lista_csv($f['contiene']);
        $f['publicado'] = (bool) $f['publicado'];
        return $f;
    }

    private static function parametros(int $id, array $f): array
    {
        return [$id, $f['q'], $f['categoria'], $f['tiempo_max'], implode(',', $f['restricciones']),
            decimal_o_nulo($f['carb_max']), decimal_o_nulo($f['azucar_max'])];
    }

    /** Filtros: q, categoria, tiempo_max, restricciones, carb_max y azucar_max por porción. */
    public function buscar(array $f): array
    {
        return array_map([self::class, 'normalizar'],
            Procedimientos::filas($this->db, 'sp_receta_consultar', self::parametros(0, $f + self::SIN_FILTROS)));
    }

    public function listar(): array
    {
        return $this->buscar([]);
    }

    public function recientes(int $cantidad = 3): array
    {
        return array_slice($this->listar(), 0, $cantidad);
    }

    /** Receta completa a partir de los 4 resultados del procedimiento. */
    public function obtener(int $id): ?array
    {
        [$receta, $ingredientes, $pasos, $sustituciones] =
            Procedimientos::conjuntos($this->db, 'sp_receta_consultar', self::parametros($id, self::SIN_FILTROS)) + [[], [], [], []];
        if (!$receta) return null;
        $r = self::normalizar($receta[0]);
        $r['lista'] = [];        // para mostrar: nombre, cantidad y restricciones que incumple
        $r['ingredientes'] = []; // para editar: id del catálogo y cantidad
        foreach ($ingredientes as $ing) {
            $r['lista'][] = ['nombre' => $ing['nombre'], 'cantidad' => $ing['cantidad'], 'contiene' => lista_csv($ing['contiene'])];
            $r['ingredientes'][] = ['ingrediente_id' => (int) $ing['ingrediente_id'], 'cantidad' => $ing['cantidad']];
        }
        $r['pasos'] = array_column($pasos, 'texto');
        $r['sustituciones'] = $sustituciones;
        return $r;
    }

    /** Une valores en una línea separada por tabuladores (sin saltos ni tabuladores internos). */
    private static function linea(array $valores): string
    {
        return implode("\t", array_map(fn($v) => trim(str_replace(["\r", "\n", "\t"], ' ', (string) $v)), $valores));
    }

    /** $publicado = false guarda una propuesta de la comunidad, oculta hasta su aprobación. */
    public function guardar(int $id, array $d, bool $publicado = true): int
    {
        $ingredientes = implode("\n", array_map(fn($l) => self::linea([(int) $l['ingrediente_id'], $l['cantidad']]), $d['ingredientes']));
        $pasos = implode("\n", array_map(fn($p) => self::linea([$p]), $d['pasos']));
        $sustituciones = implode("\n", array_map(fn($s) => self::linea([$s['original'], $s['sustituto'], $s['motivo'] ?? '']), $d['sustituciones']));
        return Procedimientos::id($this->db, 'sp_receta_guardar', [
            $id, $d['titulo'], (int) $d['categoria_id'], $d['descripcion'], (int) $d['tiempo_min'], (int) $d['porciones'],
            $d['dificultad'], (string) $d['calorias'], (string) $d['carbohidratos'], (string) $d['azucares'],
            (string) $d['fibra'], (string) $d['proteina'], (string) $d['grasa'], (int) ($d['usuario_id'] ?? 0),
            $ingredientes, $pasos, $sustituciones, $publicado ? 1 : 0,
        ]);
    }

    /** Borra el registro. Lanza PDOException 23000 si está en uso (llave foránea RESTRICT). */
    public function eliminar(int $id): void
    {
        Procedimientos::uno($this->db, 'sp_eliminar', ['recetas', $id]);
    }

    // ---------- Etiquetas visuales calculadas a partir de los datos (por porción) ----------
    public const AZUCAR_BAJO_PORCION = 5.0;
    public const CARBOS_BAJO_PORCION = 15.0;

    /** @return list<array{texto:string,clase:string}> */
    public static function etiquetas(array $r): array
    {
        $e = [];
        if (!in_array('gluten', $r['contiene'], true)) $e[] = ['texto' => 'Sin gluten', 'clase' => ''];
        if (!in_array('lactosa', $r['contiene'], true)) $e[] = ['texto' => 'Sin lactosa', 'clase' => ''];
        if ($r['porcion']['azucares'] <= self::AZUCAR_BAJO_PORCION) $e[] = ['texto' => 'Bajo en azúcar', 'clase' => 'etiqueta--nutricional'];
        if ($r['porcion']['carbohidratos'] <= self::CARBOS_BAJO_PORCION) $e[] = ['texto' => 'Bajo en carbohidratos', 'clase' => 'etiqueta--nutricional'];
        return $e;
    }
}
