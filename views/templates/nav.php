<div class="nav">
    <p>Hola: <?php echo $name ?? ''; ?></p>
    <a class="button" href="/cerrar-sesion">Cerrar Sesión</a>
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