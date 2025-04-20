document.addEventListener('DOMContentLoaded', function() {
    // Mobile Menu Toggle
    const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
    const navMenu = document.getElementById('nav-menu');
    
    if (mobileMenuToggle && navMenu) {
        mobileMenuToggle.addEventListener('click', function() {
            navMenu.classList.toggle('show');
        });
    }
    
    // Shopping Cart Functionality
    let cartItems = JSON.parse(localStorage.getItem('cartItems')) || [];
    let whistleItems = JSON.parse(localStorage.getItem('whistleItems')) || [];
    
    // Update cart and whistle counts on page load
    updateCartCount();
    updateWhistleCount();
    
    // Add to Cart buttons
    const addToCartButtons = document.querySelectorAll('.add-to-cart');
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function() {
            const product = this.getAttribute('data-product');
            const price = parseFloat(this.getAttribute('data-price'));
            
            addToCart(product, price);
            showNotification(`${product} added to cart!`);
        });
    });
    
    // Add to Whistles buttons
    const addToWhistlesButtons = document.querySelectorAll('.add-to-whistles');
    addToWhistlesButtons.forEach(button => {
        button.addEventListener('click', function() {
            const product = this.getAttribute('data-product');
            
            addToWhistles(product);
            showNotification(`${product} added to whistles!`);
        });
    });
    
    // Buy Now buttons
    const buyNowButtons = document.querySelectorAll('.buy-now');
    buyNowButtons.forEach(button => {
        button.addEventListener('click', function() {
            const product = this.getAttribute('data-product');
            const price = parseFloat(this.getAttribute('data-price'));
            
            // Add to cart and redirect to order page
            addToCart(product, price);
            window.location.href = 'confirm.php';
        });
    });
    
    // Cart button click - redirect to orders page
    const cartButton = document.getElementById('cart-button');
    if (cartButton) {
        cartButton.addEventListener('click', function() {
            window.location.href = 'order.html';
        });
    }
    
    // Whistles button click
    const whistlesButton = document.getElementById('whistles-button');
    if (whistlesButton) {
        whistlesButton.addEventListener('click', function() {
            // Display whistles or redirect to whistles page
            alert('Whistles: ' + whistleItems.join(', '));
        });
    }
    
    // Search functionality
    const searchButton = document.getElementById('search-button');
    const searchInput = document.getElementById('search-input');
    
    if (searchButton && searchInput) {
        searchButton.addEventListener('click', function() {
            performSearch();
        });
        
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                performSearch();
            }
        });
    }
    
    // Functions
    function addToCart(product, price) {
        cartItems.push({
            product: product,
            price: price,
            quantity: 1
        });
        
        localStorage.setItem('cartItems', JSON.stringify(cartItems));
        updateCartCount();
    }
    
    function addToWhistles(product) {
        if (!whistleItems.includes(product)) {
            whistleItems.push(product);
            localStorage.setItem('whistleItems', JSON.stringify(whistleItems));
            updateWhistleCount();
        }
    }
    
    function updateCartCount() {
        const cartCount = document.getElementById('cart-count');
        if (cartCount) {
            cartCount.textContent = cartItems.length;
        }
    }
    
    function updateWhistleCount() {
        const whistlesCount = document.getElementById('whistles-count');
        if (whistlesCount) {
            whistlesCount.textContent = whistleItems.length;
        }
    }
    
    function performSearch() {
        const searchTerm = searchInput.value.trim().toLowerCase();
        if (searchTerm) {
        }
    }
    
    function showNotification(message) {
        // Create notification element
        const notification = document.createElement('div');
        notification.classList.add('notification');
        notification.textContent = message;
        
        // Append to body
        document.body.appendChild(notification);
        
        // Add visible class for animation
        setTimeout(() => {
            notification.classList.add('visible');
        }, 10);
        
        // Remove after timeout
        setTimeout(() => {
            notification.classList.remove('visible');
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 3000);
    }
    
    function changeMainImage(imageUrl, clickedThumbnail) {
        // Update main image
        document.getElementById('main-product-image').src = imageUrl;
        
        // Update active thumbnail
        document.querySelectorAll('.thumbnail').forEach(thumb => {
            thumb.classList.remove('active');
        });
        clickedThumbnail.classList.add('active');
    }

    // Add notification styles if not already in CSS
    if (!document.querySelector('#notification-styles')) {
        const style = document.createElement('style');
        style.id = 'notification-styles';
        style.textContent = `
            .notification {
                position: fixed;
                bottom: 20px;
                right: 20px;
                background-color: #4CAF50;
                color: white;
                padding: 10px 20px;
                border-radius: 4px;
                box-shadow: 0 2px 5px rgba(0,0,0,0.2);
                opacity: 0;
                transform: translateY(20px);
                transition: opacity 0.3s ease, transform 0.3s ease;
                z-index: 1000;
            }
            
            .notification.visible {
                opacity: 1;
                transform: translateY(0);
            }
        `;
        document.head.appendChild(style);
    }
});