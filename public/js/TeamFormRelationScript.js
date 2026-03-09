/**
 * TeamFormRelationScript.js - Funktionalität für die Formular-Zuweisungen
 */

/**
 * Funktion zum Kopieren des Formular-Links in die Zwischenablage
 * @param {string} token - Das Token des Formulars
 */
function copyFormLink(token) {
    const baseUrl = window.location.origin + '/view/FormView.php?token=';
    const fullUrl = baseUrl + token;

    // Temporäres Textarea-Element erstellen
    const tempTextarea = document.createElement('textarea');
    tempTextarea.value = fullUrl;
    document.body.appendChild(tempTextarea);

    // Text auswählen und kopieren
    tempTextarea.select();
    document.execCommand('copy');

    // Element entfernen
    document.body.removeChild(tempTextarea);

    // CustomAlertBox anzeigen
    const copyAlertBox = document.getElementById('copyAlertBox');
    if (copyAlertBox) {
        copyAlertBox.classList.add('active');
    }
}

/**
 * Funktion zum Bestätigen des Zurücksetzens eines Formulars
 * @param {number} teamId - Die ID des Teams
 * @param {number} formId - Die ID des Formulars
 */
function confirmReset(teamId, formId) {
    // Formular-IDs in das versteckte Formular eintragen
    document.getElementById('reset_team_id').value = teamId;
    document.getElementById('reset_form_id').value = formId;

    // Bestätigungsdialog anzeigen
    const modal = document.getElementById('confirmResetModal');
    if (modal) {
        modal.classList.add('active');
    }
}

/**
 * Event-Listener hinzufügen, wenn das DOM geladen ist
 */
document.addEventListener('DOMContentLoaded', function() {
    // Prüfen, ob eine Statusmeldung vorhanden ist
    const messageBox = document.querySelector('.message-box');
    if (messageBox) {
        // Nach 5 Sekunden automatisch ausblenden
        setTimeout(() => {
            messageBox.style.opacity = '0';
            setTimeout(() => {
                messageBox.style.display = 'none';
            }, 500);
        }, 5000);
    }
});