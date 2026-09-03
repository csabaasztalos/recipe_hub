
document.addEventListener('DOMContentLoaded', function () {
    const addModal= document.getElementById('assignIngModal');
    const message = addModal.querySelector('.errorMessage');

    if (message && message.innerText.trim() !== "") {
        const modal = new bootstrap.Modal(addModal);
        modal.show();
    }
});

document.addEventListener('DOMContentLoaded', function () {
    const addModal= document.getElementById('assignIngModal');

    addModal.addEventListener('hidden.bs.modal', function () {
        const message = addModal.querySelector('.errorMessage');
        const search= document.getElementById('recipeSearch');
        const catId= document.getElementById('category_id');
        const recipeId= document.getElementById('recipe_id');
        const qty= document.getElementById('ingredientQTY');
        const unit= document.getElementById('ingredientUnit');

        if(message) {
            message.textContent = "";
            search.value = "";
            catId.selectedIndex = 0;
            recipeId.value = "";
            qty.value = "";
            unit.value = "";
        }

        document.body.classList.remove('modal-open');
        const backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) {
            backdrop.remove();
        }
    });
});