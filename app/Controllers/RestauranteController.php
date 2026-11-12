<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Auth;
use App\Core\HttpException;
use App\Core\View;
use App\Models\Aporte;
use App\Models\Establecimiento;
use App\Models\Resena;
use App\Models\Restriccion;
use App\Models\Usuario;

/** Módulo 7: Restaurantes. Directorio público; sugerir requiere sesión. */
final class RestauranteController
{
    private Establecimiento $lugares;

    public function __construct()
    {
        $this->lugares = new Establecimiento();
    }

    public function index(): void
    {
        $restr = new Restriccion();
        $base = filtros_perfil(array_keys($restr->nombres()));
        $filtros = [
            'restricciones' => $base['restricciones'],
            'desde_perfil' => $base['desde_perfil'] && $base['restricciones'],
            'q' => get_texto('q', 100),
            'provincia' => in_array(get_texto('provincia'), Establecimiento::PROVINCIAS, true) ? get_texto('provincia') : '',
            'tipo_comida' => get_texto('tipo_comida', 60),
            'seguridad' => in_array(get_texto('seguridad'), ['cocina_separada', 'opciones_adaptadas'], true) ? get_texto('seguridad') : '',
            'info_nutricional' => isset($_GET['info_nutricional']),
        ];
        $resenas = new Resena();
        $lista = array_map(fn($r) => $r + ['resumen' => Resena::resumen($resenas->de('restaurante', (int) $r['id']))],
            $this->lugares->buscarRestaurantes($filtros));
        View::render('restaurantes/index', [
            'restaurantes' => $lista,
            'filtros' => $filtros,
            'tiposComida' => $this->lugares->tiposComida(),
            'restricciones' => $restr->listar(),
            'nombresRestr' => $restr->nombres(),
        ], 'Restaurantes');
    }

    public function detalle(): void
    {
        $r = $this->lugares->obtener(identificador());
        if (!$r || $r['tipo'] !== 'restaurante' || !$r['publicado']) throw new HttpException(404, 'El restaurante solicitado no existe.');
        $perfil = Auth::perfil();
        $resenas = new Resena();
        $faltan = $perfil ? array_values(array_diff($perfil['restricciones'], $r['restricciones'])) : [];
        View::render('restaurantes/detalle', [
            'r' => $r,
            'perfil' => $perfil,
            'faltan' => $faltan,
            'nombresRestr' => (new Restriccion())->nombres(),
            'resenas' => $lista = $resenas->de('restaurante', $r['id']),
            'resumen' => Resena::resumen($lista),
            'favorito' => (new Usuario())->esFavorito($perfil, 'restaurante', $r['id']),
        ], $r['nombre']);
    }

    public function sugerir(): void
    {
        Auth::requerirSesion();
        $id = get_entero('id');
        $actual = $id ? $this->lugares->obtener($id) : null;
        if ($actual && $actual['tipo'] !== 'restaurante') $actual = null;
        $this->formulario($actual ?? self::vacio(), [], $actual ? $id : 0);
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
        $propuesta = $this->lugares->guardar(0, $datos, false);
        (new Aporte())->proponer(Auth::id(), 'restaurante', $propuesta, $corrige, ($corrige ? 'Actualización: ' : '') . $datos['nombre']);
        aviso('¡Gracias! El restaurante quedó pendiente de moderación.');
        redirigir('aportes');
    }

    private function formulario(array $datos, array $errores, int $corrige): void
    {
        View::render('restaurantes/formulario', [
            'datos' => $datos, 'errores' => $errores,
            'accionForm' => 'restaurante_sugerir_enviar', 'ocultos' => ['corrige_id' => $corrige],
            'encabezado' => $corrige ? 'Actualizar restaurante' : 'Sugerir restaurante',
            'aviso' => 'Tu aporte queda pendiente de moderación antes de publicarse.',
            'boton' => 'Enviar a moderación', 'volver' => ruta('restaurantes'),
            'restricciones' => (new Restriccion())->listar(),
            'provincias' => Establecimiento::PROVINCIAS,
        ], 'Sugerir restaurante');
    }

    public static function vacio(): array
    {
        return ['nombre' => '', 'provincia' => '', 'direccion' => '', 'telefono' => '', 'tipo_comida' => '', 'horario' => '',
            'descripcion' => '', 'seguridad' => '', 'info_nutricional' => false, 'restricciones' => [], 'opciones' => []];
    }

    /** Lee y valida el formulario (también lo usa AdminController). Devuelve [datos, errores]. */
    public static function leerFormulario(): array
    {
        $err = [];
        $claves = array_keys((new Restriccion())->nombres());
        $d = [
            'nombre' => validar_texto('nombre', 'Nombre', 150, $err),
            'tipo' => 'restaurante',
            'provincia' => in_array(post_texto('provincia'), Establecimiento::PROVINCIAS, true) ? post_texto('provincia') : '',
            'direccion' => validar_texto('direccion', 'Dirección', 250, $err),
            'telefono' => validar_texto('telefono', 'Teléfono', 20, $err),
            'tipo_comida' => validar_texto('tipo_comida', 'Tipo de comida', 60, $err),
            'horario' => validar_texto('horario', 'Horario', 100, $err, false),
            'descripcion' => validar_texto('descripcion', 'Descripción', 500, $err, false),
            'seguridad' => in_array(post_texto('seguridad'), ['cocina_separada', 'opciones_adaptadas'], true) ? post_texto('seguridad') : '',
            'info_nutricional' => post_texto('info_nutricional') === '1',
            'restricciones' => array_values(array_intersect($claves, post_lista('restricciones'))),
            'opciones' => lineas(post_texto('opciones')),
        ];
        if ($d['provincia'] === '') $err[] = 'Seleccione una provincia.';
        if ($d['seguridad'] === '') $err[] = 'Indique el nivel de seguridad.';
        if ($d['telefono'] !== '' && !preg_match('/\A[0-9 +\-]{8,20}\z/', $d['telefono'])) $err[] = 'El teléfono solo admite números, espacios, + y guiones.';
        if (!$d['restricciones']) $err[] = 'Seleccione al menos una restricción atendida.';
        return [$d, $err];
    }
}
