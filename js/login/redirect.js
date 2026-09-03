
document.addEventListener('DOMContentLoaded', function () {
    const verificationText= document.getElementById('verificationText');
    const resetText = document.getElementById('resetText');

    if(resetText !==null) {
        if (resetText.classList.contains("success")) {
            setTimeout(() => { window.location.href = "index.php";}, 3000);
        }
    }

    if(verificationText !== null) {
        if (verificationText.classList.contains("success")) {
            setTimeout(() => { window.location.href = "index.php";}, 3000);
        }
    }
});
