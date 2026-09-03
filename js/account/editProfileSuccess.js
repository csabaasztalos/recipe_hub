document.addEventListener('DOMContentLoaded', function() {
    let error = document.getElementById('errorMessages');
    let success = document.getElementById('successMessages');

    if (error.innerText !== "") {
        success.innerText = "";
    }
});