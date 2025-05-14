function validateField(value, fieldName, minLength, maxLength, errorDiv) {
    value = value.trim();
    let error = '';

    if (!value) {
        error = `${fieldName} cannot be empty.`;
    } else if (value.length < minLength) {
        error = `${fieldName} must be at least ${minLength} characters.`;
    } else if (value.length > maxLength) {
        error = `${fieldName} must be ${maxLength} characters or less.`;
    } else if (!/^[A-Za-z0-9\s]+$/.test(value)) {
        error = `${fieldName} can only contain letters, numbers, and spaces.`;
    }

    errorDiv.textContent = error;
    errorDiv.classList.toggle('show', !!error);
    return !error;
}

function validateCreateForm(form) {
    const titreInput = form.querySelector('#titre');
    const messageInput = form.querySelector('#message');
    const categoryInput = form.querySelector('#category');

    const titreError = form.querySelector(`#${titreInput.dataset.errorId}`);
    const messageError = form.querySelector(`#${messageInput.dataset.errorId}`);
    const categoryError = form.querySelector(`#${categoryInput.dataset.errorId}`);

    // Clear previous errors
    titreError.textContent = '';
    messageError.textContent = '';
    categoryError.textContent = '';
    titreError.classList.remove('show');
    messageError.classList.remove('show');
    categoryError.classList.remove('show');

    // Validate each field
    const isTitreValid = validateField(titreInput.value, 'Title', 3, 50, titreError);
    const isMessageValid = validateField(messageInput.value, 'Message', 10, 500, messageError);
    const isCategoryValid = validateField(categoryInput.value, 'Category', 3, 50, categoryError);

    // Return false if any field is invalid to prevent submission
    return isTitreValid && isMessageValid && isCategoryValid;
}

function validateEditForm(form, forumId) {
    const titreInput = form.querySelector(`#edit_titre_${forumId}`);
    const messageInput = form.querySelector(`#edit_message_${forumId}`);
    const categoryInput = form.querySelector(`#edit_category_${forumId}`);

    const titreError = form.querySelector(`#${titreInput.dataset.errorId}`);
    const messageError = form.querySelector(`#${messageInput.dataset.errorId}`);
    const categoryError = form.querySelector(`#${categoryInput.dataset.errorId}`);

    // Clear previous errors
    titreError.textContent = '';
    messageError.textContent = '';
    categoryError.textContent = '';
    titreError.classList.remove('show');
    messageError.classList.remove('show');
    categoryError.classList.remove('show');

    // Validate each field
    const isTitreValid = validateField(titreInput.value, 'Title', 3, 50, titreError);
    const isMessageValid = validateField(messageInput.value, 'Message', 10, 500, messageError);
    const isCategoryValid = validateField(categoryInput.value, 'Category', 3, 50, categoryError);

    // Return false if any field is invalid to prevent submission
    return isTitreValid && isMessageValid && isCategoryValid;
}