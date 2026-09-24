<h1 class="page-title login-brand">App Salón</h1>
<h2 class="page-title">Iniciar Sesión</h2>

<?php
include_once __DIR__ . "/../templates/alerts.php"
    ?>

<form action="/" class="form" method="post">
    <div class="field">
        <label for="email">Email</label>
        <input type="email" id="email" placeholder="Ingresa tu email" name="email">
    </div>

    <div class="field">
        <label for="password">Contraseña</label>
        <input type="password" id="password" name="password" placeholder="Ingresa tu contraseña">
    </div>
    <input type="submit" class="button" value="Iniciar Sesión">
</form>

<div class="actions">
    <a href="/crear-cuenta">¿Aún no tienes una cuenta?, crear una</a>
    <a href="/olvide">¿Olvidaste tu contraseña?</a>
</div>