<h1 class="page-title">Recuperar Contraseña</h1>
<p class="page-description">Ingresa tu nueva contraseña a continuación</p>

<?php
include_once __DIR__ . "/../templates/alerts.php"
    ?>

<?php if (isset($error) && $error)
    return null; ?>
<form class="form" method="POST">
    <?php include __DIR__ . '/../templates/csrf.php'; ?>

    <div class="field">
        <label for="password">Nueva Contraseña</label>
        <input type="password" id="password" name="password" placeholder="Ingresa tu contraseña" required>
    </div>
    <div class="field">
        <label for="password_confirmation">Repetir Contraseña</label>
        <input type="password" id="password_confirmation" name="password_confirmation"
            placeholder="Repite tu contraseña" required>
    </div>

    <input type="submit" class="button" value="Restablecer Contraseña">
</form>

<div class="actions">
    <a href="/">¿Ya tienes una cuenta? Inicia Sesión</a>
    <a href="/crear-cuenta">¿Aún no tienes una cuenta?, crear una</a>
</div>
