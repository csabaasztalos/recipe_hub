document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('recipeFilterForm');
    const filterKey = 'recipeFilterValues';

    // Restore values
    const saved = localStorage.getItem(filterKey);
    if (saved) {
        const values = JSON.parse(saved);
        Object.keys(values).forEach(function(name) {
            const field = form.elements[name];
            if (field) field.value = values[name];
        });
    }

    // Save values on submit
    form.addEventListener('submit', function() {
        const values = {};
        Array.from(form.elements).forEach(function(el) {
            if (el.name) values[el.name] = el.value;
        });
        localStorage.setItem(filterKey, JSON.stringify(values));
    });
});

document.getElementById('recipeFilterForm').addEventListener('reset', function() {
    localStorage.removeItem('recipeFilterValues');
});