document.querySelector('.loginForm').addEventListener('submit', function(e) {
    const password = document.getElementById('pass').value;
    const passwordConfrim = document.getElementById('passConfirm').value;

    if(password !== passwordConfrim) {
        e.preventDefault();
        alert('A jelszavak nem egyeznek!');
        return;
    }
});

document.querySelector('#changePasswordForm').addEventListener('submit', function(e) {
    const password = document.getElementById('newPassword').value;
    const passwordConfrim = document.getElementById('passwordConfirm').value;

    if(password !== passwordConfrim) {
        e.preventDefault();
        alert('A jelszavak nem egyeznek!');
        return;
    }
});
