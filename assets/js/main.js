// Main JavaScript for Fhinz Store

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function(popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });

    // Loading state for buttons
    const loadingButtons = document.querySelectorAll('.btn-loading');
    loadingButtons.forEach(button => {
        button.addEventListener('click', function() {
            showButtonLoading(this);
        });
    });

    // Form validation
    const forms = document.querySelectorAll('.needs-validation');
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });

    // Search functionality
    const searchInput = document.querySelector('#searchInput');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                performSearch(this.value);
            }, 500);
        });
    }

    // Lazy loading for images
    const images = document.querySelectorAll('img[data-src]');
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.removeAttribute('data-src');
                observer.unobserve(img);
            }
        });
    });

    images.forEach(img => imageObserver.observe(img));

    // Back to top button
    const backToTopButton = document.querySelector('#backToTop');
    if (backToTopButton) {
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                backToTopButton.style.display = 'block';
            } else {
                backToTopButton.style.display = 'none';
            }
        });

        backToTopButton.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
});

// Utility Functions

function showButtonLoading(button) {
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Loading...';
    button.disabled = true;
    
    // Restore button after 3 seconds (adjust as needed)
    setTimeout(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    }, 3000);
}

function showAlert(type, message, duration = 5000) {
    const alertTypes = {
        'success': 'alert-success',
        'error': 'alert-danger',
        'warning': 'alert-warning',
        'info': 'alert-info'
    };

    const iconTypes = {
        'success': 'fa-check-circle',
        'error': 'fa-exclamation-circle',
        'warning': 'fa-exclamation-triangle',
        'info': 'fa-info-circle'
    };

    const alertClass = alertTypes[type] || 'alert-info';
    const iconClass = iconTypes[type] || 'fa-info-circle';

    const alertHTML = `
        <div class="alert ${alertClass} alert-dismissible fade show position-fixed" 
             style="top: 80px; right: 20px; z-index: 9999; min-width: 300px; max-width: 500px;" 
             role="alert">
            <i class="fas ${iconClass} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;

    document.body.insertAdjacentHTML('beforeend', alertHTML);

    // Auto remove after specified duration
    if (duration > 0) {
        setTimeout(() => {
            const alert = document.querySelector('.alert:last-of-type');
            if (alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        }, duration);
    }
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(amount);
}

function performSearch(query) {
    if (query.length < 2) return;
    
    // Show loading state
    const searchResults = document.querySelector('#searchResults');
    if (searchResults) {
        searchResults.innerHTML = '<div class="text-center py-3"><i class="fas fa-spinner fa-spin"></i> Searching...</div>';
    }

    // Perform AJAX search
    fetch(`search.php?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            displaySearchResults(data);
        })
        .catch(error => {
            console.error('Search error:', error);
            if (searchResults) {
                searchResults.innerHTML = '<div class="text-center py-3 text-muted">Error performing search</div>';
            }
        });
}

function displaySearchResults(results) {
    const searchResults = document.querySelector('#searchResults');
    if (!searchResults) return;

    if (results.length === 0) {
        searchResults.innerHTML = '<div class="text-center py-3 text-muted">No results found</div>';
        return;
    }

    let html = '';
    results.forEach(result => {
        html += `
            <div class="search-result-item p-3 border-bottom">
                <h6><a href="product-detail.php?id=${result.id}" class="text-decoration-none">${result.name}</a></h6>
                <p class="mb-1 text-muted small">${result.description}</p>
                <span class="badge bg-primary">${formatCurrency(result.price)}</span>
            </div>
        `;
    });

    searchResults.innerHTML = html;
}

// Cart Functions
function updateCartCount() {
    fetch('get-cart-count.php')
        .then(response => response.json())
        .then(data => {
            const cartCountElement = document.querySelector('#cart-count');
            if (cartCountElement) {
                cartCountElement.textContent = data.count || 0;
            }
        })
        .catch(error => {
            console.error('Error updating cart count:', error);
        });
}

function addToWishlist(productId) {
    fetch('wishlist-handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'add',
            product_id: productId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', 'Product added to wishlist!');
            // Update wishlist icon
            const wishlistButton = document.querySelector(`[data-product-id="${productId}"]`);
            if (wishlistButton) {
                wishlistButton.innerHTML = '<i class="fas fa-heart text-danger"></i>';
            }
        } else {
            showAlert('error', data.message || 'Failed to add to wishlist!');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'System error occurred!');
    });
}

// Form Helpers
function validateForm(formElement) {
    const inputs = formElement.querySelectorAll('input[required], select[required], textarea[required]');
    let isValid = true;

    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('is-invalid');
            isValid = false;
        } else {
            input.classList.remove('is-invalid');
            input.classList.add('is-valid');
        }
    });

    return isValid;
}

function resetForm(formElement) {
    formElement.reset();
    const inputs = formElement.querySelectorAll('.form-control');
    inputs.forEach(input => {
        input.classList.remove('is-valid', 'is-invalid');
    });
}

// Price formatter for Indonesian currency
function formatPrice(price) {
    return 'Rp ' + price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

// Debounce function for search and other inputs
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Initialize page-specific functionality
function initializePage() {
    const currentPage = window.location.pathname.split('/').pop();
    
    switch (currentPage) {
        case 'products.php':
            initializeProductsPage();
            break;
        case 'cart.php':
            initializeCartPage();
            break;
        case 'checkout.php':
            initializeCheckoutPage();
            break;
        default:
            // Default initialization
            break;
    }
}

function initializeProductsPage() {
    // Product filtering and sorting
    const filterButtons = document.querySelectorAll('.filter-btn');
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            const category = this.dataset.category;
            filterProducts(category);
        });
    });
}

function initializeCartPage() {
    // Quantity update handlers
    const quantityInputs = document.querySelectorAll('.quantity-input');
    quantityInputs.forEach(input => {
        input.addEventListener('change', function() {
            updateCartItem(this.dataset.productId, this.value);
        });
    });
}

function initializeCheckoutPage() {
    // Payment method selection
    const paymentMethods = document.querySelectorAll('input[name="payment_method"]');
    paymentMethods.forEach(method => {
        method.addEventListener('change', function() {
            showPaymentDetails(this.value);
        });
    });
}

// Initialize when page loads
document.addEventListener('DOMContentLoaded', initializePage);