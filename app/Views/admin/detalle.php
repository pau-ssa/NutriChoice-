<?php
/** Tabla de un catálogo (alta, consulta, edición y baja) o de las cuentas de usuario. */
use App\Controllers\AdminController;
use App\Core\Auth;
use App\Models\Usuario;
$esUsuarios = $tipo === 'usuarios';
if (!$esUsuarios) {
    $tiposEst = ['supermercado' => 'Supermercado', 'tienda' => 'Tienda', 'restaurante' => 'Restaurante'];
    $restrTxt = fn(array $c) => $c ? implode(', ', array_map(fn($k) => $nombresRestr[$k] ?? $k, $c)) : '—';
    [$cabeceras, $celdas] = match ($tipo) {
        'productos' => [['Nombre', 'Marca', 'Categoría', 'Precio', 'Carbos/porción'],
            fn($f) => [$f['nombre'], $f['marca'], $nombresCat[$f['categoria_id']] ?? '', colones($f['precio']), gramos($f['carbohidratos'])]],
        'recetas' => [['Título', 'Tipo', 'Tiempo', 'Porciones', 'Alérgenos'],
            fn($f) => [$f['titulo'], $nombresCat[$f['categoria_id']] ?? '', $f['tiempo_min'] . ' min', $f['porciones'], $restrTxt($f['contiene'])]],
        'ingredientes' => [['Ingrediente', 'Incumple', 'Disponible en CR'],
            fn($f) => [$f['nombre'], $restrTxt($f['contiene']), $f['disponible_cr'] ? 'Sí' : 'No']],
        'establecimientos' => [['Nombre', 'Tipo', 'Provincia', 'Teléfono'],
            fn($f) => [$f['nombre'], $tiposEst[$f['tipo']] ?? $f['tipo'], $f['provincia'], $f['telefono']]],
        'categorias' => [['Nombre', 'Clasifica'], fn($f) => [$f['nombre'], $f['tipo'] === 'receta' ? 'Recetas' : 'Productos']],
        'caracteristicas' => [['Etiqueta', 'Clave', 'Descripción'], fn($f) => [$f['nombre'], $f['clave'], $f['descripcion']]],
    };
}
?>
<div class="contenedor">
  <h1><?= $esUsuarios ? 'Gestión de usuarios' : e(AdminController::CATALOGOS[$tipo]) ?></h1>
  <div class="lateral-layout">
    <aside class="lateral">
      <ul class="lateral__lista">
        <?= sub_link($sub, 'panel', ruta('admin'), 'Panel y moderación') ?>
        <?= sub_link($sub, 'usuarios', ruta('usuarios'), 'Gestión de usuarios') ?>
        <?= sub_link($sub, 'catalogos', ruta('catalogo'), 'Restricciones y catálogos') ?>
      </ul>
    </aside>
    <section>
      <?php if (!$esUsuarios): ?>
        <div class="chips" style="margin-bottom: 1rem">
          <?php foreach (AdminController::CATALOGOS as $k => $n): ?>
            <a class="chip<?= $k === $tipo ? ' chip--activo' : '' ?>" href="<?= e(ruta('catalogo', null, ['tipo' => $k])) ?>"><?= e($n) ?></a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
      <div class="barra-herramientas">
        <input class="campo__control" style="max-width: 260px" type="search" placeholder="Filtrar" aria-label="Filtrar registros" data-filtrar="tabla-detalle">
        <?php if ($esUsuarios): ?>
          <span class="texto-suave">Asignar roles y bloquear cuentas. (La validación por rol se agregará en el avance final.)</span>
        <?php elseif ($tipo === 'establecimientos'): ?>
          <div class="acciones">
            <a class="btn btn--primario btn--sm" href="<?= e(ruta('catalogo_form', null, ['tipo' => $tipo, 'subtipo' => 'comercio'])) ?>">+ Supermercado / tienda</a>
            <a class="btn btn--primario btn--sm" href="<?= e(ruta('catalogo_form', null, ['tipo' => $tipo, 'subtipo' => 'restaurante'])) ?>">+ Restaurante</a>
          </div>
        <?php else: ?>
          <a class="btn btn--primario btn--sm" href="<?= e(ruta('catalogo_form', null, ['tipo' => $tipo])) ?>">+ Nuevo registro</a>
        <?php endif; ?>
      </div>
      <div class="tabla-contenedor">
        <table class="tabla" id="tabla-detalle">
          <?php if ($esUsuarios): ?>
            <thead><tr><th scope="col">Nombre</th><th scope="col">Correo</th><th scope="col">Condición</th><th scope="col">Rol y estado</th></tr></thead>
            <tbody>
              <?php foreach ($filas as $u): $yo = (int) $u['id'] === Auth::id(); ?>
                <tr data-filtrable>
                  <td><?= e($u['nombre']) ?><?= $yo ? ' <span class="etiqueta etiqueta--pendiente">Usted</span>' : '' ?></td>
                  <td><?= e($u['email']) ?></td>
                  <td><?= e(Usuario::CONDICIONES[$u['condicion']] ?? '—') ?></td>
                  <td>
                    <form method="post" action="<?= e(ruta('usuario_actualizar')) ?>" class="tabla__acciones">
                      <?= csrf_campo() ?>
                      <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                      <select class="campo__control" name="rol" aria-label="Rol de <?= e($u['nombre']) ?>"<?= $yo ? ' disabled' : '' ?>>
                        <?php foreach (Usuario::ROLES as $v => $t): ?><option value="<?= $v ?>"<?= marcado($u['rol'] === $v, 'selected') ?>><?= e($t) ?></option><?php endforeach; ?>
                      </select>
                      <label class="opcion"><input type="checkbox" name="bloqueado" value="1"<?= marcado($u['bloqueado']) ?><?= $yo ? ' disabled' : '' ?>> Bloqueado</label>
                      <button type="submit" class="btn btn--primario btn--sm"<?= $yo ? ' disabled' : '' ?>>Guardar</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          <?php else: ?>
            <thead><tr><?php foreach ($cabeceras as $c): ?><th scope="col"><?= e($c) ?></th><?php endforeach; ?><th scope="col">Acciones</th></tr></thead>
            <tbody>
              <?php foreach ($filas as $f): ?>
                <tr data-filtrable>
                  <?php foreach ($celdas($f) as $v): ?><td><?= e($v) ?></td><?php endforeach; ?>
                  <td>
                    <div class="tabla__acciones">
                      <a class="btn btn--secundario btn--sm" href="<?= e(ruta('catalogo_form', (int) $f['id'], ['tipo' => $tipo])) ?>">Editar</a>
                      <form method="post" action="<?= e(ruta('catalogo_eliminar')) ?>" data-confirmar="¿Eliminar este registro? Esta acción no se puede deshacer.">
                        <?= csrf_campo() ?>
                        <input type="hidden" name="tipo" value="<?= e($tipo) ?>">
                        <input type="hidden" name="id" value="<?= (int) $f['id'] ?>">
                        <button type="submit" class="btn btn--peligro btn--sm">Eliminar</button>
                      </form>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          <?php endif; ?>
        </table>
      </div>
    </section>
  </div>
</div>
