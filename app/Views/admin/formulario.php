<?php
/** Formulario de catálogos simples: ingredientes, categorías, características y supermercados/tiendas. */
use App\Controllers\AdminController;
?>
<div class="contenedor">
  <p class="migas"><a href="<?= e(ruta('admin')) ?>">Administración</a> › <a href="<?= e(ruta('catalogo', null, ['tipo' => $tipo])) ?>"><?= e(AdminController::CATALOGOS[$tipo]) ?></a> › <?= $id ? 'Editar' : 'Nuevo' ?></p>
  <h1><?= e($encabezado) ?></h1>
  <div class="lateral-layout">
    <aside class="lateral">
      <ul class="lateral__lista">
        <?= sub_link($sub, 'panel', ruta('admin'), 'Panel y moderación') ?>
        <?= sub_link($sub, 'usuarios', ruta('usuarios'), 'Gestión de usuarios') ?>
        <?= sub_link($sub, 'catalogos', ruta('catalogo'), 'Restricciones y catálogos') ?>
      </ul>
    </aside>
    <form class="panel formulario" method="post" action="<?= e(ruta($accionForm)) ?>" data-validar novalidate>
      <?= csrf_campo() ?>
      <?php foreach ($ocultos as $nombre => $valor): ?><input type="hidden" name="<?= e($nombre) ?>" value="<?= e($valor) ?>"><?php endforeach; ?>
      <?php if ($errores): ?>
        <div class="alerta alerta--error" role="alert"><?php foreach ($errores as $error): ?><?= e($error) ?><br><?php endforeach; ?></div>
      <?php endif; ?>
      <div class="campo">
        <label class="campo__etiqueta" for="nombre"><?= $tipo === 'caracteristicas' ? 'Etiqueta visible (p. ej. «Sin gluten»)' : 'Nombre' ?> *</label>
        <input class="campo__control" id="nombre" name="nombre" required maxlength="120" value="<?= e($datos['nombre']) ?>">
      </div>
      <?php if ($tipo === 'ingredientes'): ?>
        <div class="campo">
          <span class="campo__etiqueta">Incumple las restricciones (contiene)</span>
          <div class="chips">
            <?php foreach ($restricciones as $r): $sel = in_array($r['clave'], $datos['contiene'], true); ?>
              <label class="chip<?= $sel ? ' chip--activo' : '' ?>"><input class="sr-only" type="checkbox" name="contiene[]" value="<?= e($r['clave']) ?>"<?= marcado($sel) ?>> <?= e(str_replace('Sin ', '', $r['nombre'])) ?></label>
            <?php endforeach; ?>
          </div>
        </div>
        <label class="opcion"><input type="checkbox" name="disponible_cr" value="1"<?= marcado(!empty($datos['disponible_cr'])) ?>> Disponible en Costa Rica</label>
      <?php elseif ($tipo === 'categorias'): ?>
        <div class="campo">
          <span class="campo__etiqueta">Clasifica</span>
          <label class="opcion"><input type="radio" name="tipo_categoria" value="producto"<?= marcado($datos['tipo'] === 'producto') ?>> Productos</label>
          <label class="opcion"><input type="radio" name="tipo_categoria" value="receta"<?= marcado($datos['tipo'] === 'receta') ?>> Recetas</label>
        </div>
      <?php elseif ($tipo === 'caracteristicas'): ?>
        <div class="campo">
          <label class="campo__etiqueta" for="clave">Clave interna *</label>
          <input class="campo__control" id="clave" name="clave" required pattern="[a-z_]{3,30}" maxlength="30" value="<?= e($datos['clave']) ?>">
          <span class="campo__ayuda">Minúsculas sin espacios, p. ej. sesamo.</span>
        </div>
        <div class="campo">
          <label class="campo__etiqueta" for="descripcion">Descripción</label>
          <textarea class="campo__control" id="descripcion" name="descripcion" maxlength="300"><?= e($datos['descripcion']) ?></textarea>
        </div>
      <?php else: ?>
        <div class="formulario__fila">
          <div class="campo">
            <label class="campo__etiqueta" for="tipo_comercio">Tipo</label>
            <select class="campo__control" id="tipo_comercio" name="tipo_comercio">
              <option value="supermercado"<?= marcado(($datos['tipo'] ?? '') === 'supermercado', 'selected') ?>>Supermercado</option>
              <option value="tienda"<?= marcado(($datos['tipo'] ?? '') === 'tienda', 'selected') ?>>Tienda</option>
            </select>
          </div>
          <div class="campo">
            <label class="campo__etiqueta" for="provincia">Provincia *</label>
            <select class="campo__control" id="provincia" name="provincia" required>
              <option value="">Seleccione…</option>
              <?php foreach ($provincias as $pr): ?><option<?= marcado($datos['provincia'] === $pr, 'selected') ?>><?= e($pr) ?></option><?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="formulario__fila">
          <div class="campo">
            <label class="campo__etiqueta" for="direccion">Dirección *</label>
            <input class="campo__control" id="direccion" name="direccion" required maxlength="250" value="<?= e($datos['direccion']) ?>">
          </div>
          <div class="campo">
            <label class="campo__etiqueta" for="telefono">Teléfono</label>
            <input class="campo__control" id="telefono" name="telefono" type="tel" maxlength="20" value="<?= e($datos['telefono']) ?>">
          </div>
        </div>
      <?php endif; ?>
      <div class="formulario__acciones">
        <a class="btn btn--secundario" href="<?= e($volver) ?>">Cancelar</a>
        <button type="submit" class="btn btn--primario"><?= e($boton) ?></button>
      </div>
    </form>
  </div>
</div>
