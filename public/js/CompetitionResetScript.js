/**
 * CompetitionResetScript.js - JavaScript-Funktionen für die Wettkampf-Reset-Seite
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('CompetitionReset: Initialisierung gestartet');

    // Teste ob Modals existieren
    testModalExistence();

    // Initialisierung aller Funktionen
    initializeResetForms();
    initializeMessageHandling();

    console.log('CompetitionReset: Alle Funktionen initialisiert');
});

/**
 * Testet ob alle Modals existieren
 */
function testModalExistence() {
    const resetTypes = ['staffeln', 'stationen', 'protokolle', 'mannschaften', 'formulare', 'wertungen', 'users', 'all'];

    resetTypes.forEach(type => {
        const modalId = `modal-${type}`;
        const modal = document.getElementById(modalId);
        if (modal) {
            console.log(`CompetitionReset: Modal ${modalId} gefunden`);
        } else {
            console.error(`CompetitionReset: Modal ${modalId} NICHT gefunden!`);
        }
    });
}

/**
 * Initialisiert die Reset-Formulare und ihre Funktionalität
 */
function initializeResetForms() {
    // Alle Reset-Buttons finden
    const resetButtons = document.querySelectorAll('.reset-button');

    console.log(`CompetitionReset: ${resetButtons.length} Reset-Buttons gefunden`);

    resetButtons.forEach((button, index) => {
        const resetType = button.getAttribute('data-reset-type');
        const form = button.closest('.reset-form');
        const checkbox = form.querySelector('input[type="checkbox"]');

        console.log(`CompetitionReset: Button ${index} - Reset-Type: ${resetType}`);

        // Checkbox-Funktionalität
        if (checkbox) {
            // Button initial deaktivieren
            button.disabled = true;
            button.style.opacity = '0.6';

            // Checkbox Change-Event
            checkbox.addEventListener('change', function() {
                button.disabled = !this.checked;
                button.style.opacity = this.checked ? '1' : '0.6';
                console.log(`CompetitionReset: Checkbox für ${resetType} ${this.checked ? 'aktiviert' : 'deaktiviert'}`);
            });
        }

        // Button Click-Event
        button.addEventListener('click', function(e) {
            e.preventDefault();
            console.log(`CompetitionReset: Button für ${resetType} geklickt`);

            if (!checkbox.checked) {
                alert('Bitte bestätigen Sie die Löschung durch Aktivieren der Checkbox.');
                return;
            }

            // Modal anzeigen - WICHTIG: Hier wurde modal- statt confirm- verwendet
            const modalId = `modal-${resetType}`;
            console.log(`CompetitionReset: Zeige Modal ${modalId}`);
            showModal(modalId);
        });
    });
}

/**
 * Zeigt ein Modal an
 * @param {string} modalId - Die ID des Modals
 */
function showModal(modalId) {
    console.log(`CompetitionReset: Versuche Modal ${modalId} anzuzeigen`);

    const modal = document.getElementById(modalId);
    if (modal) {
        console.log(`CompetitionReset: Modal ${modalId} gefunden, zeige es an`);

        // Modal anzeigen
        modal.style.display = 'flex';
        modal.classList.add('active');

        // Z-Index setzen um sicherzustellen, dass es über allem liegt
        modal.style.zIndex = '10000';

        console.log(`CompetitionReset: Modal ${modalId} sollte jetzt sichtbar sein`);
        console.log(`CompetitionReset: Modal display: ${modal.style.display}`);
        console.log(`CompetitionReset: Modal classList: ${modal.classList.toString()}`);

    } else {
        console.error(`CompetitionReset: Modal ${modalId} nicht gefunden!`);
        alert('Fehler: Bestätigungsdialog konnte nicht angezeigt werden.');

        // Debug: Alle verfügbaren Modals auflisten
        const allModals = document.querySelectorAll('.modal');
        console.log(`CompetitionReset: Verfügbare Modals: ${allModals.length}`);
        allModals.forEach((m, i) => {
            console.log(`CompetitionReset: Modal ${i}: ID = ${m.id}`);
        });
    }
}

/**
 * Schließt ein Modal
 * @param {string} modalId - Die ID des Modals
 */
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
        modal.style.display = 'none';
        console.log(`CompetitionReset: Modal ${modalId} geschlossen`);
    }
}

/**
 * Sendet ein Reset-Formular ab
 * @param {string} resetType - Der Reset-Typ (z.B. 'staffeln', 'all')
 */
function submitResetForm(resetType) {
    console.log(`CompetitionReset: Sende Formular für ${resetType}`);

    const formId = `form-${resetType}`;
    const form = document.getElementById(formId);

    if (!form) {
        console.error(`CompetitionReset: Formular ${formId} nicht gefunden`);
        alert('Fehler: Formular konnte nicht gefunden werden.');
        return;
    }

    // Button deaktivieren um Doppel-Submissions zu verhindern
    const button = form.querySelector('.reset-button');
    if (button) {
        button.disabled = true;
        button.textContent = 'Wird verarbeitet...';
        button.style.opacity = '0.6';
    }

    // Modal schließen - WICHTIG: Hier wurde modal- statt confirm- verwendet
    const modalId = `modal-${resetType}`;
    closeModal(modalId);

    console.log(`CompetitionReset: Formular ${formId} wird abgesendet`);

    // Formular absenden
    form.submit();
}

/**
 * Initialisiert die Behandlung von Nachrichten
 */
function initializeMessageHandling() {
    // Erfolgs- oder Fehlermeldungen automatisch ausblenden
    const messageBoxes = document.querySelectorAll('.message-box');

    messageBoxes.forEach(messageBox => {
        // Nach 8 Sekunden automatisch ausblenden
        setTimeout(() => {
            fadeOutElement(messageBox);
        }, 8000);

        // Smooth scroll zur Nachricht falls sie außerhalb des Viewports ist
        if (messageBox.classList.contains('success') || messageBox.classList.contains('error')) {
            messageBox.scrollIntoView({
                behavior: 'smooth',
                block: 'start',
                inline: 'nearest'
            });
        }
    });

    console.log(`CompetitionReset: ${messageBoxes.length} Nachrichten-Boxen initialisiert`);
}

/**
 * Blendet ein Element sanft aus
 * @param {HTMLElement} element - Das auszublendende Element
 */
function fadeOutElement(element) {
    if (!element) return;

    element.style.transition = 'opacity 0.5s ease';
    element.style.opacity = '0';

    setTimeout(() => {
        element.style.display = 'none';
    }, 500);
}

// Modal-Klick-Handler für das Schließen bei Klick außerhalb
document.addEventListener('click', function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.classList.remove('active');
        event.target.style.display = 'none';
        console.log('CompetitionReset: Modal durch Außenklick geschlossen');
    }
});

// Escape-Taste zum Schließen von Modals
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const activeModals = document.querySelectorAll('.modal.active');
        activeModals.forEach(modal => {
            modal.classList.remove('active');
            modal.style.display = 'none';
        });
        console.log('CompetitionReset: Modals durch ESC-Taste geschlossen');
    }
});

// Globale Error-Handler für unerwartete Fehler
window.addEventListener('error', function(event) {
    console.error('CompetitionReset: Unerwarteter Fehler:', event.error);
});

// Debug-Funktion um zu testen ob alles geladen wurde
console.log('CompetitionReset: Script komplett geladen');

// Globale Debug-Funktion
window.competitionResetDebug = {
    showModal: showModal,
    closeModal: closeModal,
    testAllModals: function() {
        const types = ['staffeln', 'stationen', 'protokolle', 'mannschaften', 'formulare', 'wertungen', 'users', 'all'];
        types.forEach(type => {
            console.log(`Testing modal: modal-${type}`);
            showModal(`modal-${type}`);
            setTimeout(() => closeModal(`modal-${type}`), 2000);
        });
    }
};