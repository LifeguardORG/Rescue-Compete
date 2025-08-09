/**
 * QuestionInputScript.js
 * JavaScript-Funktionalität für die Fragen-Verwaltung
 * Mit dynamischen Frage-Blöcken für mehrere Fragen
 */

// Globale Variablen
let questionCount = 1; // Anzahl der Frage-Blöcke
const maxQuestions = 10;
const minQuestions = 1;
let currentDeleteForm = null;
let currentDeleteType = null; // 'pool', 'question', 'answer'

// DOM-Ready
document.addEventListener('DOMContentLoaded', function() {
    initializeQuestionInputView();
    initializeFormValidation();
    initializeQuestionManagement();
    initializeDeleteHandlers();
});

/**
 * Initialisiert die Question-Input-View
 */
function initializeQuestionInputView() {
    // Tab-Navigation basierend auf URL-Parameter
    const urlParams = new URLSearchParams(window.location.search);
    const currentView = urlParams.get('view') || 'pool_overview';

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

    // Event-Listener für Pool-Auswahl in Fragen-Übersicht
    const overviewPoolSelect = document.getElementById('overview_pool_select');
    if (overviewPoolSelect) {
        overviewPoolSelect.addEventListener('change', loadPoolQuestions);
    }

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
    // Pool-Erstellungsformular
    const createPoolForm = document.getElementById('createPoolForm');
    if (createPoolForm) {
        createPoolForm.addEventListener('submit', validateCreatePoolForm);
    }

    // Fragen-Erstellungsformular
    const createQuestionForm = document.getElementById('createQuestionForm');
    if (createQuestionForm) {
        createQuestionForm.addEventListener('submit', validateCreateQuestionForm);
    }

    // Event-Delegation für dynamische Textareas
    document.addEventListener('input', handleQuestionTextInput);
    document.addEventListener('paste', handleQuestionTextPaste);
}

/**
 * Initialisiert die Fragen-Verwaltung
 */
function initializeQuestionManagement() {
    const addQuestionBtn = document.getElementById('add-question-btn');
    const removeQuestionBtn = document.getElementById('remove-question-btn');

    if (addQuestionBtn) {
        addQuestionBtn.addEventListener('click', addQuestion);
    }

    if (removeQuestionBtn) {
        removeQuestionBtn.addEventListener('click', removeQuestion);
    }

    // Initiale Button-Zustände setzen
    updateQuestionButtons();
    updateQuestionsInfo();
}

/**
 * Initialisiert die Lösch-Handler
 */
function initializeDeleteHandlers() {
    // Event-Delegation für dynamische Lösch-Buttons
    document.addEventListener('click', function(e) {
        if (e.target && e.target.matches('button[type="submit"]')) {
            const form = e.target.closest('form');
            if (form && form.classList.contains('delete-form')) {
                e.preventDefault();

                // Bestimme den Lösch-Typ basierend auf den Form-Daten
                if (form.querySelector('input[name="delete_question"]')) {
                    handleDeleteQuestion(form);
                } else if (form.querySelector('input[name="delete_answer"]')) {
                    handleDeleteAnswer(form);
                } else if (form.querySelector('input[name="delete_pool"]')) {
                    handleDeletePool(form);
                }
            }
        }
    });
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

    // Fix für Navbar-Position nach Tab-Wechsel
    forceLayoutRecalculation();
}

/**
 * Lädt Fragen für den ausgewählten Pool
 */
function loadPoolQuestions() {
    const poolSelect = document.getElementById('overview_pool_select');
    if (poolSelect && poolSelect.value) {
        // Navigiere zur Fragen-Übersicht mit ausgewähltem Pool
        window.location.href = `QuestionInputView.php?view=question_overview&pool_id=${poolSelect.value}`;
    }
}

/**
 * Zeigt Fragen eines Pools an (für "Fragen anzeigen" Button)
 */
function viewPoolQuestions(poolId) {
    window.location.href = `QuestionInputView.php?view=question_overview&pool_id=${poolId}`;
}

/**
 * Validiert das Pool-Erstellungsformular
 */
function validateCreatePoolForm(event) {
    const nameInput = document.getElementById('pool_name');
    const name = nameInput?.value?.trim();

    if (!name) {
        event.preventDefault();
        showValidationMessage('pool_name', 'Bitte geben Sie einen Namen für den Fragenpool ein.');
        return false;
    } else if (name.length < 3) {
        event.preventDefault();
        showValidationMessage('pool_name', 'Der Name muss mindestens 3 Zeichen lang sein.');
        return false;
    } else if (name.length > 200) {
        event.preventDefault();
        showValidationMessage('pool_name', 'Der Name darf maximal 200 Zeichen lang sein.');
        return false;
    }

    // Show loading state
    const submitButton = event.target.querySelector('button[type="submit"]');
    if (submitButton) {
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="loading-spinner"></span> Wird erstellt...';
    }

    hideValidationMessage('pool_name');
    return true;
}

/**
 * Validiert das Fragen-Erstellungsformular
 */
function validateCreateQuestionForm(event) {
    let isValid = true;

    // Pool-Auswahl prüfen
    const poolSelect = document.getElementById('question_pool_id');
    if (!poolSelect?.value) {
        showValidationMessage('question_pool_id', 'Bitte wählen Sie einen Fragenpool aus.');
        isValid = false;
    } else {
        hideValidationMessage('question_pool_id');
    }

    // Alle Frage-Blöcke validieren
    const questionBlocks = document.querySelectorAll('.question-block');
    questionBlocks.forEach((block, index) => {
        const questionIndex = block.getAttribute('data-question-index');

        // Fragetext prüfen
        const questionTextarea = block.querySelector(`textarea[name="questions[${questionIndex}][text]"]`);
        const text = questionTextarea?.value?.trim();

        if (!text) {
            showValidationMessage(`question_text_${questionIndex}`, 'Bitte geben Sie einen Fragetext ein.');
            isValid = false;
        } else if (text.length > 200) {
            showValidationMessage(`question_text_${questionIndex}`, 'Fragetext darf maximal 200 Zeichen haben.');
            isValid = false;
        } else {
            hideValidationMessage(`question_text_${questionIndex}`);
        }

        // Antworten prüfen
        const answerInputs = block.querySelectorAll(`input[name^="questions[${questionIndex}][answers]"][name$="[text]"]`);
        let filledAnswers = 0;

        answerInputs.forEach(input => {
            if (input.value.trim()) {
                filledAnswers++;
            }
        });

        const answersContainerId = `answers-container-${questionIndex}`;
        if (filledAnswers < 2) {
            showValidationMessage(answersContainerId, `Frage ${parseInt(questionIndex) + 1}: Bitte geben Sie mindestens zwei Antworten ein.`);
            isValid = false;
        } else {
            hideValidationMessage(answersContainerId);
        }

        // Korrekte Antworten prüfen
        const correctAnswers = block.querySelectorAll(`input[name^="questions[${questionIndex}][answers]"][name$="[is_correct]"]:checked`);
        const correctAnswerCount = Array.from(correctAnswers).filter(checkbox => {
            const answerMatch = checkbox.name.match(/questions\[\d+\]\[answers\]\[(\d+)\]\[is_correct\]/);
            if (answerMatch) {
                const answerIndex = answerMatch[1];
                const textInput = block.querySelector(`input[name="questions[${questionIndex}][answers][${answerIndex}][text]"]`);
                return textInput && textInput.value.trim();
            }
            return false;
        }).length;

        if (correctAnswerCount === 0) {
            showValidationMessage(answersContainerId, `Frage ${parseInt(questionIndex) + 1}: Bitte markieren Sie mindestens eine ausgefüllte Antwort als korrekt.`);
            isValid = false;
        }
    });

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
 * Fügt einen neuen Frage-Block hinzu
 */
function addQuestion() {
    if (questionCount >= maxQuestions) {
        alert(`Maximal ${maxQuestions} Fragen möglich.`);
        return;
    }

    const container = document.getElementById('questions-container');
    const newQuestionBlock = document.createElement('div');
    newQuestionBlock.className = 'question-block';
    newQuestionBlock.setAttribute('data-question-index', questionCount);

    newQuestionBlock.innerHTML = `
        <div class="question-block-header">
            <h4>Frage ${questionCount + 1}</h4>
        </div>

        <div class="form-group">
            <label for="question_text_${questionCount}">Fragetext * (max. 200 Zeichen)</label>
            <textarea id="question_text_${questionCount}" name="questions[${questionCount}][text]" rows="3" required maxlength="200"
                      placeholder="Geben Sie hier Ihre Frage ein..."></textarea>
        </div>

        <div class="answers-section">
            <h5>Antworten:</h5>
            <div class="answer-container-header">
                <div class="answer-column">Antworttext</div>
                <div class="korrekt-column">Korrekt</div>
            </div>

            <div class="answer-container">
                <input type="text" name="questions[${questionCount}][answers][0][text]" class="answer-input" placeholder="Antwort 1" required>
                <label class="checkbox-label">
                    <input type="checkbox" name="questions[${questionCount}][answers][0][is_correct]" value="1">
                </label>
            </div>
            <div class="answer-container">
                <input type="text" name="questions[${questionCount}][answers][1][text]" class="answer-input" placeholder="Antwort 2" required>
                <label class="checkbox-label">
                    <input type="checkbox" name="questions[${questionCount}][answers][1][is_correct]" value="1">
                </label>
            </div>
            <div class="answer-container">
                <input type="text" name="questions[${questionCount}][answers][2][text]" class="answer-input" placeholder="Antwort 3" required>
                <label class="checkbox-label">
                    <input type="checkbox" name="questions[${questionCount}][answers][2][is_correct]" value="1">
                </label>
            </div>
            <div class="answer-container">
                <input type="text" name="questions[${questionCount}][answers][3][text]" class="answer-input" placeholder="Antwort 4" required>
                <label class="checkbox-label">
                    <input type="checkbox" name="questions[${questionCount}][answers][3][is_correct]" value="1">
                </label>
            </div>
        </div>
    `;

    container.appendChild(newQuestionBlock);
    questionCount++;

    updateQuestionButtons();
    updateQuestionsInfo();
    forceLayoutRecalculation();

    // Scroll zum neuen Frage-Block
    setTimeout(() => {
        newQuestionBlock.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }, 100);
}

/**
 * Entfernt den letzten Frage-Block
 */
function removeQuestion() {
    if (questionCount <= minQuestions) {
        alert(`Mindestens ${minQuestions} Frage erforderlich.`);
        return;
    }

    const container = document.getElementById('questions-container');
    const questionBlocks = container.querySelectorAll('.question-block');

    if (questionBlocks.length > 0) {
        // Das letzte Element entfernen
        container.removeChild(questionBlocks[questionBlocks.length - 1]);
        questionCount--;

        updateQuestionButtons();
        updateQuestionsInfo();
        forceLayoutRecalculation();
    }
}

/**
 * Aktualisiert die Fragen-Button-Zustände
 */
function updateQuestionButtons() {
    const addBtn = document.getElementById('add-question-btn');
    const removeBtn = document.getElementById('remove-question-btn');

    if (addBtn) {
        addBtn.disabled = (questionCount >= maxQuestions);
    }

    if (removeBtn) {
        removeBtn.disabled = (questionCount <= minQuestions);
    }
}

/**
 * Aktualisiert die Fragen-Info-Anzeige
 */
function updateQuestionsInfo() {
    const infoElement = document.getElementById('questions-count');
    if (infoElement) {
        infoElement.textContent = `${questionCount} ${questionCount === 1 ? 'Frage' : 'Fragen'}`;
    }
}

/**
 * Behandelt Eingabe in Fragetext-Felder (Zeichenlimit)
 */
function handleQuestionTextInput(event) {
    if (event.target.matches('textarea[name^="questions"][name$="[text]"]')) {
        const textarea = event.target;
        const maxLength = 200;

        if (textarea.value.length > maxLength) {
            textarea.value = textarea.value.substring(0, maxLength);
        }

        updateCharacterCount(textarea.id, maxLength);
    }
}

/**
 * Behandelt Paste-Events in Fragetext-Felder
 */
function handleQuestionTextPaste(event) {
    if (event.target.matches('textarea[name^="questions"][name$="[text]"]')) {
        const textarea = event.target;
        const maxLength = 200;

        setTimeout(() => {
            if (textarea.value.length > maxLength) {
                textarea.value = textarea.value.substring(0, maxLength);
            }
            updateCharacterCount(textarea.id, maxLength);
        }, 0);
    }
}

/**
 * Aktualisiert die Zeichenanzahl-Anzeige
 */
function updateCharacterCount(fieldId, maxLength) {
    const field = document.getElementById(fieldId);
    if (!field) return;

    const currentLength = field.value.length;
    let countElement = document.getElementById(fieldId + '_count');

    if (!countElement) {
        countElement = document.createElement('div');
        countElement.id = fieldId + '_count';
        countElement.className = 'character-count';
        field.parentNode.appendChild(countElement);
    }

    countElement.textContent = `${currentLength}/${maxLength} Zeichen`;
    countElement.className = 'character-count' + (currentLength >= maxLength * 0.9 ? ' warning' : '');
}

/**
 * Erzwingt eine Layout-Neuberechnung - Fix für Navbar-Position
 */
function forceLayoutRecalculation() {
    setTimeout(() => {
        const navbar = document.querySelector('.navbar');
        if (navbar) {
            const originalPosition = navbar.style.position;
            navbar.style.position = 'relative';
            navbar.offsetHeight;
            navbar.style.position = originalPosition || 'sticky';
        }

        const body = document.body;
        const originalMinHeight = body.style.minHeight;
        body.style.minHeight = 'auto';
        body.offsetHeight;
        body.style.minHeight = originalMinHeight || '100vh';

        const currentScroll = window.pageYOffset;
        window.scrollTo(0, currentScroll + 1);
        window.scrollTo(0, currentScroll);
    }, 10);
}

/**
 * Pool-Löschung behandeln
 */
function handleDeletePool(form) {
    currentDeleteForm = form;
    currentDeleteType = 'pool';
    const modal = document.getElementById('confirmDeletePoolModal');
    if (modal) {
        modal.classList.add('active');
    }
}

/**
 * Fragen-Löschung behandeln
 */
function handleDeleteQuestion(form) {
    currentDeleteForm = form;
    currentDeleteType = 'question';
    const modal = document.getElementById('confirmDeleteQuestionModal');
    if (modal) {
        modal.classList.add('active');
    }
}

/**
 * Antwort-Löschung behandeln
 */
function handleDeleteAnswer(form) {
    currentDeleteForm = form;
    currentDeleteType = 'answer';
    const modal = document.getElementById('confirmDeleteAnswerModal');
    if (modal) {
        modal.classList.add('active');
    }
}

/**
 * Pool löschen bestätigen (für onclick aus PHP)
 */
function confirmDeletePool(poolId, poolName) {
    // Erstelle ein temporäres Formular für die Übersichts-Tabelle
    const tempForm = document.createElement('form');
    tempForm.method = 'POST';
    tempForm.innerHTML = `
        <input type="hidden" name="delete_pool" value="1">
        <input type="hidden" name="delete_id" value="${poolId}">
    `;
    document.body.appendChild(tempForm);

    currentDeleteForm = tempForm;
    currentDeleteType = 'pool';

    const modal = document.getElementById('confirmDeletePoolModal');
    const message = modal?.querySelector('p');

    if (message) {
        message.textContent = `Möchten Sie den Fragenpool "${poolName}" und alle zugehörigen Fragen wirklich löschen?`;
    }

    if (modal) {
        modal.classList.add('active');
    }
}

/**
 * Pool löschen - Ausführung
 */
function deletePool() {
    if (currentDeleteForm) {
        currentDeleteForm.submit();
    }
    closeAllModals();
}

/**
 * Frage löschen - Ausführung
 */
function deleteQuestion() {
    if (currentDeleteForm) {
        currentDeleteForm.submit();
    }
    closeAllModals();
}

/**
 * Antwort löschen - Ausführung
 */
function deleteAnswer() {
    if (currentDeleteForm) {
        currentDeleteForm.submit();
    }
    closeAllModals();
}

/**
 * Validierungsmeldung anzeigen
 */
function showValidationMessage(fieldId, message) {
    const field = document.getElementById(fieldId);
    if (!field) {
        // Fallback für Container-IDs
        const container = document.querySelector(`[id="${fieldId}"], .answers-section`);
        if (container) {
            showValidationMessageForContainer(container, fieldId, message);
        }
        return;
    }

    // Entferne existierende Validierungsmeldungen
    const existingMessage = field.parentNode.querySelector('.validation-message');
    if (existingMessage) {
        existingMessage.remove();
    }

    // Erstelle neue Validierungsmeldung
    const messageElement = document.createElement('div');
    messageElement.className = 'validation-message show';
    messageElement.textContent = message;
    messageElement.style.display = 'block';

    // Füge Meldung nach dem Feld ein
    field.parentNode.insertBefore(messageElement, field.nextSibling);
    field.classList.add('error');

    // Scroll zur Fehlermeldung
    setTimeout(() => {
        messageElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }, 100);
}

/**
 * Validierungsmeldung für Container anzeigen
 */
function showValidationMessageForContainer(container, fieldId, message) {
    // Entferne existierende Validierungsmeldungen
    const existingMessage = container.querySelector('.validation-message');
    if (existingMessage) {
        existingMessage.remove();
    }

    // Erstelle neue Validierungsmeldung
    const messageElement = document.createElement('div');
    messageElement.className = 'validation-message show';
    messageElement.textContent = message;
    messageElement.style.display = 'block';

    // Füge Meldung am Ende des Containers hinzu
    container.appendChild(messageElement);

    // Scroll zur Fehlermeldung
    setTimeout(() => {
        messageElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }, 100);
}

/**
 * Validierungsmeldung verstecken
 */
function hideValidationMessage(fieldId) {
    const field = document.getElementById(fieldId);
    if (!field) {
        // Fallback für Container-IDs
        const containers = document.querySelectorAll('.answers-section, .question-block');
        containers.forEach(container => {
            const message = container.querySelector('.validation-message');
            if (message) {
                message.remove();
            }
        });
        return;
    }

    const messageElement = field.parentNode.querySelector('.validation-message');
    if (messageElement) {
        messageElement.remove();
    }
    field.classList.remove('error');
}

/**
 * Modal schließen
 */
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
    }

    // Reset delete state
    currentDeleteForm = null;
    currentDeleteType = null;
}

/**
 * Alle Modals schließen
 */
function closeAllModals() {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        modal.classList.remove('active');
    });

    // Reset delete state
    currentDeleteForm = null;
    currentDeleteType = null;
}

/**
 * Debounce-Funktion für Performance
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Event-Listener für dynamische Elemente
document.addEventListener('change', function(event) {
    // Pool-Auswahl in verschiedenen Formularen
    if (event.target.matches('#question_pool_id')) {
        hideValidationMessage('question_pool_id');
    }

    // Antwort-Checkboxen
    if (event.target.matches('input[name*="[is_correct]"]')) {
        if (event.target.checked) {
            // Finde den zugehörigen Frage-Block
            const questionBlock = event.target.closest('.question-block');
            if (questionBlock) {
                const questionIndex = questionBlock.getAttribute('data-question-index');
                hideValidationMessage(`answers-container-${questionIndex}`);
            }
        }
    }

    // Antwort-Textfelder
    if (event.target.matches('input[name*="[answers]"][name$="[text]"]')) {
        if (event.target.value.trim()) {
            // Finde den zugehörigen Frage-Block
            const questionBlock = event.target.closest('.question-block');
            if (questionBlock) {
                const questionIndex = questionBlock.getAttribute('data-question-index');
                hideValidationMessage(`answers-container-${questionIndex}`);
            }
        }
    }
});

// Layout-Fix beim Resize
window.addEventListener('resize', debounce(forceLayoutRecalculation, 250));

// Globale Funktionen für inline event handlers (legacy support)
window.showTab = showTab;
window.loadPoolQuestions = loadPoolQuestions;
window.viewPoolQuestions = viewPoolQuestions;
window.confirmDeletePool = confirmDeletePool;
window.deletePool = deletePool;
window.deleteQuestion = deleteQuestion;
window.deleteAnswer = deleteAnswer;
window.closeModal = closeModal;
window.addQuestion = addQuestion;
window.removeQuestion = removeQuestion;