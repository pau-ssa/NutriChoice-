<?php
use App\Core\Auth;
$usuarioSesion = Auth::usuario();
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="description" content="NutriChoice: productos, recetas y restaurantes para personas con restricciones alimentarias en Costa Rica.">
<title><?= e($titulo) ?> | NutriChoice</title>
<link rel="stylesheet" href="assets/css/estilos.css">
<script src="assets/js/app.js" defer></script>
</head>
<body>
<a class="saltar" href="#contenido">Saltar al contenido</a>
<header class="navbar">
  <div class="contenedor navbar__contenido">
    <a class="navbar__marca" href="<?= e(ruta()) ?>">
      <svg width="40" height="40" viewBox="0 0 64 64" aria-hidden="true">
        <circle cx="32" cy="32" r="30" fill="#EAF1EC"/>
        <path d="M18 44c0-15 10-26 28-28-1 17-11 28-26 28h-2z" fill="#356859"/>
        <path d="M20 44c6-8 12-14 20-20" stroke="#FFFFFF" stroke-width="3" stroke-linecap="round" fill="none"/>
      </svg>
      NutriChoice
    </a>
    <button class="navbar__boton" type="button" aria-expanded="false" aria-controls="menu-principal" aria-label="Abrir menú" data-nav-toggle>☰</button>
    <nav id="menu-principal" class="navbar__menu" aria-label="Navegación principal">
      <?= nav_link($seccion, 'index', 'inicio', 'Inicio') ?>
      <?= nav_link($seccion, 'primerospasos', 'primeros_pasos', 'Primeros pasos') ?>
      <?= nav_link($seccion, 'productos', 'productos', 'Productos') ?>
      <?= nav_link($seccion, 'recetas', 'recetas', 'Recetas') ?>
      <?= nav_link($seccion, 'restaurantes', 'restaurantes', 'Restaurantes') ?>
      <?php if (Auth::rol() === 'admin'): ?>
        <?= nav_link($seccion, 'admin', 'admin', 'Administración') ?>
      <?php endif; ?>
      <?php if ($usuarioSesion): ?>
        <?= nav_link($seccion, 'comunidad', 'aportes', 'Comunidad') ?>
        <?= nav_link($seccion, 'perfil', 'perfil', 'Mi perfil') ?>
        <form method="post" action="<?= e(ruta('logout')) ?>" class="navbar__form">
          <?= csrf_campo() ?>
          <button type="submit" class="btn btn--acento btn--sm">Salir</button>
        </form>
      <?php else: ?>
        <?= nav_link($seccion, 'acceso', 'login', 'Iniciar sesión') ?>
        <a class="btn btn--acento btn--sm" href="<?= e(ruta('registro')) ?>">Crear cuenta</a>
      <?php endif; ?>
    </nav>
  </div>
</header>
<main id="contenido" class="main">
<?php if ($mensajeFlash = tomar_aviso()): ?>
  <div class="contenedor">
    <div class="alerta alerta--<?= e(['ok' => 'exito', 'info' => 'info', 'error' => 'error'][$mensajeFlash['tipo']] ?? 'info') ?>" role="status" data-cerrable>
      <?= e($mensajeFlash['texto']) ?>
    </div>
  </div>
<?php endif; ?>
