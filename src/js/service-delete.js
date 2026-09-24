document.querySelectorAll('.delete-service').forEach((form) => {
    const button = form.querySelector('button[type="submit"]');
    const error = document.createElement('p');
    error.setAttribute('role', 'alert');
    error.hidden = true;
    form.appendChild(error);

    if (typeof Swal === 'undefined') {
        error.textContent = 'No se pudo cargar la confirmación. Recarga la página.';
        error.hidden = false;
        return;
    }
    button.disabled = false;

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (button.disabled) return;
        button.disabled = true;
        error.hidden = true;
        try {
            const result = await Swal.fire({
                title: '¿Eliminar servicio?',
                text: `Se eliminará «${form.dataset.name}». Esta acción no se puede deshacer.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#cb0000',
                cancelButtonColor: '#0da6f3',
                focusCancel: true,
            });
            if (result.isConfirmed) {
                form.submit();
                return;
            }
        } catch {
            error.textContent = 'No se pudo abrir la confirmación. Inténtalo nuevamente.';
            error.hidden = false;
        }
        button.disabled = false;
    });
});
