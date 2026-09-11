<h1 class="nombre-pagina">Crear Cuenta</h1>
<p class="descripcion-pagina">Llena el siguiente formulario para crear una cuenta</p>

<?php
include_once __DIR__ . "/../templates/alertas.php"
    ?>

<form action="/crear-cuenta" class="formulario" method="POST">
    <div class="campo">
        <label for="name">Nombre</label>
        <input type="text" id="name" name="name" placeholder="Ingresa tu nombre"
            value="<?php echo s(is_object($usuario) ? $usuario->name : ''); ?>" />
    </div>
    <div class="campo">
        <label for="last_name">Apellido</label>
        <input type="text" id="last_name" name="last_name" placeholder="Ingresa tu apellido"
            value="<?php echo s(is_object($usuario) ? $usuario->last_name : ''); ?>" />
    </div>
    <div class="campo">
        <label for="phone">Teléfono</label>
        <input type="tel" id="phone" name="phone" placeholder="Ingresa tu teléfono"
            value="<?php echo s(is_object($usuario) ? $usuario->phone : ''); ?>" />
    </div>
    <div class="campo">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" placeholder="Ingresa tu email"
            value="<?php echo s(is_object($usuario) ? $usuario->email : ''); ?>" />
    </div>
    <div class="campo">
        <label for="password">Contraseña</label>
        <input type="password" id="password" name="password" placeholder="Ingresa tu contraseña" />
    </div>

    <input type="submit" class="boton" value="Crear cuenta">
</form>

<div class="acciones">
    <a href="/">¿Ya tienes una cuenta? Inicia Sesión</a>
    <a href="/olvide">¿Olvidaste tu contraseña?</a>
</div>