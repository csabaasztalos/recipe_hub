// mobileSideMenuToggle.js
// Handles collapsible sidebar menu on mobile devices

function toggleMobileMenu() {
    const menu = document.getElementById('accountSideMenu');
    const toggle = document.querySelector('.mobile-menu-toggle');
    
    if (menu && toggle) {
        menu.classList.toggle('show');
        toggle.classList.toggle('active');
        
        // Update button text and icon
        const isOpen = menu.classList.contains('show');
        const chevron = toggle.querySelector('.bi-chevron-down, .bi-chevron-up');
        
        if (chevron) {
            if (isOpen) {
                chevron.classList.remove('bi-chevron-down');
                chevron.classList.add('bi-chevron-up');
            } else {
                chevron.classList.remove('bi-chevron-up');
                chevron.classList.add('bi-chevron-down');
            }
        }
    }
}

// Close mobile menu when clicking outside
document.addEventListener('click', function(event) {
    const menu = document.getElementById('accountSideMenu');
    const toggle = document.querySelector('.mobile-menu-toggle');
    const sideMenuContainer = document.querySelector('.sideMenu');
    
    // Check if click is outside the sidebar
    if (menu && toggle && sideMenuContainer && 
        !sideMenuContainer.contains(event.target) && 
        menu.classList.contains('show')) {
        
        menu.classList.remove('show');
        toggle.classList.remove('active');
        
        const chevron = toggle.querySelector('.bi-chevron-up');
        if (chevron) {
            chevron.classList.remove('bi-chevron-up');
            chevron.classList.add('bi-chevron-down');
        }
    }
});

// Close mobile menu when window is resized to desktop
window.addEventListener('resize', function() {
    if (window.innerWidth > 991.98) {
        const menu = document.getElementById('accountSideMenu');
        const toggle = document.querySelector('.mobile-menu-toggle');
        
        if (menu && toggle) {
            menu.classList.remove('show');
            toggle.classList.remove('active');
            
            const chevron = toggle.querySelector('.bi-chevron-up');
            if (chevron) {
                chevron.classList.remove('bi-chevron-up');
                chevron.classList.add('bi-chevron-down');
            }
        }
    }
});