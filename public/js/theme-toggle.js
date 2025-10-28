/**
 * Theme Toggle Functionality
 * 
 * Handles dark/light mode theme switching with localStorage persistence.
 * The theme is stored in localStorage and persists across page loads.
 * Uses data-theme attribute on html element to trigger CSS theme changes.
 */
document.addEventListener('DOMContentLoaded', function() {
    // Get current theme from localStorage or default to dark mode
    const currentTheme = localStorage.getItem('theme') || 'dark';
    
    // Apply the theme immediately on page load
    document.documentElement.setAttribute('data-theme', currentTheme);
    
    // Update the theme icon to match the current theme
    updateThemeIcon(currentTheme);
    
    // Add event listeners to all theme toggle buttons on the page
    const themeToggles = document.querySelectorAll('.theme-toggle');
    themeToggles.forEach(function(toggle) {
        toggle.addEventListener('click', function() {
            // Get the current theme from the HTML element
            const currentTheme = document.documentElement.getAttribute('data-theme');
            
            // Toggle between dark and light mode
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            // Apply the new theme
            document.documentElement.setAttribute('data-theme', newTheme);
            
            // Persist the theme choice to localStorage
            localStorage.setItem('theme', newTheme);
            
            // Update all theme toggle icons on the page
            updateThemeIcon(newTheme);
        });
    });
});

/**
 * Update theme toggle icon based on current theme
 * @param {string} theme - The current theme ('dark' or 'light')
 */
function updateThemeIcon(theme) {
    // Find all theme toggle icons on the page
    const themeToggles = document.querySelectorAll('.theme-toggle i');
    
    themeToggles.forEach(function(icon) {
        if (theme === 'dark') {
            // Show moon icon for dark mode (indicates clicking will switch to light)
            icon.classList.remove('fa-sun');
            icon.classList.add('fa-moon');
        } else {
            // Show sun icon for light mode (indicates clicking will switch to dark)
            icon.classList.remove('fa-moon');
            icon.classList.add('fa-sun');
        }
    });
}

