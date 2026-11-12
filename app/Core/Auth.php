<?php
declare(strict_types=1);
namespace App\Core;

/** Control de sesión. El rol se guarda en la sesión; su validación se agregará en el avance final. */
final class Auth
{
    public static function usuario(): ?array
    {
        return $_SESSION['usuario'] ?? null;
    }

    public static function id(): ?int
    {
        return isset($_SESSION['usuario']['id']) ? (int) $_SESSION['usuario']['id'] : null;
    }

    public static function rol(): ?string
    {
        return $_SESSION['usuario']['rol'] ?? null;
    }

    /** Registro completo (perfil, favoritos, compras) de la persona con sesión iniciada. */
    public static function perfil(): ?array
    {
        static $perfil = null;
        $id = self::id();
        if ($id === null) return null;
        return $perfil ??= (new \App\Models\Usuario())->obtener($id);
    }

    public static function haySesion(): bool
    {
        return isset($_SESSION['usuario']);
    }

    public static function iniciar(array $usuario): void
    {
        session_regenerate_id(true);
        $_SESSION['usuario'] = [
            'id' => (int) $usuario['id'],
            'nombre' => $usuario['nombre'],
            'rol' => $usuario['rol'],
        ];
    }

    public static function cerrar(): void
    {
        unset($_SESSION['usuario']);
        session_regenerate_id(true);
    }

    /** Páginas que requieren sesión: si no hay, se guarda el destino y se envía al ingreso. */
    public static function requerirSesion(): void
    {
        if (!self::haySesion()) {
            $_SESSION['destino'] = $_SERVER['REQUEST_METHOD'] === 'GET'
                ? ($_SERVER['QUERY_STRING'] ?? '') : '';
            aviso('Inicie sesión para continuar.', 'info');
            redirigir('login');
        }
    }

    public static function requerirPerfil(): array
    {
        $perfil = self::perfil();
        if ($perfil === null) {
            self::cerrar();
            aviso('Su sesión ya no es válida. Inicie sesión de nuevo.', 'info');
            redirigir('login');
        }
        return $perfil;
    }
}
