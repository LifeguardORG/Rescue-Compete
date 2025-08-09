/**
 * Skript zur Anzeige von Login-Fehlermeldungen und zur Formularvalidierung
 */
document.addEventListener('DOMContentLoaded', function() {
    // URL-Parameter auslesen
    const urlParams = new URLSearchParams(window.location.search);
    const errorCode = urlParams.get('f');

    // Container für Fehlermeldungen finden
    let errorContainer = document.getElementById('error-message');

    // Falls Container nicht existiert, erstellen
    if (!errorContainer) {
        errorContainer = document.createElement('div');
        errorContainer.id = 'error-message';
        errorContainer.style.color = 'red';
        errorContainer.style.marginBottom = '15px';
        errorContainer.style.textAlign = 'center';

        // Vor dem Login-Formular einfügen
        const loginForm = document.getElementById('login_form');
        if (loginForm) {
            loginForm.insertBefore(errorContainer, loginForm.firstChild);
        }
    }

    // Funktion zum Markieren ungültiger Felder
    function markInvalidFields(fieldIds) {
        fieldIds.forEach(function(fieldId) {
            const field = document.getElementById(fieldId);
            if (field) {
                field.classList.remove('valid');
                field.classList.add('invalid');

                // Event-Listener hinzufügen, um Klasse zu entfernen, wenn Benutzer erneut eintippt
                field.addEventListener('input', function() {
                    this.classList.remove('invalid');
                    this.classList.add('valid');

                    // Fehlermeldung entfernen, wenn alle Felder wieder gültig sind
                    if (document.querySelectorAll('.invalid').length === 0) {
                        errorContainer.textContent = '';
                        errorContainer.style.display = 'none';
                    }
                });
            }
        });
    }

    // Je nach Fehlercode entsprechende Meldung anzeigen
    if (errorCode) {
        errorContainer.style.display = 'block';

        switch (errorCode) {
            case '1':
                errorContainer.textContent = 'Bitte geben Sie einen Benutzernamen und ein Passwort ein.';
                markInvalidFields(['username', 'password']);
                break;
            case '2':
                errorContainer.textContent = 'Dieser Benutzername ist nicht registriert.';
                markInvalidFields(['username']);
                break;
            case '3':
                errorContainer.textContent = 'Falsches Passwort.';
                markInvalidFields(['password']);
                break;
            case '4':
                errorContainer.textContent = 'Sitzung abgelaufen. Bitte erneut anmelden.';
                break;
            case '999':
                errorContainer.textContent = 'Ein technischer Fehler ist aufgetreten. Bitte versuchen Sie es später erneut.';
                break;
            default:
                errorContainer.textContent = 'Ein unbekannter Fehler ist aufgetreten.';
        }
    } else {
        // Wenn kein Fehlercode, Fehlermeldung ausblenden
        errorContainer.style.display = 'none';
    }

    // Formularvalidierung vor dem Absenden
    const loginForm = document.getElementById('login_form');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;

            // Prüfe, ob beide Felder ausgefüllt sind
            if (!username || !password) {
                e.preventDefault();

                errorContainer.textContent = 'Bitte füllen Sie alle Felder aus.';
                errorContainer.style.display = 'block';

                if (!username) markInvalidFields(['username']);
                if (!password) markInvalidFields(['password']);
            }
        });
    }
});