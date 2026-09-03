document.addEventListener('DOMContentLoaded', function () {
    const assignCategoryModal = document.getElementById('assignCategoryModal');
    if (!assignCategoryModal) return;

    const recipeList = assignCategoryModal.querySelector('.recipe-dropdown-list');
    const itemSelect = assignCategoryModal.querySelector('.item-select');
    const recipeValueInput = assignCategoryModal.querySelector('.recipe-value');

    if (!recipeList || !itemSelect || !recipeValueInput) return;

    function saveOptions() {
        if (!itemSelect._allOptions) {
            itemSelect._allOptions = Array.from(itemSelect.options).map(option => ({ value: option.value, text: option.text }));
        }
    }

    function filterUnassignedOptions(assignedItemsAttr) {
        const assignedSet = new Set(assignedItemsAttr.split(',').map(id => id.trim()).filter(Boolean));
        saveOptions();

        // Restore all options
        itemSelect.innerHTML = '';
        itemSelect._allOptions.forEach(opt => {
            const option = document.createElement('option');
            option.value = opt.value;
            option.text = opt.text;
            itemSelect.appendChild(option);
        });

        // Hide already assigned
        Array.from(itemSelect.options).forEach(option => {
            if (assignedSet.has(option.value)) {
                option.style.display = 'none';
            } else {
                option.style.display = '';
            }
        });

        if (itemSelect.options.length > 0) {
            const firstVisible = Array.from(itemSelect.options).find(opt => opt.style.display !== 'none');
            if (firstVisible) itemSelect.value = firstVisible.value;
        }
    }

    recipeList.addEventListener('click', function (event) {
        const li = event.target.closest('li');
        if (!li?.dataset?.value) return;

        const recipeId = li.dataset.value;
        const assignedItemsAttr = li.dataset.categories || li.dataset.ingredients || li.dataset.assigned || '';

        recipeValueInput.value = recipeId;
        filterUnassignedOptions(assignedItemsAttr);
    });

    assignCategoryModal.addEventListener('show.bs.modal', function () {
        // Reset all options to visible when modal opens
        if (itemSelect._allOptions) {
            itemSelect.innerHTML = '';
            itemSelect._allOptions.forEach(opt => {
                const option = document.createElement('option');
                option.value = opt.value;
                option.text = opt.text;
                itemSelect.appendChild(option);
            });
        }
    });

    // Handle error message display and clearing
    const errorMessage = assignCategoryModal.querySelector('.errorMessage');
    if (errorMessage && errorMessage.innerText.trim() !== '') {
        const modal = new bootstrap.Modal(assignCategoryModal);
        modal.show();
    }

    assignCategoryModal.addEventListener('hidden.bs.modal', function () {
        if (errorMessage) {
            errorMessage.textContent = '';
        }
        // Clear recipe selection
        recipeValueInput.value = '';
         document.body.classList.remove('modal-open');
        const backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) {
            backdrop.remove();
        }
    });
});