(() => {
  "use strict";
  const form = document.querySelector("#registerForm");
  if (!form) return;
  const fields = ["name", "email", "mobile", "password", "confirmPassword", "course", "year"];
  const inputs = Object.fromEntries(fields.map(id => [id, form.elements.namedItem(id)]));
  const terms = form.elements.namedItem("terms");
  const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
  const mobilePattern = /^\+?[0-9\s()-]{8,16}$/;
  const errors = {
    name: value => /^[\p{L}][\p{L}\p{M} .'’-]{1,59}$/u.test(value) ? "" : "Enter your first and last name.",
    email: value => emailPattern.test(value) ? "" : "Enter a valid email address, such as name@example.edu.",
    mobile: value => mobilePattern.test(value) && value.replace(/\D/g, "").length >= 10 && value.replace(/\D/g, "").length <= 15 ? "" : "Enter a mobile number with 10 to 15 digits.",
    password: value => value.length >= 8 && /[A-Za-z]/.test(value) && /\d/.test(value) ? "" : "Use at least 8 characters, including a letter and a number.",
    confirmPassword: value => value && value === inputs.password.value ? "" : "Enter the same password again.",
    course: value => value ? "" : "Choose your course.",
    year: value => value ? "" : "Choose your year of study."
  };
  function validate(id) {
    const input = inputs[id];
    const message = errors[id](input.value.trim());
    document.querySelector(`#${id}Error`).textContent = message;
    input.setAttribute("aria-invalid", String(Boolean(message)));
    return !message;
  }
  function validateGender() {
    const valid = Boolean(form.querySelector('input[name="gender"]:checked'));
    document.querySelector("#genderError").textContent = valid ? "" : "Choose an option.";
    form.querySelectorAll('input[name="gender"]').forEach(input => input.setAttribute("aria-invalid", String(!valid)));
    return valid;
  }
  function validateTerms() {
    const valid = terms.checked;
    document.querySelector("#termsError").textContent = valid ? "" : "Accept the terms to continue.";
    terms.setAttribute("aria-invalid", String(!valid));
    return valid;
  }
  function updateStrength() {
    const value = inputs.password.value;
    const score = [value.length >= 8, /[a-z]/.test(value) && /[A-Z]/.test(value), /\d/.test(value), /[^A-Za-z0-9]/.test(value)].filter(Boolean).length;
    document.querySelector("#passwordStrength").textContent = value ? `Password strength: ${["weak", "fair", "good", "strong"][Math.max(0, score - 1)]}.` : "Use a mix of letters, numbers, and symbols for a stronger password.";
  }
  fields.forEach(id => {
    inputs[id].addEventListener(id === "course" || id === "year" ? "change" : "input", () => {
      validate(id);
      if (id === "password") { updateStrength(); if (inputs.confirmPassword.value) validate("confirmPassword"); }
      document.querySelector("#formMessage").textContent = "";
    });
  });
  form.querySelectorAll('input[name="gender"]').forEach(input => input.addEventListener("change", validateGender));
  terms.addEventListener("change", validateTerms);
  updateStrength();
  form.addEventListener("submit", event => {
    event.preventDefault();
    document.querySelector("#formMessage").textContent = "";
    const validFields = fields.map(validate).every(Boolean);
    const validGender = validateGender();
    const validTerms = validateTerms();
    const valid = validFields && validGender && validTerms;
    if (!valid) {
      document.querySelector("#formMessage").textContent = "Please correct the highlighted fields and try again.";
      form.querySelector('[aria-invalid="true"]')?.focus();
      return;
    }
    document.querySelector("#formMessage").textContent = "Registration form is valid. No account was created because this practical has no backend.";
    form.reset();
    form.querySelectorAll("[aria-invalid]").forEach(input => input.removeAttribute("aria-invalid"));
    fields.forEach(id => { const error = document.querySelector(`#${id}Error`); if (error) error.textContent = ""; });
    document.querySelector("#genderError").textContent = "";
    document.querySelector("#termsError").textContent = "";
    updateStrength();
  });
})();
