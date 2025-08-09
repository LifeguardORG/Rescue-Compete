/**
 * PasswordVisibility.js
 * Funktion zum Ein-/Ausblenden des Passworts im Login-Formular
 */

document.addEventListener('DOMContentLoaded', function() {
    // Elemente auswählen
    const passwordField = document.getElementById('password');
    const toggleButton = document.getElementById('toggle-password');
    const toggleIcon = document.getElementById('toggle-icon');

    // Event-Listener für den Toggle-Button
    toggleButton.addEventListener('click', function() {
        // Passworttyp umschalten zwischen "password" und "text"
        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            toggleIcon.classList.remove('eye-closed');
            toggleIcon.classList.add('eye-open');
            toggleIcon.title = 'Passwort verbergen';
        } else {
            passwordField.type = 'password';
            toggleIcon.classList.remove('eye-open');
            toggleIcon.classList.add('eye-closed');
            toggleIcon.title = 'Passwort anzeigen';
        }
    });
});