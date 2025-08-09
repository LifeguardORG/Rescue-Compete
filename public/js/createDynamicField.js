function createDynamicField(labelText, fieldId, fieldType) {
    const fieldContainer = document.createElement("div");
    fieldContainer.className = "form-group";

    const label = document.createElement("label");
    label.htmlFor = fieldId;
    label.textContent = labelText;

    const input = document.createElement("input");
    input.type = fieldType;
    input.id = fieldId;
    input.name = fieldId;

    fieldContainer.appendChild(label);
    fieldContainer.appendChild(input);

    return fieldContainer;
}