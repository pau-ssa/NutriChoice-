<?php
declare(strict_types=1);

use App\Core\HttpException;

/** Escapa cualquier valor antes de imprimirlo en HTML. */
function e(mixed $valor): string
{
    return htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Construye URLs internas: index.php?accion=...&id=...&extra=... */
function ruta(string $accion = 'inicio', ?int $id = null, array $extra = []): string
{
    $parametros = ['accion' => $accion];
    if ($id !== null) $parametros['id'] = $id;
    return 'index.php?' . http_build_query($parametros + $extra);
}

function redirigir(string $accion = 'inicio', ?int $id = null, array $extra = []): never
{
    header('Location: ' . ruta($accion, $id, $extra), true, 303);
    exit;
}

function largo_utf8(string $texto): int
{
    return function_exists('mb_strlen') ? mb_strlen($texto, 'UTF-8') : strlen($texto);
}

function cortar_utf8(string $texto, int $max): string
{
    if (largo_utf8($texto) <= $max) return $texto;
    $corte = function_exists('mb_substr') ? mb_substr($texto, 0, $max - 1, 'UTF-8') : substr($texto, 0, $max - 1);
    return rtrim($corte) . '…';
}

function csrf_token(): string
{
    return $_SESSION['csrf'] ??= bin2hex(random_bytes(24));
}

function csrf_campo(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function csrf_comprobar(): void
{
    if (!is_string($_POST['_csrf'] ?? null) || !hash_equals(csrf_token(), $_POST['_csrf'])) {
        throw new HttpException(403, 'Solicitud rechazada: token de formulario inválido.');
    }
}

/** Mensajes flash: tipo ok | info | error. */
function aviso(string $texto, string $tipo = 'ok'): void
{
    $_SESSION['aviso'] = ['texto' => $texto, 'tipo' => $tipo];
}

function tomar_aviso(): ?array
{
    $aviso = $_SESSION['aviso'] ?? null;
    unset($_SESSION['aviso']);
    return $aviso;
}

/** Identificador numérico positivo desde GET o POST. */
function identificador(string $nombre = 'id', int $origen = INPUT_GET): int
{
    $id = filter_input($origen, $nombre, FILTER_VALIDATE_INT);
    if ($id === false || $id === null || $id < 1) {
        throw new HttpException(400, 'Se requiere un identificador numérico positivo.');
    }
    return $id;
}

/** Lecturas seguras de parámetros. */
function get_texto(string $nombre, int $max = 200): string
{
    $v = $_GET[$nombre] ?? '';
    return is_string($v) ? cortar_utf8(trim($v), $max) : '';
}

function get_entero(string $nombre, int $defecto = 0): int
{
    $v = filter_var($_GET[$nombre] ?? null, FILTER_VALIDATE_INT);
    return ($v === false || $v === null || $v < 0) ? $defecto : $v;
}

function get_decimal(string $nombre): ?float
{
    $v = $_GET[$nombre] ?? '';
    if (!is_string($v) || $v === '') return null;
    $n = filter_var($v, FILTER_VALIDATE_FLOAT);
    return ($n === false || $n < 0) ? null : $n;
}

function get_lista(string $nombre): array
{
    $v = $_GET[$nombre] ?? [];
    return is_array($v) ? array_values(array_filter($v, 'is_string')) : [];
}

function post_texto(string $nombre, int $max = 500): string
{
    $v = $_POST[$nombre] ?? '';
    return is_string($v) ? trim($v) : '';
}

function post_decimal(string $nombre): ?float
{
    $v = $_POST[$nombre] ?? '';
    if (!is_string($v) || trim($v) === '') return null;
    $n = filter_var(str_replace(',', '.', $v), FILTER_VALIDATE_FLOAT);
    return $n === false ? null : $n;
}

function post_lista(string $nombre): array
{
    $v = $_POST[$nombre] ?? [];
    return is_array($v) ? array_values(array_filter($v, 'is_string')) : [];
}

/** Formatos para la vista. */
function colones(float|int|null $monto): string
{
    return $monto === null ? '—' : '₡' . number_format((float) $monto, 0, ',', ' ');
}

function gramos(float|int|null $valor, string $unidad = 'g'): string
{
    if ($valor === null) return '—';
    $txt = rtrim(rtrim(number_format((float) $valor, 1, ',', ''), '0'), ',');
    return $txt . ' ' . $unidad;
}

function fecha_corta(string $fecha): string
{
    $t = strtotime($fecha);
    return $t ? date('d/m/Y', $t) : $fecha;
}

function estrellas(float $promedio): string
{
    $llenas = (int) round($promedio);
    return str_repeat('★', $llenas) . str_repeat('☆', 5 - $llenas);
}

/** Enlace del menú principal; marca la sección activa con clase y aria-current. */
function nav_link(string $actual, string $seccion, string $accion, string $texto): string
{
    $activo = $actual === $seccion;
    return '<a class="navbar__enlace' . ($activo ? ' navbar__enlace--activo' : '') . '" href="' . e(ruta($accion)) . '"'
        . ($activo ? ' aria-current="page"' : '') . '>' . e($texto) . '</a>';
}

/** Elemento del menú lateral (Mi perfil y Administración). */
function sub_link(string $actual, string $clave, string $url, string $texto): string
{
    $activo = $actual === $clave;
    return '<li><a class="lateral__enlace' . ($activo ? ' lateral__enlace--activo' : '') . '" href="' . e($url) . '"'
        . ($activo ? ' aria-current="page"' : '') . '>' . e($texto) . '</a></li>';
}

/** Atributo checked/selected para formularios. */
function marcado(bool $condicion, string $atributo = 'checked'): string
{
    return $condicion ? ' ' . $atributo : '';
}

/* ---------- Conversión de datos que devuelve MySQL ---------- */

/** 'gluten,lactosa' → ['gluten', 'lactosa'] */
function lista_csv(?string $texto): array
{
    return ($texto === null || $texto === '') ? [] : explode(',', $texto);
}

/** Convierte columnas DECIMAL (que PDO entrega como texto) a float. */
function con_decimales(array $fila, array $campos): array
{
    foreach ($campos as $c) {
        if (array_key_exists($c, $fila)) $fila[$c] = $fila[$c] === null ? null : (float) $fila[$c];
    }
    return $fila;
}

/** Valor opcional como texto para un parámetro DECIMAL, o null. */
function decimal_o_nulo(mixed $v): ?string
{
    return ($v === null || $v === '') ? null : (string) (float) $v;
}

/* ---------- Filtros de búsqueda a partir del perfil (flujo A → B) ---------- */

/**
 * Si el formulario de filtros fue enviado (f=1) se respetan los valores del GET;
 * si no, se usan las restricciones y los rangos del perfil de la persona.
 */
function filtros_perfil(array $clavesValidas): array
{
    if (isset($_GET['f'])) {
        return [
            'restricciones' => array_values(array_intersect($clavesValidas, get_lista('restriccion'))),
            'carb_max' => get_decimal('carb_max'),
            'azucar_max' => get_decimal('azucar_max'),
            'desde_perfil' => false,
        ];
    }
    $u = \App\Core\Auth::perfil();
    $diabetes = \App\Models\Usuario::tieneDiabetes($u);
    return [
        'restricciones' => $u['restricciones'] ?? [],
        'carb_max' => $diabetes ? $u['carb_max'] : null,
        'azucar_max' => $diabetes ? $u['azucar_max'] : null,
        'desde_perfil' => $u !== null && ($u['restricciones'] || $diabetes),
    ];
}

/* ---------- Validación de formularios (lado del servidor) ---------- */

/** Texto obligatorio u opcional con largo máximo. */
function validar_texto(string $campo, string $etiqueta, int $max, array &$errores, bool $obligatorio = true): string
{
    $v = post_texto($campo);
    if ($obligatorio && $v === '') {
        $errores[] = "El campo «{$etiqueta}» es obligatorio.";
    } elseif (largo_utf8($v) > $max) {
        $errores[] = "«{$etiqueta}» no debe superar {$max} caracteres.";
    }
    return $v;
}

/** Número dentro de un rango. */
function validar_numero(string $campo, string $etiqueta, float $min, float $max, array &$errores, bool $obligatorio = true): ?float
{
    $v = post_decimal($campo);
    if ($v === null) {
        if ($obligatorio) $errores[] = "Indique un valor numérico para «{$etiqueta}».";
        return null;
    }
    if ($v < $min || $v > $max) $errores[] = "«{$etiqueta}» debe estar entre {$min} y {$max}.";
    return $v;
}

/** Valores nutricionales; los azúcares no pueden superar los carbohidratos. */
function validar_nutricion(array $campos, array &$errores): array
{
    $etiquetas = ['calorias' => 'Calorías', 'carbohidratos' => 'Carbohidratos', 'azucares' => 'Azúcares',
        'fibra' => 'Fibra', 'proteina' => 'Proteína', 'grasa' => 'Grasa', 'sodio' => 'Sodio'];
    $d = [];
    foreach ($campos as $c) {
        $d[$c] = validar_numero($c, $etiquetas[$c], 0, in_array($c, ['sodio', 'calorias'], true) ? 20000 : 5000, $errores) ?? 0;
    }
    if ($d['azucares'] > $d['carbohidratos']) $errores[] = 'Los azúcares no pueden superar los carbohidratos totales.';
    return $d;
}

/** Categoría existente del tipo indicado. */
function validar_categoria(string $tipo, array &$errores): int
{
    $id = (int) post_texto('categoria_id');
    $validas = array_map(fn($c) => (int) $c['id'], (new \App\Models\Categoria())->listar($tipo));
    if (!in_array($id, $validas, true)) $errores[] = 'Seleccione una categoría existente.';
    return $id;
}

/** Divide un textarea en líneas no vacías. */
function lineas(string $texto): array
{
    return array_values(array_filter(array_map('trim', preg_split('/\R/', $texto)), fn($l) => $l !== ''));
}