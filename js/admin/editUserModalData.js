document.addEventListener('DOMContentLoaded', function () {
    const editUserModal = document.getElementById('editUserModal');

    editUserModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        let userId, userName, userEmail, userPermission, userStatus;

        if (button) {   
            userId = button.getAttribute('data-user-id');
            userName = button.getAttribute('data-user-name');
            userEmail = button.getAttribute('data-user-email');
            userPermission = button.getAttribute('data-user-permission');
            userStatus = button.getAttribute('data-user-status');

        } else {
            const error = editUserModal.querySelector('#errors');
            userId = error.getAttribute('data-user-id');
            userName = error.getAttribute('data-user-name');
            userEmail = error.getAttribute('data-user-email');
            userPermission = error.getAttribute('data-user-permission');
            userStatus = error.getAttribute('data-user-status');
        }

        editUserModal.querySelector('#userId').value = userId;
        editUserModal.querySelector('#userName').value = userName;
        editUserModal.querySelector('#userEmail').value = userEmail;
        editUserModal.querySelector('#permission').value = userPermission;
        editUserModal.querySelector('#status').value = String(userStatus).trim();
    });
});

document.addEventListener('DOMContentLoaded', function () {
    const editModal= document.getElementById('editUserModal');
    const message = editModal.querySelector('.errorMessage');

    if (message && message.innerText.trim() !== "") {
        const modal = new bootstrap.Modal(editModal);
        modal.show();
    }
});

document.addEventListener('DOMContentLoaded', function () {
    const editModal= document.getElementById('editUserModal');

    editModal.addEventListener('hidden.bs.modal', function () {
        const message = editModal.querySelector('.errorMessage');
        if(message) {
            message.textContent = "";
        }
    });
});