document.addEventListener("DOMContentLoaded", function() {
    // submittedTeams aus dem Data-Attribut des Body abrufen
    const submittedTeamsJSON = document.body.getAttribute('data-submitted-teams');
    const submittedTeams = submittedTeamsJSON ? JSON.parse(submittedTeamsJSON) : [];

    // Global verfügbare closeModal Funktion
    window.closeModal = function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('active');
        }
    };

    // Funktion zur Überprüfung ob die Eingabe Buchstaben enthält
    function containsLetters(str) {
        return /[a-zA-ZäöüÄÖÜß]/.test(str);
    }

    // Funktion zur Anzeige der CustomAlertBox
    function showInvalidTimeAlert() {
        const alertBox = document.getElementById('invalidTimeAlert');
        if (alertBox) {
            alertBox.classList.add('active');
        }
    }

    // Funktion zum Schließen der CustomAlertBox
    function closeInvalidTimeAlert() {
        const alertBox = document.getElementById('invalidTimeAlert');
        if (alertBox) {
            alertBox.classList.remove('active');
        }
    }

    // Funktion zur Anzeige der Bestätigungsabfrage
    function showOverwriteConfirmation(teamsToOverwrite, onConfirm) {
        // Erstelle dynamisch eine CustomAlertBox für die Bestätigung
        const teamNames = teamsToOverwrite.map(team => team.name).join(', ');
        const message = teamsToOverwrite.length === 1
            ? `Für die Mannschaft "${teamNames}" wurden bereits Ergebnisse eingetragen. Möchten Sie die bestehenden Ergebnisse überschreiben?`
            : `Für folgende Mannschaften wurden bereits Ergebnisse eingetragen: ${teamNames}. Möchten Sie die bestehenden Ergebnisse überschreiben?`;

        // Erstelle Modal HTML
        const modalHTML = `
            <div id="overwriteConfirmModal" class="modal active">
                <div class="modal-content">
                    <h2>Ergebnisse überschreiben?</h2>
                    <p>${message}</p>
                    <button type="button" class="btn primary-btn" onclick="confirmOverwrite()">Ja, überschreiben</button>
                    <button type="button" class="btn" onclick="cancelOverwrite()">Abbrechen</button>
                </div>
            </div>
        `;

        // Entferne vorhandenes Modal falls vorhanden
        const existingModal = document.getElementById('overwriteConfirmModal');
        if (existingModal) {
            existingModal.remove();
        }

        // Füge neues Modal zum Body hinzu
        document.body.insertAdjacentHTML('beforeend', modalHTML);

        // Definiere globale Funktionen für die Buttons
        window.confirmOverwrite = function() {
            closeModal('overwriteConfirmModal');
            document.getElementById('overwriteConfirmModal').remove();
            onConfirm();
        };

        window.cancelOverwrite = function() {
            closeModal('overwriteConfirmModal');
            document.getElementById('overwriteConfirmModal').remove();
        };
    }

    // Funktion zur Validierung des Zeitformats
    function isValidTime(timeStr) {
        let minutes;
        let seconds;
        timeStr = timeStr.trim();
        if (timeStr === "") return true; // Leeres Feld ist erlaubt

        // Prüfung auf Buchstaben - dies sollte bereits vorher abgefangen werden
        if (containsLetters(timeStr)) {
            return false;
        }

        timeStr = timeStr.replace(',', '.');
        // Kein Doppelpunkt: Ganze Eingabe als Sekunden interpretieren
        if (timeStr.indexOf(':') === -1) {
            seconds = parseFloat(timeStr);
            return !isNaN(seconds) && seconds >= 0;
        }
        const parts = timeStr.split(':');
        if (parts.length === 2) {
            minutes = parseInt(parts[0], 10);
            seconds = parseFloat(parts[1]);
            if (isNaN(minutes) || isNaN(seconds)) return false;
            if (minutes < 0 || minutes >= 60 || seconds < 0 || seconds >= 60) return false;
            return true;
        } else if (parts.length === 3) {
            const hours = parseInt(parts[0], 10);
            minutes = parseInt(parts[1], 10);
            seconds = parseFloat(parts[2]);
            if (isNaN(hours) || isNaN(minutes) || isNaN(seconds)) return false;
            if (minutes < 0 || minutes >= 60 || seconds < 0 || seconds >= 60) return false;
            return true;
        }
        return false; // Mehr als 3 Teile => ungültig
    }

    // Event-Listener für Alert-Box Schließen-Button
    const alertCloseButton = document.querySelector('#invalidTimeAlert .close-btn');
    if (alertCloseButton) {
        alertCloseButton.addEventListener('click', closeInvalidTimeAlert);
    }

    const form = document.querySelector('form');
    if (!form) {
        console.error("Formular nicht gefunden.");
        return;
    }

    form.addEventListener('submit', function (event) {
        let formIsValid = true;
        let containsInvalidChars = false;
        let hasTeamsWithChanges = false;
        let teamsToOverwrite = [];

        const teamRows = document.querySelectorAll('.team-row');
        teamRows.forEach(row => {
            // Header-Row überspringen
            if (row.classList.contains('header-row')) {
                return;
            }

            const teamNameElement = row.querySelector('.team-name');
            if (!teamNameElement) return;

            const teamID = teamNameElement.getAttribute('data-team-id');
            const teamName = teamNameElement.textContent.trim().replace(/\s+/g, ' ');
            const inputs = row.querySelectorAll('input.time-input');

            let rowHasChanges = false;
            inputs.forEach(input => {
                const value = input.value.trim();
                if (value !== "") {
                    rowHasChanges = true;
                    if (containsLetters(value)) {
                        containsInvalidChars = true;
                        formIsValid = false;
                    } else if (!isValidTime(value)) {
                        formIsValid = false;
                    }
                }
            });

            if (rowHasChanges) {
                hasTeamsWithChanges = true;

                // Sammle Teams die bereits Ergebnisse haben
                if (submittedTeams.includes(teamID)) {
                    teamsToOverwrite.push({
                        id: teamID,
                        name: teamName
                    });
                }
            }
        });

        // Wenn Buchstaben gefunden wurden, Alert anzeigen und Submission stoppen
        if (containsInvalidChars) {
            event.preventDefault();
            showInvalidTimeAlert();
            return;
        }

        // Wenn andere Validierungsfehler vorliegen, Submission stoppen
        if (!formIsValid) {
            event.preventDefault();
            return;
        }

        // Bestätigungsabfrage nur einmal für alle betroffenen Teams
        if (teamsToOverwrite.length > 0) {
            event.preventDefault();
            showOverwriteConfirmation(teamsToOverwrite, function() {
                // Nach Bestätigung das Formular normal abschicken
                form.submit();
            });
            return;
        }
    });

    // CustomAlertBox bei Seitenload aktivieren falls notwendig
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('show_alert') === 'invalid_time') {
        showInvalidTimeAlert();
    }
});