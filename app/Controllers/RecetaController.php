<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Auth;
use App\Core\HttpException;
use App\Core\View;
use App\Models\Aporte;
use App\Models\Categoria;
use App\Models\Ingrediente;
use App\Models\Receta;
use App\Models\Resena;
use App\Models\Restriccion;
use App\Models\Usuario;

/** Módulo 6: Recetas. Buscar y detalle públicos; publicar requiere sesión. */
final class RecetaController
{
    private Receta $recetas;

    public function __construct()
    {
        $this->recetas = new Receta();
    }

    public function index(): void
    {
        $restr = new Restriccion();
        $filtros = filtros_perfil(array_keys($restr->nombres())) + [
            'q' => get_texto('q', 100),
            'categoria' => get_entero('categoria'),
            'tiempo_max' => get_entero('tiempo_max'),
        ];
        View::render('recetas/index', [
            'recetas' => $this->recetas->buscar($filtros),
            'filtros' => $filtros,
            'categorias' => (new Categoria())->listar('receta'),
            'nombresCat' => (new Categoria())->nombres(),
            'restricciones' => $restr->listar(),
            'perfil' => Auth::perfil(),
        ], 'Recetas');
    }

    public function detalle(): void
    {
        $r = $this->recetas->obtener(identificador());
        if (!$r || !$r['publicado']) throw new HttpException(404, 'La receta solicitada no existe.');
        $perfil = Auth::perfil();
        $lista = (new Resena())->de('receta', $r['id']);
        $autor = (new Usuario())->obtener((int) $r['usuario_id']);
        View::render('recetas/detalle', [
            'r' => $r,
            'categoria' => (new Categoria())->nombres()[$r['categoria_id']] ?? '',
            'autor' => $autor['nombre'] ?? 'Comunidad NutriChoice',
            'etiquetas' => Receta::etiquetas($r),
            'aptitud' => Usuario::aptitud($perfil, $r['contiene'], [], $r['porcion']),
            'perfil' => $perfil,
            'nombresRestr' => (new Restriccion())->nombres(),
            'resenas' => $lista,
            'resumen' => Resena::resumen($lista),
            'favorito' => (new Usuario())->esFavorito($perfil, 'receta', $r['id']),
        ], $r['titulo']);
    }

    public function publicar(): void
    {
        Auth::requerirSesion();
        $this->formulario(self::vacio(), []);
    }

    public function enviarPublicacion(): void
    {
        Auth::requerirSesion();
        csrf_comprobar();
        [$datos, $errores] = self::leerFormulario();
        if ($errores) {
            http_response_code(422);
            $this->formulario($datos, $errores);
            return;
        }
        // La propuesta se guarda oculta (publicado = 0) y el aporte apunta a ella.
        $propuesta = $this->recetas->guardar(0, $datos + ['usuario_id' => Auth::id()], false);
        (new Aporte())->proponer(Auth::id(), 'receta', $propuesta, 0, $datos['titulo']);
        aviso('¡Gracias! Tu receta pasó a moderación.');
        redirigir('aportes');
    }

    private function formulario(array $datos, array $errores): void
    {
        View::render('recetas/formulario', [
            'datos' => $datos, 'errores' => $errores,
            'accionForm' => 'receta_publicar_enviar', 'ocultos' => [],
            'encabezado' => 'Publicar receta',
            'aviso' => 'Usa ingredientes del catálogo. La receta pasa a moderación antes de publicarse.',
            'boton' => 'Enviar a moderación', 'volver' => ruta('recetas'),
            'categorias' => (new Categoria())->listar('receta'),
            'ingredientes' => (new Ingrediente())->listar(),
        ], 'Publicar receta');
    }

    public static function vacio(): array
    {
        return ['titulo' => '', 'categoria_id' => 0, 'descripcion' => '', 'tiempo_min' => '', 'porciones' => '', 'dificultad' => 'Fácil',
            'ingredientes' => [], 'pasos' => [], 'sustituciones' => [],
            'calorias' => '', 'carbohidratos' => '', 'azucares' => '', 'fibra' => '', 'proteina' => '', 'grasa' => ''];
    }

    /** Lee y valida el formulario (también lo usa AdminController). Devuelve [datos, errores]. */
    public static function leerFormulario(): array
    {
        $err = [];
        $d = [
            'titulo' => validar_texto('titulo', 'Título', 150, $err),
            'categoria_id' => validar_categoria('receta', $err),
            'descripcion' => validar_texto('descripcion', 'Descripción', 500, $err),
            'tiempo_min' => (int) validar_numero('tiempo_min', 'Tiempo de preparación', 1, 1440, $err),
            'porciones' => (int) validar_numero('porciones', 'Porciones', 1, 100, $err),
            'dificultad' => in_array(post_texto('dificultad'), ['Fácil', 'Media', 'Alta'], true) ? post_texto('dificultad') : 'Fácil',
        ];
        // Filas dinámicas de ingredientes (ingrediente_id[] y cantidad[]).
        $validos = array_keys((new Ingrediente())->porId());
        $ids = post_lista('ingrediente_id');
        $cantidades = post_lista('cantidad');
        $d['ingredientes'] = [];
        foreach ($ids as $i => $id) {
            $id = (int) $id;
            $cant = trim($cantidades[$i] ?? '');
            if ($id === 0 && $cant === '') continue;
            if (!in_array($id, $validos, true) || $cant === '' || largo_utf8($cant) > 60) {
                $err[] = 'Cada ingrediente debe existir en el catálogo y tener una cantidad (máx. 60 caracteres).';
                break;
            }
            $d['ingredientes'][] = ['ingrediente_id' => $id, 'cantidad' => $cant];
        }
        if (!$d['ingredientes']) $err[] = 'Agregue al menos un ingrediente.';
        $d['pasos'] = lineas(validar_texto('pasos', 'Pasos', 5000, $err));
        $d['sustituciones'] = [];
        foreach (lineas(post_texto('sustituciones')) as $linea) {
            // Formato: original = sustituto | motivo
            if (!str_contains($linea, '=')) {
                $err[] = 'Cada sustitución debe tener el formato «original = sustituto | motivo».';
                break;
            }
            [$original, $resto] = array_map('trim', explode('=', $linea, 2));
            [$sustituto, $motivo] = array_map('trim', array_pad(explode('|', $resto, 2), 2, ''));
            $d['sustituciones'][] = compact('original', 'sustituto', 'motivo');
        }
        $d += validar_nutricion(['calorias', 'carbohidratos', 'azucares', 'fibra', 'proteina', 'grasa'], $err);
        return [$d, $err];
    }
}
