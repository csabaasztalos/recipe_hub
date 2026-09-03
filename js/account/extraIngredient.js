document.addEventListener('DOMContentLoaded', () => {
  const addIngredientButton = document.getElementById('addIngredient');
  const ingredientsList = document.getElementById('ingredientsList');
  let ingredientCounter = ingredientsList.children.length;

  addIngredientButton.addEventListener('click', () => {
    const firstSelect = document.getElementById('ingredientName0');
    const optionsHTML = firstSelect.innerHTML;

    const newIngredients = document.createElement('div');
    newIngredients.className = 'ingredients ingredientBlock';
    newIngredients.id = `ingredients${ingredientCounter}`;

    newIngredients.innerHTML = `
      <button type="button" class="ingredientDeleteBtn" title="Törlés">&times;</button>
      <label for="ingredientName${ingredientCounter}"><b>Hozzávaló neve</b></label>
      <select id="ingredientName${ingredientCounter}" name="ingredients[${ingredientCounter}][id]" class="form-select">
        ${optionsHTML}
      </select>
      <div class="ingredientNumbers">
        <div class="ingredientField">
          <label for="ingredientQuantity${ingredientCounter}"><b>Mennyisége:</b></label>
          <input id="ingredientQuantity${ingredientCounter}" name="ingredients[${ingredientCounter}][quantity]" class="form-control" required type="number" min="1">
        </div>
        <div class="ingredientField">
          <label for="ingredientUnit${ingredientCounter}"><b>Mértékegysége:</b></label>
          <input id="ingredientUnit${ingredientCounter}" name="ingredients[${ingredientCounter}][unit]" class="form-control" required type="text">
        </div>
      </div>
    `;

    ingredientsList.appendChild(newIngredients);
    ingredientCounter++;
  });

  // Single delegated listener handles both pre-existing and newly added delete buttons
  ingredientsList.addEventListener('click', (e) => {
    if (e.target.classList.contains('ingredientDeleteBtn')) {
      e.target.closest('.ingredientBlock').remove();
    }
  });
});