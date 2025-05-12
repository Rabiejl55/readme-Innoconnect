/**
 * Script de validation des formulaires
 * Version améliorée avec des messages plus conviviaux et explicites
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log("Form-validation.js chargé");
    
    // Ajouter les styles de validation
    addValidationStyles();
    
    // Masquer les messages d'erreur au démarrage
    hideAllErrorMessages();
    
    // Détecter les formulaires de manière plus robuste
    detectAndInitForms();
    
    // Ajouter l'effet de secousse aux champs invalides
    addShakeEffectToForms();
});

function addValidationStyles() {
    const styleElement = document.createElement('style');
    styleElement.textContent = `
        .form-control.is-valid, .form-select.is-valid {
            padding-right: 2.5rem !important;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%2328a745' d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e") !important;
            background-repeat: no-repeat !important;
            background-position: right 0.75rem center !important;
            background-size: 1.25rem 1.25rem !important;
            border-color: #28a745 !important;
        }

        .form-control.is-invalid, .form-select.is-invalid {
            padding-right: 2.5rem !important;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='none' stroke='%23dc3545' viewBox='0 0 12 12'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e") !important;
            background-repeat: no-repeat !important;
            background-position: right 0.75rem center !important;
            background-size: 1.25rem 1.25rem !important;
            border-color: #dc3545 !important;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }

        .shake-error {
            animation: shake 0.5s cubic-bezier(.36,.07,.19,.97) both;
        }

        .invalid-feedback {
            display: none !important;
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        .is-invalid ~ .invalid-feedback {
            display: block !important;
        }
        
        /* Supprimer les messages d'erreur statiques */
        form .invalid-feedback:empty {
            display: none !important;
        }
        
        /* Cacher tous les textes d'erreur au chargement initial */
        .invalid-feedback-static {
            display: none !important;
        }
        
        /* Cacher les coches sur les champs vides */
        textarea.form-control:placeholder-shown {
            background-image: none !important;
        }
    `;
    document.head.appendChild(styleElement);
    
    // Masquer tous les messages d'erreur statiques
    document.addEventListener('DOMContentLoaded', function() {
        // Ajouter une classe spéciale à tous les messages d'erreur statiques
        document.querySelectorAll('.invalid-feedback').forEach(el => {
            if (el.textContent.trim()) {
                el.classList.add('invalid-feedback-static');
                el.style.display = 'none';
            }
        });
    });
}

function detectAndInitForms() {
    // 1. Chercher par ID
    const categoryFormById = document.getElementById('categoryForm');
    const projectFormById = document.getElementById('projectForm');
    
    // 2. Chercher par attributs spécifiques si aucun ID n'est trouvé
    const forms = document.querySelectorAll('form');
    let categoryForm = categoryFormById;
    let projectForm = projectFormById;
    
    if (!categoryForm) {
        // Chercher un formulaire qui contient un champ 'nom' et pas de champ 'titre'
        forms.forEach(form => {
            if (form.querySelector('#nom') && !form.querySelector('#titre')) {
                console.log("Formulaire de catégorie détecté sans ID");
                form.id = 'categoryForm';
                categoryForm = form;
            }
        });
    }
    
    if (!projectForm) {
        // Chercher un formulaire qui contient un champ 'titre'
        forms.forEach(form => {
            if (form.querySelector('#titre')) {
                console.log("Formulaire de projet détecté sans ID");
                form.id = 'projectForm';
                projectForm = form;
            }
        });
    }
    
    // Initialiser les validations
    if (categoryForm) {
        console.log("Initialisation de la validation du formulaire de catégorie");
        initCategoryFormValidation(categoryForm);
    }
    
    if (projectForm) {
        console.log("Initialisation de la validation du formulaire de projet");
        initProjectFormValidation(projectForm);
    }
}

function hideAllErrorMessages() {
    document.querySelectorAll('.invalid-feedback').forEach(feedback => {
        feedback.style.display = 'none';
        feedback.textContent = '';
    });
    
    document.querySelectorAll('.form-control, .form-select').forEach(input => {
        input.classList.remove('is-valid', 'is-invalid');
    });
}

function addShakeEffectToForms() {
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(event) {
            const invalidFields = form.querySelectorAll('.is-invalid');
            if (invalidFields.length > 0) {
                invalidFields.forEach(field => {
                    field.classList.add('shake-error');
                    setTimeout(() => field.classList.remove('shake-error'), 500);
                });
                invalidFields[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    });
}

function initProjectFormValidation(form) {
    if (!form) {
        console.error("Impossible d'initialiser la validation: formulaire de projet non trouvé");
        return;
    }
    
    const titreInput = form.querySelector('#titre');
    const descriptionInput = form.querySelector('#description');
    const categorieSelect = form.querySelector('#id_categorie');
    const montantInput = form.querySelector('#montant_demande');
    const statutSelect = form.querySelector('#statut');
    
    console.log("Éléments du formulaire de projet:", {
        titreInput: titreInput,
        descriptionInput: descriptionInput,
        categorieSelect: categorieSelect,
        montantInput: montantInput,
        statutSelect: statutSelect
    });

    // Validation du titre
    if (titreInput) {
        titreInput.addEventListener('input', function() {
            validateProjectTitle(titreInput);
        });
        
        titreInput.addEventListener('blur', function() {
            validateProjectTitle(titreInput, true);
        });
    }
    
    // Validation de la description
    if (descriptionInput) {
        // Gestion de l'éditeur Quill si présent
        const quillEditor = document.querySelector('.ql-editor');
        if (quillEditor) {
            const quill = Quill.find(quillEditor.parentElement);
            if (quill) {
                const quillContainer = quill.container.querySelector('.ql-container');
                quill.on('text-change', function() {
                    validateProjectDescription(descriptionInput, quill, quillContainer);
                });
                quillEditor.addEventListener('blur', function() {
                    validateProjectDescription(descriptionInput, quill, quillContainer, true);
                });
            }
        } else {
            // Si pas d'éditeur Quill, valider le textarea directement
            descriptionInput.addEventListener('input', function() {
                validateProjectDescription(descriptionInput);
            });
            descriptionInput.addEventListener('blur', function() {
                validateProjectDescription(descriptionInput, null, null, true);
            });
        }
    }
    
    // Validation de la catégorie
    if (categorieSelect) {
        categorieSelect.addEventListener('change', function() {
            validateProjectCategory(categorieSelect);
        });
        categorieSelect.addEventListener('blur', function() {
            validateProjectCategory(categorieSelect);
        });
    }
    
    // Validation du montant
    if (montantInput) {
        montantInput.addEventListener('input', function() {
            validateProjectAmount(montantInput);
        });
        montantInput.addEventListener('blur', function() {
            validateProjectAmount(montantInput, true);
        });
    }
    
    // Validation du statut
    if (statutSelect) {
        statutSelect.addEventListener('change', function() {
            validateProjectStatus(statutSelect);
        });
    }
    
    // Validation à la soumission
    form.addEventListener('submit', function(event) {
        let isValid = true;
        
        // Valider le titre
        if (titreInput && !validateProjectTitle(titreInput, true)) {
            isValid = false;
        }
        
        // Valider la description
        if (descriptionInput) {
            const quill = document.querySelector('.ql-editor') ? Quill.find(document.querySelector('.ql-editor').parentElement) : null;
            const quillContainer = quill ? quill.container.querySelector('.ql-container') : null;
            if (!validateProjectDescription(descriptionInput, quill, quillContainer, true)) {
                isValid = false;
            }
        }
        
        // Valider la catégorie
        if (categorieSelect && !validateProjectCategory(categorieSelect)) {
            isValid = false;
        }
        
        // Valider le statut
        if (statutSelect && !validateProjectStatus(statutSelect)) {
            isValid = false;
        }
        
        // Valider le montant
        if (montantInput && !validateProjectAmount(montantInput, true)) {
            isValid = false;
        }
        
        // Empêcher la soumission si le formulaire n'est pas valide
        if (!isValid) {
            console.log("Soumission bloquée : formulaire de projet invalide");
            event.preventDefault();
            event.stopPropagation();
            
            // Afficher une alerte
            showFormAlert(form, "Veuillez corriger les erreurs dans le formulaire avant de continuer.");
        } else {
            console.log("Formulaire de projet valide, soumission autorisée");
        }
    });
}

function validateProjectTitle(input, complete = false) {
    removeErrorMessage(input);
    const value = input.value.trim();

    if (!value) return showErrorMessage(input, 'Veuillez saisir le titre du projet.');
    if (value.length < 5) return showErrorMessage(input, 'Le titre doit contenir au moins 5 caractères.');
    if (complete) {
        if (value.length > 100) return showErrorMessage(input, 'Le titre ne doit pas dépasser 100 caractères.');
        if (value.split(/\s+/).length < 2) return showErrorMessage(input, 'Le titre doit comporter au moins deux mots.');
        if (/^\d+$/.test(value)) return showErrorMessage(input, 'Le titre ne peut pas contenir uniquement des chiffres.');
    }

    input.classList.add('is-valid');
    return true;
}

function validateProjectDescription(textarea, complete = false) {
    removeErrorMessage(textarea);
    const content = textarea.value.trim();

    if (!content) return showErrorMessage(textarea, 'Veuillez ajouter une description pour le projet.');
    if (content.length < 20) return showErrorMessage(textarea, 'La description doit contenir au moins 20 caractères.');
    if (complete && content.length > 5000) return showErrorMessage(textarea, 'La description ne doit pas dépasser 5000 caractères.');

    textarea.classList.add('is-valid');
    return true;
}

function validateProjectCategory(select) {
    removeErrorMessage(select);
    if (!select.value || select.value === '0') return showErrorMessage(select, 'Merci de choisir une catégorie pour votre projet.');
    select.classList.add('is-valid');
    return true;
}

function validateProjectAmount(input, complete = false) {
    removeErrorMessage(input);
    const raw = input.value.trim().replace(',', '.');
    const montant = parseFloat(raw);

    if (!raw) return showErrorMessage(input, 'Veuillez indiquer un montant demandé.');
    if (isNaN(montant)) return showErrorMessage(input, 'Le montant doit être un nombre valide.');
    if (montant <= 0) return showErrorMessage(input, 'Le montant doit être supérieur à 0.');

    if (complete) {
        if (Math.round(montant * 100) / 100 !== montant) {
            return showErrorMessage(input, "Le montant ne peut avoir que 2 décimales maximum");
        }
        
        if (montant > 10000000) {
            return showErrorMessage(input, "Le montant maximum autorisé est de 10 000 000 €");
        }
        
        if (montant < 100) {
            return showErrorMessage(input, "Le montant minimum pour un projet est de 100 €");
        }
    }

    input.classList.add('is-valid');
    return true;
}

function showErrorMessage(input, message) {
    input.classList.remove('is-valid');
    input.classList.add('is-invalid');
    let feedback = input.nextElementSibling;
    if (!feedback || !feedback.classList.contains('invalid-feedback')) {
        feedback = document.createElement('div');
        feedback.className = 'invalid-feedback';
        input.parentNode.insertBefore(feedback, input.nextSibling);
    }
    feedback.textContent = message;
    feedback.style.display = 'block';
    return false;
}

function removeErrorMessage(input) {
    input.classList.remove('is-valid', 'is-invalid');
    const feedback = input.nextElementSibling;
    if (feedback && feedback.classList.contains('invalid-feedback')) {
        feedback.textContent = '';
        feedback.style.display = 'none';
    }
}

function initCategoryFormValidation(form) {
    if (!form) {
        console.error("Impossible d'initialiser la validation: formulaire non trouvé");
        return;
    }
    
    const nomInput = form.querySelector('#nom');
    const descriptionInput = form.querySelector('#description');
    
    console.log("Éléments du formulaire:", {
        nomInput: nomInput,
        descriptionInput: descriptionInput
    });
    
    // Validation du nom
    if (nomInput) {
        nomInput.addEventListener('input', function() {
            validateCategoryName(nomInput);
        });
        
        nomInput.addEventListener('blur', function() {
            validateCategoryName(nomInput, true);
        });
    }
    
    // Validation de la description (facultative)
    if (descriptionInput) {
        descriptionInput.addEventListener('input', function() {
            validateOptionalDescription(descriptionInput);
        });
        
        descriptionInput.addEventListener('blur', function() {
            validateOptionalDescription(descriptionInput);
        });
    }
    
    // Validation lors de la soumission
    form.addEventListener('submit', function(event) {
        let isValid = true;
        
        // Valider le nom
        if (nomInput && !validateCategoryName(nomInput, true)) {
            isValid = false;
        }
        
        // Valider la description si elle est remplie
        if (descriptionInput && descriptionInput.value.trim() !== '' && !validateOptionalDescription(descriptionInput)) {
            isValid = false;
        }
        
        // Si le formulaire n'est pas valide, empêcher la soumission
        if (!isValid) {
            console.log("Soumission bloquée : formulaire invalide");
            event.preventDefault();
            event.stopPropagation();
            
            // Afficher une alerte
            showFormAlert(form, "Veuillez corriger les erreurs dans le formulaire avant de continuer.");
        } else {
            console.log("Formulaire valide, soumission autorisée");
        }
    });
}

function validateCategoryName(input, complete = false) {
    removeErrorMessage(input);
    
    if (!input.value.trim()) {
        return showErrorMessage(input, 'Le nom de la catégorie est obligatoire');
    }
    
    if (input.value.trim().length < 3) {
        return showErrorMessage(input, 'Le nom doit contenir au moins 3 caractères');
    }
    
    if (complete) {
        if (input.value.trim().length > 50) {
            return showErrorMessage(input, 'Le nom ne doit pas dépasser 50 caractères');
        }
        
        const regex = /^[a-zA-Z0-9àáâäãåąčćęèéêëėįìíîïłńòóôöõøùúûüųūÿýżźñçšžÀÁÂÄÃÅĄĆČĖĘÈÉÊËÌÍÎÏĮŁŃÒÓÔÖÕØÙÚÛÜŲŪŸÝŻŹÑßÇŒÆŠŽ\s\-_&]+$/;
        if (!regex.test(input.value.trim())) {
            return showErrorMessage(input, 'Le nom contient des caractères non autorisés');
        }
    }
    
    input.classList.add('is-valid');
    return true;
}

function validateOptionalDescription(textarea) {
    removeErrorMessage(textarea);
    
    if (!textarea.value.trim()) {
        return true;
    }
    
    if (textarea.value.trim().length < 10) {
        return showErrorMessage(textarea, 'Si fournie, la description doit contenir au moins 10 caractères');
    }
    
    textarea.classList.add('is-valid');
    return true;
}

/**
 * Affiche une alerte en haut du formulaire
 * @param {HTMLFormElement} form - Le formulaire
 * @param {string} message - Message à afficher
 */
function showFormAlert(form, message) {
    // Vérifier si une alerte existe déjà
    let alertElement = form.querySelector('.form-alert');
    
    if (!alertElement) {
        // Créer l'élément d'alerte
        alertElement = document.createElement('div');
        alertElement.className = 'alert alert-danger form-alert mb-4';
        alertElement.role = 'alert';
        
        // Ajouter un bouton de fermeture
        const closeButton = document.createElement('button');
        closeButton.type = 'button';
        closeButton.className = 'btn-close';
        closeButton.setAttribute('data-bs-dismiss', 'alert');
        closeButton.setAttribute('aria-label', 'Close');
        
        // Ajouter l'icône d'avertissement
        const icon = document.createElement('i');
        icon.className = 'bi bi-exclamation-triangle-fill me-2';
        
        // Créer le conteneur de texte
        const textSpan = document.createElement('span');
        
        // Assembler l'alerte
        alertElement.appendChild(icon);
        alertElement.appendChild(textSpan);
        alertElement.appendChild(closeButton);
        
        // Insérer l'alerte au début du formulaire
        form.insertBefore(alertElement, form.firstChild);
    }
    
    // Mettre à jour le message
    const textSpan = alertElement.querySelector('span');
    if (textSpan) {
        textSpan.textContent = message;
    }
    
    // S'assurer que l'alerte est visible
    alertElement.style.display = 'block';
    
    // Faire défiler jusqu'à l'alerte
    alertElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

/**
 * Form validation script pour inoconnect
 * Gère la validation des formulaires de projet
 */

document.addEventListener('DOMContentLoaded', function() {
  // Récupérer le formulaire
  const form = document.getElementById('projectForm');
  
  if (form) {
    // Messages d'erreur personnalisés
    const errorMessages = {
      titre: 'Le titre du projet est obligatoire',
      description: 'La description du projet est obligatoire',
      id_categorie: 'Veuillez sélectionner une catégorie',
      montant_demande: 'Veuillez indiquer un montant valide (minimum 0)'
    };
    
    // Règles de validation
    const validators = {
      titre: (value) => value.trim().length > 0,
      description: (value) => value.trim().length > 0,
      id_categorie: (value) => value !== '' && value !== null,
      montant_demande: (value) => !isNaN(parseFloat(value)) && parseFloat(value) >= 0
    };
    
    // Fonction pour montrer l'erreur
    function showError(inputElement, message) {
      inputElement.classList.add('is-invalid');
      inputElement.classList.remove('is-valid');
      
      const feedbackElement = inputElement.nextElementSibling;
      if (feedbackElement && feedbackElement.classList.contains('invalid-feedback')) {
        feedbackElement.textContent = message;
        feedbackElement.style.display = 'block';
      }
    }
    
    // Fonction pour cacher l'erreur
    function hideError(inputElement) {
      inputElement.classList.remove('is-invalid');
      inputElement.classList.add('is-valid');
      
      const feedbackElement = inputElement.nextElementSibling;
      if (feedbackElement && feedbackElement.classList.contains('invalid-feedback')) {
        feedbackElement.textContent = '';
        feedbackElement.style.display = 'none';
      }
    }
    
    // Fonction pour valider un champ individuel
    function validateField(inputElement) {
      const fieldName = inputElement.id;
      const fieldValue = inputElement.value;
      
      if (validators[fieldName] && !validators[fieldName](fieldValue)) {
        showError(inputElement, errorMessages[fieldName]);
        return false;
      } else {
        hideError(inputElement);
        return true;
      }
    }
    
    // Attacher les événements de validation à chaque champ
    Object.keys(validators).forEach(fieldName => {
      const inputElement = document.getElementById(fieldName);
      if (inputElement) {
        // Valider lorsque le champ perd le focus
        inputElement.addEventListener('blur', function() {
          validateField(this);
        });
        
        // Valider à la modification pour les champs select
        if (inputElement.tagName === 'SELECT') {
          inputElement.addEventListener('change', function() {
            validateField(this);
          });
        }
        
        // Pour les champs text/textarea, valider aussi pendant la saisie
        if (inputElement.tagName === 'INPUT' || inputElement.tagName === 'TEXTAREA') {
          inputElement.addEventListener('input', function() {
            validateField(this);
          });
        }
      }
    });
    
    // Empêcher la soumission si le formulaire n'est pas valide
    form.addEventListener('submit', function(event) {
      let isValid = true;
      
      // Valider chaque champ
      Object.keys(validators).forEach(fieldName => {
        const inputElement = document.getElementById(fieldName);
        if (inputElement) {
          if (!validateField(inputElement)) {
            isValid = false;
          }
        }
      });
      
      // Si le formulaire n'est pas valide, empêcher la soumission
      if (!isValid) {
        event.preventDefault();
        event.stopPropagation();
        
        // Faire défiler jusqu'au premier champ en erreur
        const firstInvalidField = form.querySelector('.is-invalid');
        if (firstInvalidField) {
          firstInvalidField.focus();
          // Scroll en douceur jusqu'au champ
          firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
      }
    });
  }
});
