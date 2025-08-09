/**
 * Minimale Hilfsfunktionen zur Optimierung von Tabellen für den PDF-Export
 * Behält den originalen Tabellenstil bei
 */

/**
 * Bereitet Tabellen für den PDF-Export vor
 * Führt nur grundlegende Optimierungen durch, behält aber den originalen Tabellenstil
 */
document.addEventListener('DOMContentLoaded', function() {
    // Finde alle Export-Buttons
    const exportButtons = document.querySelectorAll('.pdf-export-btn');

    exportButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Optimiere Tabellen vor dem Export
            optimizeTablesForExport();
        });
    });
});

/**
 * Minimale Optimierung für Tabellen bei PDF-Export
 * Behält die originale Formatierung bei
 */
function optimizeTablesForExport() {
    // Bei Bedarf können hier minimale Optimierungen durchgeführt werden
    // Diese Funktion wird für die minimale Implementation leergelassen
    // um den originalen Tabellenstil beizubehalten

    console.log("Tabellen werden mit originalem Stil exportiert");
}

/**
 * Wiederherstellen der ursprünglichen Tabellendarstellung
 * (Wird nach dem PDF-Export aufgerufen, aber macht in der einfachen Version nichts)
 */
function restoreTablesAfterExport() {
    // Bei minimalem Ansatz ist keine Wiederherstellung notwendig
    console.log("Keine Wiederherstellung notwendig");
}

// Globale Variable exportieren für den Zugriff aus anderen Skripten
window.tableOptimizer = {
    optimizeTablesForExport,
    restoreTablesAfterExport
};