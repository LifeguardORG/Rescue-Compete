/**
 * TeamInputScript.js
 * JavaScript-Funktionalität für die Mannschafts-Verwaltung
 */

// Globale Variablen
let currentTab = 'overview';
let deleteTeamId = null;

// DOM-Ready
document.addEventListener('DOMContentLoaded', function() {
    initializePage();
    initializeEventListeners();
});

function initializePage() {
    const urlParams = new URLSearchParams(window.location.search);
    const viewParam = urlParams.get('view');

    if (viewParam) {
        currentTab = viewParam;
    }

    showTab(currentTab);
}

function showTab(tabName) {
    // Alle Tab-Inhalte verstecken
    const tabContents = document.querySelectorAll('.tab-content');
    tabContents.forEach(content => {
        content.classList.remove('active');
    });

    // Alle Tab-Buttons deaktivieren
    const tabButtons = document.querySelectorAll('.tab-button');
    tabButtons.forEach(button => {
        button.classList.remove('active');
    });

    // Gewählten Tab anzeigen
    const selectedTab = document.getElementById(tabName);
    if (selectedTab) {
        selectedTab.classList.add('active');
    }

    // Entsprechenden Button aktivieren
    const selectedButton = document.querySelector(`[data-tab="${tabName}"]`);
    if (selectedButton) {
        selectedButton.classList.add('active');
    }

    // Aktuellen Tab speichern
    currentTab = tabName;

    // URL aktualisieren (ohne Reload)
    const url = new URL(window.location);
    url.searchParams.set('view', tabName);
    window.history.replaceState(null, '', url);

    // Tab-spezifische Aktionen
    handleTabSpecificActions(tabName);
}

function handleTabSpecificActions(tabName) {
    switch (tabName) {
        case 'create':
            // Fokus auf das Teamname-Feld setzen
            const teamnameInput = document.getElementById('teamname');
            if (teamnameInput) {
                setTimeout(() => teamnameInput.focus(), 100);
            }
            break;
    }
}

function initializeEventListeners() {
    // Tab-Button Event-Listener
    const tabButtons = document.querySelectorAll('.tab-button');
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const tabName = this.getAttribute('data-tab');
            if (tabName) {
                showTab(tabName);
            }
        });
    });

    // Form-Validierung für Create-Tab
    const createForm = document.getElementById('createTeamForm');
    if (createForm) {
        createForm.addEventListener('submit', function(event) {
            if (!validateCreateForm()) {
                event.preventDefault();
            }
        });
    }

    // Längenbeschränkung für Eingabefelder
    const teamnameInput = document.getElementById('teamname');
    if (teamnameInput) {
        teamnameInput.addEventListener('input', function() {
            enforceMaxLength(this, 100);
        });
    }

    const kreisverbandInput = document.getElementById('kreisverband');
    if (kreisverbandInput) {
        kreisverbandInput.addEventListener('input', function() {
            enforceMaxLength(this, 32);
        });
    }

    const landesverbandInput = document.getElementById('landesverband');
    if (landesverbandInput) {
        landesverbandInput.addEventListener('input', function() {
            enforceMaxLength(this, 32);
        });
    }

    // Tastatur-Navigation für Accessibility
    document.addEventListener('keydown', handleKeyboardNavigation);
}

function enforceMaxLength(element, maxLength) {
    if (element.value.length > maxLength) {
        element.value = element.value.substring(0, maxLength);
        showMessage(`Maximale Länge von ${maxLength} Zeichen erreicht.`, 'warning');
    }
}

function validateCreateForm() {
    const teamnameInput = document.getElementById('teamname');
    const kreisverbandInput = document.getElementById('kreisverband');
    const landesverbandInput = document.getElementById('landesverband');
    let isValid = true;

    // Teamname-Validierung
    if (!teamnameInput.value.trim()) {
        showValidationError(teamnameInput, 'Bitte geben Sie einen Namen für die Mannschaft ein.');
        isValid = false;
    } else if (teamnameInput.value.trim().length > 100) {
        showValidationError(teamnameInput, 'Der Teamname darf maximal 100 Zeichen lang sein.');
        isValid = false;
    } else {
        clearValidationError(teamnameInput);
    }

    // Kreisverband-Validierung
    if (!kreisverbandInput.value.trim()) {
        showValidationError(kreisverbandInput, 'Bitte geben Sie einen Kreisverband ein.');
        isValid = false;
    } else if (kreisverbandInput.value.trim().length > 32) {
        showValidationError(kreisverbandInput, 'Der Kreisverband darf maximal 32 Zeichen lang sein.');
        isValid = false;
    } else {
        clearValidationError(kreisverbandInput);
    }

    // Landesverband-Validierung
    if (!landesverbandInput.value.trim()) {
        showValidationError(landesverbandInput, 'Bitte geben Sie einen Landesverband ein.');
        isValid = false;
    } else if (landesverbandInput.value.trim().length > 32) {
        showValidationError(landesverbandInput, 'Der Landesverband darf maximal 32 Zeichen lang sein.');
        isValid = false;
    } else {
        clearValidationError(landesverbandInput);
    }

    return isValid;
}

function showValidationError(element, message) {
    element.classList.add('error');
    clearValidationError(element);

    const errorDiv = document.createElement('div');
    errorDiv.className = 'validation-message show';
    errorDiv.textContent = message;
    errorDiv.id = element.id + '_error';

    element.parentNode.insertBefore(errorDiv, element.nextSibling);
}

function clearValidationError(element) {
    element.classList.remove('error');
    const errorDiv = document.getElementById(element.id + '_error');
    if (errorDiv) {
        errorDiv.remove();
    }
}

function confirmDeleteTeam(id, name) {
    if (!id || !name) {
        showMessage('Fehlerhafte Parameter beim Löschen.', 'error');
        return;
    }

    deleteTeamId = id;

    // Modal-Text aktualisieren
    const modal = document.getElementById('confirmDeleteModal');
    if (modal) {
        const modalContent = modal.querySelector('.modal-content p');
        if (modalContent) {
            modalContent.innerHTML = `Möchten Sie die Mannschaft <strong>"${escapeHtml(name)}"</strong> wirklich löschen?<br><br><em>Alle dazugehörigen Daten (Punkte, Zeiten, Formulare, Benutzer) werden ebenfalls gelöscht.</em><br><br><span class="warning-text">Diese Aktion kann nicht rückgängig gemacht werden!</span>`;
        }

        showModal('confirmDeleteModal');
    }
}

function deleteTeam() {
    if (!deleteTeamId) {
        showMessage('Keine Mannschaft zum Löschen ausgewählt.', 'error');
        closeModal('confirmDeleteModal');
        return;
    }

    // Loading-Zustand für Button
    const confirmButton = document.querySelector('#confirmDeleteModal .primary-btn');
    if (confirmButton) {
        const originalText = confirmButton.textContent;
        confirmButton.textContent = 'Wird gelöscht...';
        confirmButton.disabled = true;

        // Reset nach Timeout (Fallback)
        setTimeout(() => {
            confirmButton.textContent = originalText;
            confirmButton.disabled = false;
        }, 5000);
    }

    // Formular erstellen und absenden
    const form = document.createElement('form');
    form.method = 'POST';
    form.style.display = 'none';

    const deleteIdInput = document.createElement('input');
    deleteIdInput.type = 'hidden';
    deleteIdInput.name = 'delete_id';
    deleteIdInput.value = deleteTeamId;

    const deleteTeamInput = document.createElement('input');
    deleteTeamInput.type = 'hidden';
    deleteTeamInput.name = 'delete_team';
    deleteTeamInput.value = '1';

    form.appendChild(deleteIdInput);
    form.appendChild(deleteTeamInput);
    document.body.appendChild(form);
    form.submit();
}

function clearForm() {
    const form = document.getElementById('createTeamForm');
    if (form) {
        form.reset();

        // Alle Validierungsfehler entfernen
        const errorMessages = form.querySelectorAll('.validation-message');
        errorMessages.forEach(msg => msg.remove());

        const errorInputs = form.querySelectorAll('.error');
        errorInputs.forEach(input => input.classList.remove('error'));

        // Fokus auf erstes Eingabefeld setzen
        const firstInput = form.querySelector('input[type="text"]');
        if (firstInput) {
            setTimeout(() => firstInput.focus(), 100);
        }
    }
}

function showModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';

        // Fokus auf das erste fokussierbare Element setzen
        const focusableElement = modal.querySelector('button:not([disabled]), input, select');
        if (focusableElement) {
            setTimeout(() => focusableElement.focus(), 100);
        }
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';

        // Reset delete ID
        if (modalId === 'confirmDeleteModal') {
            deleteTeamId = null;
        }

        // URL Parameter entfernen bei Success/Error Modals
        if (modalId === 'successAlert' || modalId === 'errorAlert') {
            const url = new URL(window.location);
            url.searchParams.delete('created');
            url.searchParams.delete('updated');
            url.searchParams.delete('deleted');
            window.history.replaceState(null, '', url);
        }
    }
}

function handleKeyboardNavigation(event) {
    // ESC schließt Modals
    if (event.key === 'Escape') {
        const activeModal = document.querySelector('.modal.active');
        if (activeModal) {
            closeModal(activeModal.id);
        }
    }

    // Ctrl + Tab-Navigation zwischen Tabs
    if (event.key >= '1' && event.key <= '2' && event.ctrlKey) {
        event.preventDefault();
        const tabs = ['overview', 'create'];
        const tabIndex = parseInt(event.key) - 1;
        if (tabs[tabIndex]) {
            showTab(tabs[tabIndex]);
        }
    }
}

function showMessage(message, type = 'info') {
    // Bestehende temporäre Nachrichten entfernen
    const existingMessages = document.querySelectorAll('.message-box.temp-message');
    existingMessages.forEach(msg => msg.remove());

    // Neue Nachricht erstellen
    const messageDiv = document.createElement('div');
    messageDiv.className = `message-box ${type} temp-message`;
    messageDiv.textContent = message;

    // Nachricht am Anfang des main-content einfügen
    const mainContent = document.querySelector('.main-content');
    if (mainContent) {
        const firstChild = mainContent.querySelector('h2');
        if (firstChild) {
            mainContent.insertBefore(messageDiv, firstChild.nextSibling);
        } else {
            mainContent.insertBefore(messageDiv, mainContent.firstChild);
        }

        // Nachricht nach 4 Sekunden automatisch entfernen
        setTimeout(() => {
            if (messageDiv.parentNode) {
                messageDiv.style.opacity = '0';
                setTimeout(() => messageDiv.remove(), 300);
            }
        }, 4000);
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Event-Listener für Modal-Schließen beim Klick außerhalb
document.addEventListener('click', function(event) {
    if (event.target.classList.contains('modal')) {
        closeModal(event.target.id);
    }
});

// Globale Funktionen für HTML-Inline-Events
window.showTab = showTab;
window.confirmDeleteTeam = confirmDeleteTeam;
window.deleteTeam = deleteTeam;
window.closeModal = closeModal;
window.showModal = showModal;
window.showMessage = showMessage;
window.clearForm = clearForm;