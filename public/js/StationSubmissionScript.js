document.addEventListener("DOMContentLoaded", function() {
    // submittedTeams aus dem Data-Attribut des Body abrufen
    const submittedTeamsJSON = document.body.getAttribute('data-submitted-teams');
    const submittedTeams = submittedTeamsJSON ? JSON.parse(submittedTeamsJSON) : [];

    // Status-Nachricht anzeigen, falls vorhanden
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('status')) {
        const status = urlParams.get('status');

        if (status === 'success') {
            const overlay = document.createElement('div');
            overlay.className = 'success-check-overlay';
            overlay.innerHTML = '<div class="success-check-circle"><svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="4 12 10 18 20 6"></polyline></svg></div>';
            document.querySelector('.submission-form').appendChild(overlay);

            // Nach kurzem Anzeigen ausblenden
            setTimeout(() => {
                overlay.style.opacity = '0';
                setTimeout(() => overlay.remove(), 500);
            }, 1500);
        } else if (status === 'failure') {
            const statusContainer = document.createElement('div');
            statusContainer.className = 'status-message error-message';
            statusContainer.textContent = 'Fehler beim Speichern der Ergebnisse!';
            const submissionBox = document.querySelector('.submission-box');
            if (submissionBox) {
                submissionBox.parentNode.insertBefore(statusContainer, submissionBox);
                setTimeout(() => {
                    statusContainer.style.opacity = '0';
                    statusContainer.style.transition = 'opacity 1s';
                    setTimeout(() => statusContainer.remove(), 1000);
                }, 5000);
            }
        }
    }

    // Optisches Feedback für bereits eingetragene Teams im Dropdown
    const teamSelect = document.getElementById('teamSelect');
    if (teamSelect && submittedTeams.length > 0) {
        Array.from(teamSelect.options).forEach(option => {
            if (submittedTeams.includes(option.value) && option.value !== "") {
                option.textContent = option.textContent + " ✓";
                option.style.fontWeight = "bold";
            }
        });
    }

    // Funktion zur Validierung der Punkteingabe
    function isValidPoints(pointsStr, maxPoints) {
        // Leere Felder sind nicht erlaubt (required im HTML)
        if (!pointsStr || pointsStr.trim() === "") return false;

        // Konvertiere zu Zahl
        const points = parseInt(pointsStr, 10);

        // Überprüfe, ob eine gültige Zahl und innerhalb des erlaubten Bereichs
        return !isNaN(points) && points >= 0 && points <= maxPoints;
    }

    // Event-Listener für Formulareingaben (Live-Validierung)
    const protocolInputs = document.querySelectorAll('.protocol-input');
    protocolInputs.forEach(input => {
        // Live-Validierung während der Eingabe
        input.addEventListener('input', function() {
            const value = this.value.trim();
            const maxPoints = parseInt(this.getAttribute('max'), 10);

            if (value !== "" && !isValidPoints(value, maxPoints)) {
                this.classList.add('invalid');
            } else {
                this.classList.remove('invalid');
            }
        });
    });

// Formular-Validierung vor dem Absenden
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(event) {
            let formIsValid = true;
            let errorMessages = [];
            let hasChanges = false;

            // Überprüfe alle Protokoll-Eingabefelder
            const protocolInputs = document.querySelectorAll('.protocol-input');
            protocolInputs.forEach(input => {
                const value = input.value.trim();
                const maxPoints = parseInt(input.getAttribute('max'), 10);
                const protocolName = input.previousElementSibling ? input.previousElementSibling.textContent.trim() : 'Protokoll';

                // Nur ausgefüllte Felder validieren
                if (value !== "") {
                    hasChanges = true;

                    if (!isValidPoints(value, maxPoints)) {
                        errorMessages.push("Ungültiger Punktwert für " + protocolName + ": " + value);
                        formIsValid = false;
                        input.classList.add('invalid');
                    }
                }
            });

            // Überprüfe, ob mindestens ein Team ausgewählt wurde
            const teamSelect = document.getElementById('teamSelect');
            if (teamSelect && teamSelect.value === "") {
                errorMessages.push("Bitte wählen Sie eine Mannschaft aus.");
                formIsValid = false;
                teamSelect.classList.add('invalid');
            }

            // Überprüfen, ob bereits eingetragene Teams geändert werden
            if (hasChanges && submittedTeams.includes(teamSelect.value) && form.dataset.confirmed !== 'true') {
                event.preventDefault();
                const teamName = teamSelect.options[teamSelect.selectedIndex].text;
                showConfirm('Ergebnisse überschreiben',
                    'Für die Mannschaft ' + teamName + ' wurden bereits Ergebnisse eingetragen. Möchten Sie die bestehenden Ergebnisse überschreiben?',
                    function() {
                        form.dataset.confirmed = 'true';
                        form.submit();
                    }
                );
                return false;
            }
            delete form.dataset.confirmed;

            if (!formIsValid) {
                event.preventDefault();
                showAlert('Validierungsfehler', 'Bitte korrigieren Sie die folgenden Fehler:\n' + errorMessages.join('\n'));
                return false;
            }

            // Es ist in Ordnung, wenn keine Änderungen vorgenommen wurden - Form kann abgesendet werden
            // Entferne diese Überprüfung, damit leere Protokolle erlaubt sind
            /*
            if (!hasChanges) {
                event.preventDefault();
                showAlert('Hinweis', 'Bitte geben Sie mindestens einen Punktwert ein.');
                return false;
            }
            */
        });
    }
});