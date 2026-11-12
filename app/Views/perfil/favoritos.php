<?php $titulos = ['producto' => 'Productos', 'receta' => 'Recetas', 'restaurante' => 'Restaurantes']; ?>
<div class="contenedor">
  <h1>Favoritos</h1>
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
      <?php foreach ($items as $tipo => $lista): ?>
        <div class="panel" style="margin-bottom: 1.5rem">
          <h2><?= e($titulos[$tipo]) ?> (<?= count($lista) ?>)</h2>
          <?php if (!$lista): ?><p class="texto-suave">Aún no hay elementos guardados.</p><?php endif; ?>
          <ul class="lista-simple">
            <?php foreach ($lista as $item): ?>
              <li class="tarjeta__pie" style="margin: 0">
                <a href="<?= e(ruta($tipo, (int) $item['id'])) ?>"><?= e($item['nombre'] ?? $item['titulo']) ?></a>
                <form method="post" action="<?= e(ruta('favorito_alternar')) ?>">
                  <?= csrf_campo() ?>
                  <input type="hidden" name="tipo" value="<?= e($tipo) ?>">
                  <input type="hidden" name="ref_id" value="<?= (int) $item['id'] ?>">
                  <input type="hidden" name="volver" value="favoritos">
                  <button type="submit" class="btn btn--secundario btn--sm">Quitar</button>
                </form>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endforeach; ?>
    </section>
  </div>
</div>
