document.addEventListener('DOMContentLoaded', function () {
    document.body.addEventListener('click', function (e) {
        if (e.target.matches('.edit-btn')) {
            const row = e.target.closest('tr');
            const name = row.querySelector('.catName').textContent.trim();
            const id = row.querySelector('.catId').textContent.replace('#', '').trim();
            let rawStatus = '';
            const statusCell = row.querySelector('.catStatus');

            if (statusCell) {
                const badge = statusCell.querySelector('.badge');
                if (badge && badge.dataset.statusRaw) {
                    rawStatus = badge.dataset.statusRaw.trim();
                } else if (badge) {
                    rawStatus = badge.textContent.trim();
                }
            }

            document.getElementById('editCategoryId').value = id;
            document.getElementById('editCategoryName').value = name;

            const statusSelect = document.getElementById('editStatuses');
            if (statusSelect && rawStatus) {
                statusSelect.value = rawStatus;
            }
        }
    });

    if (document.getElementById('editCategoryModalFlag')) {
        const editModal = new bootstrap.Modal(document.getElementById('editCategoryModal'));
        editModal.show();
    }
    // Add Category Modal
    if (document.getElementById('newCategoryModalFlag')) {
        const addModal = new bootstrap.Modal(document.getElementById('addCategoryModal'));
        addModal.show();
    }
    // Assign Category Modal
    if (document.getElementById('recipeCategoryModalFlag')) {
        const assignModal = new bootstrap.Modal(document.getElementById('assignCategoryModal'));
        assignModal.show();
    }
    // Remove Category Modal
    if (document.getElementById('removeRecipeCategoryModalFlag')) {
        const removeModal = new bootstrap.Modal(document.getElementById('removeCategoryModal'));
        removeModal.show();
    }
    // API Import Modal
    if (document.getElementById('showImportModalFlag')) {
        const importModal = new bootstrap.Modal(document.getElementById('importAPIModal'));
        importModal.show();
    }
});

document.addEventListener('DOMContentLoaded', function () {
    const addModal= document.getElementById('editCategoryModal');
    const message = addModal.querySelector('.errorMessage');

    if (message && message.innerText.trim() !== "") {
        const modal = new bootstrap.Modal(addModal);
        modal.show();
    }
});

document.addEventListener('DOMContentLoaded', function () {
    const addModal= document.getElementById('editCategoryModal');

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