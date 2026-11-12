<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Auth;
use App\Core\HttpException;
use App\Core\View;
use App\Models\Aporte;
use App\Models\Establecimiento;
use App\Models\Producto;
use App\Models\Receta;
use App\Models\Resena;

/** Módulo 8: Comunidad (requiere sesión): mis aportes, reportes y reseñas. */
final class ComunidadController
{
    public function __construct()
    {
        Auth::requerirSesion();
    }

    public function aportes(): void
    {
        View::render('comunidad/aportes', [
            'aportes' => (new Aporte())->deUsuario(Auth::id()),
            'seccion' => 'comunidad', 'sub' => 'aportes',
        ], 'Mis aportes');
    }

    public function reportar(): void
    {
        $entidad = get_texto('entidad', 20);
        $refId = get_entero('id');
        $nombre = $this->nombreDe($entidad, $refId);
        View::render('comunidad/reportar', [
            'errores' => [],
            'datos' => ['entidad' => $nombre ? $entidad : '', 'ref_id' => $nombre ? $refId : 0, 'nombre' => $nombre ?? '', 'descripcion' => ''],
            'seccion' => 'comunidad', 'sub' => 'reportar',
        ], 'Reportar error');
    }

    public function enviarReporte(): void
    {
        csrf_comprobar();
        $datos = [
            'entidad' => post_texto('entidad'),
            'ref_id' => (int) post_texto('ref_id'),
            'descripcion' => post_texto('descripcion'),
            'nombre' => post_texto('nombre'),
        ];
        $errores = [];
        $nombreReal = $this->nombreDe($datos['entidad'], $datos['ref_id']);
        if ($datos['ref_id'] > 0 && !$nombreReal) $errores[] = 'El elemento reportado no existe.';
        if (!in_array($datos['entidad'], ['producto', 'receta', 'restaurante'], true)) $errores[] = 'Indique qué tipo de información desea reportar.';
        if ($datos['ref_id'] === 0 && ($datos['nombre'] === '' || largo_utf8($datos['nombre']) > 150)) $errores[] = 'Indique el nombre del producto, receta o restaurante.';
        if (largo_utf8($datos['descripcion']) < 10 || largo_utf8($datos['descripcion']) > 1000) $errores[] = 'Describa el error entre 10 y 1000 caracteres.';
        if ($errores) {
            http_response_code(422);
            View::render('comunidad/reportar', ['errores' => $errores, 'datos' => $datos, 'seccion' => 'comunidad', 'sub' => 'reportar'], 'Reportar error');
            return;
        }
        $titulo = 'Reporte: ' . ($nombreReal ?? $datos['nombre']);
        (new Aporte())->reportar(Auth::id(), $datos['entidad'], $datos['ref_id'], $titulo, $datos['descripcion']);
        aviso('Reporte enviado. La administración lo revisará.');
        redirigir('aportes');
    }

    public function guardarResena(): void
    {
        csrf_comprobar();
        $tipo = post_texto('tipo');
        $refId = identificador('ref_id', INPUT_POST);
        if (!$this->nombreDe($tipo, $refId)) throw new HttpException(404, 'El elemento no existe.');
        $puntaje = (int) post_texto('puntaje');
        $comentario = post_texto('comentario');
        if ($puntaje < 1 || $puntaje > 5 || largo_utf8($comentario) < 5 || largo_utf8($comentario) > 600) {
            aviso('La reseña necesita una calificación de 1 a 5 y un comentario de 5 a 600 caracteres.', 'error');
        } else {
            $nombre = explode(' ', Auth::usuario()['nombre']);
            (new Resena())->crear(['tipo' => $tipo, 'ref_id' => $refId, 'usuario_id' => Auth::id(),
                'autor' => $nombre[0] . (isset($nombre[1]) ? ' ' . mb_substr($nombre[1], 0, 1) . '.' : ''),
                'puntaje' => $puntaje, 'comentario' => $comentario]);
            aviso('¡Gracias por tu reseña!');
        }
        header('Location: ' . ruta($tipo, $refId) . '#resenas', true, 303);
        exit;
    }

    private function nombreDe(string $entidad, int $id): ?string
    {
        if ($id < 1) return null;
        return match ($entidad) {
            'producto' => (new Producto())->obtener($id)['nombre'] ?? null,
            'receta' => (new Receta())->obtener($id)['titulo'] ?? null,
            'restaurante' => (($r = (new Establecimiento())->obtener($id)) && $r['tipo'] === 'restaurante') ? $r['nombre'] : null,
            default => null,
        };
    }
}
