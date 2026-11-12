<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\View;
use App\Models\Aporte;
use App\Models\Establecimiento;
use App\Models\Producto;
use App\Models\Receta;

/** Módulo 1: Inicio (público). */
final class IndexController
{
    public function index(): void
    {
        $aprobados = array_slice((new Aporte())->listar('aprobado'), 0, 4);
        View::render('index/index', [
            'productos' => (new Producto())->recientes(4),
            'recetas' => (new Receta())->recientes(3),
            'aportes' => $aprobados,
        ], 'Inicio');
    }

    /** Buscador rápido: consulta productos, recetas y restaurantes a la vez. */
    public function buscar(): void
    {
        $q = get_texto('q', 100);
        $vacio = ['q' => $q, 'categoria' => 0, 'restricciones' => [], 'carb_max' => null, 'azucar_max' => null];
        $productos = $q === '' ? [] : (new Producto())->buscar($vacio + ['marca' => '', 'tienda' => 0, 'precio_max' => null, 'orden' => 'nombre']);
        $recetas = $q === '' ? [] : (new Receta())->buscar($vacio + ['tiempo_max' => 0]);
        $restaurantes = $q === '' ? [] : (new Establecimiento())->buscarRestaurantes(['q' => $q, 'provincia' => '',
            'tipo_comida' => '', 'seguridad' => '', 'info_nutricional' => false, 'restricciones' => []]);
        View::render('index/buscar', compact('q', 'productos', 'recetas', 'restaurantes') + ['seccion' => 'index'],
            'Resultados de búsqueda');
    }
}
