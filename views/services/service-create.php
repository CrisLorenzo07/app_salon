<h1 class="page-title">Nuevo Servicio</h1>
<p class="page-description">Llena todos los campos para añadir un nuevo servicio</p>

<?php
include_once __DIR__ . '/../templates/nav.php';
include_once __DIR__ . '/../templates/alerts.php';
?>

<form action="/servicios/crear-servicio" method="POST" class="form">

    <?php
    include_once __DIR__ . '/service-from.php';
    ?>

    <input type="submit" class="button" value="Guardar Servicio">
</form>