/**
 * Hamburger-Menü System
 */

if (!window.navbarInitialized) {
    window.navbarInitialized = true;

    document.addEventListener('DOMContentLoaded', function() {
        initHamburgerMenu();
        initDropdowns();
        initActiveState();
    });
}

/**
 * Hamburger Menü Funktionalität
 */
function initHamburgerMenu() {
    const hamburger = document.querySelector('.hamburger');
    const navMenu = document.querySelector('.nav-menu');

    if (!hamburger || !navMenu) {
        return;
    }

    hamburger.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();

        const isActive = navMenu.classList.contains('mobile-active');

        if (isActive) {
            closeMenu();
        } else {
            openMenu();
        }
    });

    document.addEventListener('click', function(e) {
        if (!hamburger.contains(e.target) &&
            !navMenu.contains(e.target) &&
            navMenu.classList.contains('mobile-active')) {
            closeMenu();
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && navMenu.classList.contains('mobile-active')) {
            closeMenu();
        }
    });

    window.addEventListener('resize', function() {
        if (window.innerWidth > 1600 && navMenu.classList.contains('mobile-active')) {
            closeMenu();
        }
    });
}

function openMenu() {
    const hamburger = document.querySelector('.hamburger');
    const navMenu = document.querySelector('.nav-menu');

    hamburger.classList.add('active');
    navMenu.classList.add('mobile-active');
    document.body.style.overflow = 'hidden';
}

function closeMenu() {
    const hamburger = document.querySelector('.hamburger');
    const navMenu = document.querySelector('.nav-menu');
    const dropdowns = document.querySelectorAll('.dropdown');

    hamburger.classList.remove('active');
    navMenu.classList.remove('mobile-active');

    dropdowns.forEach(dropdown => {
        dropdown.classList.remove('mobile-active');
    });

    document.body.style.overflow = '';
}

/**
 * Dropdown Funktionalität nur per Klick
 */
function initDropdowns() {
    const dropdowns = document.querySelectorAll('.dropdown');

    dropdowns.forEach(dropdown => {
        const dropdownLink = dropdown.querySelector('.nav-link');

        if (!dropdownLink) return;

        dropdownLink.addEventListener('click', function(e) {
            e.preventDefault();

            const isActive = dropdown.classList.contains('mobile-active');

            // Andere Dropdowns schließen
            dropdowns.forEach(otherDropdown => {
                if (otherDropdown !== dropdown) {
                    otherDropdown.classList.remove('mobile-active');
                }
            });

            // Aktuelles Dropdown toggle
            if (isActive) {
                dropdown.classList.remove('mobile-active');
            } else {
                dropdown.classList.add('mobile-active');
            }
        });
    });
}

function initActiveState() {
    const currentPath = window.location.pathname;
    const navLinks = document.querySelectorAll('.nav-menu a');

    navLinks.forEach(link => {
        try {
            const linkPath = new URL(link.href, window.location.origin).pathname;

            if (currentPath === linkPath) {
                link.classList.add('active');

                const parentDropdown = link.closest('.dropdown');
                if (parentDropdown) {
                    const dropdownLink = parentDropdown.querySelector('.nav-link');
                    if (dropdownLink) {
                        dropdownLink.classList.add('active-parent');
                    }
                }
            }
        } catch (error) {
            // Ignorieren
        }
    });
}

window.HamburgerMenu = {
    open: openMenu,
    close: closeMenu,

    isOpen: function() {
        const navMenu = document.querySelector('.nav-menu');
        return navMenu ? navMenu.classList.contains('mobile-active') : false;
    },

    toggle: function() {
        const navMenu = document.querySelector('.nav-menu');
        if (navMenu && navMenu.classList.contains('mobile-active')) {
            closeMenu();
        } else {
            openMenu();
        }
    }
};