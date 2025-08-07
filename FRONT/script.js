// script.js
// Smooth scrolling for navigation links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            window.scrollTo({
                top: target.offsetTop - 80,
                behavior: 'smooth'
            });
        }
    });
});

// Button hover effects
const buttons = document.querySelectorAll('.cta-button, .secondary-button');
buttons.forEach(button => {
    button.addEventListener('mouseenter', () => {
        button.style.transform = 'translateY(-3px)';
    });
    
    button.addEventListener('mouseleave', () => {
        button.style.transform = 'translateY(0)';
    });
});

// Feature card animations
const featureCards = document.querySelectorAll('.feature-card');
featureCards.forEach(card => {
    card.addEventListener('mouseenter', () => {
        card.style.boxShadow = '0 15px 40px rgba(0, 0, 0, 0.2)';
    });
    
    card.addEventListener('mouseleave', () => {
        card.style.boxShadow = '0 10px 30px rgba(0, 0, 0, 0.1)';
    });
});

// Login/Signup Tabs
const loginTab = document.getElementById('loginTab');
const signupTab = document.getElementById('signupTab');
const loginForm = document.getElementById('loginForm');
const signupForm = document.getElementById('signupForm');
const goToLogin = document.getElementById('goToLogin');

if (loginTab && signupTab) {
    loginTab.addEventListener('click', () => {
        loginTab.classList.add('active');
        signupTab.classList.remove('active');
        loginForm.classList.add('active');
        signupForm.classList.remove('active');
    });
    
    signupTab.addEventListener('click', () => {
        signupTab.classList.add('active');
        loginTab.classList.remove('active');
        signupForm.classList.add('active');
        loginForm.classList.remove('active');
    });
}

if (goToLogin) {
    goToLogin.addEventListener('click', (e) => {
        e.preventDefault();
        loginTab.click();
    });
}

// Toggle password visibility
document.querySelectorAll('.toggle-password').forEach(toggle => {
    toggle.addEventListener('click', function() {
        const input = this.previousElementSibling;
        if (input.type === 'password') {
            input.type = 'text';
            this.classList.remove('fa-eye');
            this.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            this.classList.remove('fa-eye-slash');
            this.classList.add('fa-eye');
        }
    });
});

// Login form submission
if (loginForm) {
    loginForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        
        // Simple validation
        if (email && password) {
            // Store login state
            localStorage.setItem('isLoggedIn', 'true');
            
            // Redirect to dashboard
            window.location.href = 'dashboard.html';
        } else {
            alert('Please fill in all fields');
        }
    });
}

// Signup form submission
if (signupForm) {
    signupForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const fullName = document.getElementById('fullName').value;
        const email = document.getElementById('signupEmail').value;
        const phone = document.getElementById('phone').value;
        const password = document.getElementById('signupPassword').value;
        const confirmPassword = document.getElementById('confirmPassword').value;
        
        // Simple validation
        if (password !== confirmPassword) {
            alert('Passwords do not match');
            return;
        }
        
        if (fullName && email && phone && password) {
            // Store login state
            localStorage.setItem('isLoggedIn', 'true');
            
            // Redirect to dashboard
            window.location.href = 'dashboard.html';
        } else {
            alert('Please fill in all fields');
        }
    });
}

// Check URL for user type
window.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const userType = urlParams.get('user');
    const loginTitle = document.getElementById('login-title');
    const loginSubtitle = document.getElementById('login-subtitle');
    
    if (userType === 'tenant' && loginTitle && loginSubtitle) {
        loginTitle.textContent = 'Tenant Portal';
        loginSubtitle.textContent = 'Manage your rental experience';
    }
});