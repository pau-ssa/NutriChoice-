<?php
use App\Core\Auth;
?>
<div class="contenedor">
  <div class="seccion__encabezado">
    <h1>Recetas</h1>
    <?php if (Auth::haySesion()): ?><a class="btn btn--acento" href="<?= e(ruta('receta_publicar')) ?>">+ Publicar receta</a><?php endif; ?>
  </div>
  <div class="catalogo">
    <aside>
      <button class="btn btn--secundario btn--bloque filtros__alternar" type="button" data-alternar="form-filtros" aria-expanded="false">Mostrar filtros</button>
      <form id="form-filtros" class="filtros" method="get" action="index.php" role="search">
        <input type="hidden" name="accion" value="recetas">
        <input type="hidden" name="f" value="1">
        <div class="campo">
          <label class="campo__etiqueta" for="q">Buscar</label>
          <input class="campo__control" id="q" name="q" type="search" maxlength="100" placeholder="Receta o ingrediente" value="<?= e($filtros['q']) ?>">
        </div>
        <div class="filtros__grupo">
          <h2 class="filtros__titulo">Tipo de plato y tiempo</h2>
          <select class="campo__control" name="categoria" aria-label="Tipo de plato">
            <option value="0">Todos los platos</option>
            <?php foreach ($categorias as $c): ?>
              <option value="<?= (int) $c['id'] ?>"<?= marcado($filtros['categoria'] === (int) $c['id'], 'selected') ?>><?= e($c['nombre']) ?></option>
            <?php endforeach; ?>
          </select>
          <select class="campo__control" style="margin-top: .5rem" name="tiempo_max" aria-label="Tiempo de preparación">
            <?php foreach ([0 => 'Cualquier tiempo', 15 => 'Hasta 15 min', 30 => 'Hasta 30 min', 60 => 'Hasta 1 hora'] as $v => $t): ?>
              <option value="<?= $v ?>"<?= marcado($filtros['tiempo_max'] === $v, 'selected') ?>><?= e($t) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="filtros__grupo">
          <h2 class="filtros__titulo">Excluir</h2>
          <div class="chips">
            <?php foreach ($restricciones as $r): $sel = in_array($r['clave'], $filtros['restricciones'], true); ?>
              <label class="chip<?= $sel ? ' chip--activo' : '' ?>"><input class="sr-only" type="checkbox" name="restriccion[]" value="<?= e($r['clave']) ?>"<?= marcado($sel) ?>> <?= e($r['nombre']) ?></label>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="filtros__grupo">
          <h2 class="filtros__titulo">Máximo por porción (g)</h2>
          <div class="filtros__rango">
            <input class="campo__control" name="carb_max" type="number" min="0" max="200" step="0.5" aria-label="Carbohidratos máximos" placeholder="Carbos" value="<?= e($filtros['carb_max']) ?>">
            <input class="campo__control" name="azucar_max" type="number" min="0" max="200" step="0.5" aria-label="Azúcares máximos" placeholder="Azúcares" value="<?= e($filtros['azucar_max']) ?>">
          </div>
        </div>
        <div class="filtros__grupo" style="border: 0">
          <button type="submit" class="btn btn--primario btn--bloque">Aplicar filtros</button>
          <a class="btn btn--secundario btn--bloque" style="margin-top: .5rem" href="<?= e(ruta('recetas', null, ['f' => 1])) ?>">Limpiar</a>
        </div>
      </form>
    </aside>
    <section>
      <div class="barra-herramientas"><span class="texto-suave"><?= count($recetas) ?> receta(s)</span></div>
      <?php if (!empty($filtros['desde_perfil'])): ?>
        <p class="filtros__perfil">Filtros de <strong>tu perfil</strong> aplicados automáticamente. <a href="<?= e(ruta('recetas', null, ['f' => 1])) ?>">Ver todo</a></p>
      <?php elseif (!Auth::haySesion()): ?>
        <p class="filtros__perfil"><a href="<?= e(ruta('login')) ?>">Inicie sesión</a> para aplicar automáticamente sus restricciones y rangos.</p>
      <?php endif; ?>
      <?php if (!$recetas): ?><div class="alerta alerta--aviso">No hay recetas que cumplan estos filtros.</div><?php endif; ?>
      <div class="grid" style="margin-top: 1rem">
        <?php foreach ($recetas as $r): ?>
          <article class="tarjeta tarjeta--enlace">
            <div class="tarjeta__imagen tarjeta__imagen--receta" aria-hidden="true">🍲</div>
            <div class="tarjeta__cuerpo">
              <h3 class="tarjeta__titulo"><a href="<?= e(ruta('receta', (int) $r['id'])) ?>"><?= e($r['titulo']) ?></a></h3>
              <p class="tarjeta__meta"><?= e($nombresCat[$r['categoria_id']] ?? '') ?> · <?= (int) $r['tiempo_min'] ?> min · <?= (int) $r['porciones'] ?> porciones</p>
              <div class="etiquetas">
                <?php foreach (\App\Models\Receta::etiquetas($r) as $et): ?><span class="etiqueta <?= e($et['clase']) ?>"><?= e($et['texto']) ?></span><?php endforeach; ?>
              </div>
              <p class="tarjeta__meta"><?= e(cortar_utf8($r['descripcion'], 110)) ?></p>
              <div class="tarjeta__pie">
                <span class="texto-suave"><?= e(gramos($r['porcion']['carbohidratos'])) ?> carb./porción</span>
                <a class="btn btn--primario btn--sm" href="<?= e(ruta('receta', (int) $r['id'])) ?>">Ver receta</a>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </section>
  </div>
</div>
