/**
 * QuestionFormInputScript.js - Verwaltung der Frageformulare
 *
 * Stellt Funktionalität zur Verfügung für:
 * - Fragen aus einem Pool laden
 * - Fragen auswählen/abwählen
 * - Fragen-Verteilung auf mehrere Formulare
 * - Erstellung von Formularen
 *
 * @author Jonas Richter & Sven Meiburg - RescueCompete
 * @version 1.0.1 (mit Fix für Formularübermittlung)
 */

// Globale Variablen
let allQuestions = [];  // Alle verfügbaren Fragen aus dem Pool
let selectedQuestions = new Set(); // Ausgewählte Fragen (als Set für eindeutige Werte)
let deleteForm = null; // Referenz zum Formular, das gelöscht werden soll

/**
 * Initialisierung beim Laden der Seite
 */
document.addEventListener("DOMContentLoaded", function() {
    initializeDeleteButtons();
    initializePoolSelection();
    initializeFormDistribution();
    initializeValidation();
    initializeFormSubmission(); // NEU: Initialisiert die Formularübermittlung

    // Wenn ein Pool in der URL ausgewählt wurde, lade direkt die Fragen
    if (typeof selectedPoolId !== 'undefined' && selectedPoolId) {
        fetchQuestions(selectedPoolId);
    }
});

/**
 * Initialisiert die Löschen-Buttons und ihre Event-Handler
 */
function initializeDeleteButtons() {
    const deleteForms = document.querySelectorAll('.delete-form');
    deleteForms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            deleteForm = this;
            const modal = document.getElementById('confirmDeleteModal');
            if (modal) {
                modal.classList.add('active');
            }
        });
    });
}

/**
 * Initialisiert die Pool-Auswahl und den Fragenlade-Button
 */
function initializePoolSelection() {
    const poolSelect = document.getElementById('question_pool');
    const loadQuestionsBtn = document.getElementById('load-questions-btn');

    if (loadQuestionsBtn) {
        loadQuestionsBtn.addEventListener('click', function() {
            const poolId = poolSelect.value;
            if (poolId) {
                fetchQuestions(poolId);
            } else {
                alert('Bitte wählen Sie zuerst einen Fragenpool aus.');
            }
        });
    }

    // Wenn ein Pool ausgewählt wird, lade automatisch die Fragen
    if (poolSelect) {
        poolSelect.addEventListener('change', function() {
            if (this.value) {
                fetchQuestions(this.value);
            }
        });
    }
}

/**
 * Initialisiert die Funktion zur Verteilung von Fragen auf mehrere Formulare
 */
function initializeFormDistribution() {
    const distributeToggle = document.getElementById('distribute-toggle');
    const formCountContainer = document.getElementById('form-count-container');
    const formCountInput = document.getElementById('form_count');
    const distributionInfo = document.getElementById('distribution-info');

    if (distributeToggle && formCountContainer) {
        distributeToggle.addEventListener('change', function() {
            if (this.checked) {
                formCountContainer.classList.remove('hidden');
                updateDistributionInfo();
            } else {
                formCountContainer.classList.add('hidden');
                document.getElementById('form_count').value = 1;
                if (distributionInfo) {
                    distributionInfo.classList.add('hidden');
                }
            }
        });
    }

    // Berechnung der Fragenverteilung aktualisieren, wenn sich die Anzahl der Formulare ändert
    if (formCountInput) {
        formCountInput.addEventListener('input', updateDistributionInfo);
    }
}

/**
 * NEU: Initialisiert die Formularübermittlung, um sicherzustellen, dass alle ausgewählten Fragen
 * korrekt als versteckte Formularfelder übermittelt werden
 */
function initializeFormSubmission() {
    const form = document.getElementById('questionform-creator');
    if (!form) return;

    // Speichern der Original-Submission-Funktion
    const originalSubmit = form.onsubmit;

    // Überschreiben der Submission-Funktion
    form.onsubmit = function(e) {
        // Falls eine Original-Funktion existiert, rufen wir sie auf
        if (originalSubmit) {
            const result = originalSubmit.call(this, e);
            if (result === false) {
                return false; // Formular nicht absenden, wenn Validierung fehlschlägt
            }
        }

        // BUGFIX: Entferne alle bestehenden versteckten Felder für ausgewählte Fragen
        const existingHiddenFields = form.querySelectorAll('input[type="hidden"][name="selected_questions[]"]');
        existingHiddenFields.forEach(field => field.remove());

        // BUGFIX: Erstelle für jede ausgewählte Frage ein verstecktes Feld
        selectedQuestions.forEach(questionId => {
            const hiddenField = document.createElement('input');
            hiddenField.type = 'hidden';
            hiddenField.name = 'selected_questions[]';
            hiddenField.value = questionId;
            form.appendChild(hiddenField);
        });

        console.log(`${selectedQuestions.size} versteckte Felder für ausgewählte Fragen erstellt`);

        // Debug-Log: Zeige die tatsächlich vorhandenen Felder
        const fieldsAfterCreation = form.querySelectorAll('input[name="selected_questions[]"]');
        console.log(`Formular enthält nach Fix ${fieldsAfterCreation.length} selected_questions[] Felder`);

        return true; // Formular absenden
    };
}

/**
 * Initialisiert die Formular-Validierung
 */
function initializeValidation() {
    const form = document.getElementById('questionform-creator');
    if (form) {
        form.addEventListener('submit', function(e) {
            // Prüfe auf Stationsauswahl
            const stationId = document.getElementById('station').value;
            if (!stationId) {
                e.preventDefault();
                alert('Bitte wählen Sie eine Station aus.');
                return;
            }

            // Prüfe auf Formulartitel
            const formTitle = document.getElementById('form_title').value.trim();
            if (!formTitle) {
                e.preventDefault();
                alert('Bitte geben Sie einen Titel für das Formular ein.');
                return;
            }

            // Prüfe auf ausgewählte Fragen
            if (selectedQuestions.size === 0) {
                e.preventDefault();
                alert('Bitte wählen Sie mindestens eine Frage aus.');
                return;
            }

            // Bei mehreren Formularen: Prüfe, ob genug Fragen ausgewählt wurden
            const distributeToggle = document.getElementById('distribute-toggle');
            if (distributeToggle && distributeToggle.checked) {
                const formCount = parseInt(document.getElementById('form_count').value, 10);
                if (selectedQuestions.size < formCount) {
                    e.preventDefault();
                    alert(`Sie müssen mindestens ${formCount} Fragen auswählen, um sie auf ${formCount} Formulare zu verteilen.`);
                    return;
                }
            }
        });
    }
}

/**
 * Lädt Fragen eines bestimmten Pools vom Server
 *
 * @param {number} poolId - ID des Fragenpools
 */
function fetchQuestions(poolId) {
    // Anzeige von Lade-Indikator
    const container = document.getElementById('questions-list');
    if (container) {
        container.innerHTML = '<div class="loading-spinner"></div>';
    }

    // AJAX-Anfrage an den Controller
    fetch(`${window.location.pathname}?action=getQuestionsByPool&poolId=${poolId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Netzwerkantwort war nicht ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                displayQuestions(data.questions);
            } else {
                alert('Fehler beim Laden der Fragen: ' + (data.message || 'Unbekannter Fehler'));
            }
        })
        .catch(error => {
            console.error('Fehler beim Laden der Fragen:', error);
            displayQuestionsFallback(poolId);
        });
}

/**
 * Fallback-Funktion für den Fall, dass die AJAX-Abfrage fehlschlägt
 * Zeigt Demo-Fragen an, wenn keine echten Fragen geladen werden können
 *
 * @param {number} poolId - ID des Fragenpools
 */
function displayQuestionsFallback(poolId) {
    // Hardcodierte Beispielfragen
    const demoQuestions = [
        { ID: 1, Text: "Wie lautet die korrekte Maßnahme bei einem Druckverband?" },
        { ID: 2, Text: "Welche Symptome zeigt ein Patient im Schock?" },
        { ID: 3, Text: "Was ist die korrekte Seitenlage?" },
        { ID: 4, Text: "Wie viele Zyklen umfasst die Herz-Lungen-Wiederbelebung?" },
        { ID: 5, Text: "Was ist bei einer Verbrennung dritten Grades zu beachten?" },
        { ID: 6, Text: "Wozu dient ein Tourniquet?" },
        { ID: 7, Text: "Wie erkennt man eine arterielle Blutung?" },
        { ID: 8, Text: "Was ist der Scherengriff?" },
        { ID: 9, Text: "Wie ist die richtige Drucktechnik bei der Herz-Druck-Massage?" },
        { ID: 10, Text: "Was ist der Unterschied zwischen einer Hypothermie und einer Hyperthermie?" }
    ];

    allQuestions = demoQuestions;
    renderQuestions(demoQuestions);
}

/**
 * Verarbeitet die vom Server geladenen Fragen
 *
 * @param {Array} questions - Liste der geladenen Fragen
 */
function displayQuestions(questions) {
    allQuestions = questions;
    renderQuestions(questions);
}

/**
 * Rendert die Fragen als Checkboxen in der UI
 *
 * @param {Array} questions - Liste der anzuzeigenden Fragen
 */
function renderQuestions(questions) {
    const container = document.getElementById('questions-list');
    if (!container) return;

    container.innerHTML = '';

    if (questions.length === 0) {
        container.innerHTML = '<p class="info-text">Keine Fragen in diesem Pool gefunden.</p>';
        return;
    }

    // Aktualisiere Zählerwerte
    document.getElementById('total-count').textContent = questions.length;
    updateSelectedCounter();

    // Erstelle Checkbox-Elemente für jede Frage
    questions.forEach(question => {
        const questionItem = document.createElement('div');
        questionItem.className = 'question-item';

        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.name = 'selected_questions[]';
        checkbox.value = question.ID;
        checkbox.id = 'question-' + question.ID;

        // Prüfe, ob die Frage bereits ausgewählt war
        if (selectedQuestions.has(parseInt(question.ID))) {
            checkbox.checked = true;
        }

        checkbox.addEventListener('change', function() {
            if (this.checked) {
                selectedQuestions.add(parseInt(question.ID));
            } else {
                selectedQuestions.delete(parseInt(question.ID));
            }
            updateSelectedCounter();
            updateDistributionInfo();

            // Überprüfe "Alle auswählen" Checkbox
            const selectAllCheckbox = document.getElementById('select-all');
            if (selectAllCheckbox) {
                selectAllCheckbox.checked = selectedQuestions.size === allQuestions.length;
            }
        });

        const label = document.createElement('label');
        label.htmlFor = 'question-' + question.ID;
        label.className = 'question-text';
        label.textContent = question.Text;

        // Füge Elemente zum Container hinzu
        questionItem.appendChild(checkbox);
        questionItem.appendChild(label);
        container.appendChild(questionItem);
    });
}

/**
 * Aktualisiert den Zähler der ausgewählten Fragen
 */
function updateSelectedCounter() {
    const selectedCountElement = document.getElementById('selected-count');
    if (selectedCountElement) {
        selectedCountElement.textContent = selectedQuestions.size;
    }

    // Aktualisiere auch die Verteilungsinformation
    updateDistributionInfo();
}

/**
 * Umschaltet die Auswahl aller Fragen (an/aus)
 */
function toggleAllQuestions() {
    const selectAllCheckbox = document.getElementById('select-all');
    if (!selectAllCheckbox) return;

    const checkboxes = document.querySelectorAll('.question-item input[type="checkbox"]');

    if (selectAllCheckbox.checked) {
        // Alle auswählen
        checkboxes.forEach(checkbox => {
            checkbox.checked = true;
            selectedQuestions.add(parseInt(checkbox.value));
        });
    } else {
        // Alle abwählen
        checkboxes.forEach(checkbox => {
            checkbox.checked = false;
            selectedQuestions.delete(parseInt(checkbox.value));
        });
    }

    updateSelectedCounter();
    updateDistributionInfo();
}

/**
 * Berechnet und zeigt die Verteilung der Fragen auf die Formulare an
 */
function updateDistributionInfo() {
    const distributeToggle = document.getElementById('distribute-toggle');
    const formCount = parseInt(document.getElementById('form_count')?.value || 1, 10);
    const distributionInfo = document.getElementById('distribution-info');

    // Nur anzeigen, wenn die Option aktiviert ist
    if (!distributeToggle || !distributeToggle.checked || !distributionInfo) {
        if (distributionInfo) {
            distributionInfo.classList.add('hidden');
        }
        return;
    }

    const numQuestions = selectedQuestions.size;

    // Berechne die Verteilung (einige Formulare haben möglicherweise eine Frage mehr)
    const questionsPerForm = Math.floor(numQuestions / formCount);
    const remainder = numQuestions % formCount;

    let infoText = '';
    if (numQuestions === 0) {
        infoText = 'Keine Fragen ausgewählt.';
    } else if (numQuestions < formCount) {
        infoText = `Nicht genug Fragen! Sie brauchen mindestens ${formCount} Fragen.`;
    } else {
        if (remainder === 0) {
            infoText = `${numQuestions} Fragen werden gleichmäßig auf ${formCount} Formulare verteilt: ${questionsPerForm} Fragen pro Formular.`;
        } else {
            infoText = `${numQuestions} Fragen werden auf ${formCount} Formulare verteilt: ${questionsPerForm}-${questionsPerForm+1} Fragen pro Formular.`;
        }
    }

    distributionInfo.textContent = infoText;
    distributionInfo.classList.remove('hidden');

    // Ändere die Farbe, wenn nicht genug Fragen ausgewählt sind
    if (numQuestions < formCount) {
        distributionInfo.classList.add('error-text');
    } else {
        distributionInfo.classList.remove('error-text');
    }
}