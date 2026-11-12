<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Models\Restriccion;
use App\Models\Usuario;

/** Módulo 2: Acceso — registro, inicio de sesión y recuperación de contraseña. */
final class AuthController
{
    private Usuario $usuarios;

    public function __construct()
    {
        $this->usuarios = new Usuario();
    }

    public function login(): void
    {
        if (Auth::haySesion()) redirigir('perfil');
        View::render('auth/login', ['errores' => [], 'email' => '', 'seccion' => 'acceso'], 'Iniciar sesión');
    }

    public function autenticar(): void
    {
        csrf_comprobar();
        $email = post_texto('email');
        $clave = is_string($_POST['clave'] ?? null) ? $_POST['clave'] : '';
        $ahora = time();
        // Límite: 5 intentos fallidos cada 10 minutos por sesión.
        $fallos = array_values(array_filter($_SESSION['login_fallos'] ?? [], fn($t) => is_int($t) && $t > $ahora - 600));
        $vista = fn(array $errores, int $codigo) => [http_response_code($codigo),
            View::render('auth/login', ['errores' => $errores, 'email' => $email, 'seccion' => 'acceso'], 'Iniciar sesión')];

        if (count($fallos) >= 5) {
            $vista(['Demasiados intentos. Espere diez minutos e intente de nuevo.'], 429);
            return;
        }
        $u = filter_var($email, FILTER_VALIDATE_EMAIL) ? $this->usuarios->buscarPorCorreo($email) : null;
        if (!$u || !password_verify($clave, $u['clave_hash'])) {
            $fallos[] = $ahora;
            $_SESSION['login_fallos'] = $fallos;
            $vista(['Correo o contraseña incorrectos.'], 422);
            return;
        }
        if (!empty($u['bloqueado'])) {
            $vista(['La cuenta está bloqueada. Comuníquese con la administración.'], 403);
            return;
        }
        unset($_SESSION['login_fallos']);
        Auth::iniciar($u);
        aviso('¡Hola, ' . $u['nombre'] . '! Tu perfil ya se aplica a las búsquedas.');
        $destino = $_SESSION['destino'] ?? '';
        unset($_SESSION['destino']);
        if ($destino !== '' && preg_match('/\Aaccion=[a-z_]+(&[a-z_]+=[0-9a-z_]*)*\z/', $destino)) {
            header('Location: index.php?' . $destino, true, 303);
            exit;
        }
        redirigir($u['rol'] === 'admin' ? 'admin' : 'perfil');
    }

    public function registro(): void
    {
        if (Auth::haySesion()) redirigir('perfil');
        View::render('auth/registro', [
            'errores' => [],
            'datos' => ['nombre' => '', 'email' => '', 'condicion' => 'exclusion', 'restricciones' => [], 'carb_max' => '', 'azucar_max' => ''],
            'restricciones' => (new Restriccion())->listar(),
            'seccion' => 'registro',
        ], 'Crear cuenta');
    }

    public function registrar(): void
    {
        csrf_comprobar();
        $restr = new Restriccion();
        $clave = is_string($_POST['clave'] ?? null) ? $_POST['clave'] : '';
        $datos = [
            'nombre' => post_texto('nombre'),
            'email' => strtolower(post_texto('email')),
            'condicion' => post_texto('condicion'),
            'restricciones' => $restr->filtrarValidas(post_lista('restricciones')),
            'carb_max' => post_decimal('carb_max'),
            'azucar_max' => post_decimal('azucar_max'),
        ];
        $errores = [];
        if ($datos['nombre'] === '' || largo_utf8($datos['nombre']) > 120) $errores[] = 'Indique su nombre (máximo 120 caracteres).';
        if (!filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) $errores[] = 'Indique un correo electrónico válido.';
        elseif ($this->usuarios->buscarPorCorreo($datos['email'])) $errores[] = 'Ya existe una cuenta con ese correo.';
        if (strlen($clave) < 8 || !preg_match('/[A-Za-z]/', $clave) || !preg_match('/\d/', $clave)) {
            $errores[] = 'La contraseña debe tener al menos 8 caracteres, con letras y números.';
        }
        if ($clave !== ($_POST['clave2'] ?? null)) $errores[] = 'Las contraseñas no coinciden.';
        if (!in_array($datos['condicion'], ['exclusion', 'diabetes', 'ambas'], true)) $errores[] = 'Seleccione el tipo de condición.';
        if (in_array($datos['condicion'], ['exclusion', 'ambas'], true) && !$datos['restricciones']) {
            $errores[] = 'Seleccione al menos una restricción por exclusión.';
        }
        if ($datos['condicion'] === 'diabetes') $datos['restricciones'] = [];
        if (in_array($datos['condicion'], ['diabetes', 'ambas'], true)) {
            foreach (['carb_max' => 'carbohidratos', 'azucar_max' => 'azúcares'] as $c => $txt) {
                if ($datos[$c] !== null && ($datos[$c] < 0 || $datos[$c] > 200)) $errores[] = "El máximo de {$txt} debe estar entre 0 y 200 g.";
            }
        } else {
            $datos['carb_max'] = $datos['azucar_max'] = null;
        }
        if (post_texto('acepto') !== '1') $errores[] = 'Debe aceptar el aviso legal para continuar.';

        if ($errores) {
            http_response_code(422);
            View::render('auth/registro', ['errores' => $errores, 'datos' => $datos,
                'restricciones' => $restr->listar(), 'seccion' => 'registro'], 'Crear cuenta');
            return;
        }
        $id = $this->usuarios->crear($datos + ['clave_hash' => password_hash($clave, PASSWORD_DEFAULT)]);
        Auth::iniciar($this->usuarios->obtener($id));
        aviso('Cuenta creada. Revisa tus restricciones y rangos en «Mi perfil».');
        redirigir('perfil');
    }

    public function recuperar(): void
    {
        View::render('auth/recuperar', ['enviado' => false, 'seccion' => 'acceso'], 'Recuperar contraseña');
    }

    /** Siempre responde igual para no revelar qué correos están registrados. */
    public function recuperarEnviar(): void
    {
        csrf_comprobar();
        View::render('auth/recuperar', ['enviado' => true, 'seccion' => 'acceso'], 'Recuperar contraseña');
    }

    public function salir(): void
    {
        csrf_comprobar();
        Auth::cerrar();
        aviso('Sesión cerrada.', 'info');
        redirigir();
    }
}
