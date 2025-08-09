/**
 * SidebarScript.js
 * Einfache JavaScript-Funktionalität für die Wettkampf-Sidebar
 *
 * Features:
 * - Responsive Verhalten
 * - Accessibility
 */

document.addEventListener('DOMContentLoaded', function() {
    initializeSidebar();
});

/**
 * Initialisiert die Sidebar-Funktionalität
 */
function initializeSidebar() {
    initializeResponsiveHandling();
    initializeAccessibility();
}

/**
 * Responsive Verhalten
 */
function initializeResponsiveHandling() {
    // Bildschirmgrößenänderung überwachen
    window.addEventListener('resize', debounce(handleResize, 250));

    // Initialen Zustand setzen
    handleResize();
}

/**
 * Behandelt Bildschirmgrößenänderungen
 */
function handleResize() {
    const mainContent = document.querySelector('.main-content');

    if (!mainContent) return;

    // Bei mobilen Geräten Sidebar-Margin entfernen
    if (window.innerWidth <= 1024) {
        mainContent.style.marginLeft = '0';
    } else {
        mainContent.style.marginLeft = '200px';
    }
}

/**
 * Accessibility-Features
 */
function initializeAccessibility() {
    // ARIA-Attribute setzen
    const sidebar = document.querySelector('.competition-sidebar');
    if (sidebar) {
        sidebar.setAttribute('role', 'navigation');
        sidebar.setAttribute('aria-label', 'Wettkampf Setup Navigation');
    }

    // Keyboard-Navigation für Links
    const sidebarLinks = document.querySelectorAll('.sidebar-link');
    sidebarLinks.forEach(link => {
        link.addEventListener('keydown', function(event) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                link.click();
            }
        });
    });
}

/**
 * Utility-Funktionen
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Error Handling
 */
window.addEventListener('error', function(event) {
    if (event.filename && event.filename.includes('SidebarScript.js')) {
        console.error('Sidebar Script Error:', event.error);
    }
});