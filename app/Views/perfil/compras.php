<div class="contenedor">
  <h1>Lista de compras</h1>
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
    <section class="panel">
      <div class="barra-herramientas">
        <p class="texto-suave" style="margin: 0">Productos e ingredientes de tus recetas, ya filtrados según tu perfil.</p>
        <?php if ($lista): ?>
          <div class="acciones no-imprimir">
            <form method="post" action="<?= e(ruta('compras_vaciar')) ?>">
              <?= csrf_campo() ?><input type="hidden" name="solo" value="hechos">
              <button class="btn btn--secundario btn--sm" type="submit">Quitar marcados</button>
            </form>
            <form method="post" action="<?= e(ruta('compras_vaciar')) ?>" data-confirmar="¿Vaciar toda la lista de compras?">
              <?= csrf_campo() ?>
              <button class="btn btn--peligro btn--sm" type="submit">Vaciar</button>
            </form>
            <button class="btn btn--secundario btn--sm" type="button" data-imprimir>Imprimir</button>
          </div>
        <?php endif; ?>
      </div>
      <?php if (!$lista): ?>
        <div class="alerta alerta--info">Tu lista está vacía. Agrega productos o los ingredientes de una receta desde su ficha.</div>
      <?php endif; ?>
      <div class="tabla-contenedor" style="margin-bottom: 1rem">
        <table class="tabla">
          <thead><tr><th scope="col">Comprado</th><th scope="col">Artículo</th><th scope="col">Origen</th><th scope="col" class="no-imprimir">Quitar</th></tr></thead>
          <tbody>
            <?php foreach ($lista as $item): ?>
              <tr>
                <td>
                  <form method="post" action="<?= e(ruta('compras_marcar')) ?>">
                    <?= csrf_campo() ?><input type="hidden" name="compra_id" value="<?= (int) $item['id'] ?>">
                    <button type="submit" class="btn btn--sm <?= $item['hecho'] ? 'btn--primario' : 'btn--secundario' ?>" aria-pressed="<?= $item['hecho'] ? 'true' : 'false' ?>"><?= $item['hecho'] ? '✓' : '—' ?></button>
                  </form>
                </td>
                <td><?= $item['hecho'] ? '<s>' . e($item['texto']) . '</s>' : e($item['texto']) ?></td>
                <td class="texto-suave"><?= e($item['origen']) ?></td>
                <td class="no-imprimir">
                  <form method="post" action="<?= e(ruta('compras_quitar')) ?>">
                    <?= csrf_campo() ?><input type="hidden" name="compra_id" value="<?= (int) $item['id'] ?>">
                    <button type="submit" class="btn btn--sm btn--secundario" aria-label="Quitar <?= e($item['texto']) ?>">✕</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <form class="formulario__fila no-imprimir" method="post" action="<?= e(ruta('compras_agregar')) ?>" data-validar>
        <?= csrf_campo() ?>
        <div class="campo">
          <label class="campo__etiqueta" for="texto">Agregar manualmente</label>
          <input class="campo__control" id="texto" name="texto" required maxlength="120" placeholder="Ej.: chayotes">
        </div>
        <div class="campo" style="justify-content: flex-end"><button class="btn btn--primario" type="submit">Agregar</button></div>
      </form>
    </section>
  </div>
</div>
