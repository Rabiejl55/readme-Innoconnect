/**
 * Form validation script for Techie Admin
 */
$(document).ready(function() {
  // Masquer tous les messages d'erreur au démarrage
  $(".error-message").hide();
  
  // Ajouter un message d'alerte en haut du formulaire
  $("form").prepend('<div class="alert alert-danger mb-4" id="form-error-message" style="display: none;">Tous les champs sont obligatoires</div>');
  
  // Validation pour le formulaire d'ajout de projet
  $("#projectForm").on("submit", function(e) {
    if (!validateForm($(this))) {
      e.preventDefault();
      e.stopPropagation();
      $("#form-error-message").show();
    } else {
      $("#form-error-message").hide();
    }
  });

  // Validation pour le formulaire d'édition de projet
  $("#projectEditForm").on("submit", function(e) {
    if (!validateForm($(this))) {
      e.preventDefault();
      e.stopPropagation();
      $("#form-error-message").show();
    } else {
      $("#form-error-message").hide();
    }
  });

  // Validation pour le formulaire d'ajout de catégorie
  $("#categoryForm").on("submit", function(e) {
    if (!validateForm($(this))) {
      e.preventDefault();
      e.stopPropagation();
      $("#form-error-message").show();
    } else {
      $("#form-error-message").hide();
    }
  });

  // Validation pour le formulaire d'édition de catégorie
  $("#categoryEditForm").on("submit", function(e) {
    if (!validateForm($(this))) {
      e.preventDefault();
      e.stopPropagation();
      $("#form-error-message").show();
    } else {
      $("#form-error-message").hide();
    }
  });

  // Fonction de validation des champs
  function validateForm(form) {
    let isValid = true;
    
    // Masquer tous les messages d'erreur
    form.find(".error-message").hide();
    
    // Valider les champs requis
    form.find(".validation-required").each(function() {
      const field = $(this);
      let fieldValue = field.val();
      
      // Vérifier si le champ est vide
      if (!fieldValue || fieldValue.trim() === "") {
        field.next(".error-message").show();
        isValid = false;
      }
    });

    // Validation supplémentaire pour les champs numériques
    form.find(".validation-number").each(function() {
      const field = $(this);
      const fieldValue = field.val();
      
      if (fieldValue && !isValidNumber(fieldValue)) {
        field.siblings(".error-message-number").show();
        isValid = false;
      }
    });
    
    return isValid;
  }

  // Fonction pour valider les nombres
  function isValidNumber(value) {
    return !isNaN(parseFloat(value)) && value > 0;
  }

  // Validation en temps réel
  $(".validation-required").on("input change", function() {
    const field = $(this);
    const fieldValue = field.val();
    
    if (!fieldValue || fieldValue.trim() === "") {
      field.next(".error-message").show();
    } else {
      field.next(".error-message").hide();
      // Masquer le message global d'erreur si tous les champs sont remplis
      if ($(this).closest("form").find(".error-message:visible").length === 0) {
        $("#form-error-message").hide();
      }
    }
  });
  
  // Validation en temps réel pour les nombres
  $(".validation-number").on("input change", function() {
    const field = $(this);
    const fieldValue = field.val();
    
    if (fieldValue && !isValidNumber(fieldValue)) {
      field.siblings(".error-message-number").show();
    } else {
      field.siblings(".error-message-number").hide();
    }
  });
}); 