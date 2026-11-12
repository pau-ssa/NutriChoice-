<?php
use App\Core\Auth;
$restrPerfil = $perfil['restricciones'] ?? [];
$aTexto = fn(array $claves) => implode(', ', array_map(fn($c) => mb_strtolower(str_replace('Sin ', '', $nombresRestr[$c] ?? $c)), $claves));
$filasNutricion = ['calorias' => ['Calorías', 'kcal'], 'carbohidratos' => ['Carbohidratos', 'g'], 'azucares' => ['Azúcares', 'g'],
    'fibra' => ['Fibra', 'g'], 'proteina' => ['Proteína', 'g'], 'grasa' => ['Grasa total', 'g']];
?>
<div class="contenedor">
  <p class="migas"><a href="<?= e(ruta('recetas')) ?>">Recetas</a> › <?= e($r['titulo']) ?></p>
  <div class="detalle">
    <div class="detalle__principal">
      <div>
        <h1 style="margin-bottom: .25rem"><?= e($r['titulo']) ?></h1>
        <p class="texto-suave"><?= e($categoria) ?> · <?= (int) $r['tiempo_min'] ?> min · <?= (int) $r['porciones'] ?> porciones · Dificultad <?= e(mb_strtolower($r['dificultad'])) ?></p>
        <p><?= e($r['descripcion']) ?></p>
        <div class="etiquetas">
          <?php foreach ($etiquetas as $et): ?><span class="etiqueta <?= e($et['clase']) ?>"><?= e($et['texto']) ?></span><?php endforeach; ?>
        </div>
      </div>
      <?php if (!$aptitud['evaluado']): ?>
        <div class="alerta alerta--aviso"><a href="<?= e(ruta('login')) ?>">Inicie sesión</a> para saber si es apta según su perfil.</div>
      <?php elseif ($aptitud['apto']): ?>
        <div class="alerta alerta--exito"><strong>✓ Apta para tu perfil.</strong></div>
      <?php else: ?>
        <div class="alerta alerta--error" role="alert"><strong>✕ No coincide con tu perfil.</strong>
          <?php if ($aptitud['conflictos']): ?>Contiene: <?= e($aTexto($aptitud['conflictos'])) ?>.<?php endif; ?>
          <?php if ($aptitud['excede']): ?>Excede tu rango de <?= e(implode(' y ', $aptitud['excede'])) ?> por porción.<?php endif; ?>
          Revisa las sustituciones sugeridas.</div>
      <?php endif; ?>
      <section class="panel">
        <h2>Ingredientes</h2>
        <ul class="lista-simple">
          <?php foreach ($r['lista'] as $ing): $choca = array_intersect($restrPerfil, $ing['contiene']); ?>
            <li class="<?= $choca ? 'ingrediente--alerta' : '' ?>"><?= e($ing['nombre']) ?> <span class="texto-suave">— <?= e($ing['cantidad']) ?></span>
              <?php if ($choca): ?> · no apto para tu perfil<?php endif; ?></li>
          <?php endforeach; ?>
        </ul>
        <div class="acciones" style="margin-top: 1rem">
          <?php if (Auth::haySesion()): ?>
            <form method="post" action="<?= e(ruta('favorito_alternar')) ?>">
              <?= csrf_campo() ?>
              <input type="hidden" name="tipo" value="receta"><input type="hidden" name="ref_id" value="<?= (int) $r['id'] ?>">
              <button type="submit" class="btn <?= $favorito ? 'btn--acento' : 'btn--secundario' ?>" aria-pressed="<?= $favorito ? 'true' : 'false' ?>"><?= $favorito ? '★ En favoritos' : '☆ Agregar a favoritos' ?></button>
            </form>
            <form method="post" action="<?= e(ruta('compras_agregar')) ?>">
              <?= csrf_campo() ?>
              <input type="hidden" name="tipo" value="receta"><input type="hidden" name="ref_id" value="<?= (int) $r['id'] ?>">
              <button type="submit" class="btn btn--secundario">+ Ingredientes a mi lista</button>
            </form>
          <?php else: ?>
            <a class="btn btn--secundario" href="<?= e(ruta('login')) ?>">☆ Inicie sesión para guardar</a>
          <?php endif; ?>
          <a href="<?= e(ruta('reportar', (int) $r['id'], ['entidad' => 'receta'])) ?>">Reportar error</a>
        </div>
      </section>
      <section class="panel">
        <h2>Preparación</h2>
        <ol class="pasos">
          <?php foreach ($r['pasos'] as $paso): ?><li><?= e($paso) ?></li><?php endforeach; ?>
        </ol>
      </section>
      <?php if ($r['sustituciones']): ?>
        <section>
          <h2>Sustituciones sugeridas</h2>
          <?php foreach ($r['sustituciones'] as $s): ?>
            <div class="sustitucion">
              <span class="sustitucion__original"><?= e($s['original']) ?></span>
              <span class="sustitucion__flecha" aria-hidden="true">→</span>
              <span><strong><?= e($s['sustituto']) ?></strong><?php if ($s['motivo'] !== ''): ?><br><span class="texto-suave"><?= e($s['motivo']) ?></span><?php endif; ?></span>
            </div>
          <?php endforeach; ?>
        </section>
      <?php endif; ?>
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
            <input type="hidden" name="tipo" value="receta"><input type="hidden" name="ref_id" value="<?= (int) $r['id'] ?>">
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
      <section class="nutricion">
        <h2 class="nutricion__titulo">Información nutricional</h2>
        <div class="nutricion__fila nutricion__fila--destacada"><span>Por porción</span><span>1 de <?= (int) $r['porciones'] ?></span></div>
        <?php foreach ($filasNutricion as $campo => [$nombre, $unidad]): ?>
          <div class="nutricion__fila<?= in_array($campo, ['calorias', 'carbohidratos', 'azucares'], true) ? ' nutricion__fila--destacada' : '' ?>">
            <span><?= e($nombre) ?></span><span><?= e(gramos($r['porcion'][$campo], $unidad)) ?></span>
          </div>
        <?php endforeach; ?>
        <p class="campo__ayuda" style="margin: .5rem 0 0">Cálculo: total de la receta (<?= e(gramos($r['carbohidratos'])) ?> de carbohidratos) ÷ <?= (int) $r['porciones'] ?> porciones.</p>
      </section>
      <p class="texto-suave">Publicada por <?= e($autor) ?> · <?= e(fecha_corta($r['creado'])) ?></p>
    </aside>
  </div>
</div>
