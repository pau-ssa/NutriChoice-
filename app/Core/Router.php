<?php
declare(strict_types=1);
namespace App\Core;

use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\ComunidadController;
use App\Controllers\IndexController;
use App\Controllers\PerfilController;
use App\Controllers\PrimerosPasosController;
use App\Controllers\ProductoController;
use App\Controllers\RecetaController;
use App\Controllers\RestauranteController;

/** Tabla de rutas "METODO accion" => controlador. Las acciones POST cambian estado. */
final class Router
{
    public function ejecutar(): void
    {
        $accion = $_GET['accion'] ?? 'inicio';
        if (!is_string($accion) || !preg_match('/\A[a-z_]+\z/D', $accion)) {
            throw new HttpException(400, 'Ruta inválida.');
        }
        $metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        $inicio = fn() => new IndexController();
        $auth = fn() => new AuthController();
        $perfil = fn() => new PerfilController();
        $pasos = fn() => new PrimerosPasosController();
        $productos = fn() => new ProductoController();
        $recetas = fn() => new RecetaController();
        $restaurantes = fn() => new RestauranteController();
        $comunidad = fn() => new ComunidadController();
        $admin = fn() => new AdminController();
        $moderacion = fn() => new AdminController(true);

        $rutas = [
            // 1) Inicio
            'GET inicio' => fn() => $inicio()->index(),
            'GET buscar' => fn() => $inicio()->buscar(),
            // 2) Acceso
            'GET login' => fn() => $auth()->login(),
            'POST autenticar' => fn() => $auth()->autenticar(),
            'GET registro' => fn() => $auth()->registro(),
            'POST registrar' => fn() => $auth()->registrar(),
            'GET recuperar' => fn() => $auth()->recuperar(),
            'POST recuperar_enviar' => fn() => $auth()->recuperarEnviar(),
            'POST logout' => fn() => $auth()->salir(),
            // 3) Mi perfil
            'GET perfil' => fn() => $perfil()->index(),
            'GET restricciones' => fn() => $perfil()->restricciones(),
            'POST restricciones_guardar' => fn() => $perfil()->guardarRestricciones(),
            'GET nutricional' => fn() => $perfil()->nutricional(),
            'POST nutricional_guardar' => fn() => $perfil()->guardarNutricional(),
            'GET favoritos' => fn() => $perfil()->favoritos(),
            'POST favorito_alternar' => fn() => $perfil()->alternarFavorito(),
            'GET compras' => fn() => $perfil()->compras(),
            'POST compras_agregar' => fn() => $perfil()->agregarCompra(),
            'POST compras_marcar' => fn() => $perfil()->marcarCompra(),
            'POST compras_quitar' => fn() => $perfil()->quitarCompra(),
            'POST compras_vaciar' => fn() => $perfil()->vaciarCompras(),
            // 4) Primeros pasos
            'GET primeros_pasos' => fn() => $pasos()->index(),
            'GET guia' => fn() => $pasos()->guia(),
            'GET sustituciones' => fn() => $pasos()->sustituciones(),
            // 5) Productos
            'GET productos' => fn() => $productos()->index(),
            'GET producto' => fn() => $productos()->detalle(),
            'GET producto_sugerir' => fn() => $productos()->sugerir(),
            'POST producto_sugerir_enviar' => fn() => $productos()->enviarSugerencia(),
            // 6) Recetas
            'GET recetas' => fn() => $recetas()->index(),
            'GET receta' => fn() => $recetas()->detalle(),
            'GET receta_publicar' => fn() => $recetas()->publicar(),
            'POST receta_publicar_enviar' => fn() => $recetas()->enviarPublicacion(),
            // 7) Restaurantes
            'GET restaurantes' => fn() => $restaurantes()->index(),
            'GET restaurante' => fn() => $restaurantes()->detalle(),
            'GET restaurante_sugerir' => fn() => $restaurantes()->sugerir(),
            'POST restaurante_sugerir_enviar' => fn() => $restaurantes()->enviarSugerencia(),
            // 8) Comunidad
            'GET aportes' => fn() => $comunidad()->aportes(),
            'GET reportar' => fn() => $comunidad()->reportar(),
            'POST reportar_enviar' => fn() => $comunidad()->enviarReporte(),
            'POST resena_guardar' => fn() => $comunidad()->guardarResena(),
            // 9) Administración
            'GET admin' => fn() => $admin()->index(),
            'GET moderacion' => fn() => $moderacion()->moderacion(),
            'POST aporte_resolver' => fn() => $moderacion()->resolverAporte(),
            'GET usuarios' => fn() => $admin()->usuarios(),
            'POST usuario_actualizar' => fn() => $admin()->actualizarUsuario(),
            'GET catalogo' => fn() => $admin()->catalogo(),
            'GET catalogo_form' => fn() => $moderacion()->formulario(),
            'POST catalogo_guardar' => fn() => $moderacion()->guardar(),
            'POST catalogo_eliminar' => fn() => $admin()->eliminar(),
        ];

        $clave = $metodo . ' ' . $accion;
        if (isset($rutas[$clave])) {
            $rutas[$clave]();
            return;
        }
        foreach (array_keys($rutas) as $ruta) {
            if (str_ends_with($ruta, ' ' . $accion)) {
                header('Allow: ' . (str_starts_with($ruta, 'GET') ? 'GET' : 'POST'));
                throw new HttpException(405, 'El método HTTP no está permitido para esta acción.');
            }
        }
        throw new HttpException(404, 'No existe la dirección solicitada.');
    }
}
