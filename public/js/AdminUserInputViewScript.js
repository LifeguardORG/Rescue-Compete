/**
 * JavaScript für AdminUserInputView
 * Verwaltet Tab-Navigation, Formularvalidierung, Admin-Löschung und Passwort-Update
 */

// Globale Variablen
let adminToDelete = null;
let deleteForm = null;

/**
 * Tab-Navigation
 */
function showTab(tabName) {
    // Alle Tab-Contents ausblenden
    const tabContents = document.querySelectorAll('.tab-content');
    tabContents.forEach(content => {
        content.classList.remove('active');
    });

    // Alle Tab-Buttons deaktivieren
    const tabButtons = document.querySelectorAll('.tab-button');
    tabButtons.forEach(button => {
        button.classList.remove('active');
    });

    // Gewünschten Tab anzeigen
    const targetTab = document.getElementById(tabName);
    if (targetTab) {
        targetTab.classList.add('active');
    }

    // Entsprechenden Button aktivieren
    const targetButton = document.querySelector(`[data-tab="${tabName}"]`);
    if (targetButton) {
        targetButton.classList.add('active');
    }

    // URL-Parameter aktualisieren (optional)
    const url = new URL(window.location);
    url.searchParams.set('view', tabName);
    history.replaceState(null, '', url);
}

/**
 * Modal-Management
 */
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
    }
}

/**
 * Admin-Löschung bestätigen
 */
function confirmDeleteAdmin(adminId, username) {
    adminToDelete = adminId;

    // Modal-Text anpassen
    const modal = document.getElementById('confirmDeleteAdminModal');
    const messageElement = modal.querySelector('p');
    if (messageElement) {
        messageElement.textContent = `Möchten Sie den Admin-Account "${username}" wirklich löschen? Diese Aktion kann nicht rückgängig gemacht werden.`;
    }

    modal.classList.add('active');
}

/**
 * Admin tatsächlich löschen
 */
function deleteAdmin() {
    if (!adminToDelete) {
        console.error('Keine Admin-ID zum Löschen vorhanden');
        return;
    }

    // Verstecktes Formular erstellen und absenden
    const form = document.createElement('form');
    form.method = 'POST';
    form.style.display = 'none';

    const deleteIdInput = document.createElement('input');
    deleteIdInput.type = 'hidden';
    deleteIdInput.name = 'delete_id';
    deleteIdInput.value = adminToDelete;

    const deleteActionInput = document.createElement('input');
    deleteActionInput.type = 'hidden';
    deleteActionInput.name = 'delete_admin_user';
    deleteActionInput.value = '1';

    form.appendChild(deleteIdInput);
    form.appendChild(deleteActionInput);
    document.body.appendChild(form);

    form.submit();
}

/**
 * Benutzer-Passwort aktualisieren
 */
function updateUserPassword(userId) {
    const passwordInput = document.getElementById(`password_${userId}`);
    const newPassword = passwordInput.value.trim();

    // Validierung
    if (!newPassword) {
        showAlert('Fehler', 'Bitte geben Sie ein neues Passwort ein.', 'error');
        return;
    }

    if (newPassword.length < 8) {
        showAlert('Fehler', 'Das Passwort muss mindestens 8 Zeichen lang sein.', 'error');
        return;
    }

    // Button temporär deaktivieren
    const button = passwordInput.nextElementSibling;
    const originalText = button.textContent;
    button.disabled = true;
    button.textContent = 'Aktualisieren...';

    // AJAX-Request
    const formData = new FormData();
    formData.append('update_password', '1');
    formData.append('user_id', userId);
    formData.append('new_password', newPassword);

    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('Erfolg', data.message, 'success');
                passwordInput.value = ''; // Eingabefeld leeren
            } else {
                showAlert('Fehler', data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Fehler beim Aktualisieren des Passworts:', error);
            showAlert('Fehler', 'Ein unerwarteter Fehler ist aufgetreten.', 'error');
        })
        .finally(() => {
            // Button wieder aktivieren
            button.disabled = false;
            button.textContent = originalText;
        });
}

/**
 * Alert-Box anzeigen
 */
function showAlert(title, message, type = 'info') {
    // Bestehende Alerts entfernen
    const existingAlerts = document.querySelectorAll('.dynamic-alert');
    existingAlerts.forEach(alert => alert.remove());

    // Neue Alert-Box erstellen
    const alertBox = document.createElement('div');
    alertBox.className = `message-box ${type} dynamic-alert`;
    alertBox.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 10000;
        min-width: 300px;
        max-width: 500px;
        padding: 15px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        animation: slideInFromRight 0.3s ease-out;
    `;

    const titleElement = document.createElement('strong');
    titleElement.textContent = title + ': ';

    const messageElement = document.createElement('span');
    messageElement.textContent = message;

    alertBox.appendChild(titleElement);
    alertBox.appendChild(messageElement);

    // Close-Button hinzufügen
    const closeButton = document.createElement('button');
    closeButton.innerHTML = '×';
    closeButton.style.cssText = `
        position: absolute;
        top: 5px;
        right: 10px;
        background: none;
        border: none;
        font-size: 20px;
        cursor: pointer;
        padding: 0;
        width: 20px;
        height: 20px;
        line-height: 1;
    `;
    closeButton.onclick = () => alertBox.remove();
    alertBox.appendChild(closeButton);

    document.body.appendChild(alertBox);

    // Automatisches Ausblenden nach 5 Sekunden
    setTimeout(() => {
        if (alertBox.parentNode) {
            alertBox.style.opacity = '0';
            setTimeout(() => alertBox.remove(), 300);
        }
    }, 5000);
}

/**
 * Passwort-Validierung
 */
function validatePasswords() {
    const password = document.getElementById('password').value;
    const passwordConfirm = document.getElementById('password_confirm').value;
    const errorMessage = document.getElementById('password-mismatch');
    const confirmField = document.getElementById('password_confirm');

    if (password && passwordConfirm && password !== passwordConfirm) {
        confirmField.classList.add('error');
        errorMessage.classList.add('show');
        return false;
    } else {
        confirmField.classList.remove('error');
        errorMessage.classList.remove('show');
        return true;
    }
}

/**
 * Formular-Validierung
 */
function validateAdminForm() {
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value;
    const passwordConfirm = document.getElementById('password_confirm').value;

    let isValid = true;

    // Benutzername prüfen
    if (!username) {
        showFieldError('username', 'Benutzername ist erforderlich');
        isValid = false;
    } else if (username.length < 3) {
        showFieldError('username', 'Benutzername muss mindestens 3 Zeichen lang sein');
        isValid = false;
    } else {
        clearFieldError('username');
    }

    // Passwort prüfen
    if (!password) {
        showFieldError('password', 'Passwort ist erforderlich');
        isValid = false;
    } else if (password.length < 8) {
        showFieldError('password', 'Passwort muss mindestens 8 Zeichen lang sein');
        isValid = false;
    } else {
        clearFieldError('password');
    }

    // Passwort-Bestätigung prüfen
    if (!passwordConfirm) {
        showFieldError('password_confirm', 'Passwort-Bestätigung ist erforderlich');
        isValid = false;
    } else if (password !== passwordConfirm) {
        showFieldError('password_confirm', 'Passwörter stimmen nicht überein');
        isValid = false;
    } else {
        clearFieldError('password_confirm');
    }

    return isValid;
}

/**
 * Feld-Fehler anzeigen
 */
function showFieldError(fieldId, message) {
    const field = document.getElementById(fieldId);
    if (field) {
        field.classList.add('error');

        // Existierende Fehlermeldung entfernen
        const existingError = field.parentNode.querySelector('.field-error');
        if (existingError) {
            existingError.remove();
        }

        // Neue Fehlermeldung hinzufügen
        const errorDiv = document.createElement('div');
        errorDiv.className = 'validation-message field-error show';
        errorDiv.textContent = message;
        field.parentNode.appendChild(errorDiv);
    }
}

/**
 * Feld-Fehler entfernen
 */
function clearFieldError(fieldId) {
    const field = document.getElementById(fieldId);
    if (field) {
        field.classList.remove('error');
        const errorElement = field.parentNode.querySelector('.field-error');
        if (errorElement) {
            errorElement.remove();
        }
    }
}

/**
 * Event-Listeners hinzufügen
 */
document.addEventListener('DOMContentLoaded', function() {

    // Tab-Button Event-Listeners
    const tabButtons = document.querySelectorAll('.tab-button');
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const tabName = this.getAttribute('data-tab');
            if (tabName) {
                showTab(tabName);
            }
        });
    });

    // Passwort-Validierung Event-Listeners
    const passwordField = document.getElementById('password');
    const passwordConfirmField = document.getElementById('password_confirm');

    if (passwordField && passwordConfirmField) {
        passwordConfirmField.addEventListener('input', validatePasswords);
        passwordField.addEventListener('input', function() {
            if (passwordConfirmField.value) {
                validatePasswords();
            }
        });
    }

    // Formular-Submit Event-Listener
    const createAdminForm = document.getElementById('createAdminForm');
    if (createAdminForm) {
        createAdminForm.addEventListener('submit', function(e) {
            if (!validateAdminForm()) {
                e.preventDefault();
                return false;
            }
        });
    }

    // Escape-Taste für Modals
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const activeModals = document.querySelectorAll('.modal.active');
            activeModals.forEach(modal => {
                modal.classList.remove('active');
            });
        }
    });

    // Automatisches Ausblenden von Nachrichten
    const messageBoxes = document.querySelectorAll('.message-box:not(.dynamic-alert)');
    messageBoxes.forEach(box => {
        setTimeout(() => {
            box.style.opacity = '0';
            setTimeout(() => {
                box.remove();
            }, 500);
        }, 5000); // Nach 5 Sekunden ausblenden
    });

    // CSS für Animationen hinzufügen
    if (!document.getElementById('dynamic-styles')) {
        const style = document.createElement('style');
        style.id = 'dynamic-styles';
        style.textContent = `
            @keyframes slideInFromRight {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
        `;
        document.head.appendChild(style);
    }

    console.log('AdminUserInputView JavaScript erfolgreich geladen');
});

// Globale Funktionen für externe Aufrufe
window.showTab = showTab;
window.closeModal = closeModal;
window.confirmDeleteAdmin = confirmDeleteAdmin;
window.deleteAdmin = deleteAdmin;
window.updateUserPassword = updateUserPassword;