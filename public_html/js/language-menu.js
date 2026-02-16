// Language menu toggle functionality
document.addEventListener('DOMContentLoaded', function () {
    const languageMenuToggle = document.querySelector('[data-language-menu-toggle]');
    const languageMenu = document.querySelector('[data-language-menu]');

    if (languageMenuToggle && languageMenu) {
        // Initially hide the menu
        languageMenu.style.display = 'none';

        // Toggle menu on button click
        languageMenuToggle.addEventListener('click', function (e) {
            e.stopPropagation();
            if (languageMenu.style.display === 'none') {
                languageMenu.style.display = 'block';
            } else {
                languageMenu.style.display = 'none';
            }
        });

        // Close menu when clicking outside
        document.addEventListener('click', function (e) {
            if (!languageMenu.contains(e.target) && !languageMenuToggle.contains(e.target)) {
                languageMenu.style.display = 'none';
            }
        });

        // Close menu when clicking on a language option
        const languageOptions = document.querySelectorAll('[data-language-option]');
        languageOptions.forEach(function (option) {
            option.addEventListener('click', function () {
                // Here you can add language switching logic
                const selectedLanguage = this.getAttribute('data-language-option');
                console.log('Selected language:', selectedLanguage);
                
                // Close menu after selection
                languageMenu.style.display = 'none';
            });
        });
    }
});

