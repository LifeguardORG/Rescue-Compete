// QR-Codes Übersicht für Schiedsrichter — Stationen + Staffeln

document.addEventListener('DOMContentLoaded', function () {
    initializeAllQrCodes();
    initializeTabs();
});

/**
 * Generiert QR-Codes für alle Container mit data-url. QRCode.js erzeugt
 * für jeden Container ein <canvas>-Element, das auch in versteckten Tabs
 * existiert (display:none stört das Rendering nicht).
 */
function initializeAllQrCodes() {
    const containers = document.querySelectorAll('[id^="qrcode-"]');
    containers.forEach(container => {
        const url = container.getAttribute('data-url');
        if (!url) return;
        new QRCode(container, {
            text: url,
            width: 180,
            height: 180,
            colorDark: '#008ccd',
            colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.H
        });
    });
}

/**
 * Tab-Wechsel per Klick. Setzt zusätzlich ?tab=... in die URL, damit
 * Reload den aktiven Tab beibehält.
 */
function initializeTabs() {
    const buttons = document.querySelectorAll('.tab-button[data-tab]');
    buttons.forEach(btn => {
        btn.addEventListener('click', () => {
            const tabName = btn.getAttribute('data-tab');
            showTab(tabName);
            try {
                const url = new URL(window.location.href);
                url.searchParams.set('tab', tabName);
                window.history.replaceState({}, '', url);
            } catch (e) {
                // history API nicht verfügbar — ignorieren
            }
        });
    });
}

function showTab(tabName) {
    document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.tab-button').forEach(el => el.classList.remove('active'));

    const content = document.getElementById('tab-' + tabName);
    if (content) content.classList.add('active');

    const button = document.querySelector(`.tab-button[data-tab="${tabName}"]`);
    if (button) button.classList.add('active');
}

/**
 * Lädt einen einzelnen QR-Code als PNG herunter.
 */
function downloadQrCode(cardId, label) {
    const canvas = document.querySelector(`#qrcode-${cardId} canvas`);
    if (!canvas) {
        console.error(`Canvas für ${cardId} nicht gefunden.`);
        return;
    }
    const link = document.createElement('a');
    link.download = `qrcode-${sanitizeFilename(label)}.png`;
    link.href = canvas.toDataURL('image/png');
    link.click();
}

/**
 * Bündelt alle QR-Codes des angegebenen Tabs in ein ZIP-Archiv.
 * Erfordert JSZip und FileSaver (per CDN eingebunden).
 */
function downloadAllQrCodes(kind) {
    if (typeof JSZip === 'undefined' || typeof saveAs === 'undefined') {
        alert('ZIP-Bibliothek nicht geladen. Bitte Seite neu laden.');
        return;
    }
    const items = document.querySelectorAll(`.qr-code-item[data-qr-kind="${kind}"]`);
    if (items.length === 0) {
        alert('Keine Codes zum Herunterladen vorhanden.');
        return;
    }

    const zip = new JSZip();
    let added = 0;
    items.forEach(item => {
        const label = item.getAttribute('data-qr-label') || 'qrcode';
        const canvas = item.querySelector('canvas');
        if (!canvas) return;
        const dataUrl = canvas.toDataURL('image/png');
        // Data-URL "data:image/png;base64,XXXX" → nur Base64-Teil
        const base64 = dataUrl.split(',')[1];
        zip.file(`qrcode-${sanitizeFilename(label)}.png`, base64, { base64: true });
        added++;
    });

    if (added === 0) {
        alert('Keine Codes zum Herunterladen vorhanden.');
        return;
    }

    const archiveName = kind === 'stations' ? 'stations-qr-codes.zip' : 'staffeln-qr-codes.zip';
    zip.generateAsync({ type: 'blob' }).then(blob => {
        saveAs(blob, archiveName);
    }).catch(err => {
        console.error('ZIP-Erstellung fehlgeschlagen:', err);
        alert('ZIP konnte nicht erstellt werden.');
    });
}

function sanitizeFilename(name) {
    return String(name).replace(/[^a-zA-Z0-9_-]+/g, '-').replace(/^-+|-+$/g, '').toLowerCase() || 'qrcode';
}
