/**
 * Theme Toggle Functionality
 *
 * Handles theme switching with persistence for guests (localStorage)
 * and authenticated users (AJAX update to profile preference).
 */
document.addEventListener('DOMContentLoaded', function() {
    const root = document.documentElement;
    const storedTheme = localStorage.getItem('theme');
    const themeCycle = ['dark', 'light', 'zen'];
    const isAuthenticated = root.dataset.authenticated === 'true';
    const initialTheme = root.dataset.initialTheme || 'dark';

    let currentTheme = isAuthenticated ? initialTheme : (storedTheme ?? initialTheme);

    if (!themeCycle.includes(currentTheme)) {
        currentTheme = 'dark';
    }

    applyTheme(currentTheme, { initial: true, skipPersist: true });

    const themeToggles = document.querySelectorAll('.theme-toggle');

    themeToggles.forEach(function(toggle) {
        toggle.classList.remove('disabled');
        toggle.removeAttribute('title');
        if (typeof toggle.disabled !== 'undefined') {
            toggle.disabled = false;
        }

        toggle.addEventListener('click', function() {
            const activeTheme = root.getAttribute('data-theme') || currentTheme;
            const currentIndex = themeCycle.indexOf(activeTheme);
            const nextTheme = themeCycle[(currentIndex + 1) % themeCycle.length];

            applyTheme(nextTheme);
        });
    });

    function applyTheme(theme, options = {}) {
        const { skipPersist = false, initial = false } = options;

        if (!themeCycle.includes(theme)) {
            theme = 'dark';
        }

        root.setAttribute('data-theme', theme);
        updateThemeIcon(theme);
        currentTheme = theme;

        if (initial) {
            localStorage.setItem('theme', theme);
        }

        if (!skipPersist) {
            localStorage.setItem('theme', theme);

            if (isAuthenticated) {
                persistUserTheme(theme);
            }
        }
    }

    function persistUserTheme(theme) {
        const tokenElement = document.querySelector('meta[name="csrf-token"]');
        if (!tokenElement) {
            return;
        }

        const token = tokenElement.getAttribute('content');

        fetch('/profile/theme', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin',
            body: JSON.stringify({ theme: theme })
        }).catch(function(error) {
            console.error('Failed to persist theme preference', error);
        });
    }
});

/**
 * Update theme toggle icon based on current theme
 * @param {string} theme - The current theme ('dark', 'light', 'zen')
 */
function updateThemeIcon(theme) {
    const icons = document.querySelectorAll('.theme-toggle i');

    icons.forEach(function(icon) {
        icon.classList.remove('fa-moon', 'fa-sun', 'fa-spa');

        if (theme === 'dark') {
            icon.classList.add('fa-moon');
        } else if (theme === 'zen') {
            icon.classList.add('fa-spa');
        } else {
            icon.classList.add('fa-sun');
        }
    });
}

