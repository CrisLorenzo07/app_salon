<h1 class="page-title">Crear Cuenta</h1>
<p class="page-description">Llena el siguiente formulario para crear una cuenta</p>

<?php
include_once __DIR__ . "/../templates/alerts.php"
    ?>

<form action="/crear-cuenta" class="form" method="POST">
    <div class="field">
        <label for="name">Nombre</label>
        <input type="text" id="name" name="name" placeholder="Ingresa tu nombre"
            value="<?php echo s(is_object($user) ? $user->name : ''); ?>" />
    </div>
    <div class="field">
        <label for="last_name">Apellido</label>
        <input type="text" id="last_name" name="last_name" placeholder="Ingresa tu apellido"
            value="<?php echo s(is_object($user) ? $user->last_name : ''); ?>" />
    </div>
    <div class="field">
        <label for="phone">Teléfono</label>
        <input type="tel" id="phone" name="phone" placeholder="Ingresa tu teléfono"
            value="<?php echo s(is_object($user) ? $user->phone : ''); ?>" />
    </div>
    <div class="field">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" placeholder="Ingresa tu email"
            value="<?php echo s(is_object($user) ? $user->email : ''); ?>" />
    </div>
    <div class="field">
        <label for="password">Contraseña</label>
        <input type="password" id="password" name="password" placeholder="Ingresa tu contraseña" />
    </div>

    <input type="submit" class="button" value="Crear cuenta">
</form>

<div class="actions">
    <a href="/">¿Ya tienes una cuenta? Inicia Sesión</a>
    <a href="/olvide">¿Olvidaste tu contraseña?</a>
</div>