<?php $pageClass = 'admin-page'; ?>
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
<?php $script = '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script><script src="/build/js/admin-date.js"></script><script src="/build/js/admin-appointments.js"></script>'; ?>

<div id="admin-appointment">
    <?php if (empty($appointments)): ?>
        <?php if (empty($alerts)): ?>
            <p>No hay citas para la fecha seleccionada.</p>
        <?php endif; ?>
    <?php else: ?>
        <?php
        $previousId = null;
        $appointmentNumber = 0;
        $total = 0;
        ?>
        <?php foreach ($appointments as $key => $appointment): ?>
            <?php if ($previousId !== $appointment->id): ?>
                <?php
                $appointmentNumber++;
                $total = 0;
                ?>
                <article class="admin-appointment-card">
                <h3>Cita #<?php echo $appointmentNumber; ?></h3>
                <p><span class="appointment-label">Fecha:</span> <?php echo s((new DateTimeImmutable($appointment->date))->format('d/m/y')); ?> · <span class="appointment-label">Hora:</span>
                    <?php echo s(substr($appointment->time, 0, 5)); ?></p>
                <section class="appointment-client">
                <h4>Datos del cliente</h4>
                <p><span class="appointment-label">Cliente:</span> <?php echo s($appointment->client); ?></p>
                <p><span class="appointment-label">Email:</span> <?php echo s($appointment->email); ?></p>
                <p><span class="appointment-label">Teléfono:</span> <?php echo s($appointment->phone); ?></p>
                </section>
                <section class="appointment-services">
                <h4>Servicios de la cita</h4>
                <?php $previousId = $appointment->id; ?>
            <?php endif; ?>
            <p><span class="appointment-label">Servicio:</span> <?php echo s($appointment->service ?: 'Sin servicio asignado'); ?>
                — <span class="appointment-label">Precio:</span> $<?php echo number_format($appointment->price, 2, ',', '.'); ?></p>
            <?php
            $total += $appointment->price;
            $nextAppointment = $appointments[$key + 1] ?? null;
            ?>
            <?php if ($nextAppointment === null || $nextAppointment->id !== $appointment->id): ?>
                <p class="appointment-total"><span class="appointment-label">Total:</span> $<?php echo number_format($total, 2, ',', '.'); ?></p>
                </section>
                <form class="delete-appointment" action="/api/eliminar" method="POST"
                    data-number="<?php echo $appointmentNumber; ?>">
                    <input type="hidden" name="id" value="<?php echo s((string) $appointment->id); ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo s($_SESSION['csrf_token'] ?? ''); ?>">
                    <button type="submit" class="button button-delete" hidden>Eliminar cita</button>
                    <p class="delete-error" role="alert"></p>
                </form>
                </article>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
