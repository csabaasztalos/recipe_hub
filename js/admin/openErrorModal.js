document.addEventListener('DOMContentLoaded', function () {
    const addModal= document.getElementById('errorModal');
    const message = addModal.querySelector('.errorMessage');

    if (message && message.innerText.trim() !== "") {
        const modal = new bootstrap.Modal(addModal);
        modal.show();
    }
});

document.addEventListener('DOMContentLoaded', function () {
    const addModal= document.getElementById('errorModal');

    addModal.addEventListener('hidden.bs.modal', function () {
        const message = addModal.querySelector('.errorMessage');
        if(message) {
            message.textContent = "";
        }
    });
});