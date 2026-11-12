<?php
/** Formulario de receta: lo usan "Publicar receta" (comunidad) y Administración. */
$filas = $datos['ingredientes'] ?: [['ingrediente_id' => 0, 'cantidad' => '']];
$sustTexto = implode("\n", array_map(fn($s) => $s['original'] . ' = ' . $s['sustituto'] . ($s['motivo'] !== '' ? ' | ' . $s['motivo'] : ''), $datos['sustituciones']));
$nutrientes = ['calorias' => 'Calorías (kcal)', 'carbohidratos' => 'Carbohidratos (g)', 'azucares' => 'Azúcares (g)',
    'fibra' => 'Fibra (g)', 'proteina' => 'Proteína (g)', 'grasa' => 'Grasa (g)'];
?>
<div class="contenedor">
  <h1><?= e($encabezado) ?></h1>
  <?php if ($aviso !== ''): ?><div class="alerta alerta--info"><?= e($aviso) ?></div><?php endif; ?>
  <?php if ($errores): ?>
    <div class="alerta alerta--error" role="alert"><strong>Revise los siguientes datos:</strong>
      <ul><?php foreach ($errores as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div>
  <?php endif; ?>
  <form class="panel formulario" method="post" action="<?= e(ruta($accionForm)) ?>" data-validar novalidate>
    <?= csrf_campo() ?>
    <?php foreach ($ocultos as $nombre => $valor): ?><input type="hidden" name="<?= e($nombre) ?>" value="<?= e($valor) ?>"><?php endforeach; ?>
    <h2>Datos generales</h2>
    <div class="formulario__fila">
      <div class="campo">
        <label class="campo__etiqueta" for="titulo">Título *</label>
        <input class="campo__control" id="titulo" name="titulo" required maxlength="150" value="<?= e($datos['titulo']) ?>">
      </div>
      <div class="campo">
        <label class="campo__etiqueta" for="categoria_id">Tipo de plato *</label>
        <select class="campo__control" id="categoria_id" name="categoria_id" required>
          <option value="">Seleccione…</option>
          <?php foreach ($categorias as $c): ?>
            <option value="<?= (int) $c['id'] ?>"<?= marcado((int) $datos['categoria_id'] === (int) $c['id'], 'selected') ?>><?= e($c['nombre']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <div class="campo">
      <label class="campo__etiqueta" for="descripcion">Descripción breve *</label>
      <textarea class="campo__control" id="descripcion" name="descripcion" required maxlength="500" data-contador><?= e($datos['descripcion']) ?></textarea>
    </div>
    <div class="formulario__fila">
      <div class="campo">
        <label class="campo__etiqueta" for="tiempo_min">Tiempo (minutos) *</label>
        <input class="campo__control" id="tiempo_min" name="tiempo_min" type="number" min="1" max="1440" required value="<?= e($datos['tiempo_min']) ?>">
      </div>
      <div class="campo">
        <label class="campo__etiqueta" for="porciones">Porciones *</label>
        <input class="campo__control" id="porciones" name="porciones" type="number" min="1" max="100" required value="<?= e($datos['porciones']) ?>">
      </div>
      <div class="campo">
        <label class="campo__etiqueta" for="dificultad">Dificultad</label>
        <select class="campo__control" id="dificultad" name="dificultad">
          <?php foreach (['Fácil', 'Media', 'Alta'] as $d): ?><option<?= marcado($datos['dificultad'] === $d, 'selected') ?>><?= $d ?></option><?php endforeach; ?>
        </select>
      </div>
    </div>

    <h2>Ingredientes del catálogo *</h2>
    <p class="campo__ayuda">Los alérgenos de la receta se calculan con los ingredientes seleccionados.</p>
    <div class="formulario" data-filas>
      <?php foreach ($filas as $fila): ?>
        <div class="fila-ingrediente" data-fila>
          <select class="campo__control" name="ingrediente_id[]" aria-label="Ingrediente">
            <option value="0">Seleccione ingrediente…</option>
            <?php foreach ($ingredientes as $ing): ?>
              <option value="<?= (int) $ing['id'] ?>"<?= marcado((int) $fila['ingrediente_id'] === (int) $ing['id'], 'selected') ?>><?= e($ing['nombre']) ?></option>
            <?php endforeach; ?>
          </select>
          <input class="campo__control" name="cantidad[]" maxlength="60" placeholder="Cantidad (p. ej. 2 tazas)" aria-label="Cantidad" value="<?= e($fila['cantidad']) ?>">
          <button type="button" class="btn btn--secundario" data-quitar-fila aria-label="Quitar ingrediente">✕</button>
        </div>
      <?php endforeach; ?>
    </div>
    <div><button type="button" class="btn btn--secundario btn--sm" data-agregar-fila>+ Agregar ingrediente</button></div>

    <h2>Preparación y sustituciones</h2>
    <div class="campo">
      <label class="campo__etiqueta" for="pasos">Pasos (uno por línea) *</label>
      <textarea class="campo__control" id="pasos" name="pasos" required maxlength="5000"><?= e(implode("\n", $datos['pasos'])) ?></textarea>
    </div>
    <div class="campo">
      <label class="campo__etiqueta" for="sustituciones">Sustituciones sugeridas (opcional, una por línea)</label>
      <textarea class="campo__control" id="sustituciones" name="sustituciones" placeholder="Azúcar = Eritritol | Reduce los azúcares por porción"><?= e($sustTexto) ?></textarea>
      <span class="campo__ayuda">Formato: original = sustituto | motivo</span>
    </div>

    <h2>Información nutricional TOTAL de la receta</h2>
    <p class="campo__ayuda">El sistema la divide entre el número de porciones. <output data-por-porcion></output></p>
    <div class="formulario__fila">
      <?php foreach ($nutrientes as $campo => $etiqueta): ?>
        <div class="campo">
          <label class="campo__etiqueta" for="<?= $campo ?>"><?= e($etiqueta) ?> *</label>
          <input class="campo__control" id="<?= $campo ?>" name="<?= $campo ?>" type="number" min="0" step="0.1" required value="<?= e($datos[$campo]) ?>"<?= $campo === 'azucares' ? ' data-max-de="carbohidratos"' : '' ?>>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="formulario__acciones">
      <a class="btn btn--secundario" href="<?= e($volver) ?>">Cancelar</a>
      <button type="submit" class="btn btn--primario"><?= e($boton) ?></button>
    </div>
  </form>
</div>
