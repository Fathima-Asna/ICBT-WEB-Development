/**
 * GlobeTrek Adventures - Client-side Authentication Validation
 */

document.addEventListener('DOMContentLoaded', () => {
    const registerForm = document.getElementById('register-form');
    const loginForm = document.getElementById('login-form');

    if (registerForm) {
        registerForm.addEventListener('submit', (e) => {
            let isValid = true;

            // 1. Validate Username
            const usernameInput = document.getElementById('username');
            const usernameErr = document.getElementById('username-error');
            const usernameVal = usernameInput.value.trim();
            if (usernameVal.length < 4) {
                showError(usernameInput, usernameErr, "Username must be at least 4 characters long.");
                isValid = false;
            } else if (!/^[a-zA-Z0-9_]+$/.test(usernameVal)) {
                showError(usernameInput, usernameErr, "Username can only contain letters, numbers, and underscores.");
                isValid = false;
            } else {
                hideError(usernameInput, usernameErr);
            }

            // 2. Validate Email
            const emailInput = document.getElementById('email');
            const emailErr = document.getElementById('email-error');
            const emailVal = emailInput.value.trim();
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(emailVal)) {
                showError(emailInput, emailErr, "Please enter a valid email address.");
                isValid = false;
            } else {
                hideError(emailInput, emailErr);
            }

            // 3. Validate Password (min 8 chars, 1 uppercase, 1 lowercase, 1 number, 1 special char)
            const passwordInput = document.getElementById('password');
            const passwordErr = document.getElementById('password-error');
            const passwordVal = passwordInput.value;
            const strongPasswordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
            if (!strongPasswordRegex.test(passwordVal)) {
                showError(
                    passwordInput, 
                    passwordErr, 
                    "Password must be at least 8 characters and include at least one uppercase letter, one lowercase letter, one number, and one special character (@$!%*?&)."
                );
                isValid = false;
            } else {
                hideError(passwordInput, passwordErr);
            }

            // 4. Validate Confirm Password
            const confirmInput = document.getElementById('confirm_password');
            const confirmErr = document.getElementById('confirm_password-error');
            const confirmVal = confirmInput.value;
            if (confirmVal !== passwordVal) {
                showError(confirmInput, confirmErr, "Passwords do not match.");
                isValid = false;
            } else {
                hideError(confirmInput, confirmErr);
            }

            if (!isValid) {
                e.preventDefault(); // Stop form submission
            }
        });
    }

    if (loginForm) {
        loginForm.addEventListener('submit', (e) => {
            let isValid = true;

            const usernameInput = document.getElementById('username');
            const usernameErr = document.getElementById('username-error');
            if (usernameInput.value.trim() === '') {
                showError(usernameInput, usernameErr, "Please enter your username.");
                isValid = false;
            } else {
                hideError(usernameInput, usernameErr);
            }

            const passwordInput = document.getElementById('password');
            const passwordErr = document.getElementById('password-error');
            if (passwordInput.value === '') {
                showError(passwordInput, passwordErr, "Please enter your password.");
                isValid = false;
            } else {
                hideError(passwordInput, passwordErr);
            }

            if (!isValid) {
                e.preventDefault();
            }
        });
    }
});

function showError(inputElement, errorElement, message) {
    inputElement.classList.add('invalid');
    errorElement.textContent = message;
    errorElement.classList.add('active');
}

function hideError(inputElement, errorElement) {
    inputElement.classList.remove('invalid');
    errorElement.textContent = '';
    errorElement.classList.remove('active');
}
