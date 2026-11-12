<div class="contenedor">
  <h1>Mi perfil nutricional</h1>
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
    <form class="panel formulario" method="post" action="<?= e(ruta('nutricional_guardar')) ?>" data-validar novalidate>
      <?= csrf_campo() ?>
      <?php if ($errores): ?>
        <div class="alerta alerta--error" role="alert"><?php foreach ($errores as $error): ?><?= e($error) ?><br><?php endforeach; ?></div>
      <?php endif; ?>
      <p class="texto-suave">Rangos personales de carbohidratos y azúcares por porción. Deje un campo vacío para no aplicar ese límite.</p>
      <div class="formulario__fila">
        <div class="campo">
          <label class="campo__etiqueta" for="carb_max">Máximo de carbohidratos (g)</label>
          <div class="filtros__rango">
            <input type="range" min="0" max="100" step="1" value="<?= e($u['carb_max'] ?? 30) ?>" data-sincroniza="carb_max" aria-label="Ajustar carbohidratos">
            <input class="campo__control" style="width: 6rem" id="carb_max" name="carb_max" type="number" min="0" max="200" step="0.5" value="<?= e($u['carb_max']) ?>">
          </div>
        </div>
        <div class="campo">
          <label class="campo__etiqueta" for="azucar_max">Máximo de azúcares (g)</label>
          <div class="filtros__rango">
            <input type="range" min="0" max="50" step="1" value="<?= e($u['azucar_max'] ?? 6) ?>" data-sincroniza="azucar_max" aria-label="Ajustar azúcares">
            <input class="campo__control" style="width: 6rem" id="azucar_max" name="azucar_max" type="number" min="0" max="200" step="0.5" value="<?= e($u['azucar_max']) ?>">
          </div>
        </div>
      </div>
      <p class="campo__ayuda">Defina estos valores con su profesional de salud.</p>
      <div class="formulario__acciones"><button type="submit" class="btn btn--primario">Guardar rangos</button></div>
    </form>
  </div>
</div>
