<div class="contenedor">
  <section class="seccion">
    <p class="migas"><a href="<?= e(ruta('primeros_pasos')) ?>">Primeros pasos</a> › <?= e($guia['titulo']) ?></p>
    <h1>Guía: <?= e($guia['titulo']) ?></h1>
    <p class="texto-suave"><?= e($guia['resumen']) ?></p>
    <div class="chips" style="margin-bottom: 1.5rem">
      <?php foreach ($guias as $k => $g): ?>
        <a class="chip<?= $k === $clave ? ' chip--activo' : '' ?>" href="<?= e(ruta('guia', null, ['condicion' => $k])) ?>"<?= $k === $clave ? ' aria-current="page"' : '' ?>><?= e($g['titulo']) ?></a>
      <?php endforeach; ?>
    </div>
    <ol class="pasos">
      <?php foreach ($guia['secciones'] as $s): ?>
        <li>
          <h3><?= e($s['titulo']) ?></h3>
          <ul>
            <?php foreach ($s['puntos'] as $punto): ?><li><?= e($punto) ?></li><?php endforeach; ?>
          </ul>
        </li>
      <?php endforeach; ?>
    </ol>
    <div class="acciones">
      <a class="btn btn--primario" href="<?= e(ruta('sustituciones')) ?>">Sustituciones comunes</a>
      <a class="btn btn--secundario" href="<?= e(ruta('productos')) ?>">Explorar productos</a>
      <a class="btn btn--secundario" href="<?= e(ruta('restaurantes')) ?>">Restaurantes seguros</a>
    </div>
  </section>
</div>
