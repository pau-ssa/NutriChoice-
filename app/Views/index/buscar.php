<?php $total = count($productos) + count($recetas) + count($restaurantes); ?>
<div class="contenedor">
  <section class="seccion">
    <div class="seccion__encabezado">
      <h1>Resultados de búsqueda</h1>
      <span class="texto-suave"><?= $q === '' ? 'Escriba un término para buscar.' : e($total . ' resultado(s) para «' . $q . '»') ?></span>
    </div>
    <form class="buscador" style="margin: 0 0 1.5rem" action="index.php" method="get" role="search">
      <input type="hidden" name="accion" value="buscar">
      <label for="q-buscar" class="sr-only">Buscar</label>
      <input id="q-buscar" class="campo__control" name="q" type="search" maxlength="100" value="<?= e($q) ?>" required>
      <button type="submit" class="btn btn--primario">Buscar</button>
    </form>
    <div class="grid">
      <?php foreach (['Productos' => [$productos, 'producto', 'nombre'], 'Recetas' => [$recetas, 'receta', 'titulo'],
          'Restaurantes' => [$restaurantes, 'restaurante', 'nombre']] as $titulo => [$lista, $accion, $campo]): ?>
        <div class="panel">
          <h2><?= e($titulo) ?> (<?= count($lista) ?>)</h2>
          <ul class="lista-simple">
            <?php foreach ($lista as $item): ?>
              <li><a href="<?= e(ruta($accion, (int) $item['id'])) ?>"><?= e($item[$campo]) ?></a></li>
            <?php endforeach; ?>
            <?php if (!$lista): ?><li class="texto-suave">Sin coincidencias.</li><?php endif; ?>
          </ul>
        </div>
      <?php endforeach; ?>
    </div>
  </section>
</div>
