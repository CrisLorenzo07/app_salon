<?php $pageClass = 'admin-page'; ?>
<h1 class="page-title">Servicios</h1>
<p class="page-description">Administración de Servicios</p>

<?php
include_once __DIR__ . '/../templates/nav.php';
include_once __DIR__ . '/../templates/alerts.php';
?>

<ul class="services">
    <?php if (!empty($services)): ?>
        <?php foreach ($services as $service): ?>
            <li class="service">
                <p><span class="service-label">Nombre:</span> <span><?php echo s($service->name); ?></span> </p>
                <p><span class="service-label">Precio:</span> <span>$<?php echo $service->price; ?></span> </p>

                <div class="actions">
                    <a class="button button-update" href="/servicios/actualizar-servicio?id=<?php echo $service->id; ?>">Actualizar</a>

                    <form action="/servicios/eliminar-servicio" method="POST">
                        <input type="hidden" name="id" value="<?php echo $service->id; ?>">
                        <button type="submit" class="button button-delete">Eliminar</button>

                    </form>
                </div>
            </li>
        <?php endforeach; ?>
    <?php else: ?>
        <li>
            <p>No hay servicios disponibles.</p>
        </li>
    <?php endif; ?>
</ul>