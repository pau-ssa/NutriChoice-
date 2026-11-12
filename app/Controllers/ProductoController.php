<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Auth;
use App\Core\HttpException;
use App\Core\View;
use App\Models\Aporte;
use App\Models\Categoria;
use App\Models\Establecimiento;
use App\Models\Producto;
use App\Models\Resena;
use App\Models\Restriccion;
use App\Models\Usuario;

/** Módulo 5: Productos. Buscar y detalle son públicos; sugerir requiere sesión. */
final class ProductoController
{
    private Producto $productos;

    public function __construct()
    {
        $this->productos = new Producto();
    }

    public function index(): void
    {
        $restr = new Restriccion();
        $base = filtros_perfil(array_keys($restr->nombres()));
        $filtros = $base + [
            'q' => get_texto('q', 100),
            'categoria' => get_entero('categoria'),
            'marca' => get_texto('marca', 100),
            'tienda' => get_entero('tienda'),
            'precio_max' => get_decimal('precio_max'),
            'orden' => get_texto('orden', 20) ?: 'nombre',
        ];
        View::render('productos/index', [
            'productos' => $this->productos->buscar($filtros),
            'filtros' => $filtros,
            'categorias' => (new Categoria())->listar('producto'),
            'nombresCat' => (new Categoria())->nombres(),
            'marcas' => $this->productos->marcas(),
            'tiendas' => (new Establecimiento())->comercios(),
            'restricciones' => $restr->listar(),
            'perfil' => Auth::perfil(),
        ], 'Productos');
    }

    public function detalle(): void
    {
        $p = $this->productos->obtener(identificador());
        if (!$p || !$p['publicado']) throw new HttpException(404, 'El producto solicitado no existe.');
        $perfil = Auth::perfil();
        $lista = (new Resena())->de('producto', $p['id']);
        $lugares = new Establecimiento();
        View::render('productos/detalle', [
            'p' => $p,
            'categoria' => (new Categoria())->nombres()[$p['categoria_id']] ?? '',
            'etiquetas' => Producto::etiquetas($p),
            'aptitud' => Usuario::aptitud($perfil, $p['contiene'], $p['trazas'], $p),
            'nombresRestr' => (new Restriccion())->nombres(),
            'tiendas' => array_filter(array_map(fn($id) => $lugares->obtener((int) $id), $p['tiendas'])),
            'resenas' => $lista,
            'resumen' => Resena::resumen($lista),
            'favorito' => (new Usuario())->esFavorito($perfil, 'producto', $p['id']),
        ], $p['nombre']);
    }

    public function sugerir(): void
    {
        Auth::requerirSesion();
        // Con ?id= se propone una corrección de un producto existente.
        $id = get_entero('id');
        $actual = $id ? $this->productos->obtener($id) : null;
        $this->formulario($actual ?? $this->vacio(), [], $actual ? $id : 0);
    }

    public function enviarSugerencia(): void
    {
        Auth::requerirSesion();
        csrf_comprobar();
        [$datos, $errores] = self::leerFormulario();
        $corrige = (int) post_texto('corrige_id');
        if ($errores) {
            http_response_code(422);
            $this->formulario($datos, $errores, $corrige);
            return;
        }
        // La propuesta se guarda oculta (publicado = 0) y el aporte apunta a ella.
        $propuesta = $this->productos->guardar(0, $datos, false);
        (new Aporte())->proponer(Auth::id(), 'producto', $propuesta, $corrige, ($corrige ? 'Corrección: ' : '') . $datos['nombre']);
        aviso('¡Gracias! Tu sugerencia quedó pendiente de moderación.');
        redirigir('aportes');
    }

    private function formulario(array $datos, array $errores, int $corrige): void
    {
        View::render('productos/formulario', [
            'datos' => $datos, 'errores' => $errores,
            'accionForm' => 'producto_sugerir_enviar', 'ocultos' => ['corrige_id' => $corrige],
            'encabezado' => $corrige ? 'Corregir producto' : 'Sugerir producto',
            'aviso' => 'Tu aporte queda pendiente de moderación antes de publicarse.',
            'boton' => 'Enviar a moderación', 'volver' => ruta('productos'),
            'categorias' => (new Categoria())->listar('producto'),
            'tiendas' => (new Establecimiento())->comercios(),
            'restricciones' => (new Restriccion())->listar(),
        ], 'Sugerir producto');
    }

    public static function vacio(): array
    {
        return ['nombre' => '', 'marca' => '', 'categoria_id' => 0, 'precio' => '', 'porcion_g' => '',
            'calorias' => '', 'carbohidratos' => '', 'azucares' => '', 'fibra' => '', 'proteina' => '', 'grasa' => '', 'sodio' => '',
            'ingredientes' => '', 'contiene' => [], 'trazas' => [], 'tiendas' => []];
    }

    /** Lee y valida el formulario (también lo usa AdminController). Devuelve [datos, errores]. */
    public static function leerFormulario(): array
    {
        $err = [];
        $claves = array_keys((new Restriccion())->nombres());
        $comercios = array_keys((new Establecimiento())->comercios());
        $d = [
            'nombre' => validar_texto('nombre', 'Nombre', 150, $err),
            'marca' => validar_texto('marca', 'Marca', 100, $err),
            'categoria_id' => validar_categoria('producto', $err),
            'precio' => validar_numero('precio', 'Precio', 0, 1000000, $err),
            'porcion_g' => validar_numero('porcion_g', 'Porción', 1, 2000, $err),
        ];
        $d += validar_nutricion(['calorias', 'carbohidratos', 'azucares', 'fibra', 'proteina', 'grasa', 'sodio'], $err);
        $d['ingredientes'] = validar_texto('ingredientes', 'Ingredientes', 2000, $err);
        $d['contiene'] = array_values(array_intersect($claves, post_lista('contiene')));
        $d['trazas'] = array_values(array_diff(array_intersect($claves, post_lista('trazas')), $d['contiene']));
        $d['tiendas'] = array_values(array_intersect($comercios, array_map('intval', post_lista('tiendas'))));
        if (!$d['tiendas']) $err[] = 'Seleccione al menos un lugar de compra.';
        return [$d, $err];
    }
}
