/**
 * FormCollectionViewScript.js
 * JavaScript-Funktionalität für die FormCollection-Verwaltung
 */

// Globale Variablen
let currentCollectionId = null;
let currentDeleteId = null;
let currentAssignId = null;
let nameCheckTimeout = null;

// DOM-Ready
document.addEventListener('DOMContentLoaded', function() {
    initializeFormCollectionView();
    initializeFormValidation();
    initializeQrCodes();
    initializeCharacterCounter();
});

/**
 * Initialisiert die FormCollection-View
 */
function initializeFormCollectionView() {
    // Tab-Navigation basierend auf URL-Parameter
    const urlParams = new URLSearchParams(window.location.search);
    const currentView = urlParams.get('view') || 'overview';

    // Aktiven Tab setzen
    showTab(currentView);

    // Event-Listener für Tab-Buttons
    const tabButtons = document.querySelectorAll('.tab-button');
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const tabName = this.getAttribute('data-tab');
            if (tabName) {
                showTab(tabName);
            } else {
                // Fallback: onclick-Attribut auswerten
                const onclick = this.getAttribute('onclick');
                if (onclick && onclick.includes('showTab')) {
                    const match = onclick.match(/showTab\('([^']+)'\)/);
                    if (match) {
                        showTab(match[1]);
                    }
                }
            }
        });
    });

    // Event-Listener für Formular-Elemente
    const questionPoolSelect = document.getElementById('question_pool');
    if (questionPoolSelect) {
        questionPoolSelect.addEventListener('change', loadQuestions);
    }

    const qrCollectionSelect = document.getElementById('qr_collection_select');
    if (qrCollectionSelect) {
        qrCollectionSelect.addEventListener('change', loadCollectionTokens);
    }

    // Event-Listener für Action-Buttons
    const actionButtons = document.querySelectorAll('[data-action]');
    actionButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const action = this.getAttribute('data-action');
            handleActionButton(action, this);
        });
    });

    // Keyboard-Navigation für Tabs
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAllModals();
        }
    });
}

/**
 * Initialisiert die Formular-Validierung
 */
function initializeFormValidation() {
    const createForm = document.getElementById('createCollectionForm');
    if (createForm) {
        createForm.addEventListener('submit', validateCreateCollectionForm);

        // Live-Validierung für Felder
        const formsCountInput = document.getElementById('forms_count');
        if (formsCountInput) {
            formsCountInput.addEventListener('input', validateFormsCount);
        }

        const timeLimitInput = document.getElementById('time_limit');
        if (timeLimitInput) {
            timeLimitInput.addEventListener('input', validateTimeLimit);
        }

        const nameInput = document.getElementById('name');
        if (nameInput) {
            nameInput.addEventListener('blur', validateName);
            nameInput.addEventListener('input', function() {
                // Debounced name check
                clearTimeout(nameCheckTimeout);
                nameCheckTimeout = setTimeout(() => {
                    checkNameExists(this.value.trim());
                }, 500);
            });
        }

        const descriptionTextarea = document.getElementById('description');
        if (descriptionTextarea) {
            descriptionTextarea.addEventListener('input', validateDescription);
        }
    }
}

/**
 * Initialisiert den Character Counter für die Beschreibung
 */
function initializeCharacterCounter() {
    const descriptionTextarea = document.getElementById('description');
    const counter = document.getElementById('description-counter');

    if (descriptionTextarea && counter) {
        function updateCounter() {
            const currentLength = descriptionTextarea.value.length;
            counter.textContent = currentLength;

            // Farbliche Kennzeichnung bei Überschreitung
            const counterContainer = counter.parentElement;
            if (currentLength > 200) {
                counterContainer.classList.add('over-limit');
                counterContainer.classList.remove('near-limit');
            } else if (currentLength > 180) {
                counterContainer.classList.add('near-limit');
                counterContainer.classList.remove('over-limit');
            } else {
                counterContainer.classList.remove('near-limit', 'over-limit');
            }
        }

        // Initial count
        updateCounter();

        // Update bei Eingabe
        descriptionTextarea.addEventListener('input', updateCounter);
        descriptionTextarea.addEventListener('keyup', updateCounter);
        descriptionTextarea.addEventListener('paste', function() {
            // Kleine Verzögerung für paste events
            setTimeout(updateCounter, 10);
        });

        // Debug-Info für Entwicklung
        debugLog('Character counter initialized successfully');
    } else {
        debugLog('Character counter elements not found', {
            textarea: !!descriptionTextarea,
            counter: !!counter
        });
    }
}

/**
 * Prüft, ob ein Name bereits existiert (AJAX)
 */
function checkNameExists(name) {
    if (!name || name.length < 3) {
        return;
    }

    const currentUrl = window.location.pathname;
    const url = `${currentUrl}?ajax=1&action=check_name&name=${encodeURIComponent(name)}`;

    fetch(url, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.exists) {
                showValidationMessage('name', 'Eine Formular-Gruppe mit diesem Namen existiert bereits.');
            } else if (data.success && !data.exists) {
                hideValidationMessage('name');
            }
        })
        .catch(error => {
            console.error('Error checking name:', error);
        });
}

/**
 * Behandelt Action-Button-Klicks
 */
function handleActionButton(action, element) {
    switch(action) {
        case 'show-create-tab':
            showTab('create');
            break;
        case 'show-overview-tab':
            showTab('overview');
            break;
        case 'process-expired':
            processExpiredForms();
            break;
        case 'select-all-questions':
            selectAllQuestions();
            break;
        case 'deselect-all-questions':
            deselectAllQuestions();
            break;
        case 'view-collection':
            if (element) {
                const collectionId = element.getAttribute('data-collection-id');
                viewCollection(collectionId);
            }
            break;
        case 'view-tokens':
            if (element) {
                const collectionId = element.getAttribute('data-collection-id');
                viewTokens(collectionId);
            }
            break;
        case 'assign-to-all-teams':
            if (element) {
                const collectionId = element.getAttribute('data-collection-id');
                assignToAllTeams(collectionId);
            }
            break;
        case 'confirm-delete-collection':
            if (element) {
                const collectionId = element.getAttribute('data-collection-id');
                const collectionName = element.getAttribute('data-collection-name');
                confirmDeleteCollection(collectionId, collectionName);
            }
            break;
        case 'download-qr-code':
            if (element) {
                const qrIndex = element.getAttribute('data-qr-index');
                const qrTitle = element.getAttribute('data-qr-title');
                downloadQrCode(qrIndex, qrTitle);
            }
            break;
        default:
            console.warn('Unknown action:', action);
    }
}

/**
 * Tab-Funktionalität
 */
function showTab(tabName) {
    // Alle Tabs ausblenden
    const tabs = document.querySelectorAll('.tab-content');
    tabs.forEach(tab => {
        tab.classList.remove('active');
        tab.style.display = 'none';
    });

    // Tab-Buttons zurücksetzen
    const buttons = document.querySelectorAll('.tab-button');
    buttons.forEach(btn => btn.classList.remove('active'));

    // Gewählten Tab anzeigen
    const targetTab = document.getElementById(tabName);
    const targetButton = document.querySelector(`[data-tab="${tabName}"]`) ||
        document.querySelector(`[onclick*="showTab('${tabName}')"]`);

    if (targetTab) {
        targetTab.classList.add('active');
        targetTab.style.display = 'block';
    }

    if (targetButton) {
        targetButton.classList.add('active');
    }

    // URL aktualisieren ohne Reload
    try {
        const url = new URL(window.location);
        url.searchParams.set('view', tabName);
        window.history.pushState({}, '', url);
    } catch (e) {
        // Fallback für ältere Browser
        console.warn('History API not supported');
    }
}

/**
 * Lädt Fragen aus ausgewähltem Pool
 */
function loadQuestions() {
    const poolSelect = document.getElementById('question_pool');
    const questionsContainer = document.getElementById('questionsContainer');
    const questionsList = document.getElementById('questionsList');

    if (!poolSelect || !poolSelect.value) {
        if (questionsContainer) {
            questionsContainer.style.display = 'none';
        }
        hideValidationMessage('question_pool');
        return;
    }

    // Loading-Indikator anzeigen
    if (questionsList) {
        questionsList.innerHTML = '<div class="loading-spinner">Fragen werden geladen...</div>';
    }

    // AJAX-Request an die aktuelle Seite
    const currentUrl = window.location.pathname;
    const url = `${currentUrl}?ajax=1&action=load_questions&pool_id=${encodeURIComponent(poolSelect.value)}`;

    fetch(url, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('Server gab kein JSON zurück');
            }

            return response.json();
        })
        .then(data => {
            if (data.success) {
                renderQuestionsList(data.questions);
                if (questionsContainer) {
                    questionsContainer.style.display = 'block';
                }

                updateTotalQuestionsCount(data.questions.length);
                hideValidationMessage('question_pool');
            } else {
                throw new Error(data.message || 'Unbekannter Fehler');
            }
        })
        .catch(error => {
            console.error('Error loading questions:', error);
            if (questionsList) {
                questionsList.innerHTML = `<div class="error">Fehler beim Laden der Fragen: ${error.message}</div>`;
            }
            showValidationMessage('question_pool', `Fehler beim Laden der Fragen: ${error.message}`);
        });
}

/**
 * Rendert die Fragenliste
 */
function renderQuestionsList(questions) {
    const questionsList = document.getElementById('questionsList');
    if (!questionsList) return;

    questionsList.innerHTML = '';

    if (questions.length === 0) {
        questionsList.innerHTML = '<div class="no-data">Keine Fragen in diesem Pool vorhanden.</div>';
        return;
    }

    questions.forEach(question => {
        const div = document.createElement('div');
        div.className = 'question-item';
        div.innerHTML = `
            <label>
                <input type="checkbox" name="question_ids[]" value="${question.ID}">
                ${escapeHtml(question.Text)}
            </label>
        `;
        questionsList.appendChild(div);
    });

    // Event-Listener für neue Checkboxen hinzufügen
    const checkboxes = questionsList.querySelectorAll('input[type="checkbox"]');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', validateQuestionSelection);
    });
}

/**
 * Aktualisiert die Gesamtanzahl der Fragen
 */
function updateTotalQuestionsCount(count) {
    const totalQuestionsInput = document.getElementById('total_questions');
    if (totalQuestionsInput) {
        totalQuestionsInput.value = count;
    }

    validateQuestionSelection();
}

/**
 * Alle Fragen auswählen
 */
function selectAllQuestions() {
    const checkboxes = document.querySelectorAll('input[name="question_ids[]"]');
    checkboxes.forEach(cb => {
        cb.checked = true;
    });
    validateQuestionSelection();
}

/**
 * Alle Fragen abwählen
 */
function deselectAllQuestions() {
    const checkboxes = document.querySelectorAll('input[name="question_ids[]"]');
    checkboxes.forEach(cb => {
        cb.checked = false;
    });
    validateQuestionSelection();
}

/**
 * Validiert den Namen der Collection
 */
function validateName() {
    const nameInput = document.getElementById('name');
    const value = nameInput?.value?.trim();

    if (!value) {
        showValidationMessage('name', 'Bitte geben Sie einen Namen für die Formular-Gruppe ein.');
        return false;
    } else if (value.length < 3) {
        showValidationMessage('name', 'Der Name muss mindestens 3 Zeichen lang sein.');
        return false;
    } else if (value.length > 255) {
        showValidationMessage('name', 'Der Name darf maximal 255 Zeichen lang sein.');
        return false;
    } else {
        hideValidationMessage('name');
        return true;
    }
}

/**
 * Validiert die Beschreibung
 */
function validateDescription() {
    const descriptionTextarea = document.getElementById('description');
    const value = descriptionTextarea?.value?.trim() || '';

    if (value.length > 200) {
        showValidationMessage('description', 'Die Beschreibung darf maximal 200 Zeichen lang sein.');
        return false;
    } else {
        hideValidationMessage('description');
        return true;
    }
}

/**
 * Validiert die Fragenauswahl
 */
function validateQuestionSelection() {
    const selectedQuestions = document.querySelectorAll('input[name="question_ids[]"]:checked');
    const formsCount = parseInt(document.getElementById('forms_count')?.value || 1);

    if (selectedQuestions.length === 0) {
        showValidationMessage('question_pool', 'Bitte wählen Sie mindestens eine Frage aus.');
        return false;
    } else if (selectedQuestions.length < formsCount) {
        showValidationMessage('question_pool', `Sie müssen mindestens ${formsCount} Fragen auswählen (mindestens eine pro Formular).`);
        return false;
    } else {
        hideValidationMessage('question_pool');
        return true;
    }
}

/**
 * Validiert die Anzahl der Formulare
 */
function validateFormsCount() {
    const formsCountInput = document.getElementById('forms_count');
    const value = parseInt(formsCountInput?.value);

    if (isNaN(value) || value < 1) {
        showValidationMessage('forms_count', 'Die Anzahl der Formulare muss mindestens 1 sein.');
        return false;
    } else if (value > 20) {
        showValidationMessage('forms_count', 'Die Anzahl der Formulare darf maximal 20 sein.');
        return false;
    } else {
        hideValidationMessage('forms_count');
        validateQuestionSelection(); // Re-validate questions
        return true;
    }
}

/**
 * Validiert das Zeitlimit
 */
function validateTimeLimit() {
    const timeLimitInput = document.getElementById('time_limit');
    const value = parseInt(timeLimitInput?.value);

    if (isNaN(value) || value < 10) {
        showValidationMessage('time_limit', 'Das Zeitlimit muss mindestens 10 Sekunden betragen.');
        return false;
    } else if (value > 1800) {
        showValidationMessage('time_limit', 'Das Zeitlimit darf maximal 30 Minuten (1800 Sekunden) betragen.');
        return false;
    } else {
        hideValidationMessage('time_limit');
        return true;
    }
}

/**
 * Validiert das Erstellungsformular
 */
function validateCreateCollectionForm(event) {
    let isValid = true;

    // Name prüfen
    if (!validateName()) isValid = false;

    // Beschreibung prüfen
    if (!validateDescription()) isValid = false;

    // Fragenpool prüfen
    const poolSelect = document.getElementById('question_pool');
    if (!poolSelect?.value) {
        showValidationMessage('question_pool', 'Bitte wählen Sie einen Fragenpool aus.');
        isValid = false;
    }

    // Weitere Validierungen
    if (!validateFormsCount()) isValid = false;
    if (!validateTimeLimit()) isValid = false;
    if (!validateQuestionSelection()) isValid = false;

    if (!isValid) {
        event.preventDefault();

        // Scroll to first error
        const firstError = document.querySelector('.validation-message.show');
        if (firstError) {
            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        return false;
    }

    // Show loading state
    const submitButton = event.target.querySelector('button[type="submit"]');
    if (submitButton) {
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="loading-spinner"></span> Wird erstellt...';
    }

    return true;
}

/**
 * Zeigt Validierungsmeldung
 */
function showValidationMessage(fieldId, message) {
    const field = document.getElementById(fieldId);
    if (!field) return;

    let messageElement = field.parentNode.querySelector('.validation-message');
    if (!messageElement) {
        messageElement = document.createElement('div');
        messageElement.className = 'validation-message';
        field.parentNode.appendChild(messageElement);
    }

    messageElement.textContent = message;
    messageElement.classList.add('show');
    field.classList.add('error');
}

/**
 * Versteckt Validierungsmeldung
 */
function hideValidationMessage(fieldId) {
    const field = document.getElementById(fieldId);
    if (!field) return;

    const messageElement = field.parentNode.querySelector('.validation-message');
    if (messageElement) {
        messageElement.classList.remove('show');
    }
    field.classList.remove('error');
}

/**
 * Collection-Details anzeigen
 */
function viewCollection(collectionId) {
    window.location.href = `FormCollectionView.php?action=view_collection&collection_id=${collectionId}`;
}

/**
 * QR-Codes anzeigen
 */
function viewTokens(collectionId) {
    window.location.href = `FormCollectionView.php?view=qrcodes&action=view_tokens&collection_id=${collectionId}`;
}

/**
 * Collection-Tokens laden
 */
function loadCollectionTokens() {
    const select = document.getElementById('qr_collection_select');
    if (select?.value) {
        window.location.href = `FormCollectionView.php?view=qrcodes&action=view_tokens&collection_id=${select.value}`;
    }
}

/**
 * QR-Codes initialisieren
 */
function initializeQrCodes() {
    const qrContainers = document.querySelectorAll('[id^="qrcode-"]');
    if (qrContainers.length === 0) return;

    // Warten bis QRCode-Bibliothek geladen ist
    if (typeof QRCode === 'undefined') {
        setTimeout(initializeQrCodes, 100);
        return;
    }

    qrContainers.forEach(container => {
        const url = container.getAttribute('data-url');
        if (url) {
            try {
                new QRCode(container, {
                    text: url,
                    width: 180,
                    height: 180,
                    colorDark: "#008ccd",
                    colorLight: "#ffffff",
                    correctLevel: QRCode.CorrectLevel.H
                });
            } catch (error) {
                console.error('Error generating QR code:', error);
                container.innerHTML = '<div class="error">Fehler beim Generieren des QR-Codes</div>';
            }
        }
    });
}

/**
 * QR-Code herunterladen
 */
function downloadQrCode(index, title) {
    const canvas = document.querySelector(`#qrcode-${index} canvas`);
    if (canvas) {
        try {
            const link = document.createElement('a');
            link.download = `qrcode-${title.replace(/[^a-zA-Z0-9]/g, '-').toLowerCase()}.png`;
            link.href = canvas.toDataURL('image/png');
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        } catch (error) {
            console.error('Error downloading QR code:', error);
            alert('Fehler beim Herunterladen des QR-Codes');
        }
    } else {
        alert('QR-Code noch nicht vollständig geladen. Bitte versuchen Sie es erneut.');
    }
}

/**
 * Collection löschen - Bestätigung
 */
function confirmDeleteCollection(collectionId, collectionName) {
    currentDeleteId = collectionId;
    const modal = document.getElementById('confirmDeleteCollectionModal');
    const message = modal?.querySelector('p');

    if (message) {
        message.textContent = `Möchten Sie die Formular-Gruppe "${collectionName}" wirklich löschen? Alle zugehörigen Formulare, Antworten und QR-Codes werden ebenfalls gelöscht.`;
    }

    if (modal) {
        modal.classList.add('active');
    }
}

/**
 * Collection löschen - Ausführung
 */
function deleteCollection() {
    if (currentDeleteId) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete_collection">
            <input type="hidden" name="collection_id" value="${currentDeleteId}">
            <input type="hidden" name="confirm_delete" value="1">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

/**
 * Allen Teams zuweisen - Bestätigung
 */
function assignToAllTeams(collectionId) {
    currentAssignId = collectionId;
    const modal = document.getElementById('confirmAssignToAllTeamsModal');
    if (modal) {
        modal.classList.add('active');
    }
}

/**
 * Allen Teams zuweisen - Ausführung
 */
function assignToAllTeamsConfirmed() {
    if (currentAssignId) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="assign_to_all_teams">
            <input type="hidden" name="collection_id" value="${currentAssignId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

/**
 * Abgelaufene Formulare verarbeiten
 */
function processExpiredForms() {
    if (confirm('Möchten Sie alle abgelaufenen Formulare verarbeiten? Diese Aktion kann nicht rückgängig gemacht werden.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = '<input type="hidden" name="action" value="process_expired">';
        document.body.appendChild(form);
        form.submit();
    }
}

/**
 * Modal schließen
 */
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
    }
}

/**
 * Alle Modals schließen
 */
function closeAllModals() {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        modal.classList.remove('active');
    });
}

/**
 * HTML-Escaping für Sicherheit
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Utility-Funktion für Debugging
 */
function debugLog(message, data = null) {
    if (console && console.log && window.location.hostname === 'localhost') {
        console.log(`[FormCollectionView] ${message}`, data);
    }
}

/**
 * Formatiert Zahlen für bessere Lesbarkeit
 */
function formatNumber(num) {
    return new Intl.NumberFormat('de-DE').format(num);
}

/**
 * Formatiert Zeit in Minuten und Sekunden
 */
function formatTime(seconds) {
    const minutes = Math.floor(seconds / 60);
    const remainingSeconds = seconds % 60;
    return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
}

// Event-Listener für dynamische Elemente
document.addEventListener('change', function(event) {
    // Checkbox-Änderungen für Fragenauswahl
    if (event.target.matches('input[name="question_ids[]"]')) {
        validateQuestionSelection();
    }
});

// Globale Fehlerbehandlung
window.addEventListener('error', function(event) {
    debugLog('JavaScript Error:', event.error);
});

// Performance-Monitoring
window.addEventListener('load', function() {
    debugLog('Page fully loaded');
});

// Expose functions for inline event handlers (legacy support)
window.showTab = showTab;
window.loadQuestions = loadQuestions;
window.selectAllQuestions = selectAllQuestions;
window.deselectAllQuestions = deselectAllQuestions;
window.viewCollection = viewCollection;
window.viewTokens = viewTokens;
window.loadCollectionTokens = loadCollectionTokens;
window.downloadQrCode = downloadQrCode;
window.confirmDeleteCollection = confirmDeleteCollection;
window.deleteCollection = deleteCollection;
window.assignToAllTeams = assignToAllTeams;
window.assignToAllTeamsConfirmed = assignToAllTeamsConfirmed;
window.processExpiredForms = processExpiredForms;
window.closeModal = closeModal;
window.initializeCharacterCounter = initializeCharacterCounter;