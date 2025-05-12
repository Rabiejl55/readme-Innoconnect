<?php
// Vérifier si une session est active, sinon en démarrer une
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Créer un Projet - inoconnect (Version Simple)</title>
  
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
  
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      padding-top: 80px;
      background-color: #f8f9fa;
    }
    .card {
      border-radius: 10px;
      box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }
    .card-header {
      background-color: #6f42c1;
      color: white;
      font-weight: bold;
    }
    .btn-primary {
      background-color: #6f42c1;
      border-color: #6f42c1;
    }
    .btn-primary:hover {
      background-color: #5a32a3;
      border-color: #5a32a3;
    }
    .invalid-feedback {
      display: none;
      color: #dc3545;
      font-size: 0.875rem;
      margin-top: 0.25rem;
    }
    .form-control.is-invalid ~ .invalid-feedback,
    .form-select.is-invalid ~ .invalid-feedback {
      display: block;
    }
    @keyframes shake {
      0%, 100% { transform: translateX(0); }
      10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
      20%, 40%, 60%, 80% { transform: translateX(5px); }
    }
    .shake-error {
      animation: shake 0.5s cubic-bezier(.36,.07,.19,.97) both;
    }
    .btn-ai {
      background-color: #34a853;
      border-color: #34a853;
      color: white;
    }
    .btn-ai:hover {
      background-color: #2d8e47;
      border-color: #2d8e47;
      color: white;
    }
    .spinner-border {
      width: 1rem;
      height: 1rem;
      margin-right: 0.5rem;
    }
  </style>
</head>

<body>
  <header class="fixed-top bg-white shadow-sm">
    <div class="container d-flex justify-content-between align-items-center py-3">
      <h1 class="h4 mb-0">inoconnect - Gestion de Projets</h1>
      <nav>
        <a href="index.php?controller=project&action=index" class="btn btn-outline-secondary">
          <i class="bi bi-arrow-left"></i> Retour à la liste
        </a>
      </nav>
    </div>
  </header>

  <main>
    <div class="container py-4">
      <!-- Notifications -->
      <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <?= $_SESSION['error'] ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
      <?php endif; ?>
      
      <div id="form-alert-container"></div>
      
      <div class="row justify-content-center">
        <div class="col-lg-8">
          <div class="card">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
              <h2 class="mb-0 h5"><i class="bi bi-plus-circle me-2"></i>Nouveau Projet (Version Simple)</h2>
              <button type="button" id="autoFillBtn" class="btn btn-ai">
                <span id="autoFillSpinner" class="spinner-border d-none" role="status"></span>
                <i class="bi bi-magic"></i> Remplir automatiquement
              </button>
            </div>
            <div class="card-body p-4">
              <form action="index.php?controller=project&action=store" method="POST" id="projectForm" class="needs-validation" novalidate>
                <div class="mb-3">
                  <label for="titre" class="form-label">Titre du projet <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="titre" name="titre">
                  <div class="invalid-feedback">Le titre du projet est obligatoire</div>
                </div>
                
                <div class="mb-3">
                  <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                  <textarea class="form-control" id="description" name="description" rows="5"></textarea>
                  <div class="invalid-feedback">La description du projet est obligatoire</div>
                </div>
                
                <div class="mb-3">
                  <label for="id_categorie" class="form-label">Catégorie <span class="text-danger">*</span></label>
                  <select class="form-select" id="id_categorie" name="id_categorie">
                    <option value="" selected disabled>Choisir une catégorie</option>
                    <?php foreach ($categories as $category): ?>
                      <option value="<?= $category['id_categorie'] ?>"><?= htmlspecialchars($category['nom']) ?></option>
                    <?php endforeach; ?>
                  </select>
                  <div class="invalid-feedback">Veuillez sélectionner une catégorie</div>
                </div>
                
                <div class="mb-3">
                  <label for="montant_demande" class="form-label">Montant demandé (€) <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <span class="input-group-text">€</span>
                    <input type="number" class="form-control" id="montant_demande" name="montant_demande" min="0" step="100">
                    <div class="invalid-feedback">Veuillez entrer un montant valide</div>
                  </div>
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                  <a href="index.php?controller=project&action=index" class="btn btn-secondary">
                    <i class="bi bi-x"></i> Annuler
                  </a>
                  <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg"></i> Enregistrer
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <footer class="bg-light text-center py-4 mt-5">
    <div class="container">
      <p class="mb-0">© 2024 inoconnect - Tous droits réservés</p>
    </div>
  </footer>

  <!-- Bootstrap JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
  
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const form = document.getElementById('projectForm');
      const titreInput = document.getElementById('titre');
      const descriptionInput = document.getElementById('description');
      const categorieSelect = document.getElementById('id_categorie');
      const montantInput = document.getElementById('montant_demande');
      const alertContainer = document.getElementById('form-alert-container');
      const autoFillBtn = document.getElementById('autoFillBtn');
      const autoFillSpinner = document.getElementById('autoFillSpinner');
      
      // Gemini API Key
      const GEMINI_API_KEY = 'AIzaSyCPFdtJ70422zFKK_6_DaXQOgeJBCDEiRM';
      
      // Function to autofill the form using Gemini API
      async function autofillFormWithAI() {
        try {
          // Show loading spinner
          autoFillBtn.disabled = true;
          autoFillSpinner.classList.remove('d-none');
          
          // Get available categories for context
          const categoryOptions = Array.from(categorieSelect.options)
            .filter(option => option.value) // Skip the placeholder option
            .map(option => option.text);
          
          // Create a timestamp to ensure uniqueness for each request
          const timestamp = new Date().getTime();
          
          // List of diverse domains to choose from randomly
          const domains = [
            "technologie", "santé", "éducation", "environnement", "agriculture", 
            "transport", "énergie", "finance", "art", "divertissement", 
            "urbanisme", "social", "tourisme", "sport", "alimentation"
          ];
          
          // Randomly select 1-3 domains for inspiration
          const shuffledDomains = domains.sort(() => 0.5 - Math.random());
          const selectedDomains = shuffledDomains.slice(0, Math.floor(Math.random() * 3) + 1).join(', ');
          
          const prompt = `Génère un projet innovant et COMPLÈTEMENT UNIQUE dans l'un de ces domaines: ${selectedDomains}. 
          
          Ce projet doit être une proposition de financement avec les informations suivantes au format JSON:
          1. Un titre de projet innovant, créatif et accrocheur (champ "titre") - sois très spécifique et unique
          2. Une description détaillée du projet (champ "description") qui explique:
             - Le problème concret que ce projet résout
             - La solution technique ou méthodologique proposée
             - Les bénéfices attendus pour les utilisateurs
             - L'impact potentiel sur la société ou l'environnement
          3. Un montant financier réaliste entre 1000 et 100000 (champ "montant")
          4. Choisis une catégorie parmi celles-ci: ${categoryOptions.join(', ')} (champ "categorie")
          
          IMPORTANT: Ce projet doit être DIFFÉRENT de toute idée conventionnelle. Sois créatif et propose quelque chose de vraiment novateur que personne n'a encore imaginé. Assure-toi que ce n'est pas un concept générique. Timestamp: ${timestamp}
          
          Réponds uniquement avec le JSON sans explications supplémentaires.`;
          
          // Make API call to Gemini
          const response = await fetch(`https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=${GEMINI_API_KEY}`, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({
              contents: [{
                parts: [{
                  text: prompt
                }]
              }],
              generationConfig: {
                temperature: 1.2,
                maxOutputTokens: 800,
                topK: 40,
                topP: 0.95
              }
            })
          });
          
          if (!response.ok) {
            throw new Error(`API request failed with status ${response.status}`);
          }
          
          const data = await response.json();
          
          // Extract the text from the response
          const generatedText = data.candidates[0].content.parts[0].text;
          
          // Parse the JSON from the response
          // Remove any markdown formatting if present
          const cleanedText = generatedText.replace(/```json|```/g, '').trim();
          const projectData = JSON.parse(cleanedText);
          
          // Fill form fields
          titreInput.value = projectData.titre;
          descriptionInput.value = projectData.description;
          montantInput.value = Math.round(parseFloat(projectData.montant));
          
          // Find and select the category
          const categoryToSelect = Array.from(categorieSelect.options)
            .find(option => option.text.toLowerCase() === projectData.categorie.toLowerCase());
          
          if (categoryToSelect) {
            categorieSelect.value = categoryToSelect.value;
          } else {
            // If exact match not found, select first category
            const firstCategory = Array.from(categorieSelect.options).find(option => option.value);
            if (firstCategory) categorieSelect.value = firstCategory.value;
          }
          
          // Trigger validation
          validateTitle();
          validateDescription();
          validateCategory();
          validateAmount();
          
          // Show success message
          alertContainer.innerHTML = `
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
              <i class="bi bi-check-circle-fill me-2"></i>
              <span>Le formulaire a été rempli automatiquement avec succès !</span>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          `;
          
        } catch (error) {
          console.error('Error autofilling form:', error);
          
          // Show error message
          alertContainer.innerHTML = `
            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
              <i class="bi bi-exclamation-triangle-fill me-2"></i>
              <span>Erreur lors du remplissage automatique : ${error.message}</span>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          `;
        } finally {
          // Hide loading spinner
          autoFillBtn.disabled = false;
          autoFillSpinner.classList.add('d-none');
        }
      }
      
      // Add click event listener to the autofill button
      autoFillBtn.addEventListener('click', autofillFormWithAI);
      
      // Validation functions
      function validateTitle() {
        if (!titreInput.value.trim()) {
          titreInput.classList.add('is-invalid');
          return false;
        } else {
          titreInput.classList.remove('is-invalid');
          titreInput.classList.add('is-valid');
          return true;
        }
      }
      
      function validateDescription() {
        if (!descriptionInput.value.trim()) {
          descriptionInput.classList.add('is-invalid');
          return false;
        } else {
          descriptionInput.classList.remove('is-invalid');
          descriptionInput.classList.add('is-valid');
          return true;
        }
      }
      
      function validateCategory() {
        if (!categorieSelect.value) {
          categorieSelect.classList.add('is-invalid');
          return false;
        } else {
          categorieSelect.classList.remove('is-invalid');
          categorieSelect.classList.add('is-valid');
          return true;
        }
      }
      
      function validateAmount() {
        const amount = montantInput.value.trim();
        if (!amount || isNaN(amount) || parseFloat(amount) < 0) {
          montantInput.classList.add('is-invalid');
          return false;
        } else {
          montantInput.classList.remove('is-invalid');
          montantInput.classList.add('is-valid');
          return true;
        }
      }
      
      // Show form alert
      function showFormAlert(message) {
        alertContainer.innerHTML = `
          <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <span>${message}</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        `;
        
        // Scroll to the alert
        alertContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
      
      // Add event listeners for input fields
      titreInput.addEventListener('input', validateTitle);
      titreInput.addEventListener('blur', validateTitle);
      
      descriptionInput.addEventListener('input', validateDescription);
      descriptionInput.addEventListener('blur', validateDescription);
      
      categorieSelect.addEventListener('change', validateCategory);
      categorieSelect.addEventListener('blur', validateCategory);
      
      montantInput.addEventListener('input', validateAmount);
      montantInput.addEventListener('blur', validateAmount);
      
      // Form submission
      form.addEventListener('submit', function(event) {
        const isTitleValid = validateTitle();
        const isDescriptionValid = validateDescription();
        const isCategoryValid = validateCategory();
        const isAmountValid = validateAmount();
        
        if (!isTitleValid || !isDescriptionValid || !isCategoryValid || !isAmountValid) {
          event.preventDefault();
          event.stopPropagation();
          
          // Add shake effect to invalid fields
          const invalidFields = form.querySelectorAll('.is-invalid');
          invalidFields.forEach(field => {
            field.classList.add('shake-error');
            setTimeout(() => field.classList.remove('shake-error'), 500);
          });
          
          // Show alert message
          showFormAlert('Veuillez corriger les erreurs dans le formulaire avant de continuer.');
          
          // Scroll to first invalid field
          if (invalidFields.length > 0) {
            invalidFields[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
          }
        }
      });
    });
  </script>
</body>
</html> 