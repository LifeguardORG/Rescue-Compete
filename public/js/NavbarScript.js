/**
 * NavbarScript.js
 * JavaScript-Funktionalität für die erweiterte Navbar mit Icons
 *
 * Features:
 * - Mobile Hamburger-Menü mit Animation
 * - Touch-optimierte Dropdown-Navigation
 * - Vollständige Keyboard-Navigation (Accessibility)
 * - ARIA-Compliance für Screen-Reader
 * - Performance-optimierte Event-Handler
 * - Icon-Fallback-System
 * - Responsive Behavior
 */

document.addEventListener('DOMContentLoaded', function() {
    initializeNavbar();
});

/**
 * Initialisiert die komplette Navbar-Funktionalität
 */
function initializeNavbar() {
    initializeMobileMenu();
    initializeDropdowns();
    initializeAccessibility();
    initializeActiveState();
    initializeIconFallback();
    setupPerformanceOptimizations();
}

/**
 * Mobile Hamburger-Menü Funktionalität
 */
function initializeMobileMenu() {
    const hamburger = document.querySelector('.hamburger');
    const navMenu = document.querySelector('.nav-menu');
    const body = document.body;

    if (!hamburger || !navMenu) {
        return;
    }

    // Hamburger Toggle
    hamburger.addEventListener('click', function(event) {
        event.preventDefault();
        toggleMobileMenu();
    });

    // Menü schließen bei Klick außerhalb
    document.addEventListener('click', function(event) {
        if (!hamburger.contains(event.target) &&
            !navMenu.contains(event.target) &&
            navMenu.classList.contains('active')) {
            closeNavigation();
        }
    });

    // Menü schließen bei Escape-Taste
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && navMenu.classList.contains('active')) {
            closeNavigation();
            hamburger.focus(); // Focus zurück zum Hamburger
        }
    });

    // Touch-Events für bessere Mobile-Erfahrung
    let touchStartY = 0;
    navMenu.addEventListener('touchstart', function(event) {
        touchStartY = event.touches[0].clientY;
    });

    navMenu.addEventListener('touchend', function(event) {
        const touchEndY = event.changedTouches[0].clientY;
        const swipeDistance = touchStartY - touchEndY;

        // Swipe up to close menu
        if (swipeDistance > 50) {
            closeNavigation();
        }
    });
}

/**
 * Toggle Mobile Menu mit Animation
 */
function toggleMobileMenu() {
    const hamburger = document.querySelector('.hamburger');
    const navMenu = document.querySelector('.nav-menu');
    const body = document.body;

    if (!hamburger || !navMenu) return;

    const isActive = navMenu.classList.contains('active');

    if (isActive) {
        closeNavigation();
    } else {
        openNavigation();
    }
}

/**
 * Öffnet die Mobile-Navigation
 */
function openNavigation() {
    const hamburger = document.querySelector('.hamburger');
    const navMenu = document.querySelector('.nav-menu');
    const body = document.body;

    hamburger.classList.add('active');
    navMenu.classList.add('active');
    body.classList.add('nav-open');

    // ARIA-Attribute aktualisieren
    hamburger.setAttribute('aria-expanded', 'true');
    navMenu.setAttribute('aria-hidden', 'false');

    // Focus management
    const firstFocusableElement = navMenu.querySelector('a, button');
    if (firstFocusableElement) {
        setTimeout(() => firstFocusableElement.focus(), 100);
    }
}

/**
 * Schließt die Mobile-Navigation
 */
function closeNavigation() {
    const hamburger = document.querySelector('.hamburger');
    const navMenu = document.querySelector('.nav-menu');
    const body = document.body;
    const dropdowns = document.querySelectorAll('.dropdown');

    if (!hamburger || !navMenu) return;

    hamburger.classList.remove('active');
    navMenu.classList.remove('active');
    body.classList.remove('nav-open');

    // ARIA-Attribute aktualisieren
    hamburger.setAttribute('aria-expanded', 'false');
    navMenu.setAttribute('aria-hidden', 'true');

    // Alle Dropdowns schließen
    dropdowns.forEach(dropdown => {
        dropdown.classList.remove('active');
        const button = dropdown.querySelector('.nav-link');
        if (button) {
            button.setAttribute('aria-expanded', 'false');
        }
    });
}

/**
 * Dropdown-Funktionalität für Mobile und Desktop
 */
function initializeDropdowns() {
    const dropdownItems = document.querySelectorAll('.dropdown');

    dropdownItems.forEach((dropdown, index) => {
        const dropdownLink = dropdown.querySelector('.nav-link');
        const dropdownMenu = dropdown.querySelector('.dropdown-menu');

        if (!dropdownLink || !dropdownMenu) return;

        // Unique IDs für ARIA
        const menuId = `dropdown-menu-${index}`;
        dropdownMenu.setAttribute('id', menuId);
        dropdownLink.setAttribute('aria-controls', menuId);
        dropdownLink.setAttribute('aria-haspopup', 'true');
        dropdownLink.setAttribute('aria-expanded', 'false');

        // Mobile Dropdown Toggle
        dropdownLink.addEventListener('click', function(event) {
            if (window.innerWidth <= 992) {
                event.preventDefault();
                toggleDropdown(dropdown);
            }
        });

        // Desktop Hover-Verhalten (CSS + JavaScript für ARIA)
        dropdown.addEventListener('mouseenter', function() {
            if (window.innerWidth > 992) {
                dropdownLink.setAttribute('aria-expanded', 'true');
            }
        });

        dropdown.addEventListener('mouseleave', function() {
            if (window.innerWidth > 992) {
                dropdownLink.setAttribute('aria-expanded', 'false');
            }
        });

        // Touch-Events für Mobile
        dropdown.addEventListener('touchstart', function(event) {
            if (window.innerWidth <= 992) {
                event.stopPropagation();
            }
        });
    });
}

/**
 * Toggle Dropdown im Mobile-Modus
 */
function toggleDropdown(dropdown) {
    const dropdownLink = dropdown.querySelector('.nav-link');
    const isActive = dropdown.classList.contains('active');

    // Andere Dropdowns schließen
    document.querySelectorAll('.dropdown').forEach(otherDropdown => {
        if (otherDropdown !== dropdown) {
            otherDropdown.classList.remove('active');
            const otherButton = otherDropdown.querySelector('.nav-link');
            if (otherButton) {
                otherButton.setAttribute('aria-expanded', 'false');
            }
        }
    });

    // Aktuelles Dropdown togglen
    dropdown.classList.toggle('active');
    dropdownLink.setAttribute('aria-expanded', (!isActive).toString());

    // Focus management
    if (!isActive) {
        const firstMenuItem = dropdown.querySelector('.dropdown-menu a');
        if (firstMenuItem) {
            setTimeout(() => firstMenuItem.focus(), 100);
        }
    }
}

/**
 * Barrierefreiheit und Keyboard-Navigation
 */
function initializeAccessibility() {
    const navLinks = document.querySelectorAll('.nav-link');
    const dropdownMenus = document.querySelectorAll('.dropdown-menu');

    // Keyboard Navigation für Hauptlinks
    navLinks.forEach(link => {
        link.addEventListener('keydown', function(event) {
            switch(event.key) {
                case 'Enter':
                case ' ':
                    if (window.innerWidth <= 992 && this.closest('.dropdown')) {
                        event.preventDefault();
                        const dropdown = this.closest('.dropdown');
                        toggleDropdown(dropdown);
                    }
                    break;
                case 'ArrowDown':
                    if (this.closest('.dropdown')) {
                        event.preventDefault();
                        const dropdownMenu = this.closest('.dropdown').querySelector('.dropdown-menu');
                        const firstMenuItem = dropdownMenu?.querySelector('a');
                        if (firstMenuItem) {
                            firstMenuItem.focus();
                        }
                    }
                    break;
                case 'Escape':
                    if (window.innerWidth <= 992) {
                        closeNavigation();
                        document.querySelector('.hamburger')?.focus();
                    }
                    break;
            }
        });
    });

    // Keyboard Navigation für Dropdown-Menüs
    dropdownMenus.forEach(menu => {
        const menuItems = menu.querySelectorAll('a');

        menuItems.forEach((item, index) => {
            item.addEventListener('keydown', function(event) {
                switch(event.key) {
                    case 'ArrowDown':
                        event.preventDefault();
                        const nextIndex = (index + 1) % menuItems.length;
                        menuItems[nextIndex].focus();
                        break;
                    case 'ArrowUp':
                        event.preventDefault();
                        const prevIndex = (index - 1 + menuItems.length) % menuItems.length;
                        menuItems[prevIndex].focus();
                        break;
                    case 'Escape':
                        event.preventDefault();
                        const dropdown = this.closest('.dropdown');
                        const dropdownLink = dropdown?.querySelector('.nav-link');
                        if (dropdown && dropdownLink) {
                            dropdown.classList.remove('active');
                            dropdownLink.setAttribute('aria-expanded', 'false');
                            dropdownLink.focus();
                        }
                        break;
                    case 'Tab':
                        // Let Tab navigation work naturally
                        break;
                    case 'Home':
                        event.preventDefault();
                        menuItems[0].focus();
                        break;
                    case 'End':
                        event.preventDefault();
                        menuItems[menuItems.length - 1].focus();
                        break;
                }
            });
        });
    });

    // Hamburger Keyboard Support
    const hamburger = document.querySelector('.hamburger');
    if (hamburger) {
        hamburger.setAttribute('role', 'button');
        hamburger.setAttribute('aria-label', 'Navigation öffnen/schließen');
        hamburger.setAttribute('aria-expanded', 'false');
        hamburger.setAttribute('tabindex', '0');

        hamburger.addEventListener('keydown', function(event) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                toggleMobileMenu();
            }
        });
    }
}

/**
 * Aktiven Menüpunkt hervorheben basierend auf URL
 */
function initializeActiveState() {
    const currentPath = window.location.pathname;
    const navLinks = document.querySelectorAll('.nav-menu a');

    navLinks.forEach(link => {
        try {
            const linkPath = new URL(link.href, window.location.origin).pathname;

            if (currentPath === linkPath) {
                link.classList.add('active');

                // Parent dropdown auch als aktiv markieren
                const parentDropdown = link.closest('.dropdown');
                if (parentDropdown) {
                    const dropdownLink = parentDropdown.querySelector('.nav-link');
                    if (dropdownLink) {
                        dropdownLink.classList.add('active-parent');
                    }
                }
            }
        } catch (error) {
            // Fehler beim Verarbeiten des Links ignorieren
        }
    });
}

/**
 * Icon-Loading-Fallback System
 * Falls Icons nicht geladen werden können, Text-Platzhalter anzeigen
 */
function initializeIconFallback() {
    const icons = document.querySelectorAll('.nav-icon');

    icons.forEach(icon => {
        // Check if icon is loaded
        if (icon.complete && icon.naturalHeight === 0) {
            createIconFallback(icon);
        }

        icon.addEventListener('error', function() {
            createIconFallback(this);
        });

        icon.addEventListener('load', function() {
            // Icon loaded successfully
        });
    });
}

/**
 * Erstellt Text-Fallback für fehlgeschlagene Icon-Loads
 */
function createIconFallback(iconElement) {
    const altText = iconElement.alt || 'Icon';
    const fallback = document.createElement('span');

    fallback.className = 'nav-icon-fallback';
    fallback.textContent = altText.charAt(0).toUpperCase();
    fallback.title = altText;
    fallback.setAttribute('aria-label', altText);

    if (iconElement.parentNode) {
        iconElement.parentNode.replaceChild(fallback, iconElement);
    }
}

/**
 * Performance-Optimierungen
 */
function setupPerformanceOptimizations() {
    // Debounced Resize Handler
    const debouncedResize = debounce(handleResize, 250);
    window.addEventListener('resize', debouncedResize);

    // Intersection Observer für bessere Performance
    if ('IntersectionObserver' in window) {
        const navObserver = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.target.classList.contains('navbar')) {
                    // Navbar visibility logic if needed
                }
            });
        });

        const navbar = document.querySelector('.navbar');
        if (navbar) {
            navObserver.observe(navbar);
        }
    }

    // Prefetch wichtiger Seiten bei Hover
    const importantLinks = document.querySelectorAll('a[href*="CompleteResultsView"], a[href*="FormCollectionView"]');
    importantLinks.forEach(link => {
        link.addEventListener('mouseenter', function() {
            prefetchPage(this.href);
        }, { once: true });
    });
}

/**
 * Responsive Verhalten bei Bildschirmgrößenänderung
 */
function handleResize() {
    const navMenu = document.querySelector('.nav-menu');
    const hamburger = document.querySelector('.hamburger');
    const dropdowns = document.querySelectorAll('.dropdown');

    // Bei Desktop-Ansicht alle Mobile-States zurücksetzen
    if (window.innerWidth > 992) {
        if (navMenu && navMenu.classList.contains('active')) {
            closeNavigation();
        }

        // Dropdowns für Desktop vorbereiten
        dropdowns.forEach(dropdown => {
            dropdown.classList.remove('active');
            const button = dropdown.querySelector('.nav-link');
            if (button) {
                button.setAttribute('aria-expanded', 'false');
            }
        });

        document.body.classList.remove('nav-open');

        if (hamburger) {
            hamburger.setAttribute('aria-expanded', 'false');
        }
    }
}

/**
 * Debounce-Funktion für Performance-Optimierung
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
 * Prefetch-Funktion für bessere Performance
 */
function prefetchPage(url) {
    if (!url || url === '#') return;

    try {
        const link = document.createElement('link');
        link.rel = 'prefetch';
        link.href = url;
        document.head.appendChild(link);
    } catch (error) {
        // Prefetch fehlgeschlagen, ignorieren
    }
}

/**
 * Error Handling
 */
window.addEventListener('error', function(event) {
    if (event.filename && event.filename.includes('NavbarScript.js')) {
        // Error-Handling für Navbar-spezifische Fehler
    }
});

/**
 * Public API für externe Nutzung
 */
window.NavbarAPI = {
    closeNavigation: closeNavigation,
    openNavigation: openNavigation,
    toggleMobileMenu: toggleMobileMenu,

    // Utility functions
    isMenuOpen: function() {
        const navMenu = document.querySelector('.nav-menu');
        return navMenu ? navMenu.classList.contains('active') : false;
    },

    isMobileView: function() {
        return window.innerWidth <= 992;
    }
};