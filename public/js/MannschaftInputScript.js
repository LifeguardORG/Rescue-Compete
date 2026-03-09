// Globale Variable, um das aktuell zu löschende Formular zu speichern
let deleteForm = null;

// Funktion wird global verfügbar gemacht, damit sie von der AlertBox aufgerufen werden kann
window.confirmDelete = function() {
    if (deleteForm) {
        deleteForm.submit();
    }
};

document.addEventListener("DOMContentLoaded", () => {

    // --- Dynamische Felder basierend auf dem Dropdown ---
    const accTypDropdown = document.getElementById("acc_typ");
    const dynamicFieldContainer = document.getElementById("dynamic-fields");

    if (accTypDropdown && dynamicFieldContainer) {
        accTypDropdown.addEventListener("change", (event) => {
            const selectedValue = event.target.value;
            // Alle dynamischen Felder entfernen
            dynamicFieldContainer.innerHTML = "";

            if (selectedValue === "Teilnehmer") {
                // Mannschaftsnummer-Feld hinzufügen
                const teamField = createDynamicField("Mannschaftsnummer (optional)", "team_number", "number");
                dynamicFieldContainer.appendChild(teamField);
            } else if (selectedValue === "Schiedsrichter" || selectedValue === "Mime") {
                // Stationsnummer-Feld hinzufügen
                const stationField = createDynamicField("Stationsnummer (optional)", "station_number", "number");
                dynamicFieldContainer.appendChild(stationField);
            }
        });
    }

    // --- Löschbestätigung: Interzeptiere alle Formulare mit der Klasse "delete-form" ---
    const deleteForms = document.querySelectorAll('.delete-form');
    deleteForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault(); // Verhindere die Standardübermittlung
            deleteForm = this;  // Speichere das aktuell betroffene Formular
            const modal = document.getElementById('confirmDeleteModal');
            if (modal) {
                modal.classList.add('active');
            }
        });
    });

});

// Funktion, die die dynamischen Felder erstellt
function createDynamicField(labelText, fieldName, inputType) {
    const fieldContainer = document.createElement("div");
    fieldContainer.className = "form-group";

    const label = document.createElement("label");
    label.setAttribute("for", fieldName);
    label.textContent = labelText;

    const input = document.createElement("input");
    input.type = inputType;
    input.id = fieldName;
    input.name = fieldName;

    fieldContainer.appendChild(label);
    fieldContainer.appendChild(input);

    return fieldContainer;
}