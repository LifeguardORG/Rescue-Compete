/**
 * Excel-Export-Funktionalität für die Ergebnisseiten
 * Erstellt eine .xlsx mit echten Zellen, je Wertung ein eigenes Tabellenblatt,
 * inkl. eingebettetem Logo. Nutzt ExcelJS (siehe PdfExportLibs.php).
 */

document.addEventListener('DOMContentLoaded', function () {
    initExcelExportButtons();
});

/**
 * Initialisiert die Excel-Export-Buttons auf der Seite
 */
function initExcelExportButtons() {
    const exportButtons = document.querySelectorAll('.excel-export-btn');

    exportButtons.forEach(button => {
        button.addEventListener('click', function () {
            const pageTitle = document.querySelector('h1')?.textContent ||
                document.title ||
                'RescueCompete Ergebnisse';
            exportResultsToExcel(pageTitle);
        });
    });
}

/**
 * Exportiert die Ergebnistabellen in eine Excel-Datei
 * @param {string} documentTitle - Der Titel für das Dokument
 */
async function exportResultsToExcel(documentTitle) {
    if (typeof ExcelJS === 'undefined') {
        showAlert('Fehler', 'Excel-Bibliothek konnte nicht geladen werden. Bitte Seite neu laden.');
        return;
    }

    const resultSections = document.querySelectorAll('.results-section');
    if (resultSections.length === 0) {
        showAlert('Hinweis', 'Keine Ergebnisdaten zum Exportieren verfügbar.');
        return;
    }

    showLoadingIndicator();

    try {
        const workbook = new ExcelJS.Workbook();
        workbook.creator = 'RescueCompete';
        workbook.created = new Date();

        // Logo einmalig vorbereiten und in die Arbeitsmappe einbetten
        const logoId = await prepareLogo(workbook);

        const usedNames = new Set();
        const now = new Date();
        const dateString = now.toLocaleDateString('de-DE') + ' ' + now.toLocaleTimeString('de-DE');

        for (let i = 0; i < resultSections.length; i++) {
            const section = resultSections[i];
            const rawTitle = section.querySelector('h2')?.textContent || '';
            const sheetName = buildSheetName(rawTitle, i, usedNames);

            const worksheet = workbook.addWorksheet(sheetName);

            // Titelzeilen (Wertung, Dokumenttitel, Erstellungsdatum)
            const titleRow = worksheet.addRow([rawTitle.trim()]);
            titleRow.font = { bold: true, size: 16, color: { argb: 'FF003366' } };

            const subtitleRow = worksheet.addRow([documentTitle]);
            subtitleRow.font = { bold: true, size: 12 };

            const dateRow = worksheet.addRow(['Erstellt am: ' + dateString]);
            dateRow.font = { size: 10, color: { argb: 'FF646464' } };

            worksheet.addRow([]); // Leerzeile vor der Tabelle

            // Tabelle übertragen
            const table = section.querySelector('.results-table');
            if (table) {
                writeTableToWorksheet(table, worksheet);
            } else {
                worksheet.addRow(['Keine Daten verfügbar']);
            }

            // Logo zuletzt einsetzen: floatet oben rechts über dem Titelbereich, ohne
            // den Zeilenfluss zu verschieben (addImage würde sonst die erste Zeile
            // nach unten drücken).
            if (logoId !== null) {
                worksheet.addImage(logoId, {
                    tl: { col: 6, row: 0 },
                    ext: { width: 90, height: 90 }
                });
            }
        }

        const buffer = await workbook.xlsx.writeBuffer();
        const blob = new Blob([buffer], {
            type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        });

        const fileName = documentTitle
            .replace(/[^a-z0-9äöüß\s-]/gi, '')
            .replace(/\s+/g, '_')
            .toLowerCase() + '.xlsx';

        triggerDownload(blob, fileName);
    } catch (error) {
        console.error('Fehler beim Excel-Export:', error);
        showAlert('Fehler', 'Beim Excel-Export ist ein Fehler aufgetreten: ' + error.message);
    } finally {
        hideLoadingIndicator();
    }
}

/**
 * Lädt das Logo aus dem DOM, wandelt es in base64 und bettet es in die Arbeitsmappe ein.
 * @param {ExcelJS.Workbook} workbook
 * @returns {Promise<number|null>} Die Bild-ID oder null, falls kein Logo verfügbar ist.
 */
async function prepareLogo(workbook) {
    try {
        const logoImg = document.querySelector('img[src*="ww-rundlogo.png"]');
        if (!logoImg) {
            return null;
        }

        const width = logoImg.naturalWidth || logoImg.width || 200;
        const height = logoImg.naturalHeight || logoImg.height || 200;

        const canvas = document.createElement('canvas');
        canvas.width = width;
        canvas.height = height;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(logoImg, 0, 0, width, height);

        const dataUrl = canvas.toDataURL('image/png');
        const base64 = dataUrl.split(',')[1];

        return workbook.addImage({ base64: base64, extension: 'png' });
    } catch (logoErr) {
        console.warn('Logo konnte nicht eingebettet werden:', logoErr);
        return null;
    }
}

/**
 * Erzeugt einen gültigen, eindeutigen Excel-Blattnamen aus dem Wertungstitel.
 * @param {string} rawTitle - Roher h2-Text (z. B. "Wertung: Männlich")
 * @param {number} index - Laufende Nummer (für Fallback)
 * @param {Set<string>} usedNames - Bereits vergebene Namen
 * @returns {string}
 */
function buildSheetName(rawTitle, index, usedNames) {
    // Führendes "Wertung:" / "Wertung " entfernen
    let name = rawTitle.replace(/^\s*Wertung\s*:?\s*/i, '').trim();

    // In Excel unzulässige Zeichen entfernen
    name = name.replace(/[:\\/?*\[\]]/g, '').trim();

    if (name === '') {
        name = 'Wertung ' + (index + 1);
    }

    // Excel-Limit: max. 31 Zeichen
    name = name.substring(0, 31);

    // Eindeutigkeit sicherstellen
    if (usedNames.has(name)) {
        let suffix = 2;
        let candidate;
        do {
            const suffixStr = '_' + suffix;
            candidate = name.substring(0, 31 - suffixStr.length) + suffixStr;
            suffix++;
        } while (usedNames.has(candidate));
        name = candidate;
    }

    usedNames.add(name);
    return name;
}

/**
 * Überträgt eine HTML-Ergebnistabelle in echte Worksheet-Zellen.
 * Mehrwertige Zellen (durch <br> getrennt) werden als mehrzeiliger Zelltext abgelegt.
 * @param {HTMLTableElement} table
 * @param {ExcelJS.Worksheet} worksheet
 */
function writeTableToWorksheet(table, worksheet) {
    const rows = table.querySelectorAll('thead tr, tbody tr');
    const headerRowIndexes = [];
    const colMaxLen = [];

    const theadRowCount = table.querySelectorAll('thead tr').length;

    rows.forEach((tr, rowIdx) => {
        const cells = tr.querySelectorAll('th, td');
        const values = [];
        let hasMultiline = false;

        cells.forEach((cell, colIdx) => {
            const text = extractCellText(cell);
            if (text.indexOf('\n') !== -1) {
                hasMultiline = true;
            }
            values.push(toCellValue(text));

            // Spaltenbreite anhand der längsten (Teil-)Zeile schätzen
            const longestLine = text.split('\n').reduce((m, l) => Math.max(m, l.length), 0);
            colMaxLen[colIdx] = Math.max(colMaxLen[colIdx] || 10, longestLine);
        });

        const excelRow = worksheet.addRow(values);

        // Kopfzeilen (aus <thead>) fett darstellen
        if (rowIdx < theadRowCount) {
            excelRow.font = { bold: true };
            headerRowIndexes.push(excelRow.number);
        }

        // Mehrzeilige Zellen umbrechen
        if (hasMultiline) {
            excelRow.eachCell(cell => {
                cell.alignment = { wrapText: true, vertical: 'top' };
            });
        }
    });

    // Spaltenbreiten setzen (mit Polster, gedeckelt)
    colMaxLen.forEach((len, idx) => {
        worksheet.getColumn(idx + 1).width = Math.min(Math.max(len + 2, 12), 40);
    });
}

/**
 * Extrahiert den Textinhalt einer Zelle und ersetzt <br> durch Zeilenumbrüche,
 * damit mehrwertige Zellen (Schwimmen: 4 Werte, Parcours: 2 Werte) lesbar bleiben.
 * @param {HTMLElement} cell
 * @returns {string}
 */
function extractCellText(cell) {
    const html = cell.innerHTML.replace(/<br\s*\/?>/gi, '\n');
    const temp = document.createElement('div');
    temp.innerHTML = html;
    const raw = temp.textContent || '';

    return raw
        .split('\n')
        .map(line => line.trim())
        .filter(line => line !== '')
        .join('\n');
}

/**
 * Wandelt einen Zelltext in eine Zahl, wenn er eindeutig numerisch und einzeilig ist,
 * sonst bleibt es Text.
 * @param {string} text
 * @returns {string|number}
 */
function toCellValue(text) {
    if (text.indexOf('\n') === -1 && /^-?\d+(\.\d+)?$/.test(text)) {
        return Number(text);
    }
    return text;
}

/**
 * Löst den Browser-Download eines Blobs aus.
 * @param {Blob} blob
 * @param {string} fileName
 */
function triggerDownload(blob, fileName) {
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = fileName;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
}
