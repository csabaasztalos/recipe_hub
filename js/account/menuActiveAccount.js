// menuActiveAccount.js
// Highlights the active menu item in the account sidebar

document.addEventListener('DOMContentLoaded', function() {
    const items = document.querySelectorAll('.sideMenuItem');
    const currentPath = window.location.pathname + window.location.search;
    items.forEach(item => {
        const a = item.querySelector('a');
        if (!a) return;
        const linkPath = a.pathname + a.search;
        if (currentPath === linkPath) {
            item.classList.add('active');
        }
    });
});
