<h1 class="page-title">Actualizar Servicios</h1>
<p class="page-description">Modifica los valores del formulario</p>

<?php
include_once __DIR__ . '/../templates/nav.php';
include_once __DIR__ . '/../templates/alerts.php';
?>

<form method="POST" class="form">

    <?php
    include_once __DIR__ . '/service-from.php';
    ?>

    <input type="submit" class="button" value="Actualizar Servicio">
</form>