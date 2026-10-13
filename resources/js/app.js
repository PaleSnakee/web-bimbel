import "./bootstrap";

document.addEventListener('DOMContentLoaded', () => {
    const profileButton = document.querySelector('[data-profile-toggle]');
    const profileMenu = document.querySelector('[data-profile-menu]');
    document.querySelector('[data-sidebar-toggle]')?.addEventListener('click', () => document.body.classList.toggle('sidebar-open'));
    document.querySelector('[data-sidebar-backdrop]')?.addEventListener('click', () => document.body.classList.remove('sidebar-open'));
    document.querySelectorAll('[data-menu-toggle]').forEach((button) => button.addEventListener('click', () => { const open = button.getAttribute('aria-expanded') === 'true'; button.setAttribute('aria-expanded', String(!open)); button.nextElementSibling.hidden = open; }));
    profileButton?.addEventListener('click', (event) => { event.stopPropagation(); const open = profileButton.getAttribute('aria-expanded') === 'true'; profileButton.setAttribute('aria-expanded', String(!open)); profileMenu.hidden = open; });
    document.addEventListener('click', () => { if (profileMenu) { profileMenu.hidden = true; profileButton?.setAttribute('aria-expanded', 'false'); } });
});
