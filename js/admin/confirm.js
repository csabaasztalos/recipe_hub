document.addEventListener('DOMContentLoaded', function() {
    const confirmButtons = document.querySelectorAll('.confirm-btn');
    
    confirmButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            const confirmMessage = this.dataset.confirm.replace(/\\n/g, '\n');
            if (confirmMessage) {
                if (!confirm(confirmMessage)) {
                    e.preventDefault();
                    return;
                }
            }
        });
    });
});