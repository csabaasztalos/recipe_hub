// editRecipeImages.js
// Handles existing image management and viewing in recipe edit form

// Track removed images
let removedMainImage = '';
let removedExtraImages = [];

// Remove existing image
function removeExistingImage(button, imageType, imageIndex = null) {
    const imageItem = button.closest('.existing-image-item');
    const imagePath = imageItem.getAttribute('data-image-path');
    
    if (imageType === 'main') {
        removedMainImage = imagePath;
        document.getElementById('removedMainImage').value = imagePath;
    } else if (imageType === 'extra' && imageIndex !== null) {
        removedExtraImages.push(imagePath);
        document.getElementById('removedExtraImages').value = removedExtraImages.join(';');
    }
    
    // Add fade-out animation and remove
    imageItem.style.opacity = '0.5';
    imageItem.style.transform = 'scale(0.8)';
    
    setTimeout(() => {
        imageItem.remove();
        
        // Check if container is empty and show message
        const container = imageItem.parentElement;
        if (container && container.children.length === 0) {
            container.classList.add('empty');
        }
    }, 300);
}

// View image in modal
function viewImage(imagePath) {
    // Create modal if it doesn't exist
    let modal = document.getElementById('imageViewerModal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'imageViewerModal';
        modal.className = 'image-viewer-modal';
        modal.innerHTML = `
            <div class="image-viewer-content">
                <span class="image-viewer-close" onclick="closeImageViewer()">&times;</span>
                <img id="modalImage" src="" alt="Recipe Image">
            </div>
        `;
        document.body.appendChild(modal);
        
        // Close modal when clicking outside the image
        modal.addEventListener('click', function(event) {
            if (event.target === modal) {
                closeImageViewer();
            }
        });
    }
    
    // Set image source and show modal
    document.getElementById('modalImage').src = imagePath;
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden'; // Prevent background scrolling
}

// Close image viewer
function closeImageViewer() {
    const modal = document.getElementById('imageViewerModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto'; // Restore scrolling
    }
}

// Close modal with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeImageViewer();
    }
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Reset removed images tracking
    removedMainImage = '';
    removedExtraImages = [];
    
    console.log('Edit Recipe Images functionality loaded');
});