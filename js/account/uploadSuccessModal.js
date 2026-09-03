document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const isUploadSuccess = urlParams.get('upload_success') === '1';
    const isEditSuccess = urlParams.get('edit_success') === '1';
    
    if (isUploadSuccess || isEditSuccess) {
        const successModal = new bootstrap.Modal(document.getElementById('successModal'));
        
        // Update modal content based on the type of success
        const modalTitle = document.getElementById('successModalLabel');
        const modalBodyText = document.querySelector('#successModal .modal-body p:first-of-type');
        
        if (isEditSuccess) {
            if (modalTitle) {
                modalTitle.textContent = '✅ Recept módosítva!';
            }
            if (modalBodyText) {
                modalBodyText.textContent = 'A recepted sikeresen módosítva! 🎉';
            }
        } else {
            // Default upload success text (keep original)
            if (modalTitle) {
                modalTitle.textContent = '✅ Recept feltöltve!';
            }
            if (modalBodyText) {
                modalBodyText.textContent = 'A recepted sikeresen feltöltve! 🎉';
            }
        }
        
        successModal.show();
        
        // Auto-close countdown
        let countdownValue = 5;
        const countdownElement = document.getElementById('countdownTimer');
        
        const autoCloseTimer = setInterval(() => {
            countdownValue--;
            if (countdownElement) {
                countdownElement.textContent = countdownValue;
            }
            
            if (countdownValue <= 0) {
                clearInterval(autoCloseTimer);
                successModal.hide();
            }
        }, 1000);
        
        // Clean up URL after showing popup
        setTimeout(() => {
            window.history.replaceState({}, document.title, window.location.pathname + '?p=account');
        }, 500);
        
        // Clear timer if user manually closes modal
        document.getElementById('successModal').addEventListener('hidden.bs.modal', function () {
            clearInterval(autoCloseTimer);
        });
    }
});