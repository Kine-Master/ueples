// frontend/login/script.js
document.addEventListener('DOMContentLoaded', () => {
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');

    if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', function () {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);

            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    }

    // Auto-focus the first empty field
    const form = document.getElementById('loginForm');
    if (form) {
        const inputs = form.querySelectorAll('input[required]');
        for (let i = 0; i < inputs.length; i++) {
            if (!inputs[i].value) {
                inputs[i].focus();
                break;
            }
        }
    }
});