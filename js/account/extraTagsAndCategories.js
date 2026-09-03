document.addEventListener('DOMContentLoaded', function () {
    console.log('extraTagsAndCategories.js loaded - Categories only');

    // Category selection functionality
    const categorySelect = document.getElementById('categories');
    const addCategoryBtn = document.getElementById('addCategory');
    const categoryTags = document.getElementById('categoryTags');
    const selectedCategoryIds = document.getElementById('selectedCategoryIds');

    let selectedCategories = [];

    // Initialize selectedCategories from existing category tags
    const existingCategoryTags = categoryTags.querySelectorAll('.categoryTag[data-category-id]');
    existingCategoryTags.forEach(tag => {
        const categoryId = tag.dataset.categoryId;
        const categoryName = tag.textContent.replace('×', '').trim();
        selectedCategories.push({
            id: categoryId,
            name: categoryName
        });
    });

    console.log('Category elements found:', {
        categorySelect: !!categorySelect,
        addCategoryBtn: !!addCategoryBtn,
        categoryTags: !!categoryTags,
        selectedCategoryIds: !!selectedCategoryIds,
        existingCategories: selectedCategories.length
    });

    console.log('Initialized selectedCategories:', selectedCategories);

    // Define updateSelectedCategoryIds function first
    function updateSelectedCategoryIds() {
        const ids = selectedCategories.map(cat => cat.id);
        selectedCategoryIds.value = ids.join(';');
        console.log('Updated selectedCategoryIds:', selectedCategoryIds.value);
    }

    // Call it to sync the hidden input with existing categories
    if (selectedCategories.length > 0) {
        updateSelectedCategoryIds();
    }

    // Category functionality
    if (addCategoryBtn && categorySelect && categoryTags && selectedCategoryIds) {
        addCategoryBtn.addEventListener('click', function () {
            console.log('Add category button clicked');
            const selectedOption = categorySelect.options[categorySelect.selectedIndex];

            if (selectedOption.value === '') {
                alert('Kérlek válassz egy kategóriát!');
                return;
            }

            const categoryId = selectedOption.value;
            const categoryName = selectedOption.text;

            console.log('Adding category:', categoryId, categoryName);

            // Check if category is already selected
            if (selectedCategories.some(cat => cat.id === categoryId)) {
                alert('Ez a kategória már ki van választva!');
                return;
            }

            // Add to selected categories
            selectedCategories.push({
                id: categoryId,
                name: categoryName
            });

            // Create visual tag element
            createCategoryTag(categoryId, categoryName);

            // Update hidden input
            updateSelectedCategoryIds();

            // Reset select
            categorySelect.selectedIndex = 0;
        });
    } else {
        console.error('Missing category elements!');
    }

    function createCategoryTag(categoryId, categoryName) {
        const tag = document.createElement('div');
        tag.className = 'categoryTag';
        tag.dataset.categoryId = categoryId;

        tag.innerHTML = `
            <span>${categoryName}</span>
            <button type="button" class="removeTag" onclick="removeCategory('${categoryId}')">&times;</button>
        `;

        categoryTags.appendChild(tag);
        console.log('Category tag created for:', categoryName);
    }

    // Make removeCategory global so onclick can access it
    window.removeCategory = function (categoryId) {
        console.log('Removing category:', categoryId);
        selectedCategories = selectedCategories.filter(cat => cat.id !== categoryId);

        const tagElement = categoryTags.querySelector(`[data-category-id="${categoryId}"]`);
        if (tagElement) {
            tagElement.remove();
        }

        updateSelectedCategoryIds();
    };

    // Form submission logging
    form.addEventListener('submit', function(e) {
        if (!form.contains(selectedCategoryIds)) {
            return;
        }

        if (!selectedCategoriesIds.value || selectedCategoryIds.value === '') {
            alert('Kérlek válassz legalább egy kategóriát!');
            e.preventDefault();
            return false;
        }
        console.log('✅ Categories OK, form will submit');
    });
});