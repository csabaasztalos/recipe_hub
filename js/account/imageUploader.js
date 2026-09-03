console.log('imageUploader.js loaded!');
console.log('DOM ready state:', document.readyState);

function setupImageUploader(uploadBoxId, inputId, previewId, maxImages) {
    const uploadBox = document.getElementById(uploadBoxId);
    const imageInput = document.getElementById(inputId);
    const previewContainer = document.getElementById(previewId);

    console.log('Setting up uploader for:', uploadBoxId, inputId);
    
    // Store files in a global DataTransfer object for multiple files
    let fileTransfer = new DataTransfer();

    uploadBox.addEventListener('click', () => {
        console.log('Upload box clicked');
        imageInput.click();
    });

    imageInput.addEventListener('change', handleFiles);

    uploadBox.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadBox.classList.add('border-primary');
    });

    uploadBox.addEventListener('dragleave', () => {
        uploadBox.classList.remove('border-primary');
    });

    uploadBox.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadBox.classList.remove('border-primary');
        
        console.log('Files dropped:', e.dataTransfer.files.length);
        if (e.dataTransfer.files.length > 0) {
            // For drag and drop, add to our file collection
            if (maxImages > 1) {
                // For multiple files, add them to our DataTransfer
                const newFiles = Array.from(e.dataTransfer.files);
                handleNewFilesAddition(newFiles);
            } else {
                // For single file, just replace
                fileTransfer = new DataTransfer();
                fileTransfer.items.add(e.dataTransfer.files[0]);
                imageInput.files = fileTransfer.files;
            }
            imageInput.dispatchEvent(new Event('change', { bubbles: true }));
        }
    });

    function handleNewFilesAddition(newFiles) {
        // Get current files in the collection
        const currentFiles = Array.from(fileTransfer.files || []);
        
        // Calculate how many more files we can add
        const remainingSlots = maxImages - currentFiles.length;
        
        if (remainingSlots <= 0) {
            alert(`Maximum ${maxImages} képet tölthetsz fel. Törölj néhányat, ha újakat szeretnél feltölteni.`);
            return;
        }
        
        // Add new files (up to the max)
        const filesToAdd = newFiles.slice(0, remainingSlots);
        
        // Create a new DataTransfer
        const newTransfer = new DataTransfer();
        
        // Add existing files
        currentFiles.forEach(file => newTransfer.items.add(file));
        
        // Add new files
        filesToAdd.forEach(file => newTransfer.items.add(file));
        
        // Update our global transfer object
        fileTransfer = newTransfer;
        
        // Update the file input
        imageInput.files = fileTransfer.files;
        
        if (newFiles.length > remainingSlots) {
            alert(`Maximum ${maxImages} képet tölthetsz fel. Csak az első ${remainingSlots} kép lett hozzáadva.`);
        }
    }

    function handleFiles(event) {
        console.log('handleFiles called');
        console.log('Files in input:', event.target.files.length);
        
        const files = event.target.files;
        
        if (maxImages === 1) {
            // Single image upload - clear any existing preview and show the one file
            previewContainer.innerHTML = '';
            if (files && files.length > 0) {
                const file = files[0];
                console.log('Processing single file:', file.name, file.size);
                createImagePreview(file, previewContainer, true);
            }
        } else {
            // Multiple image upload - use the same logic as drag and drop
            if (files && files.length > 0) {
                const newFiles = Array.from(files);
                
                // Use the existing handleNewFilesAddition function to add to current files
                handleNewFilesAddition(newFiles);
                
                // Update preview
                previewContainer.innerHTML = ''; // Clear previous previews
                console.log('Processing multiple files:', fileTransfer.files.length);
                for (let i = 0; i < fileTransfer.files.length; i++) {
                    createImagePreview(fileTransfer.files[i], previewContainer, false);
                }
            }
        }
    }

    function createImagePreview(file, container, isSingle) {
        const reader = new FileReader();
        reader.onload = (e) => {
            console.log('Preview created for:', file.name);
            
            const wrapper = document.createElement('div');
            wrapper.className = 'preview';
            wrapper.dataset.filename = file.name;

            const img = document.createElement('img');
            img.src = e.target.result;
            img.alt = file.name;

            // Create delete button
            const deleteBtn = document.createElement('button');
            deleteBtn.className = 'deleteBtn';
            deleteBtn.innerHTML = '&times;';
            deleteBtn.title = 'Kép törlése';
            deleteBtn.type = 'button';

            // Add delete functionality
            deleteBtn.addEventListener('click', (event) => {
                event.preventDefault();
                console.log('Delete button clicked for:', file.name);
                
                // Remove the preview
                wrapper.remove();
                // Clear the file input
                if (isSingle) {
                    imageInput.value = '';
                    // Reset the global DataTransfer object for single file
                    fileTransfer = new DataTransfer();
                } else {
                    // For multiple files, we need to recreate the FileList without this file
                    const newTransfer = new DataTransfer();
                    const currentFiles = Array.from(imageInput.files);
                    
                    currentFiles.forEach(currentFile => {
                        if (currentFile.name !== file.name) {
                            newTransfer.items.add(currentFile);
                        }
                    });
                    
                    // Update global DataTransfer and input
                    fileTransfer = newTransfer;
                    imageInput.files = fileTransfer.files;
                }
                
                console.log('File removed. Remaining files:', imageInput.files.length);
            });

            wrapper.appendChild(img);
            wrapper.appendChild(deleteBtn);
            container.appendChild(wrapper);
        };
        reader.readAsDataURL(file);
    }

    // Add form submit debug
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', (e) => {
            console.log('Form submitting...');
            console.log('Files in', inputId + ':', imageInput.files.length);
            if (imageInput.files.length > 0) {
                for (let i = 0; i < imageInput.files.length; i++) {
                    console.log(`File ${i + 1}:`, imageInput.files[i].name);
                }
            }
        });
    }
}

console.log('About to setup uploaders...');

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, setting up image uploaders...');
    
    // Check if elements exist before setting up
    if (document.getElementById('singleUpload')) {
        setupImageUploader('singleUpload', 'imageSingle', 'singlePreview', 1);
        console.log('Single uploader setup complete');
    }
    
    if (document.getElementById('multiUpload')) {
        setupImageUploader('multiUpload', 'imageMulti', 'multiPreview', 5);
        console.log('Multi uploader setup complete');
    }
});