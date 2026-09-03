
    document.addEventListener('DOMContentLoaded', function () {
    const collapse = document.querySelector('.navbar-collapse');
    const logo = document.querySelector('#mobileLogo');

    function hideLogo() {
        logo.style.visibility = 'hidden';
        logo.style.pointerEvents = 'none';
    }

    function showLogo() {
        logo.style.visibility = 'visible';
        logo.style.pointerEvents = 'auto';
    }

    collapse.addEventListener('show.bs.collapse', hideLogo);
    collapse.addEventListener('hide.bs.collapse', hideLogo);

    collapse.addEventListener('shown.bs.collapse', hideLogo);
    collapse.addEventListener('hidden.bs.collapse', showLogo);
});
