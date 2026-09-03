document.addEventListener('DOMContentLoaded', function() {
    const navLinks = document.querySelectorAll('.navbar-nav .nav-link');

    let currentPage = 'home';

    const urlParams = new URLSearchParams(window.location.search);
    const p = urlParams.get('p');

    if (p === 'recipes') currentPage = 'recipes';
    if (p === 'account') currentPage = 'account';

    navLinks.forEach(link => {
    link.classList.remove('active');
    link.removeAttribute('aria-current');

    if (link.dataset.page === currentPage) {
        link.classList.add('active');
        link.setAttribute('aria-current', 'page');
    }
});
});