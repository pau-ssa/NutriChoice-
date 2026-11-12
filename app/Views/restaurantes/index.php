<?php use App\Core\Auth; ?>
<div class="contenedor">
  <div class="seccion__encabezado">
    <h1>Restaurantes</h1>
    <?php if (Auth::haySesion()): ?><a class="btn btn--acento" href="<?= e(ruta('restaurante_sugerir')) ?>">+ Sugerir restaurante</a><?php endif; ?>
  </div>
  <div class="catalogo">
    <aside>
      <button class="btn btn--secundario btn--bloque filtros__alternar" type="button" data-alternar="form-filtros" aria-expanded="false">Mostrar filtros</button>
      <form id="form-filtros" class="filtros" method="get" action="index.php" role="search">
        <input type="hidden" name="accion" value="restaurantes">
        <input type="hidden" name="f" value="1">
        <div class="campo">
          <label class="campo__etiqueta" for="q">Buscar</label>
          <input class="campo__control" id="q" name="q" type="search" maxlength="100" placeholder="Nombre o plato" value="<?= e($filtros['q']) ?>">
        </div>
        <div class="filtros__grupo">
          <h2 class="filtros__titulo">Ubicación y tipo de comida</h2>
          <select class="campo__control" name="provincia" aria-label="Provincia">
            <option value="">Todo el país</option>
            <?php foreach (\App\Models\Establecimiento::PROVINCIAS as $pr): ?><option<?= marcado($filtros['provincia'] === $pr, 'selected') ?>><?= e($pr) ?></option><?php endforeach; ?>
          </select>
          <select class="campo__control" style="margin-top: .5rem" name="tipo_comida" aria-label="Tipo de comida">
            <option value="">Todos los tipos</option>
            <?php foreach ($tiposComida as $t): ?><option<?= marcado($filtros['tipo_comida'] === $t, 'selected') ?>><?= e($t) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="filtros__grupo">
          <h2 class="filtros__titulo">Debe atender</h2>
          <div class="chips">
            <?php foreach ($restricciones as $r): $sel = in_array($r['clave'], $filtros['restricciones'], true); ?>
              <label class="chip<?= $sel ? ' chip--activo' : '' ?>"><input class="sr-only" type="checkbox" name="restriccion[]" value="<?= e($r['clave']) ?>"<?= marcado($sel) ?>> <?= e($r['nombre']) ?></label>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="filtros__grupo">
          <h2 class="filtros__titulo">Nivel de seguridad</h2>
          <?php foreach (['' => 'Cualquiera', 'cocina_separada' => 'Cocina separada', 'opciones_adaptadas' => 'Opciones adaptadas'] as $v => $t): ?>
            <label class="opcion"><input type="radio" name="seguridad" value="<?= $v ?>"<?= marcado($filtros['seguridad'] === $v) ?>> <?= e($t) ?></label>
          <?php endforeach; ?>
          <label class="opcion"><input type="checkbox" name="info_nutricional" value="1"<?= marcado($filtros['info_nutricional']) ?>> Ofrece información nutricional</label>
        </div>
        <div class="filtros__grupo" style="border: 0">
          <button type="submit" class="btn btn--primario btn--bloque">Aplicar filtros</button>
          <a class="btn btn--secundario btn--bloque" style="margin-top: .5rem" href="<?= e(ruta('restaurantes', null, ['f' => 1])) ?>">Limpiar</a>
        </div>
      </form>
    </aside>
    <section>
      <div class="barra-herramientas"><span class="texto-suave"><?= count($restaurantes) ?> restaurante(s)</span></div>
      <?php if (!empty($filtros['desde_perfil'])): ?>
        <p class="filtros__perfil">Filtros de <strong>tu perfil</strong> aplicados automáticamente. <a href="<?= e(ruta('restaurantes', null, ['f' => 1])) ?>">Ver todo</a></p>
      <?php endif; ?>
      <?php if (!$restaurantes): ?><div class="alerta alerta--aviso">No hay restaurantes que cumplan estos filtros.</div><?php endif; ?>
      <div class="grid" style="margin-top: 1rem">
        <?php foreach ($restaurantes as $r): ?>
          <article class="tarjeta tarjeta--enlace">
            <div class="tarjeta__imagen tarjeta__imagen--restaurante" aria-hidden="true">🍽️</div>
            <div class="tarjeta__cuerpo">
              <h3 class="tarjeta__titulo"><a href="<?= e(ruta('restaurante', (int) $r['id'])) ?>"><?= e($r['nombre']) ?></a></h3>
              <p class="tarjeta__meta"><?= e($r['tipo_comida']) ?> · <?= e($r['provincia']) ?></p>
              <div class="etiquetas">
                <span class="etiqueta etiqueta--<?= $r['seguridad'] === 'cocina_separada' ? 'cocina-separada' : 'opciones-adaptadas' ?>"><?= $r['seguridad'] === 'cocina_separada' ? 'Cocina separada' : 'Opciones adaptadas' ?></span>
                <?php if ($r['info_nutricional']): ?><span class="etiqueta etiqueta--nutricional">Info. nutricional</span><?php endif; ?>
                <?php foreach ($r['restricciones'] as $c): ?><span class="etiqueta"><?= e($nombresRestr[$c] ?? $c) ?></span><?php endforeach; ?>
              </div>
              <div class="tarjeta__pie">
                <span><span class="calificacion" aria-hidden="true"><?= e(estrellas($r['resumen']['promedio'])) ?></span> <span class="texto-suave">(<?= (int) $r['resumen']['total'] ?>)</span></span>
                <a class="btn btn--primario btn--sm" href="<?= e(ruta('restaurante', (int) $r['id'])) ?>">Ver detalle</a>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </section>
  </div>
</div>
