document.addEventListener('DOMContentLoaded', function () {
    const addModal= document.getElementById('addCategoryModal');
    const message = addModal.querySelector('.errorMessage');

    if (message && message.innerText.trim() !== "") {
        const modal = new bootstrap.Modal(addModal);
        modal.show();
    }
});

document.addEventListener('DOMContentLoaded', function () {
    const addModal= document.getElementById('addCategoryModal');

    addModal.addEventListener('hidden.bs.modal', function () {
        const message = addModal.querySelector('.errorMessage');
        const catName = addModal.querySelector('#categoryName');
        const userId = addModal.querySelector('#userid');
        const status = addModal.querySelector('#statuses');

        if(message) {
            message.textContent = "";
            catName.value = "";
            userId.value = 1;
            status.selectedIndex = 0;
        }
        
        document.body.classList.remove('modal-open');
        const backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) {
            backdrop.remove();
        }
    });
});