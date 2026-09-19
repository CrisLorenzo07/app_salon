<h1 class="page-title">Crear nueva cita</h1>
<p class="page-description">Elige tus servicios e ingresa tus datos</p>

<div id="app">

    <nav class="tabs">
        <button type="button" data-step="1">Servicios</button>
        <button type="button" data-step="2">Información Cita</button>
        <button type="button" data-step="3">Resumen</button>
    </nav>
    <div id="step-1" class="section">
        <h2>Servicios</h2>
        <p class="text-center">Elige tus servicios a continuación</p>
        <div id="services" class="service-list"></div>
    </div>

    <div id="step-2" class="section">
        <h2>Tus Datos y Cita</h2>
        <p class="text-center">Coloca tus datos y fecha de tu cita</p>

        <form class="form">
            <div class="field">
                <label for="name">Nombre</label>
                <input type="text" id="name" placeholder="Tu Nombre"
                    value="<?php echo htmlspecialchars($name ?? '', ENT_QUOTES, 'UTF-8'); ?>" disabled>
            </div>
            <div class="field">
                <label for="date">Fecha</label>
                <input type="date" id="date" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">

            </div>
            <div class="field">
                <label for="time">Hora</label>
                <input type="time" id="time">
            </div>
        </form>
    </div>

    <div id="step-3" class="section summary-content">
        <h2 id="summary-heading">Resumen</h2>
        <div class="summary-details"></div>
    </div>

    <div class="pagination">
        <button id="previous" class="button">
            &laquo; Anterior
        </button>
        <button id="next" class="button">
            Siguiente &raquo;
        </button>
    </div>
</div>

<?php
$script = "
<script src='/build/js/app.js'></script>
"
    ?>

<?php include_once __DIR__ . '/../templates/alerts.php'; ?>
