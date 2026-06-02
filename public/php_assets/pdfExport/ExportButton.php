<?php
/**
 * Export-Button-Komponente für Ergebnisseiten
 * Diese Komponente fügt einen PDF-Export-Button hinzu
 *
 * @param string $pageTitle Optional - Titel für das PDF (Standard ist der Seiten-Titel)
 */

// Prüfe, ob ein Seitentitel übergeben wurde
$buttonTitle = $pageTitle ?? "Ergebnisse";

?>

<div class="export-button-container">
    <button class="pdf-export-btn" id="exportPdfBtn" title="Ergebnisse als PDF exportieren">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
            <polyline points="14 2 14 8 20 8"></polyline>
            <line x1="12" y1="18" x2="12" y2="12"></line>
            <line x1="9" y1="15" x2="15" y2="15"></line>
        </svg>
        <?php echo htmlspecialchars($buttonTitle); ?> als PDF exportieren
    </button>
    <button class="excel-export-btn" id="exportExcelBtn" title="Ergebnisse als Excel exportieren">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
            <polyline points="14 2 14 8 20 8"></polyline>
            <line x1="8" y1="13" x2="16" y2="13"></line>
            <line x1="8" y1="17" x2="16" y2="17"></line>
            <line x1="10" y1="9" x2="14" y2="9"></line>
        </svg>
        <?php echo htmlspecialchars($buttonTitle); ?> als Excel exportieren
    </button>
</div>

<!-- HTML-Struktur für die Ladeanimation (wird mittels JavaScript angezeigt) -->
<div id="pdf-loading-indicator" style="display: none;">
    <div class="loading-overlay"></div>
    <div class="loading-content">
        <div class="loading-spinner"></div>
        <p>PDF wird erstellt...</p>
        <div class="loading-progress">
            <div class="loading-progress-bar" id="pdf-progress-bar"></div>
        </div>
    </div>
</div>