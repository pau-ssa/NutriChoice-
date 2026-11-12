<?php use App\Models\Aporte; ?>
<div class="contenedor">
  <h1>Mis aportes</h1>
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
    <section>
      <p class="texto-suave">Lo que has enviado a la comunidad y su estado de moderación.</p>
      <?php if (!$aportes): ?>
        <div class="alerta alerta--info">Aún no has enviado aportes. ¡Tu experiencia ayuda a otras personas!</div>
      <?php else: ?>
        <div class="tabla-contenedor">
          <table class="tabla">
            <thead><tr><th scope="col">Fecha</th><th scope="col">Tipo</th><th scope="col">Aporte</th><th scope="col">Estado</th><th scope="col">Motivo</th></tr></thead>
            <tbody>
              <?php foreach ($aportes as $a): ?>
                <tr>
                  <td><?= e(fecha_corta($a['fecha'])) ?></td>
                  <td><?= e(Aporte::TIPOS[$a['tipo']]) ?></td>
                  <td><?= e($a['titulo']) ?></td>
                  <td><span class="etiqueta etiqueta--<?= e($a['estado']) ?>"><?= e(Aporte::ESTADOS[$a['estado']]) ?></span></td>
                  <td><?= e($a['motivo'] ?: '—') ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </section>
  </div>
</div>
