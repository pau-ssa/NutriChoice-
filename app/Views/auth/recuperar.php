<div class="contenedor">
  <section class="seccion panel" style="max-width: 460px; margin: 0 auto">
    <h1>Recuperar contraseña</h1>
    <?php if ($enviado): ?>
      <div class="alerta alerta--exito" role="status">Si el correo está registrado, recibirá un enlace para restablecer su contraseña.</div>
      <a class="btn btn--primario" href="<?= e(ruta('login')) ?>">Volver a iniciar sesión</a>
    <?php else: ?>
      <p class="texto-suave">Le enviaremos un enlace de restablecimiento a su correo.</p>
      <form class="formulario" method="post" action="<?= e(ruta('recuperar_enviar')) ?>" data-validar novalidate>
        <?= csrf_campo() ?>
        <div class="campo">
          <label class="campo__etiqueta" for="email">Correo electrónico</label>
          <input class="campo__control" id="email" type="email" name="email" autocomplete="email" required maxlength="190">
        </div>
        <div class="formulario__acciones">
          <a class="btn btn--secundario" href="<?= e(ruta('login')) ?>">Volver</a>
          <button class="btn btn--primario" type="submit">Enviar enlace</button>
        </div>
      </form>
    <?php endif; ?>
  </section>
</div>
