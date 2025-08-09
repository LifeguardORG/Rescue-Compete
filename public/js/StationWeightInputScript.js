document.addEventListener('DOMContentLoaded', function() {
    const decreaseButtons = document.querySelectorAll('.decrease-weight');
    const increaseButtons = document.querySelectorAll('.increase-weight');
    const weightInputs = document.querySelectorAll('.weight-input');

    // Funktion zur Validierung und Begrenzung der Eingabewerte
    function validateWeightValue(input) {
        let value = parseInt(input.value, 10);

        // Wert auf gültige Grenzen begrenzen
        if (isNaN(value) || value < 0) {
            value = 0;
        } else if (value > 200) {
            value = 200;
        }

        input.value = value;
        return value;
    }

    // Event Listener für Verringerung der Gewichtung
    decreaseButtons.forEach(button => {
        button.addEventListener('click', function() {
            const stationId = this.dataset.station;
            const input = document.getElementById('weight-' + stationId);
            let value = parseInt(input.value, 10);
            input.value = Math.max(0, value - 10);
        });
    });

    // Event Listener für Erhöhung der Gewichtung
    increaseButtons.forEach(button => {
        button.addEventListener('click', function() {
            const stationId = this.dataset.station;
            const input = document.getElementById('weight-' + stationId);
            let value = parseInt(input.value, 10);

            // Maximalwert von 200 beachten
            input.value = Math.min(200, value + 10);
        });
    });

    // Event Listener für direkte Eingabe in die Input-Felder
    weightInputs.forEach(input => {
        // Validierung während der Eingabe
        input.addEventListener('input', function() {
            validateWeightValue(this);
        });

        // Validierung beim Verlassen des Feldes
        input.addEventListener('blur', function() {
            validateWeightValue(this);
        });

        // Verhindert das Einfügen von ungültigen Werten
        input.addEventListener('paste', function(e) {
            setTimeout(() => {
                validateWeightValue(this);
            }, 10);
        });

        // Verhindert das Eingeben von nicht-numerischen Zeichen
        input.addEventListener('keypress', function(e) {
            // Erlaube nur Zahlen, Backspace, Delete, Tab, Escape, Enter
            if (!/[\d]/.test(e.key) &&
                !['Backspace', 'Delete', 'Tab', 'Escape', 'Enter', 'ArrowLeft', 'ArrowRight'].includes(e.key)) {
                e.preventDefault();
            }
        });
    });
});