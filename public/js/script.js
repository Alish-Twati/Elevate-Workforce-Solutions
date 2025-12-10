/**
 * Custom JavaScript for Elevate Workforce Solutions
 * 
 * @author Alish Twati
 * @date June 2025
 * @version 1.0
 */

// ============================
// Document Ready
// ============================

document.addEventListener('DOMContentLoaded', function() {
    
    // Initialize tooltips
    initializeTooltips();
    
    // Auto-hide alerts
    autoHideAlerts();
    
    // Form validation
    enableFormValidation();
    
    // Confirm delete actions
    confirmDeleteActions();
    
    // Character counter for textareas
    characterCounter();
    
    // File upload preview
    fileUploadPreview();
    
});

// ============================
// Initialize Bootstrap Tooltips
// ============================

function initializeTooltips() {
    const tooltipTriggerList = [].slice.call(
        document.querySelectorAll('[data-bs-toggle="tooltip"]')
    );
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

// ============================
// Auto Hide Alerts
// ============================

function autoHideAlerts() {
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000); // Hide after 5 seconds
    });
}

// ============================
// Form Validation
// ============================

function enableFormValidation() {
    const forms = document.querySelectorAll('.needs-validation');
    
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
}

// ============================
// Confirm Delete Actions
// ============================

function confirmDeleteActions() {
    const deleteButtons = document.querySelectorAll('[data-action="delete"]');
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const confirmMessage = this.getAttribute('data-confirm') || 
                                   'Are you sure you want to delete this item?';
            if (! confirm(confirmMessage)) {
                e.preventDefault();
            }
        });
    });
}

// ============================
// Character Counter
// ============================

function characterCounter() {
    const textareas = document.querySelectorAll('textarea[data-max-length]');
    
    textareas.forEach(textarea => {
        const maxLength = textarea.getAttribute('data-max-length');
        const counterId = textarea.id + '-counter';
        
        // Create counter element
        const counter = document.createElement('small');
        counter.id = counterId;
        counter.className = 'text-muted';
        textarea.parentNode.insertBefore(counter, textarea.nextSibling);
        
        // Update counter
        const updateCounter = () => {
            const remaining = maxLength - textarea.value.length;
            counter.textContent = `${remaining} characters remaining`;
            
            if (remaining < 0) {
                counter.classList.add('text-danger');
            } else {
                counter.classList.remove('text-danger');
            }
        };
        
        textarea.addEventListener('input', updateCounter);
        updateCounter(); // Initial update
    });
}

// ============================
// File Upload Preview
// ============================

function fileUploadPreview() {
    const fileInputs = document.querySelectorAll('input[type="file"][data-preview]');
    
    fileInputs.forEach(input => {
        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            const previewId = this.getAttribute('data-preview');
            const preview = document.getElementById(previewId);
            
            if (file && preview) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    if (file.type.startsWith('image/')) {
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                    }
                };
                
                reader.readAsDataURL(file);
            }
        });
    });
}

// ============================
// Debounce Function
// ============================

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

// ============================
// Search Filter
// ============================

function filterResults(searchTerm, targetSelector) {
    const items = document.querySelectorAll(targetSelector);
    const lowerCaseSearchTerm = searchTerm.toLowerCase();
    
    items.forEach(item => {
        const text = item.textContent.toLowerCase();
        if (text.includes(lowerCaseSearchTerm)) {
            item.style.display = '';
        } else {
            item.style.display = 'none';
        }
    });
}

// ============================
// Loading Spinner
// ============================

function showLoadingSpinner(element) {
    const spinner = document.createElement('span');
    spinner.className = 'spinner-border spinner-border-sm me-2';
    spinner.setAttribute('role', 'status');
    element.prepend(spinner);
    element.disabled = true;
}

function hideLoadingSpinner(element) {
    const spinner = element.querySelector('.spinner-border');
    if (spinner) {
        spinner.remove();
    }
    element.disabled = false;
}

// ============================
// Copy to Clipboard
// ============================

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showNotification('Copied to clipboard!', 'success');
    }).catch(err => {
        console.error('Failed to copy:', err);
        showNotification('Failed to copy to clipboard', 'danger');
    });
}

// ============================
// Show Notification
// ============================

function showNotification(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3`;
    alertDiv.style.zIndex = '9999';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(alertDiv);
    
    setTimeout(() => {
        alertDiv. remove();
    }, 3000);
}

// ============================
// Password Strength Indicator
// ============================

function checkPasswordStrength(password) {
    let strength = 0;
    
    if (password.length >= 8) strength++;
    if (password.match(/[a-z]+/)) strength++;
    if (password.match(/[A-Z]+/)) strength++;
    if (password.match(/[0-9]+/)) strength++;
    if (password.match(/[$@#&! ]+/)) strength++;
    
    return strength;
}

function updatePasswordStrengthIndicator(inputId, indicatorId) {
    const input = document.getElementById(inputId);
    const indicator = document.getElementById(indicatorId);
    
    if (input && indicator) {
        input.addEventListener('input', function() {
            const strength = checkPasswordStrength(this.value);
            const strengthText = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong'];
            const strengthColor = ['danger', 'warning', 'info', 'primary', 'success'];
            
            if (this.value.length > 0) {
                indicator. textContent = strengthText[strength - 1] || 'Very Weak';
                indicator.className = `text-${strengthColor[strength - 1] || 'danger'}`;
                indicator.style.display = 'block';
            } else {
                indicator.style.display = 'none';
            }
        });
    }
}

// ============================
// Smooth Scroll
// ============================

document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        const href = this.getAttribute('href');
        if (href !== '#' && href !== '') {
            e.preventDefault();
            const target = document.querySelector(href);
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        }
    });
});

// ============================
// Back to Top Button
// ============================

const backToTopButton = document.createElement('button');
backToTopButton.innerHTML = '<i class="fas fa-arrow-up"></i>';
backToTopButton.className = 'btn btn-primary position-fixed bottom-0 end-0 m-4 rounded-circle';
backToTopButton.style.display = 'none';
backToTopButton.style.zIndex = '9999';
backToTopButton.style.width = '50px';
backToTopButton.style.height = '50px';

backToTopButton.addEventListener('click', () => {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
});

document.body.appendChild(backToTopButton);

window.addEventListener('scroll', () => {
    if (window.pageYOffset > 300) {
        backToTopButton.style.display = 'block';
    } else {
        backToTopButton.style.display = 'none';
    }
});

// ==================== FOOTER FUNCTIONALITY ====================

// Back to Top Button
document.addEventListener('DOMContentLoaded', function() {
    const backToTopBtn = document.getElementById('backToTop');
    
    if (backToTopBtn) {
        // Show/hide button based on scroll position
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                backToTopBtn.style.display = 'flex';
            } else {
                backToTopBtn.style.display = 'none';
            }
        });
        
        // Scroll to top on click
        backToTopBtn.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
});

// Cookie Consent Functions
function acceptCookies() {
    // Set cookie for 1 year
    document.cookie = "cookie_consent=true; max-age=31536000; path=/";
    document.getElementById('cookieConsent').style. display = 'none';
}

function dismissCookies() {
    // Set cookie for 1 day only
    document.cookie = "cookie_consent=dismissed; max-age=86400; path=/";
    document.getElementById('cookieConsent').style. display = 'none';
}

// Form validation helper
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (form) {
        form.addEventListener('submit', function(e) {
            if (! form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList. add('was-validated');
        });
    }
}

// Auto-hide alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('. alert:not(.alert-permanent)');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
});

// Confirm before dangerous actions
function confirmAction(message = 'Are you sure? ') {
    return confirm(message);
}

// Loading state for buttons
function setButtonLoading(button, loading = true) {
    if (loading) {
        button. disabled = true;
        button. dataset.originalText = button.innerHTML;
        button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Loading...';
    } else {
        button.disabled = false;
        button.innerHTML = button.dataset.originalText;
    }
}

// ============================
// Console Welcome Message
// ============================

console.log('%c Elevate Workforce Solutions ', 'background: #0d6efd; color: white; font-size: 20px; padding: 10px;');
console.log('%c Developed by Alish Twati ', 'background: #198754; color: white; font-size: 14px; padding: 5px;');
console.log('%c Version 1.0.0 ', 'background: #6c757d; color: white; font-size: 12px; padding: 5px;');