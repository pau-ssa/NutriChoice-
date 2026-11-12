<div class="contenedor">
  <h1>Mis restricciones</h1>
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
    <form class="panel formulario" method="post" action="<?= e(ruta('restricciones_guardar')) ?>">
      <?= csrf_campo() ?>
      <div class="campo">
        <span class="campo__etiqueta">Tipo de condición</span>
        <div class="opciones-grid">
          <?php foreach (\App\Models\Usuario::CONDICIONES as $valor => $texto): ?>
            <label class="opcion"><input type="radio" name="condicion" value="<?= e($valor) ?>"<?= marcado($u['condicion'] === $valor) ?>> <?= e($texto) ?></label>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="campo">
        <span class="campo__etiqueta">Restricciones por exclusión</span>
        <div class="opciones-grid">
          <?php foreach ($restricciones as $r): ?>
            <label class="opcion" title="<?= e($r['descripcion']) ?>"><input type="checkbox" name="restricciones[]" value="<?= e($r['clave']) ?>"<?= marcado(in_array($r['clave'], $u['restricciones'], true)) ?>> <?= e($r['nombre']) ?></label>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="formulario__acciones"><button type="submit" class="btn btn--primario">Guardar restricciones</button></div>
    </form>
  </div>
</div>
