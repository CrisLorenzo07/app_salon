<div class="nav">
    <div class="nav-user">
        <span class="nav-avatar" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                <circle cx="12" cy="8" r="4" />
                <path d="M4 21v-2a8 8 0 0 1 16 0v2" />
            </svg>
        </span>
        <p class="nav-greeting">
            <span class="nav-hello">Hola,</span>
            <span class="nav-name"><?php echo s($name ?? ''); ?></span>
        </p>
    </div>
    <form action="/cerrar-sesion" method="POST">
        <?php include __DIR__ . '/csrf.php'; ?>
        <button class="nav-logout" type="submit">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                <path d="M10 4H5v16h5M10 12h11m-4-4 4 4-4 4" />
            </svg>
            Cerrar sesión
        </button>
    </form>
</div>

<?php if (($_SESSION['admin'] ?? 0) === 1): ?>
    <div class="services-nav">
        <a class="button" href="/admin">Ver Citas</a>
        <a class="button" href="/servicios">Ver Servicios</a>
        <a class="button" href="/servicios/crear-servicio">
            Nuevo Servicio
        </a>
    </div>
<?php endif; ?>
