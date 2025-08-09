/**
 * FooterScript.js
 * JavaScript-Funktionalität für den Footer
 *
 * Features:
 * - Icon-Fallback-System für fehlgeschlagene Logo-Loads
 * - Link-Validierung für externe Links
 * - Accessibility-Verbesserungen
 * - Performance-Optimierungen
 */

document.addEventListener('DOMContentLoaded', function() {
    initializeFooter();
});

/**
 * Initialisiert die Footer-Funktionalität
 */
function initializeFooter() {
    initializeLogoFallback();
    initializeExternalLinks();
    initializeAccessibility();
    setupPerformanceOptimizations();
}

/**
 * Logo-Loading-Fallback System
 * Falls Logos nicht geladen werden können, Text-Platzhalter anzeigen
 */
function initializeLogoFallback() {
    const logoImages = document.querySelectorAll('.footerLogo img');

    logoImages.forEach(img => {
        // Prüfen ob das Bild bereits geladen ist
        if (img.complete && img.naturalHeight === 0) {
            createLogoFallback(img);
        }

        img.addEventListener('error', function() {
            createLogoFallback(this);
        });

        img.addEventListener('load', function() {
            // Logo erfolgreich geladen
        });
    });
}

/**
 * Erstellt Text-Fallback für fehlgeschlagene Logo-Loads
 */
function createLogoFallback(logoElement) {
    const altText = logoElement.alt || 'Logo';
    const fallback = document.createElement('div');

    fallback.className = 'footer-logo-fallback';
    fallback.textContent = altText;
    fallback.title = altText;
    fallback.setAttribute('aria-label', altText);

    // Styling für Fallback
    fallback.style.cssText = `
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 50px;
        height: 50px;
        background-color: var(--ww-blue-100);
        color: var(--white);
        border-radius: 4px;
        font-size: 12px;
        font-weight: bold;
        text-align: center;
        line-height: 1.2;
    `;

    if (logoElement.parentNode) {
        logoElement.parentNode.replaceChild(fallback, logoElement);
    }
}

/**
 * Externe Link-Funktionalität
 */
function initializeExternalLinks() {
    const externalLinks = document.querySelectorAll('.footerLogo[target="_blank"], .footer-links a[target="_blank"]');

    externalLinks.forEach(link => {
        // Sicherheitsattribute hinzufügen falls nicht vorhanden
        if (!link.hasAttribute('rel')) {
            link.setAttribute('rel', 'noopener noreferrer');
        }

        // Click-Handler für Analytics oder Logging (falls gewünscht)
        link.addEventListener('click', function(event) {
            logExternalLinkClick(this.href);
        });
    });
}

/**
 * Logging für externe Link-Klicks (optional)
 */
function logExternalLinkClick(url) {
    // Hier könnte Analytics-Code eingefügt werden
    try {
        console.log('External link clicked:', url);
        // Beispiel: Analytics-Tracking
        // gtag('event', 'click', {
        //     event_category: 'footer',
        //     event_label: url
        // });
    } catch (error) {
        // Fehler beim Logging ignorieren
    }
}

/**
 * Barrierefreiheit und Keyboard-Navigation
 */
function initializeAccessibility() {
    const footerLinks = document.querySelectorAll('.footerLogo, .footer-links a');

    footerLinks.forEach(link => {
        // Keyboard-Navigation verbessern
        link.addEventListener('keydown', function(event) {
            switch(event.key) {
                case 'Enter':
                case ' ':
                    // Standard-Verhalten für Links beibehalten
                    break;
                case 'Tab':
                    // Tab-Navigation funktioniert bereits durch Browser
                    break;
            }
        });

        // Focus-Verbesserungen
        link.addEventListener('focus', function() {
            // Optional: Zusätzliche visuelle Hervorhebung
        });

        link.addEventListener('blur', function() {
            // Optional: Focus-Cleanup
        });
    });

    // ARIA-Labels für bessere Accessibility
    const footerElement = document.querySelector('.footer');
    if (footerElement && !footerElement.hasAttribute('role')) {
        footerElement.setAttribute('role', 'contentinfo');
    }

    // Landmark für Screen-Reader
    const footerContent = document.querySelector('.footer-content');
    if (footerContent && !footerContent.hasAttribute('aria-label')) {
        footerContent.setAttribute('aria-label', 'Website-Informationen und Links');
    }
}

/**
 * Performance-Optimierungen
 */
function setupPerformanceOptimizations() {
    // Lazy Loading für Footer (falls er sich außerhalb des Viewports befindet)
    if ('IntersectionObserver' in window) {
        const footerObserver = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    // Footer ist sichtbar - hier könnten zusätzliche Aktionen ausgeführt werden
                    loadFooterExtras();
                    footerObserver.unobserve(entry.target);
                }
            });
        }, {
            rootMargin: '50px'
        });

        const footer = document.querySelector('.footer');
        if (footer) {
            footerObserver.observe(footer);
        }
    }

    // Prefetch für wichtige externe Links bei Hover
    const importantLinks = document.querySelectorAll('.footerLogo[href*="drk.de"], .footerLogo[href*="th-luebeck.de"]');
    importantLinks.forEach(link => {
        link.addEventListener('mouseenter', function() {
            prefetchPage(this.href);
        }, { once: true });
    });
}

/**
 * Lädt zusätzliche Footer-Funktionalitäten
 */
function loadFooterExtras() {
    // Hier könnten zusätzliche Features geladen werden
    // z.B. Social Media Widgets, weitere Analytics, etc.
}

/**
 * Prefetch-Funktion für bessere Performance
 */
function prefetchPage(url) {
    if (!url || url === '#') return;

    try {
        // DNS-Prefetch für externe Domains
        const urlObj = new URL(url);
        const link = document.createElement('link');
        link.rel = 'dns-prefetch';
        link.href = urlObj.origin;
        document.head.appendChild(link);
    } catch (error) {
        // Prefetch fehlgeschlagen, ignorieren
    }
}

/**
 * Error Handling
 */
window.addEventListener('error', function(event) {
    if (event.filename && event.filename.includes('FooterScript.js')) {
        // Error-Handling für Footer-spezifische Fehler
        console.warn('Footer script error:', event.error);
    }
});

/**
 * Public API für externe Nutzung
 */
window.FooterAPI = {
    // Utility functions
    isFooterVisible: function() {
        const footer = document.querySelector('.footer');
        if (!footer) return false;

        const rect = footer.getBoundingClientRect();
        return rect.top < window.innerHeight && rect.bottom > 0;
    },

    logExternalLinkClick: logExternalLinkClick,

    refreshFooter: function() {
        // Footer neu initialisieren
        initializeFooter();
    }
};