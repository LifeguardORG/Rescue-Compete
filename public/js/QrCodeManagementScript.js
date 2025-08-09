// QR-Codes erstellen, sobald das Dokument geladen ist
document.addEventListener('DOMContentLoaded', function() {
    initializeQrCodes();
});

/**
 * Initialisiert alle QR-Codes auf der Seite
 * Sucht nach Containern mit data-url Attribut und generiert QR-Codes
 */
function initializeQrCodes() {
    const qrContainers = document.querySelectorAll('[id^="qrcode-"]');

    qrContainers.forEach(container => {
        const url = container.getAttribute('data-url');
        if (url) {
            new QRCode(container, {
                text: url,
                width: 180,
                height: 180,
                colorDark: "#008ccd",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.H
            });
        }
    });
}

/**
 * Lädt einen QR-Code als PNG-Bild herunter
 *
 * @param {number} index - Index des QR-Codes in der Liste
 * @param {string} title - Titel für den Dateinamen
 */
function downloadQrCode(index, title) {
    const canvas = document.querySelector(`#qrcode-${index} canvas`);
    if (canvas) {
        // Link-Element erstellen
        const link = document.createElement('a');
        // Dateinamen festlegen (unerwünschte Zeichen ersetzen)
        link.download = `qrcode-${title.replace(/[^a-zA-Z0-9]/g, '-').toLowerCase()}.png`;
        // Canvas in Bild-URL umwandeln
        link.href = canvas.toDataURL('image/png');
        // Link-Klick simulieren (Download starten)
        link.click();
    } else {
        console.error(`Canvas für QR-Code ${index} nicht gefunden.`);
    }
}