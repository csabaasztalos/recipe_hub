document.addEventListener("DOMContentLoaded", function(){
    const form = document.querySelector("#uploadForm");
    if (!form) return;
    const imageInput = document.querySelector("#imageSingle");
    const uploadBox = document.querySelector("#singleUpload");

    form.addEventListener("submit", function (e){
        uploadBox.classList.remove('upload-error');

        if(!imageInput.files || imageInput.files.length === 0) {
            e.preventDefault();
            uploadBox.classList.add("upload-error");
            uploadBox.scrollIntoView({ behavior: 'smooth', block: 'bottom' });
        }
    }); 

    imageInput.addEventListener("change", function (){
        if(imageInput.files || imageInput.files.length > 0) {
            uploadBox.classList.remove("upload-error");
        }
    }); 
});