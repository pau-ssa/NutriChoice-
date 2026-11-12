<?php
use App\Models\Usuario;
?>
<div class="contenedor">
  <h1>Mi perfil</h1>
  <div class="lateral-layout">
    <aside class="lateral">
      <ul class="lateral__lista">
        <?= sub_link($sub, 'resumen', ruta('perfil'), 'Resumen') ?>
        <?= sub_link($sub, 'restricciones', ruta('restricciones'), 'Mis restricciones') ?>
        <?= sub_link($sub, 'nutricional', ruta('nutricional'), 'Mi perfil nutricional') ?>
        <?= sub_link($sub, 'favoritos', ruta('favoritos'), 'Favoritos') ?>
        <?= sub_link($sub, 'compras', ruta('compras'), 'Lista de compras') ?>
      </ul>
    </aside>
    <section>
      <p class="texto-suave">Hola, <?= e($u['nombre']) ?>. Esta información se aplica como filtro en tus búsquedas.</p>
      <div class="indicadores">
        <a class="indicador" href="<?= e(ruta('restricciones')) ?>"><span class="texto-suave">Condición</span><strong style="display:block"><?= e(Usuario::CONDICIONES[$u['condicion']] ?? '—') ?></strong></a>
        <a class="indicador" href="<?= e(ruta('favoritos')) ?>"><span class="indicador__valor"><?= count($u['favoritos']) ?></span>Favoritos</a>
        <a class="indicador" href="<?= e(ruta('compras')) ?>"><span class="indicador__valor"><?= count(array_filter($u['compras'], fn($c) => !$c['hecho'])) ?></span>Artículos por comprar</a>
      </div>
      <div class="panel">
        <h2>Mis restricciones</h2>
        <div class="etiquetas">
          <?php foreach ($u['restricciones'] as $c): ?><span class="etiqueta"><?= e($nombresRestr[$c] ?? $c) ?></span><?php endforeach; ?>
          <?php if (!$u['restricciones']): ?><span class="texto-suave">Sin restricciones por exclusión.</span><?php endif; ?>
        </div>
        <h2 style="margin-top: 1.5rem">Perfil nutricional</h2>
        <?php if (Usuario::tieneDiabetes($u)): ?>
          <div class="nutricion" style="max-width: 360px">
            <div class="nutricion__fila"><span>Carbohidratos por porción</span><span><?= e($u['carb_max'] !== null ? '≤ ' . gramos($u['carb_max']) : 'sin límite') ?></span></div>
            <div class="nutricion__fila"><span>Azúcares por porción</span><span><?= e($u['azucar_max'] !== null ? '≤ ' . gramos($u['azucar_max']) : 'sin límite') ?></span></div>
          </div>
        <?php else: ?>
          <p class="texto-suave">No aplica (sin perfil de diabetes).</p>
        <?php endif; ?>
      </div>
    </section>
  </div>
</div>
