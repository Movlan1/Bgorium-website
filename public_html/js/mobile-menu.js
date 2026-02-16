// Mobile menu toggle functionality
document.addEventListener('DOMContentLoaded', function () {
    const burgerButton = document.querySelector('.u-burger-button, .sc-header__burger-button');
    const header = document.querySelector('.sc-header');
    const menuWrapper = document.querySelector('.sc-header__menu-main-wrapper');
    const overlay = document.querySelector('.sc-header__overlay');
    
    if (burgerButton && header) {
        // Toggle menu on burger button click
        burgerButton.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            
            const isOpen = header.hasAttribute('data-sc-header-mobile-menu-open');
            
            if (isOpen) {
                header.removeAttribute('data-sc-header-mobile-menu-open');
                burgerButton.classList.remove('u-burger-button_state_active');
                burgerButton.setAttribute('aria-expanded', 'false');
                document.body.style.overflow = '';
            } else {
                header.setAttribute('data-sc-header-mobile-menu-open', '');
                burgerButton.classList.add('u-burger-button_state_active');
                burgerButton.setAttribute('aria-expanded', 'true');
                document.body.style.overflow = 'hidden';
            }
        });
        
        // Close menu when clicking on overlay
        if (overlay) {
            overlay.addEventListener('click', function () {
                header.removeAttribute('data-sc-header-mobile-menu-open');
                burgerButton.classList.remove('u-burger-button_state_active');
                burgerButton.setAttribute('aria-expanded', 'false');
                document.body.style.overflow = '';
            });
        }
        
        // Close menu when clicking outside
        document.addEventListener('click', function (e) {
            if (header.hasAttribute('data-sc-header-mobile-menu-open')) {
                if (!header.contains(e.target) && !burgerButton.contains(e.target)) {
                    header.removeAttribute('data-sc-header-mobile-menu-open');
                    burgerButton.classList.remove('u-burger-button_state_active');
                    burgerButton.setAttribute('aria-expanded', 'false');
                    document.body.style.overflow = '';
                }
            }
        });
        
        // Close menu on escape key
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && header.hasAttribute('data-sc-header-mobile-menu-open')) {
                header.removeAttribute('data-sc-header-mobile-menu-open');
                burgerButton.classList.remove('u-burger-button_state_active');
                burgerButton.setAttribute('aria-expanded', 'false');
                document.body.style.overflow = '';
            }
        });
        
        // Close menu when window is resized to desktop size
        window.addEventListener('resize', function () {
            if (window.innerWidth >= 1024 && header.hasAttribute('data-sc-header-mobile-menu-open')) {
                header.removeAttribute('data-sc-header-mobile-menu-open');
                burgerButton.classList.remove('u-burger-button_state_active');
                burgerButton.setAttribute('aria-expanded', 'false');
                document.body.style.overflow = '';
            }
        });
    }
});

