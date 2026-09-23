<h1 class="page-title">Panel de Administración</h1>

<?php
include_once __DIR__ . '/../templates/nav.php';
include_once __DIR__ . '/../templates/alerts.php';
?>

<h2>Buscar Citas</h2>

<div class="search">
    <form action="/admin" method="GET" class="form">
        <div class="field">
            <label for="date">Fecha:</label>
            <div class="admin-date">
                <input type="text" name="date" id="date" placeholder="dd/mm/aa"
                    pattern="[0-9]{2}/[0-9]{2}/[0-9]{2}" maxlength="8"
                    value="<?php echo !empty($date) ? s((new DateTimeImmutable($date))->format('d/m/y')) : ''; ?>" required>
                <button type="button" id="open-calendar" aria-label="Abrir calendario" hidden>📅</button>
                <input type="date" id="date-calendar" class="admin-date-picker"
                    aria-label="Seleccionar fecha" tabindex="-1" value="<?php echo s($date ?? ''); ?>">
            </div>
        </div>
        <button type="submit" class="button">Buscar citas</button>
    </form>
</div>
<?php $script = '<script src="/build/js/admin-date.js"></script>'; ?>

<div id="admin-appointment">
    <?php if (empty($appointments)): ?>
        <?php if (empty($alerts)): ?>
            <p>No hay citas para la fecha seleccionada.</p>
        <?php endif; ?>
    <?php else: ?>
        <?php
        $previousId = null;
        $appointmentNumber = 0;
        ?>
        <?php foreach ($appointments as $appointment): ?>
            <?php if ($previousId !== $appointment->id): ?>
                <?php $appointmentNumber++; ?>
                <h3>Cita #<?php echo $appointmentNumber; ?></h3>
                <p>Fecha: <?php echo s((new DateTimeImmutable($appointment->date))->format('d/m/y')); ?> · Hora:
                    <?php echo s(substr($appointment->time, 0, 5)); ?></p>
                <p>Cliente: <?php echo s($appointment->client); ?></p>
                <p>Email: <?php echo s($appointment->email); ?></p>
                <p>Teléfono: <?php echo s($appointment->phone); ?></p>
                <?php $previousId = $appointment->id; ?>
            <?php endif; ?>
            <p>Servicio: <?php echo s($appointment->service ?: 'Sin servicio asignado'); ?>
                — Precio: $<?php echo number_format($appointment->price, 2, ',', '.'); ?></p>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
