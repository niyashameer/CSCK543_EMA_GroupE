
document.addEventListener('DOMContentLoaded', function () {
    setupNavToggle();
    setupPasswordMatchValidation('register-form', 'password', 'confirm_password');
    setupPasswordMatchValidation('password-form', 'new_password', 'confirm_password');
});

function setupNavToggle() {
    const toggle = document.getElementById('nav-toggle');
    const nav = document.getElementById('site-nav');
    if (!toggle || !nav) {
        return;
    }

    toggle.addEventListener('click', function () {
        const isOpen = nav.classList.toggle('is-open');
        toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });
}

function setupPasswordMatchValidation(formId, passwordFieldId, confirmFieldId) {
    const form = document.getElementById(formId);
    if (!form) {
        return;
    }

    const passwordField = form.querySelector('#' + passwordFieldId);
    const confirmField = form.querySelector('#' + confirmFieldId);
    const errorMessage = form.querySelector('#confirm-password-error');

    if (!passwordField || !confirmField) {
        return;
    }

    function checkMatch() {
        const matches = passwordField.value === confirmField.value;
        confirmField.setCustomValidity(matches ? '' : 'Passwords do not match');
        if (errorMessage) {
            errorMessage.hidden = matches || confirmField.value === '';
        }
        return matches;
    }

    passwordField.addEventListener('input', checkMatch);
    confirmField.addEventListener('input', checkMatch);

    form.addEventListener('submit', function (event) {
        if (!checkMatch()) {
            event.preventDefault();
            confirmField.focus();
        }
    });
}
