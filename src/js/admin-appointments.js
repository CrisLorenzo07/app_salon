document.querySelectorAll('.delete-appointment').forEach((form) => {
    const button = form.querySelector('button');
    const error = form.querySelector('.delete-error');
    button.hidden = false;

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (button.disabled) return;
        button.disabled = true;
        error.textContent = '';

        try {
            if (typeof Swal === 'undefined') {
                throw new Error('No se pudo cargar el diálogo. Recarga la página e inténtalo nuevamente.');
            }
            const confirmation = await Swal.fire({
                title: `¿Eliminar la cita #${form.dataset.number}?`,
                text: 'Esta acción no se puede deshacer.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar cita',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#cb0000',
                cancelButtonColor: '#0da6f3',
                focusCancel: true,
            });
            if (!confirmation.isConfirmed) return;

            const response = await fetch(form.action, { method: 'POST', body: new FormData(form) });
            const result = await response.json();
            if (!response.ok || !result.result) throw new Error(result.message || 'No se pudo eliminar la cita.');
            await Swal.fire({
                title: 'Cita eliminada',
                text: 'La cita se eliminó correctamente.',
                icon: 'success',
                confirmButtonText: 'Aceptar',
                confirmButtonColor: '#0da6f3',
            });
            window.location.reload();
        } catch (failure) {
            const message = failure.message || 'No se pudo conectar con el servidor.';
            error.textContent = message;
            if (typeof Swal !== 'undefined') {
                await Swal.fire({ title: 'No se pudo eliminar la cita', text: message, icon: 'error', confirmButtonText: 'Aceptar' });
            }
        } finally {
            button.disabled = false;
        }
    });
});
