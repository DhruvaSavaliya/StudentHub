(() => {
    'use strict';

    const form = document.querySelector('#registrationForm');
    if (!form) return;

    const password = form.querySelector('#password');
    const confirmation = form.querySelector('#password_confirm');
    const fields = [...form.querySelectorAll('input:not([type="hidden"])')];

    const updatePasswordMatch = () => {
        confirmation.setCustomValidity(
            confirmation.value && confirmation.value !== password.value
                ? 'The passwords do not match.'
                : ''
        );
    };

    const showFieldValidity = (field) => {
        const invalid = !field.validity.valid;
        field.setAttribute('aria-invalid', String(invalid));
        const message = document.querySelector(`#${field.id}Error`);
        if (message) message.textContent = invalid ? field.validationMessage : '';
    };

    fields.forEach((field) => {
        field.addEventListener('input', () => {
            if (field === password || field === confirmation) updatePasswordMatch();
            if (field.getAttribute('aria-invalid') === 'true') showFieldValidity(field);
        });
        field.addEventListener('blur', () => {
            if (field === password || field === confirmation) updatePasswordMatch();
            if (field.value !== '') showFieldValidity(field);
        });
    });

    form.addEventListener('submit', (event) => {
        updatePasswordMatch();
        const firstInvalid = fields.find((field) => !field.validity.valid);
        if (firstInvalid) {
            event.preventDefault();
            fields.forEach(showFieldValidity);
            firstInvalid.focus();
        }
    });
})();
