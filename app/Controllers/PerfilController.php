<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Auth;
use App\Core\HttpException;
use App\Core\View;
use App\Models\Establecimiento;
use App\Models\Producto;
use App\Models\Receta;
use App\Models\Restriccion;
use App\Models\Usuario;

/** Módulo 3: Mi perfil (requiere sesión). */
final class PerfilController
{
    private Usuario $usuarios;
    private array $yo;

    public function __construct()
    {
        Auth::requerirSesion();
        $this->usuarios = new Usuario();
        $this->yo = Auth::perfil() ?? throw new HttpException(401, 'La sesión ya no es válida.');
    }

    public function index(): void
    {
        View::render('perfil/index', [
            'u' => $this->yo,
            'nombresRestr' => (new Restriccion())->nombres(),
            'seccion' => 'perfil', 'sub' => 'resumen',
        ], 'Mi perfil');
    }

    public function restricciones(): void
    {
        View::render('perfil/restricciones', [
            'u' => $this->yo,
            'restricciones' => (new Restriccion())->listar(),
            'seccion' => 'perfil', 'sub' => 'restricciones',
        ], 'Mis restricciones');
    }

    public function guardarRestricciones(): void
    {
        csrf_comprobar();
        $condicion = post_texto('condicion');
        if (!in_array($condicion, ['exclusion', 'diabetes', 'ambas', 'ninguna'], true)) {
            throw new HttpException(422, 'Tipo de condición inválido.');
        }
        $lista = (new Restriccion())->filtrarValidas(post_lista('restricciones'));
        $this->usuarios->actualizar($this->yo['id'], ['condicion' => $condicion, 'restricciones' => $lista]);
        aviso('Restricciones actualizadas. Se aplicarán como filtros en tus búsquedas.');
        redirigir('restricciones');
    }

    public function nutricional(): void
    {
        View::render('perfil/nutricional', [
            'u' => $this->yo, 'errores' => [],
            'seccion' => 'perfil', 'sub' => 'nutricional',
        ], 'Mi perfil nutricional');
    }

    public function guardarNutricional(): void
    {
        csrf_comprobar();
        $carb = post_decimal('carb_max');
        $azucar = post_decimal('azucar_max');
        $errores = [];
        foreach (['carbohidratos' => $carb, 'azúcares' => $azucar] as $nombre => $v) {
            if ($v !== null && ($v < 0 || $v > 200)) $errores[] = "El máximo de {$nombre} debe estar entre 0 y 200 g por porción.";
        }
        if ($errores) {
            http_response_code(422);
            View::render('perfil/nutricional', ['u' => ['carb_max' => $carb, 'azucar_max' => $azucar] + $this->yo,
                'errores' => $errores, 'seccion' => 'perfil', 'sub' => 'nutricional'], 'Mi perfil nutricional');
            return;
        }
        $cambios = ['carb_max' => $carb, 'azucar_max' => $azucar];
        // Definir rangos activa el perfil de diabetes si aún no estaba.
        if (($carb !== null || $azucar !== null) && !Usuario::tieneDiabetes($this->yo)) {
            $cambios['condicion'] = $this->yo['restricciones'] ? 'ambas' : 'diabetes';
        }
        $this->usuarios->actualizar($this->yo['id'], $cambios);
        aviso('Rangos nutricionales guardados.');
        redirigir('nutricional');
    }

    public function favoritos(): void
    {
        $productos = new Producto();
        $recetas = new Receta();
        $lugares = new Establecimiento();
        $items = ['producto' => [], 'receta' => [], 'restaurante' => []];
        foreach ($this->yo['favoritos'] as $f) {
            $fila = match ($f['tipo']) {
                'producto' => $productos->obtener((int) $f['ref_id']),
                'receta' => $recetas->obtener((int) $f['ref_id']),
                'restaurante' => $lugares->obtener((int) $f['ref_id']),
                default => null,
            };
            if ($fila) $items[$f['tipo']][] = $fila;
        }
        View::render('perfil/favoritos', ['items' => $items, 'seccion' => 'perfil', 'sub' => 'favoritos'], 'Favoritos');
    }

    public function alternarFavorito(): void
    {
        csrf_comprobar();
        $tipo = post_texto('tipo');
        $refId = identificador('ref_id', INPUT_POST);
        $existe = match ($tipo) {
            'producto' => (new Producto())->obtener($refId),
            'receta' => (new Receta())->obtener($refId),
            'restaurante' => (new Establecimiento())->obtener($refId),
            default => null,
        };
        if (!$existe) throw new HttpException(404, 'El elemento no existe.');
        $agregado = $this->usuarios->alternarFavorito($this->yo['id'], $tipo, $refId);
        aviso($agregado ? 'Agregado a tus favoritos.' : 'Quitado de tus favoritos.', 'info');
        $this->volver($tipo, $refId);
    }

    public function compras(): void
    {
        View::render('perfil/compras', ['lista' => $this->yo['compras'], 'seccion' => 'perfil', 'sub' => 'compras'], 'Lista de compras');
    }

    /** Agrega un producto, los ingredientes de una receta (filtrados según el perfil) o texto libre. */
    public function agregarCompra(): void
    {
        csrf_comprobar();
        $tipo = post_texto('tipo');
        if ($tipo === 'producto') {
            $p = (new Producto())->obtener(identificador('ref_id', INPUT_POST)) ?? throw new HttpException(404, 'Producto inexistente.');
            $n = $this->usuarios->agregarCompras($this->yo['id'], [$p['nombre'] . ' (' . $p['marca'] . ')'], 'Producto');
            aviso($n ? 'Producto agregado a la lista de compras.' : 'El producto ya estaba en tu lista.', 'info');
            $this->volver('producto', (int) $p['id']);
        }
        if ($tipo === 'receta') {
            $r = (new Receta())->obtener(identificador('ref_id', INPUT_POST)) ?? throw new HttpException(404, 'Receta inexistente.');
            $aptos = [];
            $omitidos = [];
            foreach ($r['lista'] as $ing) {
                if (array_intersect($this->yo['restricciones'], $ing['contiene'])) {
                    $omitidos[] = $ing['nombre'];
                } else {
                    $aptos[] = $ing['nombre'] . ' — ' . $ing['cantidad'];
                }
            }
            $n = $this->usuarios->agregarCompras($this->yo['id'], $aptos, 'Receta: ' . $r['titulo']);
            $msg = "Se agregaron {$n} ingrediente(s) a tu lista.";
            if ($omitidos) $msg .= ' Omitidos por tu perfil: ' . implode(', ', $omitidos) . '. Revisa las sustituciones sugeridas.';
            aviso($msg, $omitidos ? 'info' : 'ok');
            $this->volver('receta', (int) $r['id']);
        }
        $texto = post_texto('texto');
        if ($texto === '' || largo_utf8($texto) > 120) {
            aviso('Escribe un artículo de hasta 120 caracteres.', 'error');
        } else {
            $this->usuarios->agregarCompras($this->yo['id'], [$texto], 'Manual');
        }
        redirigir('compras');
    }

    public function marcarCompra(): void
    {
        csrf_comprobar();
        $this->usuarios->marcarCompra($this->yo['id'], identificador('compra_id', INPUT_POST));
        redirigir('compras');
    }

    public function quitarCompra(): void
    {
        csrf_comprobar();
        $this->usuarios->quitarCompra($this->yo['id'], identificador('compra_id', INPUT_POST));
        redirigir('compras');
    }

    public function vaciarCompras(): void
    {
        csrf_comprobar();
        $soloHechos = post_texto('solo') === 'hechos';
        $this->usuarios->vaciarCompras($this->yo['id'], $soloHechos);
        aviso($soloHechos ? 'Se quitaron los artículos marcados.' : 'Lista de compras vaciada.', 'info');
        redirigir('compras');
    }

    /** Regresa a la ficha de origen (lista blanca de destinos). */
    private function volver(string $tipo, int $id): never
    {
        $volver = post_texto('volver');
        if ($volver === 'favoritos') redirigir('favoritos');
        redirigir(['producto' => 'producto', 'receta' => 'receta', 'restaurante' => 'restaurante'][$tipo] ?? 'perfil', $id);
    }
}
