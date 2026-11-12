<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\Database;
use App\Core\Procedimientos;
use PDO;

final class Aporte
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::obtener();
    }

    public const TIPOS = ['producto' => 'Producto', 'receta' => 'Receta', 'restaurante' => 'Restaurante', 'reporte' => 'Reporte de error'];
    public const ESTADOS = ['pendiente' => 'Pendiente', 'aprobado' => 'Aprobado', 'rechazado' => 'Rechazado'];

    private function consultar(int $id, string $estado, int $usuario): array
    {
        return array_map(function ($f) {
            $f['ref_id'] = $f['ref_id'] === null ? 0 : (int) $f['ref_id'];
            $f['corrige_id'] = $f['corrige_id'] === null ? 0 : (int) $f['corrige_id'];
            return $f;
        }, Procedimientos::filas($this->db, 'sp_aporte_consultar', [$id, $estado, $usuario]));
    }

    public function listar(?string $estado = null): array { return $this->consultar(0, $estado ?? '', 0); }
    public function deUsuario(int $usuarioId): array { return $this->consultar(0, '', $usuarioId); }
    public function obtener(int $id): ?array { return $this->consultar($id, '', 0)[0] ?? null; }
    public function contarPendientes(): int { return count($this->listar('pendiente')); }

    /** Registra una propuesta (producto, receta o restaurante) ya guardada con publicado = 0. */
    public function proponer(int $usuarioId, string $tipo, int $propuestaId, int $corrigeId, string $titulo): int
    {
        return Procedimientos::id($this->db, 'sp_aporte_guardar', [0, $usuarioId, $tipo, '', $propuestaId, $corrigeId,
            cortar_utf8($titulo, 150), '', 'pendiente', '']);
    }

    /** Registra un reporte de información incorrecta. */
    public function reportar(int $usuarioId, string $entidad, int $refId, string $titulo, string $descripcion): int
    {
        return Procedimientos::id($this->db, 'sp_aporte_guardar', [0, $usuarioId, 'reporte', $entidad, $refId, 0,
            cortar_utf8($titulo, 150), $descripcion, 'pendiente', '']);
    }

    /** Aprueba o rechaza. Al aprobar una propuesta nueva, MySQL la publica. */
    public function resolver(int $id, string $estado, string $motivo = ''): void
    {
        Procedimientos::id($this->db, 'sp_aporte_guardar', [$id, 0, '', '', 0, 0, '', '', $estado, $motivo]);
    }
}
