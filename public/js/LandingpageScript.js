/**
 * RescueCompete Landingpage Scripts
 * Autor: Jonas Richter & Sven Meiburg
 * Projekt: Designprojekt 2 - TH Lübeck
 */

document.addEventListener('DOMContentLoaded', function() {
    initializeLandingpage();
});

/**
 * Initialisiert alle Landingpage-Funktionen
 */
function initializeLandingpage() {
    initializeContactButtons();
    addKeyboardNavigation();
    initializeSmoothScroll();
}

/**
 * Initialisiert Contact Button Funktionalitäten
 */
function initializeContactButtons() {
    const contactButtons = document.querySelectorAll('.contact-button, .cta-button');

    contactButtons.forEach(function(button) {
        // Click-Feedback ohne übertriebene Animationen
        button.addEventListener('click', function() {
            // Einfaches visuelles Feedback
            this.style.opacity = '0.8';
            setTimeout(() => {
                this.style.opacity = '1';
            }, 100);
        });
    });
}

/**
 * Fügt Keyboard-Navigation hinzu für bessere Accessibility
 */
function addKeyboardNavigation() {
    const focusableElements = document.querySelectorAll('a, button, [tabindex]:not([tabindex="-1"])');

    focusableElements.forEach(function(element) {
        element.addEventListener('focus', function() {
            this.style.outline = '2px solid var(--ww-blue-100)';
            this.style.outlineOffset = '2px';
        });

        element.addEventListener('blur', function() {
            this.style.outline = 'none';
        });
    });
}

/**
 * Smooth Scroll für interne Links
 */
function initializeSmoothScroll() {
    const internalLinks = document.querySelectorAll('a[href^="#"]');

    internalLinks.forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href').substring(1);
            const targetElement = document.getElementById(targetId);

            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

/**
 * Utility Funktion für Custom Alert Boxes
 */
function showCustomAlert(message, type = 'info') {
    // Implementierung für Custom Alert Boxes
    // Wird später für Feedback-Funktionen verwendet
    console.log(`Custom Alert (${type}): ${message}`);
}

/**
 * Error Handler für Contact Forms
 */
function handleContactFormSubmission(formData) {
    // Placeholder für zukünftige Kontaktformular-Funktionalität
    console.log('Contact form submission:', formData);
}

// Export für Verwendung in anderen Modulen
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        showCustomAlert,
        handleContactFormSubmission
    };
}