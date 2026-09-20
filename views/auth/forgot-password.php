<h1 class="page-title">Olvide mi Contraseña</h1>
<p class="page-description">Restablecer tu contraseña ingresando tu email</p>

<?php
include_once __DIR__ . "/../templates/alerts.php"
    ?>

<form action="/olvide" class="form" method="POST">

    <div class="field">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" placeholder="Ingresa tu email">
    </div>

    <input type="submit" class="button" value="Restablecer Contraseña">
</form>

<div class="actions">
    <a href="/">¿Ya tienes una cuenta? Inicia Sesión</a>
    <a href="/crear-cuenta">¿Aún no tienes una cuenta?, crear una</a>
</div>