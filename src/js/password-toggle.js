const passwordInput = document.querySelector('#password');
const passwordToggle = document.querySelector('.password-toggle');

if (passwordInput && passwordToggle) {
    passwordToggle.hidden = false;
    passwordToggle.addEventListener('click', () => {
        const visible = passwordInput.type === 'password';
        passwordInput.type = visible ? 'text' : 'password';
        passwordToggle.setAttribute('aria-label', visible ? 'Ocultar contraseña' : 'Mostrar contraseña');
        passwordToggle.querySelector('.eye-open').toggleAttribute('hidden', !visible);
        passwordToggle.querySelector('.eye-closed').toggleAttribute('hidden', visible);
    });
}
