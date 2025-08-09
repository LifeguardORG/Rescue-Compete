/**
 * ResultConfiguration.js
 *
 * Verwaltet die automatische Berechnung und Synchronisation der
 * Punkte- und Prozentverteilung in der Ergebnis-Konfiguration
 */

document.addEventListener('DOMContentLoaded', function() {
    // DOM-Elemente abrufen
    const swimmingPointsInput = document.getElementById('swimmingPoints');
    const parcoursPointsInput = document.getElementById('parcoursPoints');
    const totalPointsInput = document.getElementById('TOTAL_POINTS');
    const swimmingPercentageInput = document.getElementById('SHARE_SWIMMING');
    const parcoursPercentageInput = document.getElementById('SHARE_PARCOURS');

    // Event-Listener für alle relevanten Eingabefelder
    swimmingPointsInput.addEventListener('input', handleSwimmingPointsChange);
    parcoursPointsInput.addEventListener('input', handleParcoursPointsChange);
    totalPointsInput.addEventListener('input', handleTotalPointsChange);
    swimmingPercentageInput.addEventListener('input', handleSwimmingPercentageChange);
    parcoursPercentageInput.addEventListener('input', handleParcoursPercentageChange);

    /**
     * Behandelt Änderungen der Schwimm-Punkte
     * Berechnet neue Prozentanteile basierend auf der geänderten Punktzahl
     */
    function handleSwimmingPointsChange() {
        const swimmingPoints = parseFloat(swimmingPointsInput.value) || 0;
        const parcoursPoints = parseFloat(parcoursPointsInput.value) || 0;
        const totalPoints = swimmingPoints + parcoursPoints;

        if (totalPoints > 0) {
            // Gesamtpunkte aktualisieren
            totalPointsInput.value = totalPoints;

            // Prozentanteile neu berechnen
            const swimmingPercentage = roundToOneDecimal((swimmingPoints / totalPoints) * 100);
            const parcoursPercentage = roundToOneDecimal((parcoursPoints / totalPoints) * 100);

            updatePercentages(swimmingPercentage, parcoursPercentage);
        }
    }

    /**
     * Behandelt Änderungen der Parcours-Punkte
     * Berechnet neue Prozentanteile basierend auf der geänderten Punktzahl
     */
    function handleParcoursPointsChange() {
        const swimmingPoints = parseFloat(swimmingPointsInput.value) || 0;
        const parcoursPoints = parseFloat(parcoursPointsInput.value) || 0;
        const totalPoints = swimmingPoints + parcoursPoints;

        if (totalPoints > 0) {
            // Gesamtpunkte aktualisieren
            totalPointsInput.value = totalPoints;

            // Prozentanteile neu berechnen
            const swimmingPercentage = roundToOneDecimal((swimmingPoints / totalPoints) * 100);
            const parcoursPercentage = roundToOneDecimal((parcoursPoints / totalPoints) * 100);

            updatePercentages(swimmingPercentage, parcoursPercentage);
        }
    }

    /**
     * Behandelt Änderungen der Gesamtpunkte
     * Verteilt die Punkte basierend auf den aktuellen Prozentanteilen neu
     */
    function handleTotalPointsChange() {
        const totalPoints = parseFloat(totalPointsInput.value) || 0;
        const swimmingPercentage = parseFloat(swimmingPercentageInput.value) || 0;
        const parcoursPercentage = parseFloat(parcoursPercentageInput.value) || 0;

        if (totalPoints > 0) {
            // Punkte basierend auf Prozentanteilen neu verteilen
            const swimmingPoints = Math.round((totalPoints * swimmingPercentage) / 100);
            const parcoursPoints = Math.round((totalPoints * parcoursPercentage) / 100);

            swimmingPointsInput.value = swimmingPoints;
            parcoursPointsInput.value = parcoursPoints;
        }
    }

    /**
     * Behandelt Änderungen des Schwimm-Prozentanteils
     * Passt den Parcours-Anteil automatisch an, damit beide 100% ergeben
     */
    function handleSwimmingPercentageChange() {
        const swimmingPercentage = parseFloat(swimmingPercentageInput.value) || 0;

        // Parcours-Anteil automatisch anpassen
        const parcoursPercentage = roundToOneDecimal(100 - swimmingPercentage);

        // Werte begrenzen
        const validSwimmingPercentage = Math.max(0, Math.min(100, swimmingPercentage));
        const validParcoursPercentage = roundToOneDecimal(100 - validSwimmingPercentage);

        // UI aktualisieren
        swimmingPercentageInput.value = validSwimmingPercentage;
        parcoursPercentageInput.value = validParcoursPercentage;

        // Punkte neu berechnen
        recalculatePointsFromPercentages();
    }

    /**
     * Behandelt Änderungen des Parcours-Prozentanteils
     * Passt den Schwimm-Anteil automatisch an, damit beide 100% ergeben
     */
    function handleParcoursPercentageChange() {
        const parcoursPercentage = parseFloat(parcoursPercentageInput.value) || 0;

        // Schwimm-Anteil automatisch anpassen
        const swimmingPercentage = roundToOneDecimal(100 - parcoursPercentage);

        // Werte begrenzen
        const validParcoursPercentage = Math.max(0, Math.min(100, parcoursPercentage));
        const validSwimmingPercentage = roundToOneDecimal(100 - validParcoursPercentage);

        // UI aktualisieren
        parcoursPercentageInput.value = validParcoursPercentage;
        swimmingPercentageInput.value = validSwimmingPercentage;

        // Punkte neu berechnen
        recalculatePointsFromPercentages();
    }

    /**
     * Berechnet die Punkte neu basierend auf den aktuellen Prozentanteilen
     */
    function recalculatePointsFromPercentages() {
        const totalPoints = parseFloat(totalPointsInput.value) || 0;
        const swimmingPercentage = parseFloat(swimmingPercentageInput.value) || 0;
        const parcoursPercentage = parseFloat(parcoursPercentageInput.value) || 0;

        if (totalPoints > 0) {
            const swimmingPoints = Math.round((totalPoints * swimmingPercentage) / 100);
            const parcoursPoints = Math.round((totalPoints * parcoursPercentage) / 100);

            swimmingPointsInput.value = swimmingPoints;
            parcoursPointsInput.value = parcoursPoints;
        }
    }

    /**
     * Aktualisiert die Prozentanteile in der UI
     *
     * @param {number} swimmingPercentage - Schwimm-Prozentanteil
     * @param {number} parcoursPercentage - Parcours-Prozentanteil
     */
    function updatePercentages(swimmingPercentage, parcoursPercentage) {
        swimmingPercentageInput.value = swimmingPercentage;
        parcoursPercentageInput.value = parcoursPercentage;
    }

    /**
     * Rundet eine Zahl auf eine Nachkommastelle
     *
     * @param {number} value - Zu rundender Wert
     * @returns {number} - Gerundeter Wert
     */
    function roundToOneDecimal(value) {
        return Math.round(value * 10) / 10;
    }

    /**
     * Validiert, dass die Prozentanteile zusammen 100% ergeben
     *
     * @returns {boolean} - True wenn gültig, false wenn nicht
     */
    function validatePercentageSum() {
        const swimmingPercentage = parseFloat(swimmingPercentageInput.value) || 0;
        const parcoursPercentage = parseFloat(parcoursPercentageInput.value) || 0;
        const sum = roundToOneDecimal(swimmingPercentage + parcoursPercentage);

        return Math.abs(sum - 100) < 0.1; // Toleranz für Rundungsfehler
    }

    /**
     * Fügt visuelles Feedback für ungültige Eingaben hinzu
     */
    function addValidationFeedback() {
        const form = document.getElementById('configForm');

        form.addEventListener('submit', function(event) {
            if (!validatePercentageSum()) {
                event.preventDefault();
                alert('Die Prozentanteile müssen zusammen 100% ergeben!');
                return false;
            }

            // Weitere Validierungen
            const totalPoints = parseFloat(totalPointsInput.value) || 0;
            if (totalPoints < 1000) {
                event.preventDefault();
                alert('Die Gesamtpunkte müssen mindestens 1000 betragen!');
                return false;
            }
        });
    }

    // Validierung aktivieren
    addValidationFeedback();

    // Initial einmal alle Werte synchronisieren
    handleTotalPointsChange();
});