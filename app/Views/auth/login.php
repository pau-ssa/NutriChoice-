<div class="contenedor">
  <section class="seccion panel" style="max-width: 460px; margin: 0 auto">
    <h1>Iniciar sesión</h1>
    <p class="texto-suave">Accede con tu correo para aplicar tu perfil a las búsquedas.</p>
    <?php if ($errores): ?>
      <div class="alerta alerta--error" role="alert"><?php foreach ($errores as $error): ?><?= e($error) ?><br><?php endforeach; ?></div>
    <?php endif; ?>
    <form class="formulario" method="post" action="<?= e(ruta('autenticar')) ?>" data-validar novalidate>
      <?= csrf_campo() ?>
      <div class="campo">
        <label class="campo__etiqueta" for="email">Correo electrónico</label>
        <input class="campo__control" id="email" type="email" name="email" autocomplete="username" required maxlength="190" value="<?= e($email) ?>">
      </div>
      <div class="campo">
        <label class="campo__etiqueta" for="clave">Contraseña</label>
        <input class="campo__control" id="clave" type="password" name="clave" autocomplete="current-password" required>
        <label class="opcion campo__ayuda"><input type="checkbox" data-ver-clave="clave"> Mostrar contraseña</label>
      </div>
      <button class="btn btn--primario btn--bloque" type="submit">Ingresar</button>
    </form>
    <p class="texto-suave" style="margin-top: 1rem">
      <a href="<?= e(ruta('recuperar')) ?>">¿Olvidó su contraseña?</a> · ¿No tiene cuenta? <a href="<?= e(ruta('registro')) ?>">Regístrese</a>
    </p>
    <div class="alerta alerta--aviso">
      <strong>Cuentas de demostración (solo localhost)</strong><br>
      admin@nutrichoice.com / Admin#2026!<br>
      colaborador@nutrichoice.com / Colab#2026!<br>
      carlosusuario@gmail.com / Usuario#2026!
    </div>
  </section>
</div>