<?php
use App\Core\Auth;
$aTexto = fn(array $claves) => implode(', ', array_map(fn($c) => mb_strtolower(str_replace('Sin ', '', $nombresRestr[$c] ?? $c)), $claves));
$filasNutricion = ['calorias' => ['Calorías', 'kcal'], 'carbohidratos' => ['Carbohidratos', 'g'], 'azucares' => ['Azúcares', 'g'],
    'fibra' => ['Fibra', 'g'], 'proteina' => ['Proteína', 'g'], 'grasa' => ['Grasa total', 'g'], 'sodio' => ['Sodio', 'mg']];
?>
<div class="contenedor">
  <p class="migas"><a href="<?= e(ruta('productos')) ?>">Productos</a> › <?= e($p['nombre']) ?></p>
  <div class="detalle">
    <div class="detalle__principal">
      <div>
        <h1 style="margin-bottom: .25rem"><?= e($p['nombre']) ?></h1>
        <p class="texto-suave"><?= e($p['marca']) ?> · <?= e($categoria) ?> · <span class="tarjeta__precio"><?= e(colones($p['precio'])) ?></span></p>
        <div class="etiquetas">
          <?php foreach ($etiquetas as $et): ?><span class="etiqueta <?= e($et['clase']) ?>"><?= e($et['texto']) ?></span><?php endforeach; ?>
        </div>
      </div>
      <?php if (!$aptitud['evaluado']): ?>
        <div class="alerta alerta--aviso"><a href="<?= e(ruta('login')) ?>">Inicie sesión</a> para saber si es apto según su perfil.</div>
      <?php elseif ($aptitud['apto']): ?>
        <div class="alerta alerta--exito"><strong>✓ Apto para tu perfil.</strong>
          <?php if ($aptitud['trazas']): ?>Atención: puede contener trazas de <?= e($aTexto($aptitud['trazas'])) ?>.<?php endif; ?></div>
      <?php else: ?>
        <div class="alerta alerta--error" role="alert"><strong>✕ No coincide con tu perfil.</strong>
          <?php if ($aptitud['conflictos']): ?>Contiene: <?= e($aTexto($aptitud['conflictos'])) ?>.<?php endif; ?>
          <?php if ($aptitud['excede']): ?>Excede tu rango de <?= e(implode(' y ', $aptitud['excede'])) ?> por porción.<?php endif; ?>
          <?php if ($aptitud['trazas']): ?>Además, puede contener trazas de <?= e($aTexto($aptitud['trazas'])) ?>.<?php endif; ?></div>
      <?php endif; ?>
      <section class="panel">
        <h2>Ingredientes</h2>
        <p><?= e($p['ingredientes']) ?></p>
        <h3>Alérgenos</h3>
        <ul class="lista-simple">
          <li><strong>Contiene:</strong> <?= e($p['contiene'] ? $aTexto($p['contiene']) : 'ninguno de los alérgenos registrados') ?></li>
          <li><strong>Puede contener trazas de:</strong> <?= e($p['trazas'] ? $aTexto($p['trazas']) : 'no declara') ?></li>
        </ul>
        <div class="acciones" style="margin-top: 1rem">
          <?php if (Auth::haySesion()): ?>
            <form method="post" action="<?= e(ruta('favorito_alternar')) ?>">
              <?= csrf_campo() ?>
              <input type="hidden" name="tipo" value="producto"><input type="hidden" name="ref_id" value="<?= (int) $p['id'] ?>">
              <button type="submit" class="btn <?= $favorito ? 'btn--acento' : 'btn--secundario' ?>" aria-pressed="<?= $favorito ? 'true' : 'false' ?>"><?= $favorito ? '★ En favoritos' : '☆ Agregar a favoritos' ?></button>
            </form>
            <form method="post" action="<?= e(ruta('compras_agregar')) ?>">
              <?= csrf_campo() ?>
              <input type="hidden" name="tipo" value="producto"><input type="hidden" name="ref_id" value="<?= (int) $p['id'] ?>">
              <button type="submit" class="btn btn--secundario">+ Lista de compras</button>
            </form>
          <?php else: ?>
            <a class="btn btn--secundario" href="<?= e(ruta('login')) ?>">☆ Inicie sesión para guardar</a>
          <?php endif; ?>
          <a href="<?= e(ruta('reportar', (int) $p['id'], ['entidad' => 'producto'])) ?>">Reportar error</a>
        </div>
      </section>
      <section class="panel">
        <h2>Dónde comprarlo</h2>
        <ul class="lista-simple">
          <?php foreach ($tiendas as $t): ?>
            <li><strong><?= e($t['nombre']) ?></strong> <span class="texto-suave">· <?= e($t['direccion']) ?>, <?= e($t['provincia']) ?> · Tel. <?= e($t['telefono']) ?></span></li>
          <?php endforeach; ?>
        </ul>
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
            <input type="hidden" name="tipo" value="producto"><input type="hidden" name="ref_id" value="<?= (int) $p['id'] ?>">
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
      <section class="nutricion" data-calculadora data-porcion="<?= e($p['porcion_g']) ?>">
        <h2 class="nutricion__titulo">Información nutricional</h2>
        <div class="nutricion__fila nutricion__fila--destacada"><span>Tamaño de porción</span><span><?= e(gramos($p['porcion_g'])) ?></span></div>
        <div class="nutricion__calculo">
          <label for="cantidad-consumo">Calcular para</label>
          <input class="campo__control" id="cantidad-consumo" type="number" min="1" max="2000" step="1" value="<?= e($p['porcion_g']) ?>" data-cantidad>
          <span>g/ml</span>
        </div>
        <?php foreach ($filasNutricion as $campo => [$nombre, $unidad]): ?>
          <div class="nutricion__fila<?= in_array($campo, ['calorias', 'carbohidratos', 'azucares'], true) ? ' nutricion__fila--destacada' : '' ?>">
            <span><?= e($nombre) ?></span>
            <span data-valor="<?= e($p[$campo]) ?>" data-unidad="<?= e($unidad) ?>"><?= e(gramos($p[$campo], $unidad)) ?></span>
          </div>
        <?php endforeach; ?>
        <p class="campo__ayuda" style="margin: .5rem 0 0">Por 100 g: <?= e(gramos(\App\Models\Producto::por100($p['carbohidratos'], $p['porcion_g']))) ?> carbohidratos, <?= e(gramos(\App\Models\Producto::por100($p['azucares'], $p['porcion_g']))) ?> azúcares.</p>
      </section>
      <?php if (Auth::haySesion()): ?>
        <a class="btn btn--secundario btn--bloque" href="<?= e(ruta('producto_sugerir', (int) $p['id'])) ?>">Proponer una corrección</a>
      <?php endif; ?>
      <p class="texto-suave">Verifique siempre la etiqueta: los fabricantes pueden cambiar la fórmula.</p>
    </aside>
  </div>
</div>
