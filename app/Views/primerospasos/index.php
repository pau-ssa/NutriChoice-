<div class="contenedor">
  <section class="seccion">
    <h1>Primeros pasos</h1>
    <p class="texto-suave">Acompañamiento para personas con un diagnóstico reciente: reaprender a comprar, cocinar y comer fuera, paso a paso.</p>
    <h2>Guía de transición</h2>
    <div class="grid">
      <?php foreach ($guias as $clave => $g): ?>
        <article class="tarjeta tarjeta--enlace">
          <div class="tarjeta__cuerpo">
            <h3 class="tarjeta__titulo"><?= e($g['titulo']) ?></h3>
            <p class="tarjeta__meta"><?= e($g['resumen']) ?></p>
            <ul>
              <?php foreach ($g['secciones'] as $s): ?><li><?= e($s['titulo']) ?></li><?php endforeach; ?>
            </ul>
            <div class="tarjeta__pie"><a class="btn btn--primario btn--bloque" href="<?= e(ruta('guia', null, ['condicion' => $clave])) ?>">Leer la guía</a></div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </section>
  <section class="seccion panel">
    <div class="seccion__encabezado">
      <div>
        <h2>Sustituciones comunes</h2>
        <p class="texto-suave" style="margin: 0">Alternativas para harinas, lácteos y azúcares con opciones disponibles en el país.</p>
      </div>
      <a class="btn btn--acento" href="<?= e(ruta('sustituciones')) ?>">Ver sustituciones</a>
    </div>
  </section>
</div>
