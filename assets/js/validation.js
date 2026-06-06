/**
 * Client-side form validation
 */
document.addEventListener('DOMContentLoaded', function () {
    // Registration form validation
    const regForm = document.getElementById('registerForm');
    if (regForm) {
        regForm.addEventListener('submit', function (e) {
            const pwd = document.getElementById('password');
            const confirm = document.getElementById('confirm_password');
            if (pwd.value.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters.');
                return;
            }
            if (pwd.value !== confirm.value) {
                e.preventDefault();
                alert('Passwords do not match.');
            }
        });
    }

    // Checkout form
    const checkoutForm = document.getElementById('checkoutForm');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function (e) {
            const required = checkoutForm.querySelectorAll('[required]');
            let valid = true;
            required.forEach(function (el) {
                if (!el.value.trim()) {
                    el.classList.add('is-invalid');
                    valid = false;
                } else {
                    el.classList.remove('is-invalid');
                }
            });
            if (!valid) {
                e.preventDefault();
                alert('Please fill all required fields.');
            }
        });
    }

    // Contact form
    const contactForm = document.getElementById('contactForm');
    if (contactForm) {
        contactForm.addEventListener('submit', function (e) {
            const email = document.getElementById('contact_email');
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email.value)) {
                e.preventDefault();
                alert('Please enter a valid email address.');
            }
        });
    }

    // Review form rating
    const reviewForm = document.getElementById('reviewForm');
    if (reviewForm) {
        reviewForm.addEventListener('submit', function (e) {
            const rating = document.querySelector('input[name="rating"]:checked');
            if (!rating) {
                e.preventDefault();
                alert('Please select a rating.');
            }
        });
    }
});
