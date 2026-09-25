<?php $useTangerine = true; ?>
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
        <div class="password-field">
            <input type="password" id="password" name="password" placeholder="Ingresa tu contraseña" autocomplete="current-password">
            <button type="button" class="password-toggle" aria-label="Mostrar contraseña" aria-controls="password" hidden>
                <svg class="eye-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" hidden>
                    <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z" />
                    <circle cx="12" cy="12" r="3" />
                </svg>
                <svg class="eye-closed" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M3 9s3 6 9 6 9-6 9-6M5 12l-2 3m6-1-1 4m7-4 1 4m3-6 2 3" />
                </svg>
            </button>
        </div>
    </div>
    <input type="submit" class="button" value="Iniciar Sesión">
</form>

<div class="actions">
    <a href="/crear-cuenta">¿Aún no tienes una cuenta?, crear una</a>
    <a href="/olvide">¿Olvidaste tu contraseña?</a>
</div>

<?php $script = '<script defer src="/build/js/password-toggle.js"></script>'; ?>
