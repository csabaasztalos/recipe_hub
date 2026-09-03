document.addEventListener('DOMContentLoaded', function() {
    const links = document.querySelectorAll('.sidebar a');
    const currentPath = window.location.pathname + window.location.search;
    links.forEach(link => {
        const linkPath = link.pathname + link.search;
        if (currentPath === linkPath) {
            link.classList.add('active');
        }
    });
});
