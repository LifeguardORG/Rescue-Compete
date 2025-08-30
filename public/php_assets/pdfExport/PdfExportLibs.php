<!-- JavaScript-Bibliotheken für PDF-Export -->

<!-- jsPDF-Bibliothek über CDN einbinden -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<!-- html2canvas für die Konvertierung von HTML zu Canvas -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<!-- Eigene Skripte für die PDF-Erzeugung -->
<script src="../../js/TableOptimizer.js"></script>
<script src="../../js/PdfExport.js"></script>

<!-- Styles NUR für die Export-Buttons und Ladeanimation, nicht für Tabellen -->
<style>
    /* Export-Button-Stil */
    .pdf-export-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 10px 14px;
        margin: 12px 0;
        background-color: var(--ww-blue-100);
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-weight: bold;
        transition: all 0.3s ease;
        text-decoration: none;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    .pdf-export-btn:hover {
        background-color: var(--ww-blue-30);
        transform: translateY(-1px);
        box-shadow: 0 3px 6px rgba(0, 0, 0, 0.25);
    }

    .pdf-export-btn:active {
        transform: translateY(1px);
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
    }

    .pdf-export-btn svg {
        margin-right: 8px;
    }

    /* Ladeanimation während des PDF-Exports */
    #pdf-loading-indicator {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }

    .loading-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(2px);
    }

    .loading-content {
        position: relative;
        background-color: white;
        padding: 25px;
        border-radius: 8px;
        text-align: center;
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.3);
        width: 300px;
    }

    .loading-spinner {
        display: inline-block;
        width: 45px;
        height: 45px;
        border: 4px solid rgba(0, 0, 0, 0.1);
        border-left-color: var(--ww-darkblue-100);
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin-bottom: 15px;
    }

    /* Fortschrittsbalken-Stil */
    .loading-progress {
        width: 100%;
        height: 8px;
        background-color: #f0f0f0;
        border-radius: 4px;
        margin-top: 15px;
        overflow: hidden;
    }

    .loading-progress-bar {
        height: 100%;
        background-color: var(--ww-darkblue-100, #0051a2);
        width: 0%;
        transition: width 0.3s ease;
        box-shadow: 0 0 3px rgba(0, 81, 162, 0.5);
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }

    /* Ausblenden der Buttons bei Druck */
    @media print {
        .pdf-export-btn,
        .export-button-container,
        #pdf-loading-indicator {
            display: none !important;
        }
    }
</style>