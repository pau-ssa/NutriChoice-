<?php use App\Core\Auth; ?>
<div class="contenedor">
  <p class="migas"><a href="<?= e(ruta('restaurantes')) ?>">Restaurantes</a> › <?= e($r['nombre']) ?></p>
  <div class="detalle">
    <div class="detalle__principal">
      <div>
        <h1 style="margin-bottom: .25rem"><?= e($r['nombre']) ?></h1>
        <p class="texto-suave"><?= e($r['tipo_comida']) ?> · <?= e($r['provincia']) ?></p>
        <?php if ($r['descripcion'] !== ''): ?><p><?= e($r['descripcion']) ?></p><?php endif; ?>
        <div class="etiquetas">
          <span class="etiqueta etiqueta--<?= $r['seguridad'] === 'cocina_separada' ? 'cocina-separada' : 'opciones-adaptadas' ?>"><?= $r['seguridad'] === 'cocina_separada' ? 'Cocina separada' : 'Solo opciones adaptadas' ?></span>
          <span class="etiqueta etiqueta--nutricional"><?= $r['info_nutricional'] ? 'Ofrece información nutricional' : 'Sin información nutricional' ?></span>
        </div>
      </div>
      <?php if ($perfil && $perfil['restricciones']): ?>
        <?php if (!$faltan): ?>
          <div class="alerta alerta--exito"><strong>✓ Atiende todas tus restricciones.</strong> Confirma siempre con el personal al ordenar.</div>
        <?php else: ?>
          <div class="alerta alerta--error" role="alert"><strong>✕ No declara atender:</strong> <?= e(implode(', ', array_map(fn($c) => $nombresRestr[$c] ?? $c, $faltan))) ?>.</div>
        <?php endif; ?>
      <?php endif; ?>
      <section class="panel">
        <h2>Opciones aptas</h2>
        <p class="texto-suave"><?= $r['seguridad'] === 'cocina_separada' ? 'Preparación en área exclusiva: menor riesgo de contacto cruzado.' : 'Cocina compartida: consulte por contacto cruzado.' ?></p>
        <div class="etiquetas" style="margin-bottom: 1rem">
          <?php foreach ($r['restricciones'] as $c): ?><span class="etiqueta"><?= e($nombresRestr[$c] ?? $c) ?></span><?php endforeach; ?>
        </div>
        <?php if ($r['opciones']): ?>
          <ul><?php foreach ($r['opciones'] as $o): ?><li><?= e($o) ?></li><?php endforeach; ?></ul>
        <?php endif; ?>
        <div class="acciones" style="margin-top: 1rem">
          <?php if (Auth::haySesion()): ?>
            <form method="post" action="<?= e(ruta('favorito_alternar')) ?>">
              <?= csrf_campo() ?>
              <input type="hidden" name="tipo" value="restaurante"><input type="hidden" name="ref_id" value="<?= (int) $r['id'] ?>">
              <button type="submit" class="btn <?= $favorito ? 'btn--acento' : 'btn--secundario' ?>" aria-pressed="<?= $favorito ? 'true' : 'false' ?>"><?= $favorito ? '★ En favoritos' : '☆ Agregar a favoritos' ?></button>
            </form>
          <?php else: ?>
            <a class="btn btn--secundario" href="<?= e(ruta('login')) ?>">☆ Inicie sesión para guardar</a>
          <?php endif; ?>
          <a href="<?= e(ruta('reportar', (int) $r['id'], ['entidad' => 'restaurante'])) ?>">Reportar error</a>
        </div>
      </section>
      <section id="resenas" class="panel">
        <div class="seccion__encabezado">
          <h2>Reseñas</h2>
          <span><span class="calificacion" aria-hidden="true"><?= e(estrellas($resumen['promedio'])) ?></span>
            <strong><?= e(number_format($resumen['promedio'], 1, ',', '')) ?></strong> <span class="texto-suave">(<?= (int) $resumen['total'] ?>)</span></span>
        </div>
        <?php if (!$resenas): ?><p class="texto-suave">Aún no hay reseñas. ¡Sé la primera persona en opinar!</p><?php endif; ?>
        <?php foreach ($resenas as $re): ?>
          <article class="resena">
            <div class="resena__encabezado">
              <span><span class="resena__autor"><?= e($re['autor']) ?></span> <span class="calificacion" aria-label="<?= (int) $re['puntaje'] ?> de 5"><?= e(estrellas((float) $re['puntaje'])) ?></span></span>
              <span class="resena__fecha"><?= e(fecha_corta($re['fecha'])) ?></span>
            </div>
            <p><?= e($re['comentario']) ?></p>
          </article>
        <?php endforeach; ?>
        <?php if (Auth::haySesion()): ?>
          <form class="formulario" style="margin-top: 1rem" method="post" action="<?= e(ruta('resena_guardar')) ?>" data-validar>
            <?= csrf_campo() ?>
            <input type="hidden" name="tipo" value="restaurante"><input type="hidden" name="ref_id" value="<?= (int) $r['id'] ?>">
            <fieldset class="estrellas-entrada">
              <legend class="campo__etiqueta">Tu calificación</legend>
              <?php for ($i = 5; $i >= 1; $i--): ?>
                <input type="radio" id="estrella-<?= $i ?>" name="puntaje" value="<?= $i ?>" required>
                <label for="estrella-<?= $i ?>" title="<?= $i ?> de 5">★<span class="sr-only"><?= $i ?> de 5</span></label>
              <?php endfor; ?>
            </fieldset>
            <div class="campo">
              <label class="campo__etiqueta" for="comentario">Comentario</label>
              <textarea class="campo__control" id="comentario" name="comentario" minlength="5" maxlength="600" required data-contador></textarea>
            </div>
            <div class="formulario__acciones"><button type="submit" class="btn btn--primario">Publicar reseña</button></div>
          </form>
        <?php else: ?>
          <p><a href="<?= e(ruta('login')) ?>">Inicie sesión</a> para calificar y comentar.</p>
        <?php endif; ?>
      </section>
    </div>
    <aside class="detalle__lateral">
      <section class="panel">
        <h2>Ubicación y contacto</h2>
        <ul class="lista-simple">
          <li><strong>Dirección:</strong> <?= e($r['direccion']) ?>, <?= e($r['provincia']) ?></li>
          <li><strong>Teléfono:</strong> <a href="tel:<?= e(preg_replace('/\D/', '', $r['telefono'])) ?>"><?= e($r['telefono']) ?></a></li>
          <?php if ($r['horario'] !== ''): ?><li><strong>Horario:</strong> <?= e($r['horario']) ?></li><?php endif; ?>
        </ul>
      </section>
      <?php if (Auth::haySesion()): ?>
        <a class="btn btn--secundario btn--bloque" href="<?= e(ruta('restaurante_sugerir', (int) $r['id'])) ?>">Proponer actualización de datos</a>
      <?php endif; ?>
    </aside>
  </div>
</div>
