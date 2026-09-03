document.getElementById('show').addEventListener('change', function () {
    const passInput = document.getElementById('pass');
    const label = document.getElementById('text');

    if (this.checked) {
        passInput.type = 'text';
    } else {
        passInput.type = 'password';
    }
});
