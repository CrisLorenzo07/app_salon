<?php include __DIR__ . '/../templates/csrf.php'; ?>
<div class="field">
    <label for="name">Nombre: </label>
    <input type="text" id="name" placeholder="Nombre del Servicio" name="name"
        value="<?php echo s($service->name ?? ''); ?>">
</div>

<div class="field">
    <label for="price">Precio: </label>
    <input type="number" id="price" placeholder="Precio del Servicio" name="price" min="0.01" max="999.99" step="0.01"
        value="<?php echo s($priceValue ?? (string) ($service->price ?? '')); ?>">
</div>
