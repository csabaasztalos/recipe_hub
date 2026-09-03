
document.addEventListener('DOMContentLoaded', function () {
    const addModal= document.getElementById('removeCategoryModal');
    const message = addModal.querySelector('.errorMessage');

    if (message && message.innerText.trim() !== "") {
        const modal = new bootstrap.Modal(addModal);
        modal.show();
    }
});

document.addEventListener('DOMContentLoaded', function () {
    const addModal= document.getElementById('removeCategoryModal');

    addModal.addEventListener('hidden.bs.modal', function () {
        const message = addModal.querySelector('.errorMessage');
        if(message) {
            message.textContent = "";
        }
        
        document.body.classList.remove('modal-open');
        const backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) {
            backdrop.remove();
        }
    });
});