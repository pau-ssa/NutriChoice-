<?php $fijo = $datos['ref_id'] > 0; ?>
<div class="contenedor">
  <h1>Reportar error</h1>
  <div class="lateral-layout">
    <aside class="lateral">
      <ul class="lateral__lista">
        <?= sub_link($sub, 'aportes', ruta('aportes'), 'Mis aportes') ?>
        <?= sub_link($sub, 'reportar', ruta('reportar'), 'Reportar error') ?>
        <?= sub_link($sub, 'x', ruta('producto_sugerir'), 'Sugerir producto') ?>
        <?= sub_link($sub, 'x', ruta('receta_publicar'), 'Publicar receta') ?>
        <?= sub_link($sub, 'x', ruta('restaurante_sugerir'), 'Sugerir restaurante') ?>
      </ul>
    </aside>
    <form class="panel formulario" method="post" action="<?= e(ruta('reportar_enviar')) ?>" data-validar novalidate>
      <?= csrf_campo() ?>
      <?php if ($errores): ?>
        <div class="alerta alerta--error" role="alert"><?php foreach ($errores as $error): ?><?= e($error) ?><br><?php endforeach; ?></div>
      <?php endif; ?>
      <p class="texto-suave">Avísanos de datos incorrectos en un producto, receta o restaurante.</p>
      <input type="hidden" name="ref_id" value="<?= (int) $datos['ref_id'] ?>">
      <?php if ($fijo): ?>
        <input type="hidden" name="entidad" value="<?= e($datos['entidad']) ?>">
        <input type="hidden" name="nombre" value="<?= e($datos['nombre']) ?>">
        <div class="alerta alerta--info">Reportando: <strong><?= e($datos['nombre']) ?></strong> (<?= e($datos['entidad']) ?>)</div>
      <?php else: ?>
        <div class="formulario__fila">
          <div class="campo">
            <label class="campo__etiqueta" for="entidad">¿Qué desea reportar?</label>
            <select class="campo__control" id="entidad" name="entidad" required>
              <option value="">Seleccione…</option>
              <?php foreach (['producto' => 'Producto', 'receta' => 'Receta', 'restaurante' => 'Restaurante'] as $v => $t): ?>
                <option value="<?= $v ?>"<?= marcado($datos['entidad'] === $v, 'selected') ?>><?= $t ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="campo">
            <label class="campo__etiqueta" for="nombre">Nombre</label>
            <input class="campo__control" id="nombre" name="nombre" required maxlength="150" value="<?= e($datos['nombre']) ?>">
          </div>
        </div>
      <?php endif; ?>
      <div class="campo">
        <label class="campo__etiqueta" for="descripcion">Describa el error</label>
        <textarea class="campo__control" id="descripcion" name="descripcion" required minlength="10" maxlength="1000" data-contador
          placeholder="Ej.: la etiqueta actual indica que contiene trazas de maní."><?= e($datos['descripcion']) ?></textarea>
      </div>
      <div class="formulario__acciones">
        <a class="btn btn--secundario" href="<?= e(ruta('aportes')) ?>">Cancelar</a>
        <button type="submit" class="btn btn--primario">Enviar reporte</button>
      </div>
    </form>
  </div>
</div>
