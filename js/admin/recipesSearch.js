

function setupRecipeSearch(modalSelector) {
  const modal = document.querySelector(modalSelector);
  if (!modal) return;

  const search = modal.querySelector('.recipe-search');
  const options = modal.querySelector('.recipe-dropdown-list');
  const hiddenInput = modal.querySelector('.recipe-value');
  if (!search || !options || !hiddenInput) return;

  let inputHandler, clickHandler;
  
  function attachHandlers() {
    inputHandler = function() {
      const filter = this.value.toLowerCase();
      if (filter) {
        options.style.display = 'block';
        Array.from(options.children).forEach(li => {
          const textMatch = li.textContent.toLowerCase().includes(filter);
          const valueMatch = (li.dataset.value || '').toLowerCase().includes(filter);
          li.style.display = (textMatch || valueMatch) ? '' : 'none';
        });
      } else {
        options.style.display = 'none';
      }
    };
    clickHandler = function(e) {
      if (e.target.tagName === 'LI') {
        search.value = e.target.textContent;
        const recipeId = Number.parseInt(e.target.dataset.value, 10);
        hiddenInput.value = Number.isInteger(recipeId) ? recipeId : '';
        options.style.display = 'none';
      }
    };
    search.addEventListener('input', inputHandler);
    options.addEventListener('click', clickHandler);
  }

  function detachHandlers() {
    if (inputHandler) search.removeEventListener('input', inputHandler);
    if (clickHandler) options.removeEventListener('click', clickHandler);
  }

  // Use Bootstrap modal events
  modal.addEventListener('show.bs.modal', attachHandlers);
  modal.addEventListener('hide.bs.modal', function() {
    detachHandlers();
    // Clear search input and hide dropdown
    search.value = '';
    hiddenInput.value = '';
    options.style.display = 'none';
  });
}

// Assign modal
setupRecipeSearch('#assignCategoryModal');
// Remove modal
setupRecipeSearch('#removeCategoryModal');