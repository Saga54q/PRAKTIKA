/**
 * Общие скрипты интерфейса: мобильная навигация и управление состоянием меню.
 */
(function () {
    'use strict';

    const toggleBtn = document.querySelector('[data-nav-toggle]');
    const nav = document.getElementById('primary-navigation');

    if (!toggleBtn || !nav) {
        return;
    }

    function setMenuOpen(isOpen) {
        nav.classList.toggle('is-open', isOpen);
        toggleBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        toggleBtn.setAttribute('aria-label', isOpen ? 'Закрыть меню' : 'Открыть меню');
        document.body.style.overflow = isOpen ? 'hidden' : '';
    }

    toggleBtn.addEventListener('click', function () {
        const currentlyOpen = nav.classList.contains('is-open');
        setMenuOpen(!currentlyOpen);
    });

    nav.addEventListener('click', function (event) {
        const link = event.target.closest('a');
        if (link && window.innerWidth <= 768) {
            setMenuOpen(false);
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && nav.classList.contains('is-open')) {
            setMenuOpen(false);
            toggleBtn.focus();
        }
    });

    window.addEventListener('resize', function () {
        if (window.innerWidth > 768 && nav.classList.contains('is-open')) {
            setMenuOpen(false);
        }
    });
})();
