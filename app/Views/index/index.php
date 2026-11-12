<?php
use App\Core\Auth;
$nombresCat = (new \App\Models\Categoria())->nombres();
?>
<section class="hero">
  <div class="contenedor">
    <h1>Come seguro, según tu perfil</h1>
    <p class="hero__texto">Productos de supermercados nacionales, recetas con ingredientes locales y restaurantes
      con opciones aptas para celiaquía, alergias, intolerancias y diabetes.</p>
    <form class="buscador" action="index.php" method="get" role="search">
      <input type="hidden" name="accion" value="buscar">
      <label for="q-rapido" class="sr-only">Buscar productos, recetas o restaurantes</label>
      <input id="q-rapido" class="campo__control" name="q" type="search" maxlength="100" placeholder="Ej.: pan sin gluten, queque, soda…" required>
      <button type="submit" class="btn btn--acento">Buscar</button>
    </form>
    <div class="hero__acciones">
      <a class="btn btn--secundario" href="<?= e(ruta('primeros_pasos')) ?>">¿Diagnóstico reciente? Primeros pasos</a>
      <?php if (!Auth::haySesion()): ?><a class="btn btn--acento" href="<?= e(ruta('registro')) ?>">Crear mi perfil</a><?php endif; ?>
    </div>
  </div>
</section>

<div class="contenedor">
  <section class="seccion" style="margin-top: 2rem">
    <h2>¿Cómo funciona?</h2>
    <div class="pasos-inicio">
      <div class="panel"><h3>1. Crea tu perfil</h3><p>Indica tus restricciones por exclusión y/o tus rangos de carbohidratos y azúcares.</p></div>
      <div class="panel"><h3>2. Busca con filtros automáticos</h3><p>Tu perfil se aplica a productos, recetas y restaurantes.</p></div>
      <div class="panel"><h3>3. Aporta a la comunidad</h3><p>Sugiere productos y restaurantes; la administración los verifica antes de publicarlos.</p></div>
    </div>
  </section>

  <section class="seccion">
    <div class="seccion__encabezado">
      <h2>Productos recientes</h2>
      <a href="<?= e(ruta('productos')) ?>">Ver catálogo →</a>
    </div>
    <div class="grid">
      <?php foreach ($productos as $p): ?>
        <article class="tarjeta tarjeta--enlace">
          <div class="tarjeta__imagen" aria-hidden="true">🛒</div>
          <div class="tarjeta__cuerpo">
            <h3 class="tarjeta__titulo"><a href="<?= e(ruta('producto', (int) $p['id'])) ?>"><?= e($p['nombre']) ?></a></h3>
            <p class="tarjeta__meta"><?= e($p['marca']) ?> · <?= e($nombresCat[$p['categoria_id']] ?? '') ?></p>
            <div class="etiquetas">
              <?php foreach (\App\Models\Producto::etiquetas($p) as $et): ?><span class="etiqueta <?= e($et['clase']) ?>"><?= e($et['texto']) ?></span><?php endforeach; ?>
            </div>
            <div class="tarjeta__pie">
              <span class="tarjeta__precio"><?= e(colones($p['precio'])) ?></span>
              <a class="btn btn--primario btn--sm" href="<?= e(ruta('producto', (int) $p['id'])) ?>">Ver detalle</a>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="seccion">
    <div class="seccion__encabezado">
      <h2>Recetas nuevas</h2>
      <a href="<?= e(ruta('recetas')) ?>">Ver todas →</a>
    </div>
    <div class="grid">
      <?php foreach ($recetas as $r): ?>
        <article class="tarjeta tarjeta--enlace">
          <div class="tarjeta__imagen tarjeta__imagen--receta" aria-hidden="true">🍲</div>
          <div class="tarjeta__cuerpo">
            <h3 class="tarjeta__titulo"><a href="<?= e(ruta('receta', (int) $r['id'])) ?>"><?= e($r['titulo']) ?></a></h3>
            <p class="tarjeta__meta"><?= e($nombresCat[$r['categoria_id']] ?? '') ?> · <?= (int) $r['tiempo_min'] ?> min</p>
            <div class="etiquetas">
              <?php foreach (\App\Models\Receta::etiquetas($r) as $et): ?><span class="etiqueta <?= e($et['clase']) ?>"><?= e($et['texto']) ?></span><?php endforeach; ?>
            </div>
            <div class="tarjeta__pie">
              <span class="texto-suave"><?= e(gramos($r['porcion']['carbohidratos'])) ?> carb./porción</span>
              <a class="btn btn--primario btn--sm" href="<?= e(ruta('receta', (int) $r['id'])) ?>">Ver receta</a>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="seccion">
    <h2>Aportes aprobados de la comunidad</h2>
    <?php if (!$aportes): ?>
      <p class="texto-suave">Aún no hay aportes publicados.</p>
    <?php else: ?>
      <ul class="lista-simple">
        <?php foreach ($aportes as $a): ?>
          <li><span class="etiqueta etiqueta--aprobado"><?= e(\App\Models\Aporte::TIPOS[$a['tipo']]) ?></span>
            <?= e($a['titulo']) ?> <span class="texto-suave">· <?= e(fecha_corta($a['fecha'])) ?></span></li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </section>
</div>
