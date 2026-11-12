<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\Database;
use App\Core\Procedimientos;
use PDO;

/** Cuentas, perfil alimentario, favoritos y lista de compras. */
final class Usuario
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::obtener();
    }

    // El rol se guarda y se administra; su validación se agregará en el avance final.
    public const ROLES = ['usuario' => 'Usuario', 'colaborador' => 'Colaborador', 'admin' => 'Administrador'];
    public const CONDICIONES = [
        'exclusion' => 'Restricción por exclusión',
        'diabetes' => 'Diabetes',
        'ambas' => 'Ambas',
        'ninguna' => 'Sin condición registrada',
    ];

    private static function normalizar(array $u): array
    {
        $u = con_decimales($u, ['carb_max', 'azucar_max']);
        $u['bloqueado'] = (bool) $u['bloqueado'];
        $u['restricciones'] = lista_csv($u['restricciones']);
        $u['favoritos'] = array_map(function (string $f) {
            [$tipo, $ref] = explode(':', $f);
            return ['tipo' => $tipo, 'ref_id' => (int) $ref];
        }, lista_csv($u['favoritos']));
        return $u;
    }

    public function listar(): array
    {
        return array_map([self::class, 'normalizar'], Procedimientos::filas($this->db, 'sp_usuario_consultar', [0, '']));
    }

    /** Perfil completo: restricciones, rangos, favoritos y lista de compras. */
    public function obtener(int $id): ?array
    {
        $u = Procedimientos::uno($this->db, 'sp_usuario_consultar', [$id, '']);
        if (!$u) return null;
        $u = self::normalizar($u);
        $u['compras'] = array_map(fn($c) => ['hecho' => (bool) $c['hecho']] + $c, $this->compras('listar', $id));
        return $u;
    }

    /** Incluye clave_hash: usar solo para autenticar. */
    public function buscarPorCorreo(string $correo): ?array
    {
        return Procedimientos::uno($this->db, 'sp_usuario_consultar', [0, $correo]);
    }

    /** Inserta (id = 0) o actualiza. $d necesita todos los campos del usuario. */
    private function guardar(int $id, array $d): int
    {
        return Procedimientos::id($this->db, 'sp_usuario_guardar', [
            $id, $d['nombre'], $d['email'], $d['clave_hash'] ?? null, $d['rol'] ?? 'usuario', $d['condicion'],
            decimal_o_nulo($d['carb_max']), decimal_o_nulo($d['azucar_max']),
            !empty($d['bloqueado']) ? 1 : 0, implode(',', $d['restricciones']),
        ]);
    }

    public function crear(array $d): int
    {
        return $this->guardar(0, $d);
    }

    /** Actualiza solo las claves recibidas, conservando el resto de los datos actuales. */
    public function actualizar(int $id, array $cambios): void
    {
        $actual = $this->obtener($id);
        if ($actual) $this->guardar($id, ['clave_hash' => null] + $cambios + $actual);
    }

    // ----- Favoritos -----
    public function esFavorito(?array $u, string $tipo, int $refId): bool
    {
        foreach ($u['favoritos'] ?? [] as $f) {
            if ($f['tipo'] === $tipo && $f['ref_id'] === $refId) return true;
        }
        return false;
    }

    /** Agrega o quita un favorito; devuelve true si quedó agregado. */
    public function alternarFavorito(int $id, string $tipo, int $refId): bool
    {
        return (bool) (Procedimientos::uno($this->db, 'sp_favorito_alternar', [$id, $tipo, $refId])['agregado'] ?? false);
    }

    // ----- Lista de compras (un solo procedimiento con acciones) -----
    private function compras(string $accion, int $usuario, int $compraId = 0, string $texto = '', string $origen = ''): array
    {
        return Procedimientos::filas($this->db, 'sp_compra_gestionar', [$accion, $usuario, $compraId, $texto, $origen]);
    }

    /** Devuelve cuántos artículos nuevos se agregaron (los repetidos se ignoran). */
    public function agregarCompras(int $id, array $textos, string $origen): int
    {
        $n = 0;
        foreach ($textos as $t) {
            if ($t === '') continue;
            $n += (int) ($this->compras('agregar', $id, 0, cortar_utf8($t, 160), cortar_utf8($origen, 180))[0]['filas'] ?? 0);
        }
        return $n;
    }

    public function marcarCompra(int $id, int $compraId): void { $this->compras('marcar', $id, $compraId); }
    public function quitarCompra(int $id, int $compraId): void { $this->compras('quitar', $id, $compraId); }
    public function vaciarCompras(int $id, bool $soloHechos): void { $this->compras($soloHechos ? 'vaciar_hechos' : 'vaciar', $id); }

    // ---------- Perfil alimentario (flujo A → B) ----------

    public static function tieneDiabetes(?array $u): bool
    {
        return in_array($u['condicion'] ?? '', ['diabetes', 'ambas'], true);
    }

    /**
     * Compara un producto o receta con el perfil de la persona.
     * $valores: ['carbohidratos' => x, 'azucares' => y] por porción.
     * @return array{apto:bool, conflictos:array, trazas:array, excede:array, evaluado:bool}
     */
    public static function aptitud(?array $perfil, array $contiene, array $trazas, array $valores): array
    {
        if ($perfil === null) {
            return ['apto' => true, 'conflictos' => [], 'trazas' => [], 'excede' => [], 'evaluado' => false];
        }
        $restr = $perfil['restricciones'];
        $excede = [];
        if ($perfil['carb_max'] !== null && $valores['carbohidratos'] > $perfil['carb_max']) {
            $excede[] = 'carbohidratos (' . gramos($valores['carbohidratos']) . ' > ' . gramos($perfil['carb_max']) . ')';
        }
        if ($perfil['azucar_max'] !== null && $valores['azucares'] > $perfil['azucar_max']) {
            $excede[] = 'azúcares (' . gramos($valores['azucares']) . ' > ' . gramos($perfil['azucar_max']) . ')';
        }
        $conflictos = array_values(array_intersect($restr, $contiene));
        return [
            'apto' => !$conflictos && !$excede,
            'conflictos' => $conflictos,
            'trazas' => array_values(array_intersect($restr, $trazas)),
            'excede' => $excede,
            'evaluado' => true,
        ];
    }
}
