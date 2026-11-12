<?php $nombresRestr = (new \App\Models\Restriccion())->nombres() + ['diabetes' => 'Diabetes']; ?>
<div class="contenedor">
  <section class="seccion">
    <p class="migas"><a href="<?= e(ruta('primeros_pasos')) ?>">Primeros pasos</a> › Sustituciones</p>
    <h1>Sustituciones comunes</h1>
    <div class="barra-herramientas">
      <div class="chips">
        <a class="chip<?= $grupo === '' ? ' chip--activo' : '' ?>" href="<?= e(ruta('sustituciones')) ?>">Todas</a>
        <?php foreach ($grupos as $g): ?>
          <a class="chip<?= $grupo === $g ? ' chip--activo' : '' ?>" href="<?= e(ruta('sustituciones', null, ['grupo' => $g])) ?>"><?= e($g) ?></a>
        <?php endforeach; ?>
      </div>
      <input class="campo__control" style="max-width: 280px" type="search" placeholder="Filtrar, p. ej. leche" aria-label="Filtrar sustituciones" data-filtrar="lista-sustituciones">
    </div>
    <?php if (empty($sustituciones)): ?>
      <div class="alerta alerta--info" role="status">
        No hay sustituciones disponibles en este momento.
      </div>
    <?php endif; ?>
    <div id="lista-sustituciones">
      <?php foreach ($sustituciones as $s): ?>
        <div class="sustitucion" data-filtrable>
          <div>
            <span class="texto-suave">En lugar de</span><br>
            <strong class="sustitucion__original"><?= e($s['original']) ?></strong>
          </div>
          <span class="sustitucion__flecha" aria-hidden="true">→</span>
          <div>
            <strong><?= e($s['sustituto']) ?></strong><br>
            <span class="texto-suave"><?= e($s['uso']) ?></span>
            <div class="etiquetas">
              <?php foreach ($s['restricciones'] as $c): ?><span class="etiqueta etiqueta--nutricional"><?= e($nombresRestr[$c] ?? $c) ?></span><?php endforeach; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>
</div>
