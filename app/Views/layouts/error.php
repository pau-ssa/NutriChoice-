<div class="contenedor">
  <section class="seccion panel" style="max-width: 640px; margin: 0 auto; text-align: center">
    <h1><?= e($encabezado ?? 'No se pudo completar la operación') ?></h1>
    <p><?= e($mensaje ?? 'Intente nuevamente.') ?></p>
    <a class="btn btn--primario" href="<?= e(ruta()) ?>">Volver al inicio</a>
  </section>
</div>
