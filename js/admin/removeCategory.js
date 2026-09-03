
document.addEventListener('DOMContentLoaded', function () {
    const removeCategoryModal = document.getElementById('removeCategoryModal');
    if (!removeCategoryModal) return;

    const recipeList = removeCategoryModal.querySelector('.recipe-dropdown-list');
    const itemSelect = removeCategoryModal.querySelector('.item-select');
    const recipeValueInput = removeCategoryModal.querySelector('.recipe-value');
    const errorMessage = removeCategoryModal.querySelector('.errorMessage');

    if (!recipeList || !itemSelect || !recipeValueInput) return;

    function saveOptions() {
        if (!itemSelect._allOptions) {
            itemSelect._allOptions = Array.from(itemSelect.options).map(option => ({ value: option.value, text: option.text }));
        }
    }

    function filterAssignedOptions(assignedItemsAttr) {
        const assignedSet = new Set(assignedItemsAttr.split(',').map(id => id.trim()).filter(Boolean));
        saveOptions();

        itemSelect.innerHTML = '';
        itemSelect._allOptions.forEach(opt => {
            if (assignedSet.has(opt.value)) {
                const option = document.createElement('option');
                option.value = opt.value;
                option.text = opt.text;
                itemSelect.appendChild(option);
            }
        });

        if (itemSelect.options.length > 0) {
            itemSelect.value = itemSelect.options[0].value;
        }
    }

    recipeList.addEventListener('click', function (event) {
        const li = event.target.closest('li');
        if (!li?.dataset?.value) return;

        const recipeId = li.dataset.value;
        const assignedItemsAttr = li.dataset.categories || li.dataset.ingredients || li.dataset.assigned || '';

        recipeValueInput.value = recipeId;
        filterAssignedOptions(assignedItemsAttr);
    });

    removeCategoryModal.addEventListener('show.bs.modal', function () {
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

    if (errorMessage && errorMessage.innerText.trim() !== '') {
        const modal = new bootstrap.Modal(removeCategoryModal);
        modal.show();
    }

    removeCategoryModal.addEventListener('hidden.bs.modal', function () {
        if (errorMessage) {
            errorMessage.textContent = '';
        }
        // Clear recipe selection
        recipeValueInput.value = '';
    });
});