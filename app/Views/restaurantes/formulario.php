<?php /** Formulario de restaurante: lo usan "Sugerir restaurante" (comunidad) y Administración. */ ?>
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
    <h2>Datos del establecimiento</h2>
    <div class="formulario__fila">
      <div class="campo">
        <label class="campo__etiqueta" for="nombre">Nombre *</label>
        <input class="campo__control" id="nombre" name="nombre" required maxlength="150" value="<?= e($datos['nombre']) ?>">
      </div>
      <div class="campo">
        <label class="campo__etiqueta" for="tipo_comida">Tipo de comida *</label>
        <input class="campo__control" id="tipo_comida" name="tipo_comida" required maxlength="60" list="tipos-comida" value="<?= e($datos['tipo_comida']) ?>">
        <datalist id="tipos-comida">
          <?php foreach (['Comida típica', 'Vegetariana', 'Italiana', 'Mariscos', 'Cafetería', 'Asiática', 'Comida rápida'] as $t): ?><option value="<?= e($t) ?>"><?php endforeach; ?>
        </datalist>
      </div>
    </div>
    <div class="formulario__fila">
      <div class="campo">
        <label class="campo__etiqueta" for="provincia">Provincia *</label>
        <select class="campo__control" id="provincia" name="provincia" required>
          <option value="">Seleccione…</option>
          <?php foreach ($provincias as $pr): ?><option<?= marcado($datos['provincia'] === $pr, 'selected') ?>><?= e($pr) ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="campo">
        <label class="campo__etiqueta" for="telefono">Teléfono *</label>
        <input class="campo__control" id="telefono" name="telefono" type="tel" required maxlength="20" pattern="[0-9 +\-]{8,20}" placeholder="2222-0000" value="<?= e($datos['telefono']) ?>">
      </div>
    </div>
    <div class="campo">
      <label class="campo__etiqueta" for="direccion">Dirección *</label>
      <input class="campo__control" id="direccion" name="direccion" required maxlength="250" value="<?= e($datos['direccion']) ?>">
    </div>
    <div class="formulario__fila">
      <div class="campo">
        <label class="campo__etiqueta" for="horario">Horario</label>
        <input class="campo__control" id="horario" name="horario" maxlength="100" placeholder="L-S 8:00 a 18:00" value="<?= e($datos['horario'] ?? '') ?>">
      </div>
      <div class="campo">
        <label class="campo__etiqueta" for="descripcion">Descripción</label>
        <input class="campo__control" id="descripcion" name="descripcion" maxlength="500" value="<?= e($datos['descripcion'] ?? '') ?>">
      </div>
    </div>

    <h2>Seguridad alimentaria</h2>
    <div class="campo">
      <span class="campo__etiqueta">Nivel de seguridad *</span>
      <label class="opcion"><input type="radio" name="seguridad" value="cocina_separada" required<?= marcado($datos['seguridad'] === 'cocina_separada') ?>> Cocina separada</label>
      <label class="opcion"><input type="radio" name="seguridad" value="opciones_adaptadas"<?= marcado($datos['seguridad'] === 'opciones_adaptadas') ?>> Solo opciones adaptadas (cocina compartida)</label>
      <label class="opcion"><input type="checkbox" name="info_nutricional" value="1"<?= marcado(!empty($datos['info_nutricional'])) ?>> Ofrece información nutricional</label>
    </div>
    <div class="campo">
      <span class="campo__etiqueta">Restricciones que atiende *</span>
      <div class="chips">
        <?php foreach ($restricciones as $r): $sel = in_array($r['clave'], $datos['restricciones'], true); ?>
          <label class="chip<?= $sel ? ' chip--activo' : '' ?>"><input class="sr-only" type="checkbox" name="restricciones[]" value="<?= e($r['clave']) ?>"<?= marcado($sel) ?>> <?= e($r['nombre']) ?></label>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="campo">
      <label class="campo__etiqueta" for="opciones">Platos u opciones aptas (uno por línea)</label>
      <textarea class="campo__control" id="opciones" name="opciones"><?= e(implode("\n", $datos['opciones'])) ?></textarea>
    </div>
    <div class="formulario__acciones">
      <a class="btn btn--secundario" href="<?= e($volver) ?>">Cancelar</a>
      <button type="submit" class="btn btn--primario"><?= e($boton) ?></button>
    </div>
  </form>
</div>
