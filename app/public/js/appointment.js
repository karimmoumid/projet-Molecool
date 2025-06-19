document.addEventListener('DOMContentLoaded', function () {
    const dateInput = document.getElementById('appointment_form_date');
    const timeSelect = document.getElementById('time_select');
    const hiddenTimeInput = document.getElementById('appointment_form_time');

    if (!dateInput || !timeSelect || !hiddenTimeInput) return;

    const i18n = {
        previousMonth: 'Mois précédent',
        nextMonth: 'Mois suivant',
        months: ['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'],
        weekdays: ['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'],
        weekdaysShort: ['Dim','Lun','Mar','Mer','Jeu','Ven','Sam']
    };

    function handleDateSelection(date) {
        if (!(date instanceof Date) || isNaN(date)) {
            console.warn('Date invalide:', date);
            return;
        }
        const day = ('0' + date.getDate()).slice(-2);
        const month = ('0' + (date.getMonth() + 1)).slice(-2);
        const year = date.getFullYear();

        dateInput.value = `${day}/${month}/${year}`;

        const dateISO = `${year}-${month}-${day}`;

        hiddenTimeInput.value = '';
        while (timeSelect.options.length > 1) {
            timeSelect.remove(1);
        }
        timeSelect.disabled = true;

        fetch('/api/available-slots?date=' + dateISO)
            .then(response => {
                if (!response.ok) throw new Error('Erreur réseau');
                return response.json();
            })
            .then(data => {
                data.forEach(slot => {
                    const option = document.createElement('option');
                    option.value = slot.value;
                    option.textContent = slot.label;
                    timeSelect.appendChild(option);
                });
                timeSelect.disabled = data.length === 0;
            })
            .catch(err => {
                console.error('Erreur lors de la récupération des créneaux:', err);
                timeSelect.disabled = true;
            });
    }

    const picker = new Pikaday({
        field: dateInput,
        format: 'DD/MM/YYYY',
        i18n: i18n,
        minDate: new Date(),
        disableDayFn: function(date) {
            return date.getDay() === 0;
        },
        onSelect: handleDateSelection,
        onOpen: function() {
            if (dateInput.value) {
                const parts = dateInput.value.split('/');
                if (parts.length === 3) {
                    const d = new Date(parts[2], parts[1] - 1, parts[0]);
                    handleDateSelection(d);
                }
            }
        }
    });

    timeSelect.addEventListener('change', function() {
        hiddenTimeInput.value = this.value;
    });

    // Appel initial si date pré-remplie
    if (dateInput.value) {
        const parts = dateInput.value.split('/');
        if (parts.length === 3) {
            const d = new Date(parts[2], parts[1] - 1, parts[0]);
            handleDateSelection(d);
        }
    }
});
