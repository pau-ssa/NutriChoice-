<?php
/** Formulario de producto: lo usan "Sugerir producto" (comunidad) y Administración. */
$nutrientes = ['porcion_g' => 'Porción (g/ml)', 'calorias' => 'Calorías (kcal)', 'carbohidratos' => 'Carbohidratos (g)',
    'azucares' => 'Azúcares (g)', 'fibra' => 'Fibra (g)', 'proteina' => 'Proteína (g)', 'grasa' => 'Grasa (g)', 'sodio' => 'Sodio (mg)'];
?>
<div class="contenedor">
  <h1><?= e($encabezado) ?></h1>
  <?php if ($aviso !== ''): ?><div class="alerta alerta--info"><?= e($aviso) ?></div><?php endif; ?>
  <?php if ($errores): ?>
    <div class="alerta alerta--error" role="alert"><strong>Revise los siguientes datos:</strong>
      <ul><?php foreach ($errores as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div>
  <?php endif; ?>
  <form class="panel formulario" method="post" action="<?= e(ruta($accionForm)) ?>" data-validar novalidate>
    <?= csrf_campo() ?>
    <?php foreach ($ocultos as $nombre => $valor): ?><input type="hidden" name="<?= e($nombre) ?>" value="<?= e($valor) ?>"><?php endforeach; ?>
    <h2>Datos del producto</h2>
    <div class="formulario__fila">
      <div class="campo">
        <label class="campo__etiqueta" for="nombre">Nombre *</label>
        <input class="campo__control" id="nombre" name="nombre" required maxlength="150" value="<?= e($datos['nombre']) ?>">
      </div>
      <div class="campo">
        <label class="campo__etiqueta" for="marca">Marca *</label>
        <input class="campo__control" id="marca" name="marca" required maxlength="100" value="<?= e($datos['marca']) ?>">
      </div>
    </div>
    <div class="formulario__fila">
      <div class="campo">
        <label class="campo__etiqueta" for="categoria_id">Categoría *</label>
        <select class="campo__control" id="categoria_id" name="categoria_id" required>
          <option value="">Seleccione…</option>
          <?php foreach ($categorias as $c): ?>
            <option value="<?= (int) $c['id'] ?>"<?= marcado((int) $datos['categoria_id'] === (int) $c['id'], 'selected') ?>><?= e($c['nombre']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="campo">
        <label class="campo__etiqueta" for="precio">Precio aproximado (₡) *</label>
        <input class="campo__control" id="precio" name="precio" type="number" min="0" step="5" required value="<?= e($datos['precio']) ?>">
      </div>
    </div>
    <div class="campo">
      <label class="campo__etiqueta" for="ingredientes">Ingredientes (como aparecen en la etiqueta) *</label>
      <textarea class="campo__control" id="ingredientes" name="ingredientes" required maxlength="2000" data-contador><?= e($datos['ingredientes']) ?></textarea>
    </div>

    <h2>Información nutricional por porción</h2>
    <div class="formulario__fila">
      <?php foreach ($nutrientes as $campo => $etiqueta): ?>
        <div class="campo">
          <label class="campo__etiqueta" for="<?= $campo ?>"><?= e($etiqueta) ?> *</label>
          <input class="campo__control" id="<?= $campo ?>" name="<?= $campo ?>" type="number" min="0" step="0.1" required value="<?= e($datos[$campo]) ?>"<?= $campo === 'azucares' ? ' data-max-de="carbohidratos"' : '' ?>>
        </div>
      <?php endforeach; ?>
    </div>
    <p class="campo__ayuda">Las etiquetas «sin gluten», «bajo en azúcar», etc. se calculan automáticamente a partir de estos datos.</p>

    <h2>Alérgenos y lugares de compra</h2>
    <?php foreach (['contiene' => 'Contiene', 'trazas' => 'Puede contener trazas de'] as $grupo => $titulo): ?>
      <div class="campo">
        <span class="campo__etiqueta"><?= e($titulo) ?></span>
        <div class="chips">
          <?php foreach ($restricciones as $r): $sel = in_array($r['clave'], $datos[$grupo], true); ?>
            <label class="chip<?= $sel ? ' chip--activo' : '' ?>"><input class="sr-only" type="checkbox" name="<?= $grupo ?>[]" value="<?= e($r['clave']) ?>"<?= marcado($sel) ?>> <?= e(str_replace('Sin ', '', $r['nombre'])) ?></label>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>
    <div class="campo">
      <span class="campo__etiqueta">Dónde se compra *</span>
      <div class="opciones-grid">
        <?php foreach ($tiendas as $tid => $tnombre): ?>
          <label class="opcion"><input type="checkbox" name="tiendas[]" value="<?= (int) $tid ?>"<?= marcado(in_array((int) $tid, array_map('intval', $datos['tiendas']), true)) ?>> <?= e($tnombre) ?></label>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="formulario__acciones">
      <a class="btn btn--secundario" href="<?= e($volver) ?>">Cancelar</a>
      <button type="submit" class="btn btn--primario"><?= e($boton) ?></button>
    </div>
  </form>
</div>
