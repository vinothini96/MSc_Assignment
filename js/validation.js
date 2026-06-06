/**
 * Client-side form validation
 */
(function () {
    'use strict';

    var emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    var phoneRe = /^[0-9]{10}$/;

    function validatePassword(pw) {
        if (pw.length < 8) return false;
        if (!/[A-Z]/.test(pw)) return false;
        if (!/[a-z]/.test(pw)) return false;
        if (!/[0-9]/.test(pw)) return false;
        return true;
    }

    function passwordStrength(pw) {
        var score = 0;
        if (pw.length >= 8) score++;
        if (/[A-Z]/.test(pw)) score++;
        if (/[a-z]/.test(pw)) score++;
        if (/[0-9]/.test(pw)) score++;
        if (/[^A-Za-z0-9]/.test(pw)) score++;
        return score;
    }

    function setInvalid(input, message) {
        input.classList.add('is-invalid');
        var fb = input.parentElement.querySelector('.invalid-feedback');
        if (fb) fb.textContent = message;
        return false;
    }

    function clearInvalid(input) {
        input.classList.remove('is-invalid');
        return true;
    }

    function validateField(input) {
        var type = input.getAttribute('data-validate');
        var val = input.value.trim();

        if (input.hasAttribute('required') && !val) {
            return setInvalid(input, 'This field is required.');
        }

        if (type === 'email' && val && !emailRe.test(val)) {
            return setInvalid(input, 'Enter a valid email address.');
        }

        if (type === 'phone' && val) {
            var digits = val.replace(/\D/g, '');
            if (!phoneRe.test(digits)) {
                return setInvalid(input, 'Enter a valid 10-digit phone number.');
            }
        }

        if (type === 'password' && val && !validatePassword(val)) {
            return setInvalid(input, 'Password must be 8+ chars with upper, lower, and number.');
        }

        if (type === 'confirm') {
            var matchSel = input.getAttribute('data-match');
            var matchEl = matchSel ? document.querySelector(matchSel) : null;
            if (matchEl && val !== matchEl.value) {
                return setInvalid(input, 'Passwords do not match.');
            }
        }

        return clearInvalid(input);
    }

    document.addEventListener('DOMContentLoaded', function () {
        var pwdInput = document.getElementById('reg-password');
        var strengthBar = document.getElementById('pwd-strength-bar');
        if (pwdInput && strengthBar) {
            pwdInput.addEventListener('input', function () {
                var score = passwordStrength(pwdInput.value);
                var widths = ['0%', '25%', '50%', '75%', '100%'];
                var colors = ['#dc3545', '#fd7e14', '#ffc107', '#20c997', '#198754'];
                strengthBar.style.width = widths[score] || '0%';
                strengthBar.style.background = colors[score - 1] || '#e9ecef';
            });
        }

        document.querySelectorAll('form[novalidate]').forEach(function (form) {
            form.querySelectorAll('input, textarea, select').forEach(function (input) {
                input.addEventListener('blur', function () {
                    validateField(input);
                });
            });

            form.addEventListener('submit', function (e) {
                var valid = true;
                form.querySelectorAll('input, textarea, select').forEach(function (input) {
                    if (input.type === 'hidden') return;
                    if (!validateField(input)) valid = false;
                });
                if (!valid) e.preventDefault();
            });
        });
    });
})();
