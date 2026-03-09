/**
 * ModalUtils.js – Dynamische Custom-Modals als Ersatz für native alert()/confirm()
 * Nutzt die bestehende .modal / .modal-content CSS-Struktur aus Components.css
 */

function _escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function _closeAndRemoveModal(modal) {
    modal.classList.remove('active');
    setTimeout(function () { modal.remove(); }, 300);
}

/**
 * Zeigt ein Alert-Modal mit OK-Button.
 */
function showAlert(title, message) {
    const modal = document.createElement('div');
    modal.className = 'modal active';

    const content = document.createElement('div');
    content.className = 'modal-content';
    content.innerHTML = '<h2>' + _escapeHtml(title) + '</h2>' +
        '<p>' + _escapeHtml(message) + '</p>';

    const okBtn = document.createElement('button');
    okBtn.type = 'button';
    okBtn.className = 'btn';
    okBtn.textContent = 'OK';
    okBtn.addEventListener('click', function () { _closeAndRemoveModal(modal); });

    content.appendChild(okBtn);
    modal.appendChild(content);
    document.body.appendChild(modal);
}

/**
 * Zeigt ein Confirm-Modal mit Ja/Nein-Buttons.
 * @param {string} title
 * @param {string} message
 * @param {Function} onYes – Callback bei Klick auf "Ja"
 */
function showConfirm(title, message, onYes) {
    const modal = document.createElement('div');
    modal.className = 'modal active';

    const content = document.createElement('div');
    content.className = 'modal-content';
    content.innerHTML = '<h2>' + _escapeHtml(title) + '</h2>' +
        '<p>' + _escapeHtml(message) + '</p>';

    const yesBtn = document.createElement('button');
    yesBtn.type = 'button';
    yesBtn.className = 'btn primary-btn';
    yesBtn.textContent = 'Ja';
    yesBtn.addEventListener('click', function () {
        _closeAndRemoveModal(modal);
        if (onYes) onYes();
    });

    const noBtn = document.createElement('button');
    noBtn.type = 'button';
    noBtn.className = 'btn';
    noBtn.textContent = 'Nein';
    noBtn.addEventListener('click', function () { _closeAndRemoveModal(modal); });

    content.appendChild(yesBtn);
    content.appendChild(noBtn);
    modal.appendChild(content);
    document.body.appendChild(modal);
}
