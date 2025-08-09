/**
 * ScoringInputScript.js
 * JavaScript-Funktionalität für die Wertungsklassen-Verwaltung
 */

// Globale Variablen
let currentTab = 'overview';
let teamCounter = 1;
let deleteWertungId = null;
let selectedTeamsForRemoval = [];
let formSubmissionInProgress = false;

// DOM-Ready
document.addEventListener('DOMContentLoaded', function() {
    initializePage();
    populateDropdowns();
    initializeEventListeners();
    updateTeamManagementButtons();
});

/**
 * Initialisiert die Seite und setzt den aktiven Tab
 */
function initializePage() {
    // Aktuellen Tab aus der URL oder PHP-Variable ermitteln
    const urlParams = new URLSearchParams(window.location.search);
    const viewParam = urlParams.get('view');

    if (viewParam) {
        currentTab = viewParam;
    }

    // Tab anzeigen
    showTab(currentTab);
}

/**
 * Zeigt den angegebenen Tab an
 * @param {string} tabName - Name des anzuzeigenden Tabs
 */
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

/**
 * Behandelt tab-spezifische Aktionen
 * @param {string} tabName - Name des aktiven Tabs
 */
function handleTabSpecificActions(tabName) {
    switch (tabName) {
        case 'assign':
            // Sicherstellen, dass Dropdowns gefüllt sind
            populateDropdowns();
            updateTeamManagementButtons();
            break;
        case 'create':
            // Fokus auf das Name-Feld setzen
            const nameInput = document.getElementById('name');
            if (nameInput) {
                setTimeout(() => nameInput.focus(), 100);
            }
            break;
        case 'remove':
            // Remove-Tab initialisieren
            populateRemoveDropdowns();
            resetRemoveTab();
            break;
    }
}

/**
 * Füllt die Dropdown-Menüs mit verfügbaren Daten
 */
function populateDropdowns() {
    // Wertungsklassen-Dropdown füllen
    const wertungSelect = document.getElementById('wertung');
    if (wertungSelect && typeof wertungsklassen !== 'undefined') {
        // Bestehende Optionen entfernen (außer der ersten)
        while (wertungSelect.children.length > 1) {
            wertungSelect.removeChild(wertungSelect.lastChild);
        }

        // Neue Optionen hinzufügen
        wertungsklassen.forEach(wertung => {
            const option = document.createElement('option');
            option.value = wertung.wertung_name;
            option.textContent = wertung.wertung_name;
            wertungSelect.appendChild(option);
        });
    }

    // Team-Dropdowns füllen
    populateTeamDropdowns();
}

/**
 * Füllt die Dropdown-Menüs für den Remove-Tab
 */
function populateRemoveDropdowns() {
    const removeWertungSelect = document.getElementById('removeWertung');
    if (removeWertungSelect && typeof wertungsklassen !== 'undefined') {
        // Bestehende Optionen entfernen (außer der ersten)
        while (removeWertungSelect.children.length > 1) {
            removeWertungSelect.removeChild(removeWertungSelect.lastChild);
        }

        // Neue Optionen hinzufügen
        wertungsklassen.forEach(wertung => {
            const option = document.createElement('option');
            option.value = wertung.wertung_name;
            option.textContent = wertung.wertung_name;
            removeWertungSelect.appendChild(option);
        });
    }
}

/**
 * Füllt alle Team-Dropdown-Menüs und erhält bestehende Auswahlen
 */
function populateTeamDropdowns() {
    const teamSelects = document.querySelectorAll('select[name^="teams["]');

    teamSelects.forEach(select => {
        if (typeof mannschaften !== 'undefined') {
            // Aktuelle Auswahl speichern
            const currentValue = select.value;

            // Bestehende Optionen entfernen (außer der ersten)
            while (select.children.length > 1) {
                select.removeChild(select.lastChild);
            }

            // Neue Optionen hinzufügen
            mannschaften.forEach(mannschaft => {
                const option = document.createElement('option');
                option.value = mannschaft.Teamname;
                option.textContent = mannschaft.Teamname;
                select.appendChild(option);
            });

            // Vorherige Auswahl wiederherstellen
            if (currentValue) {
                select.value = currentValue;
            }
        }
    });
}

/**
 * Füllt ein einzelnes Team-Dropdown-Menü
 * @param {HTMLSelectElement} selectElement - Das zu befüllende Select-Element
 */
function populateSingleTeamDropdown(selectElement) {
    if (!selectElement || typeof mannschaften === 'undefined') {
        return;
    }

    // Leere Option hinzufügen falls nicht vorhanden
    if (selectElement.children.length === 0) {
        const emptyOption = document.createElement('option');
        emptyOption.value = '';
        emptyOption.disabled = true;
        emptyOption.selected = true;
        emptyOption.hidden = true;
        emptyOption.textContent = 'Bitte auswählen';
        selectElement.appendChild(emptyOption);
    }

    // Team-Optionen hinzufügen
    mannschaften.forEach(mannschaft => {
        const option = document.createElement('option');
        option.value = mannschaft.Teamname;
        option.textContent = mannschaft.Teamname;
        selectElement.appendChild(option);
    });
}

/**
 * Lädt die zugewiesenen Teams für die ausgewählte Wertungsklasse
 */
function loadAssignedTeams() {
    const wertungSelect = document.getElementById('removeWertung');
    const assignedContainer = document.getElementById('assignedTeamsContainer');
    const noTeamsMessage = document.getElementById('noTeamsMessage');
    const teamsList = document.getElementById('assignedTeamsList');

    if (!wertungSelect || !assignedContainer || !noTeamsMessage || !teamsList) {
        return;
    }

    const selectedWertung = wertungSelect.value;

    if (!selectedWertung) {
        resetRemoveTab();
        return;
    }

    // Loading-Zustand anzeigen
    teamsList.innerHTML = '<div class="loading-spinner"></div> Teams werden geladen...';
    assignedContainer.style.display = 'block';
    noTeamsMessage.style.display = 'none';

    // AJAX-Request an den Controller
    const url = `ScoringInputView.php?action=getAssignedTeams&wertung=${encodeURIComponent(selectedWertung)}`;

    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                if (data.teams && data.teams.length > 0) {
                    displayAssignedTeams(data.teams);
                } else {
                    showNoTeamsMessage();
                }
            } else {
                throw new Error(data.error || 'Unbekannter Fehler beim Laden der Teams');
            }
        })
        .catch(error => {
            console.error('Error loading assigned teams:', error);
            showLoadingError(error.message);
        });
}

/**
 * Zeigt die zugewiesenen Teams in der Liste an
 * @param {Array} teams - Array mit Team-Daten
 */
function displayAssignedTeams(teams) {
    const assignedContainer = document.getElementById('assignedTeamsContainer');
    const noTeamsMessage = document.getElementById('noTeamsMessage');
    const teamsList = document.getElementById('assignedTeamsList');

    // Container anzeigen
    noTeamsMessage.style.display = 'none';
    assignedContainer.style.display = 'block';

    // Teams-Liste erstellen
    teamsList.innerHTML = '';
    selectedTeamsForRemoval = [];

    teams.forEach(team => {
        const teamItem = createTeamCheckboxItem(team);
        teamsList.appendChild(teamItem);
    });

    // Submit-Button-Status aktualisieren
    updateSelectedTeams();
}

/**
 * Zeigt eine Nachricht an, wenn keine Teams zugewiesen sind
 */
function showNoTeamsMessage() {
    const assignedContainer = document.getElementById('assignedTeamsContainer');
    const noTeamsMessage = document.getElementById('noTeamsMessage');

    assignedContainer.style.display = 'none';
    noTeamsMessage.style.display = 'block';
}

/**
 * Zeigt eine Fehlermeldung beim Laden der Teams an
 * @param {string} errorMessage - Die Fehlermeldung
 */
function showLoadingError(errorMessage) {
    const teamsList = document.getElementById('assignedTeamsList');

    teamsList.innerHTML = `
        <div class="loading-error">
            <p><strong>Fehler beim Laden der Teams:</strong></p>
            <p>${escapeHtml(errorMessage)}</p>
            <button class="btn secondary-btn" onclick="loadAssignedTeams()">
                Erneut versuchen
            </button>
        </div>
    `;
}

/**
 * Erstellt ein Checkbox-Element für ein Team
 * @param {Object} team - Team-Daten (jetzt mit mannschaft_id statt ID)
 * @returns {HTMLElement} - Das erstellte Team-Item
 */
function createTeamCheckboxItem(team) {
    const teamItem = document.createElement('div');
    teamItem.className = 'team-checkbox-item';

    const checkbox = document.createElement('input');
    checkbox.type = 'checkbox';
    checkbox.id = `team_${team.mannschaft_id}`;
    checkbox.name = 'selected_teams[]';
    checkbox.value = team.mannschaft_id;
    checkbox.addEventListener('change', updateSelectedTeams);

    const label = document.createElement('label');
    label.htmlFor = `team_${team.mannschaft_id}`;
    label.innerHTML = `
        <strong>${escapeHtml(team.Teamname)}</strong>
        <span class="team-details">${escapeHtml(team.Kreisverband)} • ${escapeHtml(team.Landesverband)}</span>
    `;

    teamItem.appendChild(checkbox);
    teamItem.appendChild(label);

    return teamItem;
}

/**
 * Aktualisiert die Liste der ausgewählten Teams
 */
function updateSelectedTeams() {
    const checkboxes = document.querySelectorAll('input[name="selected_teams[]"]:checked');
    selectedTeamsForRemoval = Array.from(checkboxes).map(cb => parseInt(cb.value));

    // Submit-Button Status aktualisieren
    const submitBtn = document.querySelector('button[name="remove_selected_teams"]');
    if (submitBtn) {
        if (selectedTeamsForRemoval.length > 0) {
            submitBtn.disabled = false;
            submitBtn.classList.remove('disabled');
            submitBtn.textContent = `${selectedTeamsForRemoval.length} ${selectedTeamsForRemoval.length === 1 ? 'Team' : 'Teams'} entfernen`;
        } else {
            submitBtn.disabled = true;
            submitBtn.classList.add('disabled');
            submitBtn.textContent = 'Ausgewählte Teams entfernen';
        }
    }
}

/**
 * Wählt alle Teams aus
 */
function selectAllTeams() {
    const checkboxes = document.querySelectorAll('input[name="selected_teams[]"]');
    checkboxes.forEach(checkbox => {
        checkbox.checked = true;
    });
    updateSelectedTeams();
}

/**
 * Wählt alle Teams ab
 */
function deselectAllTeams() {
    const checkboxes = document.querySelectorAll('input[name="selected_teams[]"]');
    checkboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
    updateSelectedTeams();
}

/**
 * Setzt den Remove-Tab zurück
 */
function resetRemoveTab() {
    const assignedContainer = document.getElementById('assignedTeamsContainer');
    const noTeamsMessage = document.getElementById('noTeamsMessage');

    if (assignedContainer) {
        assignedContainer.style.display = 'none';
    }
    if (noTeamsMessage) {
        noTeamsMessage.style.display = 'none';
    }

    selectedTeamsForRemoval = [];
}

/**
 * Bestätigt die Entfernung der ausgewählten Teams
 */
function confirmTeamRemoval() {
    if (formSubmissionInProgress) {
        return;
    }

    const form = document.getElementById('removeTeamsForm');
    if (form && selectedTeamsForRemoval.length > 0) {
        formSubmissionInProgress = true;

        // Submit-Button deaktivieren
        const submitBtn = document.querySelector('button[name="remove_selected_teams"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Entfernen...';
        }

        // Verstecktes Feld für remove_selected_teams erstellen
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'remove_selected_teams';
        hiddenInput.value = '1';
        form.appendChild(hiddenInput);

        form.submit();
    }
    closeModal('confirmRemoveTeamsModal');
}

/**
 * Initialisiert Event-Listener
 */
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
    const createForm = document.getElementById('createScoringForm');
    if (createForm) {
        createForm.addEventListener('submit', function(event) {
            if (!validateCreateForm()) {
                event.preventDefault();
            }
        });
    }

    // Form-Validierung für Assign-Tab
    const assignForm = document.getElementById('assignTeamsForm');
    if (assignForm) {
        assignForm.addEventListener('submit', function(event) {
            if (!validateAssignForm()) {
                event.preventDefault();
            }
        });
    }

    // Form-Validierung für Remove-Tab
    const removeForm = document.getElementById('removeTeamsForm');
    if (removeForm) {
        removeForm.addEventListener('submit', function(event) {
            event.preventDefault(); // Immer verhindern, Bestätigung über Modal

            if (!validateRemoveFormPreSubmit()) {
                return;
            }

            // Modal für Bestätigung anzeigen
            const teamCount = selectedTeamsForRemoval.length;
            const teamText = teamCount === 1 ? 'Team' : 'Teams';
            const wertungSelect = document.getElementById('removeWertung');
            const wertungName = wertungSelect ? wertungSelect.value : '';

            const modal = document.getElementById('confirmRemoveTeamsModal');
            if (modal) {
                const modalBody = modal.querySelector('.modal-body');
                if (modalBody) {
                    modalBody.innerHTML = `
                        <p>Möchten Sie wirklich <strong>${teamCount} ${teamText}</strong> aus der Wertungsklasse <strong>"${escapeHtml(wertungName)}"</strong> entfernen?</p>
                        <p><em>Die Teams bleiben bestehen und können anderen Wertungsklassen zugewiesen werden.</em></p>
                    `;
                }
                showModal('confirmRemoveTeamsModal');
            }
        });
    }

    // Längenbeschränkung für Name-Feld
    const nameInput = document.getElementById('name');
    if (nameInput) {
        nameInput.addEventListener('input', function() {
            enforceMaxLength(this, 100);
        });
    }

    // Tastatur-Navigation für Accessibility
    document.addEventListener('keydown', handleKeyboardNavigation);
}

/**
 * Stellt sicher, dass die maximale Zeichenlänge nicht überschritten wird
 * @param {HTMLElement} element - Das Eingabeelement
 * @param {number} maxLength - Maximale Anzahl Zeichen
 */
function enforceMaxLength(element, maxLength) {
    if (element.value.length > maxLength) {
        element.value = element.value.substring(0, maxLength);
    }
}

/**
 * Validiert das Create-Formular
 * @returns {boolean} True wenn valid, false wenn nicht
 */
function validateCreateForm() {
    const nameInput = document.getElementById('name');
    let isValid = true;

    // Name-Validierung
    if (!nameInput.value.trim()) {
        showValidationError(nameInput, 'Bitte geben Sie einen Namen für die Wertungsklasse ein.');
        isValid = false;
    } else if (nameInput.value.trim().length > 32) {
        showValidationError(nameInput, 'Der Name darf maximal 32 Zeichen lang sein.');
        isValid = false;
    } else {
        clearValidationError(nameInput);
    }

    return isValid;
}

/**
 * Validiert das Assign-Formular
 * @returns {boolean} True wenn valid, false wenn nicht
 */
function validateAssignForm() {
    const wertungSelect = document.getElementById('wertung');
    const teamSelects = document.querySelectorAll('select[name^="teams["]');
    let isValid = true;

    // Wertung-Validierung
    if (!wertungSelect.value) {
        showValidationError(wertungSelect, 'Bitte wählen Sie eine Wertungsklasse aus.');
        isValid = false;
    } else {
        clearValidationError(wertungSelect);
    }

    // Team-Validierung
    let hasValidTeam = false;
    teamSelects.forEach(select => {
        if (select.value) {
            hasValidTeam = true;
            clearValidationError(select);
        }
    });

    if (!hasValidTeam) {
        teamSelects[0] && showValidationError(teamSelects[0], 'Bitte wählen Sie mindestens ein Team aus.');
        isValid = false;
    }

    // Duplikat-Prüfung
    const selectedTeams = Array.from(teamSelects)
        .map(select => select.value)
        .filter(value => value);

    const duplicates = selectedTeams.filter((team, index) =>
        selectedTeams.indexOf(team) !== index
    );

    if (duplicates.length > 0) {
        teamSelects.forEach(select => {
            if (duplicates.includes(select.value)) {
                showValidationError(select, 'Jedes Team kann nur einmal ausgewählt werden.');
            }
        });
        isValid = false;
    }

    return isValid;
}

/**
 * Validiert das Remove-Formular vor der Anzeige des Bestätigungs-Modals
 * @returns {boolean} True wenn valid, false wenn nicht
 */
function validateRemoveFormPreSubmit() {
    const wertungSelect = document.getElementById('removeWertung');
    let isValid = true;

    // Wertung-Validierung
    if (!wertungSelect.value) {
        showValidationError(wertungSelect, 'Bitte wählen Sie eine Wertungsklasse aus.');
        isValid = false;
    } else {
        clearValidationError(wertungSelect);
    }

    // Team-Auswahl-Validierung
    if (selectedTeamsForRemoval.length === 0) {
        showMessage('Bitte wählen Sie mindestens ein Team zum Entfernen aus.', 'error');
        isValid = false;
    }

    return isValid;
}

/**
 * Zeigt eine Validierungsfehlermeldung an
 * @param {HTMLElement} element - Das Eingabeelement
 * @param {string} message - Die Fehlermeldung
 */
function showValidationError(element, message) {
    // Element als fehlerhaft markieren
    element.classList.add('error');

    // Bestehende Fehlermeldung entfernen
    clearValidationError(element);

    // Neue Fehlermeldung erstellen
    const errorDiv = document.createElement('div');
    errorDiv.className = 'validation-message show';
    errorDiv.textContent = message;
    errorDiv.id = element.id + '_error';

    // Fehlermeldung nach dem Element einfügen
    element.parentNode.insertBefore(errorDiv, element.nextSibling);
}

/**
 * Entfernt Validierungsfehlermeldungen
 * @param {HTMLElement} element - Das Eingabeelement
 */
function clearValidationError(element) {
    element.classList.remove('error');
    const errorDiv = document.getElementById(element.id + '_error');
    if (errorDiv) {
        errorDiv.remove();
    }
}

/**
 * Fügt ein neues Team-Eingabefeld hinzu
 */
function addTeam() {
    const container = document.getElementById('teamsContainer');
    if (!container) return;

    teamCounter++;

    // Neues Team-Entry-Element erstellen
    const teamEntry = document.createElement('div');
    teamEntry.className = 'team-entry';
    teamEntry.innerHTML = `
        <h4>${teamCounter}. Team:</h4>
        <div class="form-group">
            <label for="teamname_${teamCounter}">Teamname:</label>
            <select id="teamname_${teamCounter}" name="teams[${teamCounter - 1}][name]" required>
                <option value="" disabled selected hidden>Bitte auswählen</option>
            </select>
        </div>
    `;

    // Element zum Container hinzufügen
    container.appendChild(teamEntry);

    // Nur das neue Dropdown füllen
    const newSelect = teamEntry.querySelector('select');
    if (newSelect) {
        populateSingleTeamDropdown(newSelect);
    }

    // Button-Status aktualisieren
    updateTeamManagementButtons();

    // Animation hinzufügen
    setTimeout(() => {
        teamEntry.style.opacity = '1';
        teamEntry.style.transform = 'translateY(0)';
    }, 10);
}

/**
 * Entfernt das letzte Team-Eingabefeld
 */
function removeTeam() {
    const container = document.getElementById('teamsContainer');
    if (!container) return;

    const teamEntries = container.querySelectorAll('.team-entry');

    // Mindestens ein Team muss bleiben
    if (teamEntries.length > 1) {
        const lastEntry = teamEntries[teamEntries.length - 1];

        // Animation für das Entfernen
        lastEntry.style.opacity = '0';
        lastEntry.style.transform = 'translateY(-10px)';

        setTimeout(() => {
            lastEntry.remove();
            teamCounter--;
            updateTeamManagementButtons();
        }, 300);
    }
}

/**
 * Aktualisiert die Verfügbarkeit der Team-Management-Buttons
 */
function updateTeamManagementButtons() {
    const addBtn = document.getElementById('addTeamBtn');
    const removeBtn = document.getElementById('removeTeamBtn');
    const container = document.getElementById('teamsContainer');

    if (!container) return;

    const teamCount = container.querySelectorAll('.team-entry').length;

    // Remove-Button nur aktivieren, wenn mehr als ein Team vorhanden
    if (removeBtn) {
        if (teamCount <= 1) {
            removeBtn.disabled = true;
            removeBtn.classList.add('disabled');
        } else {
            removeBtn.disabled = false;
            removeBtn.classList.remove('disabled');
        }
    }

    // Add-Button limitieren (optional)
    if (addBtn && typeof mannschaften !== 'undefined') {
        if (teamCount >= mannschaften.length) {
            addBtn.disabled = true;
            addBtn.classList.add('disabled');
        } else {
            addBtn.disabled = false;
            addBtn.classList.remove('disabled');
        }
    }
}

/**
 * Zeigt den Assignment-Tab für eine bestimmte Wertungsklasse
 * @param {string} wertungName - Name der Wertungsklasse
 */
function showAssignmentForWertung(wertungName) {
    // Zum Assignment-Tab wechseln
    showTab('assign');

    // Wertungsklasse im Dropdown auswählen
    setTimeout(() => {
        const wertungSelect = document.getElementById('wertung');
        if (wertungSelect) {
            wertungSelect.value = wertungName;
        }
    }, 100);
}

/**
 * Zeigt den Remove-Tab für eine bestimmte Wertungsklasse
 * @param {string} wertungName - Name der Wertungsklasse
 */
function showRemovalForWertung(wertungName) {
    // Zum Remove-Tab wechseln
    showTab('remove');

    // Wertungsklasse im Dropdown auswählen und Teams laden
    setTimeout(() => {
        const removeWertungSelect = document.getElementById('removeWertung');
        if (removeWertungSelect) {
            removeWertungSelect.value = wertungName;
            loadAssignedTeams();
        }
    }, 100);
}

/**
 * Bestätigt das Löschen einer Wertungsklasse
 * @param {number} id - ID der zu löschenden Wertungsklasse
 * @param {string} name - Name der Wertungsklasse
 */
function confirmDeleteWertung(id, name) {
    deleteWertungId = id;

    // Modal-Text aktualisieren
    const modal = document.getElementById('confirmDeleteModal');
    if (modal) {
        const modalBody = modal.querySelector('.modal-body');
        if (modalBody) {
            modalBody.innerHTML = `
                <p>Möchten Sie die Wertungsklasse <strong>"${escapeHtml(name)}"</strong> wirklich löschen?</p>
                <p><em>Die zugehörigen Mannschaften bleiben erhalten.</em></p>
            `;
        }

        // Modal anzeigen
        showModal('confirmDeleteModal');
    }
}

/**
 * Löscht die bestätigte Wertungsklasse
 */
function deleteWertung() {
    if (deleteWertungId) {
        // Formular erstellen mit dem richtigen Feldnamen
        const form = document.createElement('form');
        form.method = 'POST';
        form.style.display = 'none';

        // delete_scoring statt delete_wertung
        const deleteInput = document.createElement('input');
        deleteInput.type = 'hidden';
        deleteInput.name = 'delete_scoring';
        deleteInput.value = '1';

        // Zusätzliches Feld für die ID
        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'delete_id';
        idInput.value = deleteWertungId;

        form.appendChild(deleteInput);
        form.appendChild(idInput);
        document.body.appendChild(form);
        form.submit();
    }

    closeModal('confirmDeleteModal');
}

/**
 * Zeigt ein Modal an
 * @param {string} modalId - ID des Modals
 */
function showModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';

        // Fokus auf das erste fokussierbare Element setzen
        const focusableElement = modal.querySelector('button, input, select');
        if (focusableElement) {
            setTimeout(() => focusableElement.focus(), 100);
        }
    }
}

/**
 * Schließt ein Modal
 * @param {string} modalId - ID des Modals
 */
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
        deleteWertungId = null;
    }
}

/**
 * Behandelt Tastatur-Navigation
 * @param {KeyboardEvent} event - Das Tastatur-Event
 */
function handleKeyboardNavigation(event) {
    // ESC schließt Modals
    if (event.key === 'Escape') {
        const activeModal = document.querySelector('.modal.active');
        if (activeModal) {
            closeModal(activeModal.id);
        }
    }

    // Tab-Navigation zwischen Tabs
    if (event.key >= '1' && event.key <= '4' && event.ctrlKey) {
        event.preventDefault();
        const tabs = ['overview', 'create', 'assign', 'remove'];
        const tabIndex = parseInt(event.key) - 1;
        if (tabs[tabIndex]) {
            showTab(tabs[tabIndex]);
        }
    }
}

/**
 * Utility-Funktion für das Anzeigen von Nachrichten
 * @param {string} message - Die anzuzeigende Nachricht
 * @param {string} type - Typ der Nachricht (success, error, info)
 */
function showMessage(message, type = 'info') {
    // Bestehende Nachrichten entfernen
    const existingMessages = document.querySelectorAll('.message-box');
    existingMessages.forEach(msg => msg.remove());

    // Neue Nachricht erstellen
    const messageDiv = document.createElement('div');
    messageDiv.className = `message-box ${type}`;
    messageDiv.textContent = message;

    // Nachricht am Anfang des main-content einfügen
    const mainContent = document.querySelector('.main-content');
    if (mainContent) {
        mainContent.insertBefore(messageDiv, mainContent.firstChild);

        // Nachricht nach 5 Sekunden automatisch entfernen
        setTimeout(() => {
            if (messageDiv.parentNode) {
                messageDiv.remove();
            }
        }, 5000);
    }
}

/**
 * Escape HTML-Zeichen für sicherere Anzeige
 * @param {string} text - Der zu escapende Text
 * @returns {string} - Der escapte Text
 */
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}

/**
 * Event-Listener für Modal-Schließen beim Klick außerhalb
 */
document.addEventListener('click', function(event) {
    if (event.target.classList.contains('modal')) {
        closeModal(event.target.id);
    }
});

// Globale Funktionen für HTML-Inline-Events
window.showTab = showTab;
window.addTeam = addTeam;
window.removeTeam = removeTeam;
window.showAssignmentForWertung = showAssignmentForWertung;
window.showRemovalForWertung = showRemovalForWertung;
window.confirmDeleteWertung = confirmDeleteWertung;
window.deleteWertung = deleteWertung;
window.closeModal = closeModal;
window.loadAssignedTeams = loadAssignedTeams;
window.selectAllTeams = selectAllTeams;
window.deselectAllTeams = deselectAllTeams;
window.confirmTeamRemoval = confirmTeamRemoval;