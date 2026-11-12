<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Auth;
use App\Core\HttpException;
use App\Core\View;
use App\Models\Aporte;
use App\Models\Categoria;
use App\Models\Establecimiento;
use App\Models\Ingrediente;
use App\Models\Producto;
use App\Models\Receta;
use App\Models\Restriccion;
use App\Models\Usuario;

/** Módulo 9: Administración (rol admin): moderación, usuarios y catálogos (CRUD). El rol colaborador solo modera aportes. */
final class AdminController
{
    /** Catálogos administrables: clave de URL => etiqueta. */
    public const CATALOGOS = [
        'productos' => 'Productos',
        'recetas' => 'Recetas',
        'ingredientes' => 'Ingredientes',
        'establecimientos' => 'Establecimientos',
        'categorias' => 'Categorías',
        'caracteristicas' => 'Características alimentarias',
    ];

    private Aporte $aportes;

    public function __construct(bool $moderacion = false)
    {
        Auth::requerirSesion();
        if (Auth::rol() !== 'admin' && !($moderacion && Auth::rol() === 'colaborador')) {
            throw new HttpException(403, 'No cuenta con permisos para acceder a esta sección.');
        }
        $this->aportes = new Aporte();
    }

    /** Solo el rol admin gestiona usuarios y catálogos. */
    private function soloAdmin(): void
    {
        if (Auth::rol() !== 'admin') throw new HttpException(403, 'No cuenta con permisos para acceder a esta sección.');
    }

    /** Bandeja a la que vuelve cada rol: el panel del admin o la moderación del colaborador. */
    private function bandeja(): string
    {
        return Auth::rol() === 'admin' ? 'admin' : 'moderacion';
    }

    /** Nadie, salvo el admin, modera un aporte que envió con su propia cuenta. */
    private function ajeno(array $a): void
    {
        if (Auth::rol() !== 'admin' && (int) $a['usuario_id'] === Auth::id()) {
            throw new HttpException(403, 'No puede moderar sus propios aportes.');
        }
    }

    /** Panel con indicadores y bandeja de moderación de aportes (flujo 1 → 2 → 3). */
    public function index(): void
    {
        $estado = get_texto('estado', 20) ?: 'pendiente';
        if (!isset(Aporte::ESTADOS[$estado]) && $estado !== 'todos') $estado = 'pendiente';
        View::render('admin/index', [
            'pendientes' => $this->aportes->contarPendientes(),
            'totales' => [
                'productos' => count((new Producto())->listar()),
                'recetas' => count((new Receta())->listar()),
                'restaurantes' => count((new Establecimiento())->listar('restaurante')),
                'usuarios' => count((new Usuario())->listar()),
            ],
            'aportes' => $this->aportes->listar($estado === 'todos' ? null : $estado),
            'estado' => $estado,
            'seccion' => 'admin', 'sub' => 'panel',
        ], 'Administración');
    }

    /** Bandeja de moderación del rol colaborador: aprueba, edita o rechaza aportes de otras personas. */
    public function moderacion(): void
    {
        $estado = get_texto('estado', 20) ?: 'pendiente';
        if (!isset(Aporte::ESTADOS[$estado]) && $estado !== 'todos') $estado = 'pendiente';
        View::render('admin/moderacion', [
            'aportes' => $this->aportes->listar($estado === 'todos' ? null : $estado),
            'estado' => $estado,
            'seccion' => 'moderacion',
        ], 'Moderación');
    }

    public function resolverAporte(): void
    {
        csrf_comprobar();
        $a = $this->aportes->obtener(identificador('id', INPUT_POST)) ?? throw new HttpException(404, 'El aporte no existe.');
        if ($a['estado'] !== 'pendiente') throw new HttpException(409, 'El aporte ya fue resuelto.');
        $this->ajeno($a);
        $decision = post_texto('decision');
        if ($decision === 'rechazar') {
            $motivo = post_texto('motivo');
            if (largo_utf8($motivo) < 5 || largo_utf8($motivo) > 300) {
                aviso('Indique el motivo del rechazo (5 a 300 caracteres).', 'error');
                redirigir($this->bandeja());
            }
            $this->aportes->resolver($a['id'], 'rechazado', $motivo);
            aviso('Aporte rechazado. La persona verá el motivo en «Mis aportes».', 'info');
            redirigir($this->bandeja());
        }
        if ($decision !== 'aprobar') throw new HttpException(400, 'Decisión inválida.');
        $this->aprobar($a, $a['tipo'] === 'reporte' ? 'Reporte atendido.' : '');
        aviso($a['tipo'] === 'reporte' ? 'Reporte marcado como atendido.' : 'Aporte aprobado y publicado en el catálogo.');
        redirigir($this->bandeja());
    }

    /** Catálogo y modelo donde vive la propuesta de cada tipo de aporte. */
    private const DESTINO = ['producto' => 'productos', 'receta' => 'recetas', 'restaurante' => 'establecimientos'];

    /**
     * Flujo 1 → 2 → 3.
     * Propuesta nueva: MySQL la publica al aprobar (publicado = 1).
     * Corrección: se copian los datos propuestos al registro original y se elimina la propuesta.
     */
    private function aprobar(array $a, string $motivo): void
    {
        $tabla = self::DESTINO[$a['tipo']] ?? null;
        if ($tabla && $a['corrige_id'] > 0) {
            $m = $this->modelo($tabla);
            $propuesta = $m->obtener($a['ref_id']);
            if ($propuesta && $m->obtener($a['corrige_id'])) $m->guardar($a['corrige_id'], $propuesta);
            $this->aportes->resolver($a['id'], 'aprobado', $motivo);
            $m->eliminar($a['ref_id']);
            return;
        }
        $this->aportes->resolver($a['id'], 'aprobado', $motivo);
    }

    // ---------- Gestión de usuarios ----------
    public function usuarios(): void
    {
        View::render('admin/detalle', [
            'tipo' => 'usuarios', 'filas' => (new Usuario())->listar(),
            'seccion' => 'admin', 'sub' => 'usuarios',
        ], 'Gestión de usuarios');
    }

    public function actualizarUsuario(): void
    {
        csrf_comprobar();
        $id = identificador('id', INPUT_POST);
        $m = new Usuario();
        $u = $m->obtener($id) ?? throw new HttpException(404, 'El usuario no existe.');
        if ($id === Auth::id()) {
            aviso('Por seguridad no puede modificar su propio rol ni bloquear su cuenta.', 'error');
            redirigir('usuarios');
        }
        $rol = post_texto('rol');
        if (!isset(Usuario::ROLES[$rol])) throw new HttpException(422, 'Rol inválido.');
        $m->actualizar($id, ['rol' => $rol, 'bloqueado' => post_texto('bloqueado') === '1']);
        aviso('Usuario «' . $u['nombre'] . '» actualizado.');
        redirigir('usuarios');
    }

    // ---------- Gestión de catálogos (CRUD) ----------
    private function tipo(string $origen = 'get'): string
    {
        $tipo = $origen === 'get' ? get_texto('tipo', 30) : post_texto('tipo');
        if (!isset(self::CATALOGOS[$tipo])) throw new HttpException(404, 'Catálogo inexistente.');
        return $tipo;
    }

    public function catalogo(): void
    {
        $tipo = get_texto('tipo', 30) ?: 'productos';
        if (!isset(self::CATALOGOS[$tipo])) throw new HttpException(404, 'Catálogo inexistente.');
        $filas = match ($tipo) {
            'productos' => (new Producto())->listar(),
            'recetas' => (new Receta())->listar(),
            'ingredientes' => (new Ingrediente())->listar(),
            'establecimientos' => (new Establecimiento())->listar(),
            'categorias' => (new Categoria())->listar(),
            'caracteristicas' => (new Restriccion())->listar(),
        };
        View::render('admin/detalle', [
            'tipo' => $tipo, 'filas' => $filas,
            'nombresCat' => (new Categoria())->nombres(),
            'nombresRestr' => (new Restriccion())->nombres(),
            'seccion' => 'admin', 'sub' => 'catalogos',
        ], self::CATALOGOS[$tipo]);
    }

    public function formulario(): void
    {
        $tipo = $this->tipo();
        $id = get_entero('id');
        $aporteId = get_entero('aporte');
        if (!$aporteId) $this->soloAdmin();
        $subtipo = get_texto('subtipo', 20) === 'restaurante' ? 'restaurante' : 'comercio';
        $datos = null;
        if ($aporteId) {
            // Editar un aporte antes de aprobarlo: se edita la propuesta oculta a la que apunta.
            $id = $this->propuesta($aporteId, $tipo)['ref_id'];
            $datos = $this->modelo($tipo)->obtener($id) ?? throw new HttpException(404, 'Propuesta inexistente.');
        } elseif ($id) {
            $datos = $this->modelo($tipo)->obtener($id) ?? throw new HttpException(404, 'Registro inexistente.');
        }
        if ($tipo === 'establecimientos' && $datos) $subtipo = ($datos['tipo'] ?? '') === 'restaurante' ? 'restaurante' : 'comercio';
        $this->vistaFormulario($tipo, $datos ?? $this->vacio($tipo, $subtipo), $id, $aporteId, $subtipo, []);
    }

    public function guardar(): void
    {
        csrf_comprobar();
        $tipo = $this->tipo('post');
        $id = (int) post_texto('id');
        $aporteId = (int) post_texto('aporte_id');
        if (!$aporteId) $this->soloAdmin();
        if ($aporteId) $id = $this->propuesta($aporteId, $tipo)['ref_id'];
        $subtipo = post_texto('subtipo') === 'restaurante' ? 'restaurante' : 'comercio';
        [$datos, $errores] = $this->validar($tipo, $subtipo);
        if ($errores) {
            http_response_code(422);
            $this->vistaFormulario($tipo, $datos, $id, $aporteId, $subtipo, $errores);
            return;
        }
        $m = $this->modelo($tipo);
        $existe = $id > 0 && $m->obtener($id);
        if ($tipo === 'recetas' && !$existe) $datos['usuario_id'] = (int) Auth::id();
        $m->guardar($existe ? $id : 0, $datos);
        $msg = $existe ? 'Registro actualizado.' : 'Registro creado.';
        if ($aporteId) {
            $a = $this->aportes->obtener($aporteId);
            if ($a && $a['estado'] === 'pendiente') $this->aprobar($a, 'Aprobado con ediciones de la administración.');
            aviso('Aporte editado, aprobado y publicado.');
            redirigir($this->bandeja());
        }
        aviso($msg);
        redirigir('catalogo', null, ['tipo' => $tipo]);
    }

    public function eliminar(): void
    {
        csrf_comprobar();
        $tipo = $this->tipo('post');
        $id = identificador('id', INPUT_POST);
        $m = $this->modelo($tipo);
        $m->obtener($id) ?? throw new HttpException(404, 'Registro inexistente.');
        try {
            $m->eliminar($id);
            aviso('Registro eliminado.', 'info');
        } catch (\PDOException $e) {
            // SQLSTATE 23000: una llave foránea (ON DELETE RESTRICT) impide borrar un registro en uso.
            if ($e->getCode() !== '23000') throw $e;
            aviso('No se puede eliminar: el registro está en uso por otros datos del catálogo.', 'error');
        }
        redirigir('catalogo', null, ['tipo' => $tipo]);
    }

    /** Aporte pendiente cuya propuesta vive en el catálogo indicado. */
    private function propuesta(int $aporteId, string $tipo): array
    {
        $a = $this->aportes->obtener($aporteId);
        if (!$a || $a['estado'] !== 'pendiente' || (self::DESTINO[$a['tipo']] ?? '') !== $tipo) {
            throw new HttpException(404, 'Aporte no disponible.');
        }
        $this->ajeno($a);
        return $a;
    }

    private function modelo(string $tipo): object
    {
        return match ($tipo) {
            'productos' => new Producto(),
            'recetas' => new Receta(),
            'ingredientes' => new Ingrediente(),
            'establecimientos' => new Establecimiento(),
            'categorias' => new Categoria(),
            'caracteristicas' => new Restriccion(),
        };
    }

    private function vacio(string $tipo, string $subtipo): array
    {
        return match ($tipo) {
            'productos' => ProductoController::vacio(),
            'recetas' => RecetaController::vacio(),
            'establecimientos' => $subtipo === 'restaurante' ? RestauranteController::vacio()
                : ['nombre' => '', 'tipo' => 'supermercado', 'provincia' => '', 'direccion' => '', 'telefono' => ''],
            'ingredientes' => ['nombre' => '', 'contiene' => [], 'disponible_cr' => true],
            'categorias' => ['nombre' => '', 'tipo' => 'producto'],
            'caracteristicas' => ['clave' => '', 'nombre' => '', 'descripcion' => ''],
        };
    }

    /** @return array{0: array, 1: list<string>} */
    private function validar(string $tipo, string $subtipo): array
    {
        if ($tipo === 'productos') return ProductoController::leerFormulario();
        if ($tipo === 'recetas') return RecetaController::leerFormulario();
        if ($tipo === 'establecimientos' && $subtipo === 'restaurante') return RestauranteController::leerFormulario();

        $err = [];
        $nombre = post_texto('nombre');
        if ($nombre === '' || largo_utf8($nombre) > 120) $err[] = 'El nombre es obligatorio (máximo 120 caracteres).';
        switch ($tipo) {
            case 'ingredientes':
                $d = ['nombre' => $nombre,
                    'contiene' => (new Restriccion())->filtrarValidas(post_lista('contiene')),
                    'disponible_cr' => post_texto('disponible_cr') === '1'];
                break;
            case 'categorias':
                $d = ['nombre' => $nombre, 'tipo' => post_texto('tipo_categoria') === 'receta' ? 'receta' : 'producto'];
                break;
            case 'caracteristicas':
                $d = ['nombre' => $nombre, 'clave' => post_texto('clave'), 'descripcion' => post_texto('descripcion')];
                if (!preg_match('/\A[a-z_]{3,30}\z/', $d['clave'])) $err[] = 'La clave usa de 3 a 30 letras minúsculas o guion bajo.';
                if (largo_utf8($d['descripcion']) > 300) $err[] = 'La descripción no debe superar 300 caracteres.';
                $id = (int) post_texto('id');
                foreach ((new Restriccion())->listar() as $r) {
                    if ($r['clave'] === $d['clave'] && (int) $r['id'] !== $id) $err[] = 'Ya existe una característica con esa clave.';
                }
                break;
            default: // comercio
                $d = ['nombre' => $nombre,
                    'tipo' => post_texto('tipo_comercio') === 'tienda' ? 'tienda' : 'supermercado',
                    'provincia' => in_array(post_texto('provincia'), Establecimiento::PROVINCIAS, true) ? post_texto('provincia') : '',
                    'direccion' => post_texto('direccion'), 'telefono' => post_texto('telefono')];
                if ($d['provincia'] === '') $err[] = 'Seleccione una provincia.';
                if ($d['direccion'] === '' || largo_utf8($d['direccion']) > 250) $err[] = 'La dirección es obligatoria (máximo 250 caracteres).';
        }
        return [$d, $err];
    }

    private function vistaFormulario(string $tipo, array $datos, int $id, int $aporteId, string $subtipo, array $errores): void
    {
        $comun = [
            'datos' => $datos, 'errores' => $errores, 'seccion' => $this->bandeja(),
            'accionForm' => 'catalogo_guardar',
            'ocultos' => ['tipo' => $tipo, 'id' => $id, 'aporte_id' => $aporteId, 'subtipo' => $subtipo],
            'encabezado' => ($aporteId ? 'Editar aporte · ' : ($id ? 'Editar · ' : 'Nuevo · ')) . self::CATALOGOS[$tipo],
            'aviso' => $aporteId ? 'Está editando el aporte #' . $aporteId . '. Al guardar se aprobará y publicará.' : '',
            'boton' => $aporteId ? 'Guardar y aprobar' : 'Guardar',
            'volver' => $aporteId ? ruta($this->bandeja()) : ruta('catalogo', null, ['tipo' => $tipo]),
        ];
        $titulo = $comun['encabezado'];
        if ($tipo === 'productos') {
            View::render('productos/formulario', $comun + [
                'categorias' => (new Categoria())->listar('producto'),
                'tiendas' => (new Establecimiento())->comercios(),
                'restricciones' => (new Restriccion())->listar(),
            ], $titulo);
        } elseif ($tipo === 'recetas') {
            View::render('recetas/formulario', $comun + [
                'categorias' => (new Categoria())->listar('receta'),
                'ingredientes' => (new Ingrediente())->listar(),
            ], $titulo);
        } elseif ($tipo === 'establecimientos' && $subtipo === 'restaurante') {
            View::render('restaurantes/formulario', $comun + [
                'restricciones' => (new Restriccion())->listar(),
                'provincias' => Establecimiento::PROVINCIAS,
            ], $titulo);
        } else {
            View::render('admin/formulario', $comun + [
                'tipo' => $tipo, 'id' => $id, 'subtipo' => $subtipo,
                'restricciones' => (new Restriccion())->listar(),
                'provincias' => Establecimiento::PROVINCIAS,
                'sub' => 'catalogos',
            ], $titulo);
        }
    }
}
