document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.check-all').forEach(function (checkAllBox) {
        checkAllBox.addEventListener('change', function () {
            // Find the closest table
            const table = checkAllBox.closest('table');
            if (!table) return;
            // Find all checkboxes in the table body with the same name pattern
            const name = checkAllBox.dataset.targetName || 'recordIDS[]';
            const checkboxes = table.querySelectorAll('input[type="checkbox"][name="' + name + '"]');
            checkboxes.forEach(cb => cb.checked = checkAllBox.checked);
        });
    });
});
document.querySelector('form:has([name="bulkDelete"])').addEventListener('submit', function (e) {
    const checked = document.querySelectorAll('input[name="recordIDS[]"]:checked');

    if (checked.length === 0) {
        e.preventDefault();
        return;
    }

    checked.forEach(function (cb) {
        const hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = 'recordIDS[]';
        hidden.value = cb.value;
        e.target.appendChild(hidden);
    });
});