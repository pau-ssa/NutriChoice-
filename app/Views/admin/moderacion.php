<?php
use App\Core\Auth;
use App\Models\Aporte;
// Formulario de edición de cada tipo de propuesta (la propuesta vive en su tabla con publicado = 0).
$editar = ['producto' => ['tipo' => 'productos'], 'receta' => ['tipo' => 'recetas'], 'restaurante' => ['tipo' => 'establecimientos', 'subtipo' => 'restaurante']];
?>
<div class="contenedor">
  <h1>Moderación</h1>
  <p class="texto-suave">Revise los aportes de la comunidad antes de publicarlos. Los aportes propios los resuelve otra persona.</p>
  <div class="seccion__encabezado">
    <h2>Moderar aportes</h2>
    <div class="chips">
      <?php foreach (Aporte::ESTADOS + ['todos' => 'Todos'] as $v => $t): ?>
        <a class="chip<?= $estado === $v ? ' chip--activo' : '' ?>" href="<?= e(ruta('moderacion', null, ['estado' => $v])) ?>"><?= e($t) ?></a>
      <?php endforeach; ?>
    </div>
  </div>
  <?php if (!$aportes): ?><div class="alerta alerta--info">No hay aportes en este estado.</div><?php else: ?>
  <div class="tabla-contenedor">
    <table class="tabla">
      <thead><tr><th scope="col">Aporte</th><th scope="col">Enviado por</th><th scope="col">Estado</th><th scope="col">Acciones</th></tr></thead>
      <tbody>
        <?php foreach ($aportes as $a): ?>
          <tr>
            <td>
              <span class="etiqueta etiqueta--nutricional"><?= e(Aporte::TIPOS[$a['tipo']]) ?></span>
              <strong><?= e($a['titulo']) ?></strong>
              <?php if ($a['corrige_id']): ?><br><span class="texto-suave">Corrige el registro #<?= (int) $a['corrige_id'] ?></span><?php endif; ?>
              <?php if ($a['descripcion'] !== ''): ?><br><span class="texto-suave"><?= e($a['descripcion']) ?></span><?php endif; ?>
              <?php if ($a['motivo'] !== ''): ?><br><span class="texto-suave">Motivo: <?= e($a['motivo']) ?></span><?php endif; ?>
            </td>
            <td><?= e($a['autor']) ?><br><span class="texto-suave"><?= e(fecha_corta($a['fecha'])) ?></span></td>
            <td><span class="etiqueta etiqueta--<?= e($a['estado']) ?>"><?= e(Aporte::ESTADOS[$a['estado']]) ?></span></td>
            <td>
              <?php if ($a['estado'] === 'pendiente' && Auth::rol() !== 'admin' && (int) $a['usuario_id'] === Auth::id()): ?>
                <span class="texto-suave">Aporte propio</span>
              <?php elseif ($a['estado'] === 'pendiente'): ?>
                <div class="tabla__acciones">
                  <?php if (isset($editar[$a['tipo']])): ?>
                    <a class="btn btn--secundario btn--sm" href="<?= e(ruta('catalogo_form', null, $editar[$a['tipo']] + ['aporte' => (int) $a['id']])) ?>">Revisar / editar</a>
                  <?php elseif ($a['ref_id']): ?>
                    <a class="btn btn--secundario btn--sm" href="<?= e(ruta($a['entidad'], $a['ref_id'])) ?>" target="_blank" rel="noopener">Ver elemento</a>
                  <?php endif; ?>
                  <form method="post" action="<?= e(ruta('aporte_resolver')) ?>">
                    <?= csrf_campo() ?>
                    <input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
                    <input type="hidden" name="decision" value="aprobar">
                    <button type="submit" class="btn btn--primario btn--sm"><?= $a['tipo'] === 'reporte' ? 'Atendido' : 'Aprobar' ?></button>
                  </form>
                  <button type="button" class="btn btn--peligro btn--sm" data-abrir-modal="modal-rechazo" data-aporte="<?= (int) $a['id'] ?>" data-titulo="<?= e($a['titulo']) ?>">Rechazar</button>
                </div>
              <?php else: ?>—<?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>

<div class="modal" id="modal-rechazo" role="dialog" aria-modal="true" aria-labelledby="modal-rechazo-titulo">
  <div class="modal__contenido">
    <div class="modal__encabezado">
      <h2 id="modal-rechazo-titulo">Rechazar aporte</h2>
      <button type="button" class="modal__cerrar" data-cerrar-modal aria-label="Cerrar">×</button>
    </div>
    <p class="texto-suave" data-modal-titulo></p>
    <form class="formulario" method="post" action="<?= e(ruta('aporte_resolver')) ?>" data-validar novalidate>
      <?= csrf_campo() ?>
      <input type="hidden" name="id" value="" data-modal-id>
      <input type="hidden" name="decision" value="rechazar">
      <div class="campo">
        <label class="campo__etiqueta" for="motivo">Motivo del rechazo (lo verá la persona)</label>
        <textarea class="campo__control" id="motivo" name="motivo" required minlength="5" maxlength="300"></textarea>
      </div>
      <div class="formulario__acciones">
        <button type="button" class="btn btn--secundario" data-cerrar-modal>Cancelar</button>
        <button type="submit" class="btn btn--peligro">Confirmar rechazo</button>
      </div>
    </form>
  </div>
</div>
