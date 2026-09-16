<h1 class="nombre-pagina">Recuperar Contraseña</h1>
<p class="descripcion-pagina">Ingresa tu nueva contraseña a continuación</p>

<?php
include_once __DIR__ . "/../templates/alertas.php"
    ?>

<?php if (isset($error) && $error)
    return null; ?>
<form class="formulario" method="POST">

    <div class="campo">
        <label for="password">Nueva Contraseña</label>
        <input type="password" id="password" name="password" placeholder="Ingresa tu contraseña" required>
    </div>
    <div class="campo">
        <label for="password_confirmation">Repetir Contraseña</label>
        <input type="password" id="password_confirmation" name="password_confirmation"
            placeholder="Repite tu contraseña" required>
    </div>

    <input type="submit" class="boton" value="Restablecer Contraseña">
</form>

<div class="acciones">
    <a href="/">¿Ya tienes una cuenta? Inicia Sesión</a>
    <a href="/crear-cuenta">¿Aún no tienes una cuenta?, crear una</a>
</div>