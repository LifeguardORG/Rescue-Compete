/**
 * Verbesserte Version von FormViewScript.js - Funktionalität für das Fragenformular
 * mit persistentem Timer und korrekter Formularverarbeitung
 */

document.addEventListener('DOMContentLoaded', function() {

    // Elemente referenzieren
    const form = document.getElementById('question-form');
    const timerElement = document.getElementById('timer');
    const timerContainer = document.getElementById('timer-container');
    const progressBar = document.getElementById('progress-bar');
    const localStorageKey = 'formTimer_' + formConfig.instanceToken;

    if (!form || !timerElement || !timerContainer || !progressBar) {
        console.error("FEHLER: Notwendige Elemente wurden nicht gefunden!");
        return;
    }

    // Alert Box Buttons registrieren
    const yesButton = document.querySelector("#incompleteFormConfirm .btn.submit-btn");
    const noButton = document.querySelector("#incompleteFormConfirm .btn:not(.submit-btn)");

    if (yesButton) {
        yesButton.addEventListener("click", submitIncompleteForm);
    }

    if (noButton) {
        noButton.addEventListener("click", function(event) {
            event.preventDefault();
            document.getElementById('incompleteFormConfirm').classList.remove('active');
            document.getElementById('alert-container').style.display = 'none';
        });
    }

    // Zeit-Management
    let endTime;
    let timeLeft;

    const browserNow = Date.now();

    const savedTimerData = localStorage.getItem(localStorageKey);

    function saveTimerToLocalStorage(endTime, startTime) {
        const timerData = { endTime, startTime };
        localStorage.setItem(localStorageKey, JSON.stringify(timerData));
        console.log("🕒 Timer im LocalStorage gespeichert", timerData);
    }

    function clearTimerFromLocalStorage() {
        localStorage.removeItem(localStorageKey);
        console.log("🗑️ Timer aus dem LocalStorage gelöscht");
    }

    window.clearTimerFromLocalStorage = clearTimerFromLocalStorage;

    if (savedTimerData) {
        console.log("⌛ Versuche gespeicherten Timer zu laden");
        try {
            const timerData = JSON.parse(savedTimerData);
            endTime = timerData.endTime;

            if (endTime <= browserNow) {
                console.log("⏰ Gespeicherte Endzeit ist abgelaufen");
                submitExpiredForm();
                return;
            } else {
                timeLeft = Math.max(0, Math.floor((endTime - browserNow) / 1000));
            }
        } catch (error) {
            console.error("⚠️ Fehler beim Parsen der Timer-Daten:", error);
        }
    } else {
        console.log("🆕 Kein Timer im LocalStorage gefunden, erstelle neuen Timer");
        timeLeft = formConfig.timeLimit;
        endTime = browserNow + (timeLeft * 1000);
        saveTimerToLocalStorage(endTime, browserNow);
    }

    /**
     * Funktion zur Verarbeitung abgelaufener Formulare
     */
    async function submitExpiredForm() {
        console.log("🚀 Versuche, abgelaufenes Formular automatisch abzusenden");

        // WICHTIG: Zuerst den Timer aus dem LocalStorage löschen, um die Schleife zu verhindern
        clearTimerFromLocalStorage();

        try {
            const formData = new FormData();
            formData.append('token', formConfig.instanceToken);
            formData.append('auto_submit', '1');
            formData.append('client_timezone', Intl.DateTimeFormat().resolvedOptions().timeZone);

            const response = await fetch('../php_assets/TimerUpdateHandler.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            if (data.success) {
                window.location.href = data.redirect || (window.location.href + (window.location.href.includes('?') ? '&expired=1' : '?expired=1'));
            } else {
                throw new Error('Server konnte das Formular nicht automatisch abschließen');
            }
        } catch (error) {
            console.error("❌ Fehler beim automatischen Absenden:", error);

            // Bei Fehler: Füge expired=1 zur URL hinzu
            const currentAction = form.getAttribute('action');
            const separator = currentAction.includes('?') ? '&' : '?';
            const newAction = currentAction + separator + 'expired=1';
            form.setAttribute('action', newAction);

            // Füge ein verstecktes Feld hinzu, um sicherzustellen, dass der Server es erhält
            const expiredField = document.createElement('input');
            expiredField.type = 'hidden';
            expiredField.name = 'expired';
            expiredField.value = '1';
            form.appendChild(expiredField);

            form.submit();
        }
    }

    /**
     * Timer-Update-Logik
     */
    const timer = setInterval(function() {
        const now = Date.now();
        timeLeft = Math.max(0, Math.floor((endTime - now) / 1000));

        const minutes = Math.floor(timeLeft / 60);
        const seconds = timeLeft % 60;
        timerElement.textContent = `Verbleibende Zeit: ${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;

        progressBar.style.width = (100 - ((timeLeft / formConfig.timeLimit) * 100)) + '%';

        if (timeLeft <= 0) {
            clearInterval(timer);
            submitExpiredForm();
        }
    }, 1000);

    /**
     * Formular-Validierung beim Absenden
     */
    form.addEventListener('submit', function(e) {
        const teamSelect = document.getElementById('team_id');
        if (teamSelect && !teamSelect.value) {
            e.preventDefault();
            alert('Bitte wählen Sie Ihre Mannschaft aus.');
            return false;
        }

        const questions = document.querySelectorAll('.question-box');
        let unansweredQuestions = [];

        questions.forEach(function(question, index) {
            const isChecked = question.querySelector('input[type="radio"]:checked');
            if (!isChecked) unansweredQuestions.push(index + 1);
        });

        if (unansweredQuestions.length > 0) {
            e.preventDefault();
            document.querySelector('#incompleteFormConfirm p').textContent =
                `Sie haben nicht alle Fragen beantwortet. Fehlende Fragen: ${unansweredQuestions.join(', ')}. Möchten Sie das Formular trotzdem absenden?`;
            document.getElementById('alert-container').style.display = 'block';
            document.getElementById('incompleteFormConfirm').classList.add('active');
        } else {
            clearTimerFromLocalStorage();
        }
    });
});

/**
 * Formular abschicken, wenn der Benutzer in der AlertBox auf "Ja" klickt
 */
function submitIncompleteForm(event) {
    event.preventDefault();
    console.log("📝 Unvollständiges Formular wird jetzt abgesendet...");
    document.getElementById('incompleteFormConfirm').classList.remove('active');
    document.getElementById('alert-container').style.display = 'none';
    clearTimerFromLocalStorage();
    document.getElementById('question-form').submit();
}