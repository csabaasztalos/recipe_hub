document.addEventListener('DOMContentLoaded', function () {
    const resendBtn = document.getElementById('resendBtn');
    const resetBtn = document.getElementById('resetBtn');
    const resetText = document.getElementById('resetText');
    const resendtText = document.getElementById('resendtText');
    const verificationText = document.getElementById('verificationText');
    const newPasswordText = document.getElementById('newPasswordText');
    const newPassword = document.getElementById('newPassword');
    const passwordConfirmText = document.getElementById('passwordConfirmText');
    const passwordConfirm = document.getElementById('passwordConfirm');


    if(resetBtn !== null) {
        if (resetText.innerText === "Ez a link már nem érvényes.") {
            resendBtn.remove();
            resetBtn.remove();
            newPasswordText.remove();
            newPassword.remove();
            passwordConfirmText.remove();
            passwordConfirm.remove();
        }
    }

    if(verificationText !== null) {
       if (verificationText.innerText === "Ez a link már nem érvényes.") {
            resendBtn.remove();
            resendtText.remove();
        }
    }
    
});