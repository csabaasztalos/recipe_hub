document.addEventListener('DOMContentLoaded', function () {
    const toggleButton = document.querySelector("#sidebarToggle");
    const sidebar = document.querySelector(".sidebar");

    if (!toggleButton || !sidebar) return;

    toggleButton.addEventListener("click", () => {
        if (window.innerWidth <= 991.98) {
            sidebar.classList.toggle("open");
        }
    });
});