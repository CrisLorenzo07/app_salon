const dateText = document.querySelector('#date');
const calendar = document.querySelector('#date-calendar');
const calendarButton = document.querySelector('#open-calendar');

if (dateText && calendar && calendarButton) {
    calendarButton.hidden = false;

    function readTextDate() {
        const match = /^(\d{2})\/(\d{2})\/(\d{2})$/.exec(dateText.value);
        if (match) {
            const [, day, month, year] = match;
            const century = Number(year) <= 69 ? '20' : '19';
            calendar.value = `${century}${year}-${month}-${day}`;
        } else {
            calendar.value = '';
        }
    }

    function displayCalendarDate() {
        const [year, month, day] = calendar.value.split('-');
        dateText.value = calendar.value ? `${day}/${month}/${year.slice(-2)}` : '';
    }

    dateText.addEventListener('input', () => {
        readTextDate();
        const valid = calendar.value !== '' && calendar.checkValidity();
        dateText.setCustomValidity(valid ? '' : 'Selecciona una fecha válida.');
        calendar.dispatchEvent(new Event('input', { bubbles: true }));
        if (valid && !calendar.value) {
            displayCalendarDate();
        }
    });

    calendarButton.addEventListener('click', () => {
        readTextDate();
        try {
            calendar.showPicker();
        } catch {
            calendar.classList.add('is-visible');
            calendar.tabIndex = 0;
            calendar.focus();
        }
    });

    calendar.addEventListener('change', () => {
        calendar.dispatchEvent(new Event('input', { bubbles: true }));
        displayCalendarDate();
        dateText.setCustomValidity(calendar.checkValidity() ? '' : 'Selecciona una fecha válida.');
    });
}
