<div class="contenedor">
  <section class="seccion panel" style="max-width: 820px; margin: 0 auto">
    <h1>Crear cuenta</h1>
    <p class="texto-suave">Tu perfil define los filtros que se aplican en productos, recetas y restaurantes.</p>
    <?php if ($errores): ?>
      <div class="alerta alerta--error" role="alert"><strong>Revise los siguientes datos:</strong>
        <ul><?php foreach ($errores as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>
    <form class="formulario" method="post" action="<?= e(ruta('registrar')) ?>" data-validar novalidate>
      <?= csrf_campo() ?>
      <div class="formulario__fila">
        <div class="campo">
          <label class="campo__etiqueta" for="nombre">Nombre completo</label>
          <input class="campo__control" id="nombre" name="nombre" autocomplete="name" required maxlength="120" value="<?= e($datos['nombre']) ?>">
        </div>
        <div class="campo">
          <label class="campo__etiqueta" for="email">Correo electrónico</label>
          <input class="campo__control" id="email" type="email" name="email" autocomplete="email" required maxlength="190" value="<?= e($datos['email']) ?>">
        </div>
      </div>
      <div class="formulario__fila">
        <div class="campo">
          <label class="campo__etiqueta" for="clave">Contraseña</label>
          <input class="campo__control" id="clave" type="password" name="clave" autocomplete="new-password" required minlength="8" pattern="(?=.*[A-Za-z])(?=.*\d).{8,}">
          <span class="campo__ayuda">Mínimo 8 caracteres, con letras y números.</span>
        </div>
        <div class="campo">
          <label class="campo__etiqueta" for="clave2">Confirmar contraseña</label>
          <input class="campo__control" id="clave2" type="password" name="clave2" autocomplete="new-password" required data-igual-a="clave">
        </div>
      </div>

      <div class="campo">
        <span class="campo__etiqueta">Tipo de condición</span>
        <div class="opciones-grid">
          <?php foreach (['exclusion' => 'Restricción por exclusión', 'diabetes' => 'Diabetes', 'ambas' => 'Ambas'] as $valor => $texto): ?>
            <label class="opcion"><input type="radio" name="condicion" value="<?= $valor ?>" required data-condicion<?= marcado($datos['condicion'] === $valor) ?>> <?= e($texto) ?></label>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="campo" data-mostrar-si="exclusion ambas">
        <span class="campo__etiqueta">Mis restricciones</span>
        <div class="chips">
          <?php foreach ($restricciones as $r): $sel = in_array($r['clave'], $datos['restricciones'], true); ?>
            <label class="chip<?= $sel ? ' chip--activo' : '' ?>"><input class="sr-only" type="checkbox" name="restricciones[]" value="<?= e($r['clave']) ?>"<?= marcado($sel) ?>> <?= e($r['nombre']) ?></label>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="formulario__fila" data-mostrar-si="diabetes ambas">
        <div class="campo">
          <label class="campo__etiqueta" for="carb_max">Máx. carbohidratos por porción (g)</label>
          <input class="campo__control" id="carb_max" name="carb_max" type="number" min="0" max="200" step="0.5" value="<?= e($datos['carb_max']) ?>">
        </div>
        <div class="campo">
          <label class="campo__etiqueta" for="azucar_max">Máx. azúcares por porción (g)</label>
          <input class="campo__control" id="azucar_max" name="azucar_max" type="number" min="0" max="200" step="0.5" value="<?= e($datos['azucar_max']) ?>">
          <span class="campo__ayuda">Defina estos rangos con su profesional de salud.</span>
        </div>
      </div>
      <label class="opcion">
        <input type="checkbox" name="acepto" value="1" required>
        Entiendo que la información es de apoyo y no sustituye la guía de un profesional de la salud ni la lectura de la etiqueta.
      </label>
      <div class="formulario__acciones">
        <a class="btn btn--secundario" href="<?= e(ruta('login')) ?>">Ya tengo cuenta</a>
        <button class="btn btn--primario" type="submit">Crear cuenta</button>
      </div>
    </form>
  </section>
</div>
