<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\HttpException;
use App\Core\View;
use App\Models\Ingrediente;
use App\Models\Restriccion;

/** Módulo 4: Primeros pasos (público). */
final class PrimerosPasosController
{
    private Restriccion $restricciones;
    private Ingrediente $ingredientes;

    public function __construct()
    {
        $this->restricciones = new Restriccion(); // guías de transición por condición
        $this->ingredientes = new Ingrediente();  // sustituciones comunes
    }

    public function index(): void
    {
        View::render('primerospasos/index', ['guias' => $this->restricciones->guias(), 'seccion' => 'primerospasos'], 'Primeros pasos');
    }

    public function guia(): void
    {
        $clave = get_texto('condicion', 20) ?: 'celiaquia';
        $guia = $this->restricciones->guia($clave) ?? throw new HttpException(404, 'No existe una guía para esa condición.');
        View::render('primerospasos/guia', [
            'guia' => $guia, 'clave' => $clave, 'guias' => $this->restricciones->guias(), 'seccion' => 'primerospasos',
        ], 'Guía: ' . $guia['titulo']);
    }

    public function sustituciones(): void
    {
        $grupos = $this->ingredientes->gruposSustitucion();
        $grupo = get_texto('grupo', 30);
        if (!in_array($grupo, $grupos, true)) $grupo = '';
        View::render('primerospasos/sustituciones', [
            'sustituciones' => $this->ingredientes->sustituciones($grupo),
            'grupos' => $grupos, 'grupo' => $grupo, 'seccion' => 'primerospasos',
        ], 'Sustituciones comunes');
    }
}
