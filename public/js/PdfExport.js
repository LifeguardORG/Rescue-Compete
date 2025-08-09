/**
 * PDF-Export-Funktionalität für die Ergebnisseiten
 * Erstellt PDF-Dokumente im A4-Querformat, behält originale Tabellenformatierung
 */

// Warte, bis das Dokument vollständig geladen ist
document.addEventListener('DOMContentLoaded', function() {
    // Initialisiere Export-Buttons, falls vorhanden
    initPdfExportButtons();
});

/**
 * Initialisiert die PDF-Export-Buttons auf der Seite
 */
function initPdfExportButtons() {
    const exportButtons = document.querySelectorAll('.pdf-export-btn');

    exportButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Starte den Export-Prozess mit dem entsprechenden Titel
            const pageTitle = document.querySelector('h1')?.textContent ||
                document.title ||
                'RescueCompete Ergebnisse';
            exportResultsToPdf(pageTitle);
        });
    });
}

/**
 * Exportiert die Ergebnistabellen in ein PDF
 * @param {string} documentTitle - Der Titel für das PDF-Dokument
 */
function exportResultsToPdf(documentTitle) {
    // Zeige Ladeanimation
    showLoadingIndicator();

    // Verzögerung, um die Ladeanimation anzuzeigen, bevor der rechenintensive Teil beginnt
    setTimeout(async () => {
        try {
            // Stelle sicher, dass jsPDF korrekt initialisiert wird
            if (typeof jspdf === 'undefined' || typeof jspdf.jsPDF === 'undefined') {
                throw new Error('jsPDF-Bibliothek konnte nicht geladen werden. Bitte Seite neu laden.');
            }

            // Erstelle ein neues PDF im Querformat (A4)
            const pdf = new jspdf.jsPDF({
                orientation: 'landscape',
                unit: 'mm',
                format: 'a4',
                compress: true
            });

            // Definiere A4-Querformat-Maße (in mm)
            const pageWidth = 297;
            const pageHeight = 210;

            // Definiere Seitenränder
            const margin = 15;
            const contentWidth = pageWidth - (2 * margin);
            const contentHeight = pageHeight - (2 * margin);

            // Erfasse die Legenden-Container für die Legende auf jeder Seite
            const legendContainer = document.querySelector('.legend-container');
            let legendContent = "";

            // Extrahiere die Legendeninhalte
            if (legendContainer) {
                const legendElements = legendContainer.querySelectorAll('.legend');
                legendElements.forEach(legend => {
                    const legendTitle = legend.querySelector('h3')?.textContent || '';
                    const legendItems = legend.querySelectorAll('li, tr');

                    legendContent += legendTitle + ":\n";

                    legendItems.forEach(item => {
                        const itemText = item.textContent.trim().replace(/\s+/g, ' ');
                        if (itemText) {
                            legendContent += "- " + itemText + "\n";
                        }
                    });

                    legendContent += "\n";
                });
            }

            // Erfasse alle Ergebnissektionen
            const resultSections = document.querySelectorAll('.results-section');
            let pageCount = resultSections.length;

            // Füge Logo vor dem Verarbeiten hinzu
            let logoImgData = null;
            try {
                const logoImg = document.querySelector('img[src*="ww-rundlogo.png"]');
                if (logoImg) {
                    const logoCanvas = await html2canvas(logoImg, { scale: 2 });
                    logoImgData = logoCanvas.toDataURL('image/png');
                }
            } catch (logoErr) {
                console.warn('Logo konnte nicht hinzugefügt werden:', logoErr);
            }

            // Verarbeite jede Ergebnissektion auf einer neuen Seite
            let processedSections = 0;
            const totalSections = resultSections.length;

            // Promise-basierte Verarbeitung der Tabellen
            const processSections = async () => {
                for (let i = 0; i < resultSections.length; i++) {
                    const section = resultSections[i];

                    // Füge eine neue Seite hinzu, außer für die erste Sektion
                    if (i > 0) {
                        pdf.addPage();
                    }

                    // Hole den Titel der Sektion (Wertungsklasse)
                    const sectionTitle = section.querySelector('h2')?.textContent || 'Ergebnisse';

                    // Füge Logo in die rechte obere Ecke (falls verfügbar)
                    if (logoImgData) {
                        pdf.addImage(logoImgData, 'PNG', pageWidth - margin - 25, margin, 20, 20);
                    }

                    // Zeichne Wertungsklasse als Hauptüberschrift
                    pdf.setFontSize(18);
                    pdf.setTextColor(0, 51, 102); // Dunkelblau
                    pdf.text(sectionTitle, margin, margin + 10);

                    // Dokumenttitel als Untertitel
                    pdf.setFontSize(14);
                    pdf.text(documentTitle, margin, margin + 20);

                    // Jetziges Datum/Uhrzeit
                    const now = new Date();
                    const dateString = now.toLocaleDateString('de-DE') + ' ' + now.toLocaleTimeString('de-DE');
                    pdf.setFontSize(10);
                    pdf.setTextColor(100, 100, 100); // Grau
                    pdf.text('Erstellt am: ' + dateString, margin, margin + 28);

                    // Hole die Tabelle aus der Sektion
                    const table = section.querySelector('.results-table');

                    if (table) {
                        try {
                            // Verbesserte Canvas-Konvertierung mit optimierten Optionen
                            const canvas = await html2canvas(table, {
                                scale: 3, // Höhere Auflösung für bessere Qualität
                                logging: false,
                                useCORS: true,
                                allowTaint: true,
                                backgroundColor: '#ffffff', // Weißer Hintergrund
                                letterRendering: true // Verbesserte Textdarstellung
                            });

                            // Berechne das Verhältnis, um die Tabelle auf die Seite anzupassen
                            const tableWidth = canvas.width;
                            const tableHeight = canvas.height;

                            // Anpassung der Tabellengröße - etwas kleiner, um Platz für Legende zu lassen
                            const scaleWidth = contentWidth / tableWidth;
                            const scaleHeight = (contentHeight - 60) / tableHeight; // Mehr Platz für Legende reservieren

                            // Nehme den kleineren Wert, um sicherzustellen, dass die Tabelle vollständig sichtbar ist
                            const scale = Math.min(scaleWidth, scaleHeight, 0.9); // Maximal 90% der verfügbaren Größe

                            // Berechne die endgültigen Maße der skalierten Tabelle
                            const finalWidth = tableWidth * scale;
                            const finalHeight = tableHeight * scale;

                            // Zentriere die Tabelle horizontal, positioniere sie unter dem Titel
                            const xPosition = margin + (contentWidth - finalWidth) / 2;
                            const yPosition = margin + 35; // Versatz vom oberen Rand

                            // Verbesserte Bildqualität
                            const imgData = canvas.toDataURL('image/png', 1.0);
                            pdf.addImage(imgData, 'PNG', xPosition, yPosition, finalWidth, finalHeight);

                            // Füge Legende am unteren Rand der Seite hinzu
                            const legendY = yPosition + finalHeight + 10;

                            // Prüfe, ob noch genug Platz für die Legende ist
                            if (legendY < pageHeight - margin - 30) {
                                pdf.setFontSize(12);
                                pdf.setTextColor(0, 51, 102); // Dunkelblau
                                pdf.text('Legende:', margin, legendY);

                                // Legende in kleinerer Schrift
                                pdf.setFontSize(9);
                                pdf.setTextColor(0, 0, 0); // Schwarz

                                const legendLines = legendContent.split('\n');
                                let currentY = legendY + 6;

                                // Maximal 6 Zeilen der Legende anzeigen, um Platzmangel zu vermeiden
                                const maxLines = Math.min(legendLines.length, 6);
                                for (let j = 0; j < maxLines; j++) {
                                    if (legendLines[j].trim() !== '') {
                                        pdf.text(legendLines[j], margin, currentY);
                                        currentY += 4;
                                    }
                                }
                            }

                            // Fußzeile mit Seitenzahl und Dokumententitel
                            pdf.setFontSize(8);
                            pdf.setTextColor(100, 100, 100); // Grau
                            pdf.text('RescueCompete - ' + documentTitle, margin, pageHeight - 8);
                            pdf.text('Seite ' + (i + 1) + ' von ' + pageCount, pageWidth - margin - 25, pageHeight - 8);

                            processedSections++;

                            // Aktualisiere die Ladeanimation
                            updateLoadingIndicator(processedSections, totalSections);

                        } catch (canvasError) {
                            console.error('Fehler bei der Tabellen-Konvertierung:', canvasError);
                            pdf.setFontSize(12);
                            pdf.setTextColor(255, 0, 0); // Rot
                            pdf.text('Fehler beim Rendern der Tabelle: ' + canvasError.message, margin, margin + 40);
                            processedSections++;
                        }
                    } else {
                        // Wenn keine Tabelle gefunden wurde
                        pdf.setFontSize(12);
                        pdf.text('Keine Daten verfügbar', margin, margin + 30);
                        processedSections++;
                    }
                }

                // Wenn keine Sektionen gefunden wurden, erstelle trotzdem ein einfaches PDF
                if (totalSections === 0) {
                    pdf.setFontSize(18);
                    pdf.setTextColor(0, 51, 102); // Dunkelblau
                    pdf.text(documentTitle, margin, margin + 10);

                    pdf.setFontSize(12);
                    pdf.text('Keine Ergebnisdaten verfügbar', margin, contentHeight / 2);

                    // Füge Logo hinzu, falls verfügbar
                    if (logoImgData) {
                        pdf.addImage(logoImgData, 'PNG', pageWidth - margin - 25, margin, 20, 20);
                    }
                }

                // Speichere das PDF mit bereinigtem Dateinamen
                const fileName = documentTitle
                    .replace(/[^a-z0-9äöüß\s-]/gi, '')
                    .replace(/\s+/g, '_')
                    .toLowerCase() + '.pdf';

                pdf.save(fileName);
                hideLoadingIndicator();
            };

            // Starte die Verarbeitung
            await processSections();

        } catch (error) {
            console.error('Fehler beim PDF-Export:', error);
            alert('Beim PDF-Export ist ein Fehler aufgetreten: ' + error.message);
            hideLoadingIndicator();
        }
    }, 100);
}

/**
 * Aktualisiert den Fortschritt in der Ladeanimation
 * @param {number} current - Aktueller Fortschritt
 * @param {number} total - Gesamtzahl der zu verarbeitenden Elemente
 */
function updateLoadingIndicator(current, total) {
    const loadingText = document.querySelector('#pdf-loading-indicator .loading-content p');
    if (loadingText) {
        const percent = Math.round((current / total) * 100);
        loadingText.textContent = `PDF wird erstellt... ${percent}%`;
    }

    // Aktualisiere auch den Fortschrittsbalken, wenn vorhanden
    const progressBar = document.getElementById('pdf-progress-bar');
    if (progressBar) {
        const percentValue = Math.round((current / total) * 100);
        progressBar.style.width = percentValue + '%';
    }
}

/**
 * Zeigt eine Ladeanimation während des PDF-Exports an
 */
function showLoadingIndicator() {
    // Erstelle eine Ladeanimation, falls noch nicht vorhanden
    let loadingIndicator = document.getElementById('pdf-loading-indicator');

    if (!loadingIndicator) {
        loadingIndicator = document.createElement('div');
        loadingIndicator.id = 'pdf-loading-indicator';
        loadingIndicator.innerHTML = `
            <div class="loading-overlay"></div>
            <div class="loading-content">
                <div class="loading-spinner"></div>
                <p>PDF wird erstellt...</p>
                <div class="loading-progress">
                    <div class="loading-progress-bar" id="pdf-progress-bar"></div>
                </div>
            </div>
        `;
        document.body.appendChild(loadingIndicator);
    }

    // Zeige die Ladeanimation
    loadingIndicator.style.display = 'flex';

    // Setze den Fortschrittsbalken auf 0%
    const progressBar = document.getElementById('pdf-progress-bar');
    if (progressBar) {
        progressBar.style.width = '0%';
    }
}

/**
 * Versteckt die Ladeanimation nach dem PDF-Export
 */
function hideLoadingIndicator() {
    const loadingIndicator = document.getElementById('pdf-loading-indicator');
    if (loadingIndicator) {
        loadingIndicator.style.display = 'none';
    }
}

/**
 * Bestimmt den Typ der aktuellen Seite (Schwimmergebnisse, Parcoursergebnisse, Gesamtergebnisse)
 * @returns {string} Der Seitentyp
 */
function determinePageType() {
    const pageTitle = document.title || '';

    if (pageTitle.includes('Schwimm') || document.querySelector('h1')?.textContent?.includes('Schwimm')) {
        return 'swimming';
    } else if (pageTitle.includes('Parcours') || document.querySelector('h1')?.textContent?.includes('Parcours')) {
        return 'parcours';
    } else {
        return 'complete';
    }
}