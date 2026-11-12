<?php
use App\Core\Auth;
$nombresRestr = array_column($restricciones, 'nombre', 'clave');
?>
<div class="contenedor">
  <div class="seccion__encabezado">
    <h1>Productos</h1>
    <?php if (Auth::haySesion()): ?><a class="btn btn--acento" href="<?= e(ruta('producto_sugerir')) ?>">+ Sugerir producto</a><?php endif; ?>
  </div>
  <div class="catalogo">
    <aside>
      <button class="btn btn--secundario btn--bloque filtros__alternar" type="button" data-alternar="form-filtros" aria-expanded="false">Mostrar filtros</button>
      <form id="form-filtros" class="filtros" method="get" action="index.php" role="search">
        <input type="hidden" name="accion" value="productos">
        <input type="hidden" name="f" value="1">
        <div class="campo">
          <label class="campo__etiqueta" for="q">Buscar</label>
          <input class="campo__control" id="q" name="q" type="search" maxlength="100" placeholder="Nombre, marca o ingrediente" value="<?= e($filtros['q']) ?>">
        </div>
        <div class="filtros__grupo">
          <h2 class="filtros__titulo">Categoría y marca</h2>
          <select class="campo__control" name="categoria" aria-label="Categoría">
            <option value="0">Todas las categorías</option>
            <?php foreach ($categorias as $c): ?>
              <option value="<?= (int) $c['id'] ?>"<?= marcado($filtros['categoria'] === (int) $c['id'], 'selected') ?>><?= e($c['nombre']) ?></option>
            <?php endforeach; ?>
          </select>
          <select class="campo__control" style="margin-top: .5rem" name="marca" aria-label="Marca">
            <option value="">Todas las marcas</option>
            <?php foreach ($marcas as $m): ?><option<?= marcado($filtros['marca'] === $m, 'selected') ?>><?= e($m) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="filtros__grupo">
          <h2 class="filtros__titulo">Lugar de compra y precio</h2>
          <select class="campo__control" name="tienda" aria-label="Lugar de compra">
            <option value="0">Todos los lugares</option>
            <?php foreach ($tiendas as $id => $n): ?>
              <option value="<?= (int) $id ?>"<?= marcado($filtros['tienda'] === (int) $id, 'selected') ?>><?= e($n) ?></option>
            <?php endforeach; ?>
          </select>
          <input class="campo__control" style="margin-top: .5rem" name="precio_max" type="number" min="0" step="50" aria-label="Precio máximo" placeholder="Precio máximo (₡)" value="<?= e($filtros['precio_max']) ?>">
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
          <a class="btn btn--secundario btn--bloque" style="margin-top: .5rem" href="<?= e(ruta('productos', null, ['f' => 1])) ?>">Limpiar</a>
        </div>
      </form>
    </aside>
    <section>
      <div class="barra-herramientas">
        <span class="texto-suave"><?= count($productos) ?> producto(s)</span>
        <form method="get" action="index.php" class="filtros__rango">
          <?php foreach ($_GET as $k => $v): if ($k === 'orden' || !is_string($v)) continue; ?><input type="hidden" name="<?= e($k) ?>" value="<?= e($v) ?>"><?php endforeach; ?>
          <?php foreach ($filtros['restricciones'] as $c): ?><input type="hidden" name="restriccion[]" value="<?= e($c) ?>"><?php endforeach; ?>
          <label class="campo__etiqueta" for="orden">Ordenar</label>
          <select class="campo__control" id="orden" name="orden" data-autoenviar>
            <?php foreach (['nombre' => 'Nombre', 'precio' => 'Precio', 'carbohidratos' => 'Carbohidratos', 'recientes' => 'Más recientes'] as $v => $t): ?>
              <option value="<?= $v ?>"<?= marcado($filtros['orden'] === $v, 'selected') ?>><?= e($t) ?></option>
            <?php endforeach; ?>
          </select>
        </form>
      </div>
      <?php if (!empty($filtros['desde_perfil'])): ?>
        <p class="filtros__perfil">Filtros de <strong>tu perfil</strong> aplicados automáticamente. <a href="<?= e(ruta('productos', null, ['f' => 1])) ?>">Ver todo</a></p>
      <?php elseif (!Auth::haySesion()): ?>
        <p class="filtros__perfil"><a href="<?= e(ruta('login')) ?>">Inicie sesión</a> para aplicar automáticamente sus restricciones y rangos.</p>
      <?php endif; ?>
      <?php if (!$productos): ?>
        <div class="alerta alerta--aviso">No hay productos que cumplan estos filtros.</div>
      <?php endif; ?>
      <div class="grid" style="margin-top: 1rem">
        <?php foreach ($productos as $p): ?>
          <article class="tarjeta tarjeta--enlace">
            <div class="tarjeta__imagen" aria-hidden="true">🛒</div>
            <div class="tarjeta__cuerpo">
              <h3 class="tarjeta__titulo"><a href="<?= e(ruta('producto', (int) $p['id'])) ?>"><?= e($p['nombre']) ?></a></h3>
              <p class="tarjeta__meta"><?= e($p['marca']) ?> · <?= e($nombresCat[$p['categoria_id']] ?? '') ?></p>
              <div class="etiquetas">
                <?php foreach (\App\Models\Producto::etiquetas($p) as $et): ?><span class="etiqueta <?= e($et['clase']) ?>"><?= e($et['texto']) ?></span><?php endforeach; ?>
              </div>
              <p class="tarjeta__meta">Por porción (<?= e(gramos($p['porcion_g'])) ?>): <?= e(gramos($p['carbohidratos'])) ?> carbohidratos · <?= e(gramos($p['azucares'])) ?> azúcares</p>
              <?php if ($p['contiene']): ?>
                <p class="tarjeta__alergenos">Contiene: <?= e(implode(', ', array_map(fn($c) => mb_strtolower(str_replace('Sin ', '', $nombresRestr[$c] ?? $c)), $p['contiene']))) ?></p>
              <?php endif; ?>
              <div class="tarjeta__pie">
                <span class="tarjeta__precio"><?= e(colones($p['precio'])) ?></span>
                <a class="btn btn--primario btn--sm" href="<?= e(ruta('producto', (int) $p['id'])) ?>">Ver detalle</a>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </section>
  </div>
</div>
