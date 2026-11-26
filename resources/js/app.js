const menuButton = document.querySelector('.menu-toggle');
if (menuButton) {
    const nav = document.getElementById(menuButton.getAttribute('aria-controls'));
    const close = () => {
        nav.classList.remove('is-open');
        menuButton.setAttribute('aria-expanded', 'false');
    };
    menuButton.hidden = false;
    document.querySelector('.masthead').classList.add('menu-ready');
    menuButton.addEventListener('click', () => {
        const open = nav.classList.toggle('is-open');
        menuButton.setAttribute('aria-expanded', String(open));
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && nav.classList.contains('is-open')) {
            close();
            menuButton.focus();
        }
    });
    matchMedia('(max-width: 760px)').addEventListener('change', close);
}

if (document.querySelector('.opening') && !matchMedia('(prefers-reduced-motion: reduce)').matches) {
    import('./museum-motion');
}
