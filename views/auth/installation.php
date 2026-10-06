<h1 class="page-title">Configuración inicial</h1>
<?php if (!$available): ?>
    <p class="page-description">La configuración inicial no está disponible.</p>
<?php else: ?>
    <p class="page-description"><?php echo $authorized ? 'Crea la cuenta del administrador de tu salón.' : 'Recibe un enlace privado para configurar el administrador de tu salón.'; ?></p>
    <?php include __DIR__ . '/../templates/alerts.php'; ?>
    <?php if (!$authorized): ?>
    <form action="/configuracion-inicial" class="form" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo s($_SESSION['installation_csrf']); ?>">
        <input type="hidden" name="action" value="send-link">
        <input type="submit" class="button" value="Enviar enlace de activación">
    </form>
    <?php else: ?>
    <form action="/configuracion-inicial" class="form" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo s($_SESSION['installation_csrf']); ?>">
        <?php foreach (['name' => ['Nombre', 'text'], 'last_name' => ['Apellido', 'text'], 'phone' => ['Teléfono', 'tel'], 'email' => ['Email', 'email']] as $field => [$label, $type]): ?>
            <div class="field">
                <label for="<?php echo s($field); ?>"><?php echo s($label); ?></label>
                <input type="<?php echo s($type); ?>" id="<?php echo s($field); ?>" name="<?php echo s($field); ?>" value="<?php echo s($user->$field); ?>" <?php echo $field !== 'phone' ? 'required' : ''; ?> <?php echo $field === 'email' ? 'readonly' : ''; ?>>
            </div>
        <?php endforeach; ?>
        <div class="field">
            <label for="password">Contraseña de tu cuenta</label>
            <input type="password" id="password" name="password" required minlength="8" autocomplete="new-password">
        </div>
        <p>Usa al menos 8 caracteres, con mayúsculas, minúsculas y números.</p>
        <input type="submit" class="button" value="Crear administrador">
    </form>
    <?php endif; ?>
<?php endif; ?>
