// Function to change the main image when a thumbnail is clicked
function changeMainImage(imageSrc) {
    const mainImage = document.getElementById('main-product-image');
    
    // Store the current src to allow for smooth transition
    const currentSrc = mainImage.src;
    
    // Apply active class to the clicked thumbnail
    const thumbnails = document.querySelectorAll('.thumbnail');
    thumbnails.forEach(thumb => {
        if (thumb.src === imageSrc) {
            thumb.classList.add('active');
        } else {
            thumb.classList.remove('active');
        }
    });
    
    // Only change if different image selected
    if (currentSrc !== imageSrc) {
        // Create a smooth transition effect
        mainImage.style.opacity = '0';
        
        setTimeout(() => {
            mainImage.src = imageSrc;
            mainImage.style.opacity = '1';
        }, 300);
    }
}

// Function to handle tab switching
function setupTabs() {
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabPanes = document.querySelectorAll('.tab-pane');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            // Remove active class from all buttons
            tabButtons.forEach(btn => btn.classList.remove('active'));
            // Add active class to the clicked button
            button.classList.add('active');
            
            // Hide all panes
            tabPanes.forEach(pane => pane.style.display = 'none');
            // Show the corresponding pane
            const targetPane = document.getElementById(button.dataset.target);
            targetPane.style.display = 'block';
        });
    });
}