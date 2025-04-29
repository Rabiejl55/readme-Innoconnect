<?php
require_once '../../Model/Contrat.php';
require_once '../../Controller/ContratController.php';

$successMessages = [];
$errorMessages = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $innovateurSignaturePath = null;
        if (!empty($_POST['signature_innovateur'])) {
            $data = str_replace('data:image/png;base64,', '', $_POST['signature_innovateur']);
            $data = str_replace(' ', '+', $data);
            $binaryData = base64_decode($data);

            if (!is_dir('uploads')) {
                mkdir('uploads', 0777, true);
            }

            $innovateurSignaturePath = 'uploads/innovateur_' . uniqid() . '.png';
            file_put_contents($innovateurSignaturePath, $binaryData);
        }

        // No investisseur signature handling here unless needed

        $contrat = new Contrat();
        $contrat->setInnovateurNom($_POST['nom_innovateur']);
        $contrat->setInnovateurId($_POST['id_innovateur']);
        $contrat->setInnovateurEmail($_POST['email_innovateur']);
        $contrat->setInnovateurSignature($innovateurSignaturePath);

        $contrat->setMontant($_POST['montant_innovateur']);
        $contrat->setDateSignature($_POST['date_signature']);
        $contrat->setStatut($_POST['statut']);
        $contrat->setProjetNom('Projet Inconnu');
        $contrat->setTypeFinancement('Financement Inconnu'); 
        $contrat->setInvestisseurId(null);
        $contrat->setInvestisseurNom(null);
        $contrat->setInvestisseurEmail(null);
        $contrat->setInvestisseurSignature(null);

        $contratC = new ContratController();
        $id = $contratC->addContrat($contrat);

      // Success message
      $successMessages[] = "Contrat successfully added with ID: " . htmlspecialchars($id);
        
    } catch (Exception $e) {
        // Error message
        $errorMessages[] = "An error occurred: " . htmlspecialchars($e->getMessage());
    }
} 
?>



<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="UTF-8" />
    <title>Contrat d'Investissement - Signature Innovateur</title>
    <style>
      /* ▼▼▼ CSS DU HEADER DU PREMIER CODE ▼▼▼ */
      :root {
        --primary-color: #6f42c1;
        --secondary-color: #6610f2;
        --accent-blue: #094f88;
        --text-dark: #2c3e50;
        --text-light: #6c757d;
        --background-light: #e3f2fd;
        --white: #ffffff;
        --gradient-purple: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        --shadow-sm: 0 2px 10px rgba(0, 0, 0, 0.1);
        --shadow-md: 0 5px 15px rgba(0, 0, 0, 0.1);
        --transition: all 0.3s ease;
      }

      .header {
        background: var(--gradient-purple);
        padding: 1rem 0;
        position: fixed;
        width: 100%;
        top: 0;
        z-index: 1000;
        box-shadow: var(--shadow-sm);
      }

      .header-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 1.25rem;
      }

      .logo {
        color: var(--white);
        font-size: 1.5rem;
        font-weight: 700;
        text-decoration: none;
      }

      .btn {
        background: var(--white);
        color: var(--primary-color);
        padding: 0.625rem 1.5rem;
        border-radius: 2rem;
        font-weight: 500;
        text-decoration: none;
        transition: var(--transition);
        margin-left: 1rem;
      }

      .btn:hover {
        background: var(--primary-color);
        color: var(--white);
      }

      .active {
        background: var(--primary-color);
        color: var(--white);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      }

      @media (max-width: 768px) {
        .header-container {
          flex-direction: column;
          gap: 1rem;
          padding: 1rem;
        }
        
        .btn {
          margin: 0.25rem;
          padding: 0.5rem 1rem;
        }
      }
      /* ▲▲▲ FIN DU HEADER ▲▲▲ */

      /* ▼▼▼ CSS ORIGINAL DU DEUXIÈME CODE ▼▼▼ */
      * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
      }

      body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f8f9fa;
        color: #333;
      }

      .container {
        background-color: #fff;
        padding: 30px 40px;
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        max-width: 800px;
        margin: 120px auto 60px;
      }

      h1, h2 {
        color: #5e72e4;
      }

      .info {
        margin-bottom: 25px;
      }

      .info p {
        margin: 6px 0;
        font-size: 16px;
      }

      .signature-container {
        margin-top: 30px;
      }

      .signature-pad {
        border: 2px solid #5e72e4;
        border-radius: 12px;
        width: 100%;
        height: 200px;
        background-color: #ffffff;
        box-shadow: 0 4px 12px rgba(94, 114, 228, 0.2);
      }

      .signature-buttons {
        margin-top: 15px;
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
      }

      button {
        padding: 10px 18px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-weight: bold;
        font-size: 14px;
      }

      .clear-btn {
        background-color: #f5365c;
        color: white;
      }

      .download-btn {
        background-color: #11cdef;
        color: white;
      }

      small {
        display: block;
        margin-top: 5px;
        color: #6c757d;
      }

      .footer {
        background: linear-gradient(135deg, #6f42c1, #6610f2);
        color: white;
        padding: 60px 0 30px;
      }

      .footer-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 30px;
      }

      .footer-about .logo {
        font-size: 24px;
        margin-bottom: 15px;
        display: block;
        color: white;
      }

      .footer-about p {
        margin-bottom: 20px;
      }

      .footer-links h4 {
        margin-bottom: 20px;
        font-size: 1.2rem;
        color: white;
      }

      .footer-links ul {
        list-style: none;
      }

      .footer-links li {
        margin-bottom: 10px;
      }

      .footer-links a {
        color: rgba(255, 255, 255, 0.8);
        text-decoration: none;
        transition: color 0.3s;
      }

      .footer-links a:hover {
        color: white;
      }

      .social-links {
        display: flex;
        gap: 10px;
      }

      .social-links a {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        background: rgba(255, 255, 255, 0.1);
        color: white;
        border-radius: 50%;
        transition: all 0.3s;
      }

      .social-links a:hover {
        background: white;
        color: #4a6cf7;
      }

      .copyright {
        text-align: center;
        padding-top: 30px;
        margin-top: 30px;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        color: rgba(255, 255, 255, 0.7);
        font-size: 0.9rem;
      }
    </style>
    <style>
              .info {
            background: #fff;
            padding: 30px 40px;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(75, 0, 130, 0.2);
        }
        .info h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #4b0082;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #4b0082;
        }
        input, select {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
            background: #f3f0ff;
        }
        select {
            background: #f3f0ff;
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: #800080;
            color: white;
            font-size: 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #a020f0;
        }
        #message-container {
    transition: opacity 0.5s ease-in-out;
    opacity: 0;
}

#message-container div {
    opacity: 1;
    padding: 10px;
    border-radius: 5px;
    margin-bottom: 10px;
}

.success-message {
    color: green;
}

.error-message {
    color: red;
}

#message-container.show {
    opacity: 1;
}

.message-container {
            max-width: 600px;
        }
        .message {
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            display: flex;
            align-items: center;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .icon {
            margin-right: 10px;
        }
        @media (max-width: 600px) {
            .message {
                font-size: 14px;
            }
        }
    </style>
  </head>
  <body>
    <!-- ▼▼▼ NOUVEAU HEADER DU PREMIER CODE ▼▼▼ -->
    <header class="header">
      <div class="header-container">
        <a href="index.html" class="logo">InnoConnect</a>
        <a href="InnovateurQuiz.html" class="btn">Home</a>
        <a href="ContratInnovateur.html" class="btn active">Contrat</a>
        <a href="InnovateurProjet.html" class="btn">Projet</a>
        <a href="index.html" class="btn">Get Started</a>
      </div>
    </header>

    <!-- ▼▼▼ CONTENU ORIGINAL DU DEUXIÈME CODE ▼▼▼ -->
    <div class="container">
    <div class="message-container">
        <?php
            // Display success messages
            foreach ($successMessages as $message) {
                echo '<div class="message success"><span class="icon">✅</span>' . $message . '</div>';
            }

            // Display error messages
            foreach ($errorMessages as $message) {
                echo '<div class="message error"><span class="icon">❌</span>' . $message . '</div>';
            }
        ?>
    </div>
      <h1>Contrat d'Investissement</h1>
      <p>
        Connecter les innovateurs avec les ressources dont ils ont besoin
        pour transformer leurs idées en réalité
      </p>
      <div id="message-container" style="margin-top: 20px;"></div> 

      <form action="" method="POST" onsubmit="return prepareSignature()">
  <div class="info">

    <h2>Informations du contrat</h2>

    <label>Nom de l'Innovateur :</label>
    <input type="text" name="nom_innovateur" placeholder="Entrer le nom" required>

    <label>ID de l'Innovateur :</label>
    <input type="number" name="id_innovateur" placeholder="Entrer l'ID" required>

    <label>Email de l'Innovateur :</label>
    <input type="email" name="email_innovateur" placeholder="Entrer l'email" required>

    <label>Montant Investi (€) :</label>
    <input type="number" name="montant_innovateur" placeholder="Entrer le montant" step="0.01" required>

    <label>Date de Signature :</label>
    <input type="date" name="date_signature" required>

    <label>Statut du Contrat :</label>
    <select name="statut" required>
      <option value="">-- Sélectionnez un statut --</option>
      <option value="en attente">En attente</option>
      <option value="validé">Validé</option>
      <option value="rejeté">Rejeté</option>
    </select>

    <!-- Hidden input to store signature -->
    <input type="hidden" id="signatureData" name="signature_innovateur" required>

    <div class="signature-container">
  <h2>Signature de l'Innovateur</h2>
  <canvas id="signatureCanvas" class="signature-pad"></canvas>
  <small>Signature électronique ayant valeur contractuelle</small>

  <div class="signature-buttons">
    <button class="clear-btn" type="button" onclick="clearSignature()">Effacer la Signature</button>
    <button class="download-btn" type="submit" onclick="validateSignature()">Valider la signaturee</button>
  </div>
</div>
  </div>

</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const form = document.getElementById('contractForm');
  const signatureCanvas = document.getElementById('signatureCanvas');
  const signatureDataInput = document.getElementById('signatureData');

  form.addEventListener('submit', function(event) {
    if (!validateForm()) {
      event.preventDefault(); // Empêche l'envoi du formulaire si erreur
    }
  });

  function validateForm() {
    let isValid = true;
    let errorMessages = [];

    // Vérifier le nom
    const nom = form.elements['nom_innovateur'].value.trim();
    if (nom.length < 2 || !/^[A-Za-zÀ-ÿ\s\-']+$/.test(nom)) {
      isValid = false;
      errorMessages.push("Le nom de l'innovateur est invalide.");
    }

    // Vérifier l'ID
    const id = form.elements['id_innovateur'].value.trim();
    if (id === "" || isNaN(id) || id <= 0 || id > 999999) {
      isValid = false;
      errorMessages.push("L'ID de l'innovateur doit être un nombre positif inférieur à 999999.");
    }

    // Vérifier l'email
    const email = form.elements['email_innovateur'].value.trim();
    if (email === "" || !email.includes('@')) {
      isValid = false;
      errorMessages.push("L'email de l'innovateur est invalide.");
    }

    // Vérifier le montant
    const montant = form.elements['montant_innovateur'].value.trim();
    if (montant === "" || isNaN(montant) || montant <= 0) {
      isValid = false;
      errorMessages.push("Le montant investi doit être un nombre positif.");
    }

    // Vérifier la date
    const dateSignature = form.elements['date_signature'].value.trim();
    const today = new Date().toISOString().split('T')[0];
    if (dateSignature === "" || dateSignature > today) {
      isValid = false;
      errorMessages.push("La date de signature est invalide ou future.");
    }

    // Vérifier le statut
    const statut = form.elements['statut'].value;
    if (statut === "") {
      isValid = false;
      errorMessages.push("Veuillez sélectionner un statut pour le contrat.");
    }

    // Vérifier la signature
    if (isCanvasBlank(signatureCanvas)) {
      isValid = false;
      errorMessages.push("Veuillez signer avant de valider le formulaire.");
    } else {
      // Préparer la signature (convertir en image base64)
      signatureDataInput.value = signatureCanvas.toDataURL();
    }

    // Afficher les erreurs si besoin
    if (!isValid) {
      alert(errorMessages.join("\n"));
    }

    return isValid;
  }

  // Fonction pour vérifier si le canvas est vide
  function isCanvasBlank(canvas) {
    const context = canvas.getContext('2d');
    const pixelBuffer = new Uint32Array(
      context.getImageData(0, 0, canvas.width, canvas.height).data.buffer
    );
    return !pixelBuffer.some(color => color !== 0);
  }

  // Fonction pour effacer la signature
  window.clearSignature = function() {
    const ctx = signatureCanvas.getContext('2d');
    ctx.clearRect(0, 0, signatureCanvas.width, signatureCanvas.height);
  }

  // Fonction appelée au clic sur "Valider la signature"
  window.validateSignature = function() {
    // Rien à faire ici, car la validation complète se fait à l'envoi du formulaire
  }
});
</script>



    <!-- ▼▼▼ FOOTER ORIGINAL DU DEUXIÈME CODE ▼▼▼ -->
    <footer class="footer">
      <div class="footer-container">
        <div class="footer-about">
          <a href="index.html" class="logo">InnoConnect</a>
          <p>
            Connecter les innovateurs avec les ressources dont ils ont besoin
            pour transformer leurs idées en réalité.
          </p>
          <div class="social-links">
            <a href="#"><i>Twitter</i></a>
            <a href="#"><i>Facebook</i></a>
            <a href="#"><i>Instagram</i></a>
            <a href="#"><i>LinkedIn</i></a>
          </div>
        </div>

        <div class="footer-links">
          <h4>Liens utiles</h4>
          <ul>
            <li><a href="index.html">Accueil</a></li>
            <li><a href="#about">À propos</a></li>
            <li><a href="#services">Services</a></li>
            <li><a href="#financement">Financement</a></li>
            <li><a href="#contact">Contact</a></li>
          </ul>
        </div>

        <div class="footer-links">
          <h4>Nos services</h4>
          <ul>
            <li><a href="#">Conseil en innovation</a></li>
            <li><a href="#">Accès au financement</a></li>
            <li><a href="#">Réseau d'experts</a></li>
            <li><a href="#">Partenariats stratégiques</a></li>
          </ul>
        </div>
      </div>

      <div class="copyright">
        <p>&copy; 2025 InnoConnect. Tous droits réservés.</p>
      </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
    <script>
  const canvas = document.getElementById('signatureCanvas');
  const signaturePad = new SignaturePad(canvas, {
    penColor: '#1d3557',
    minWidth: 1.5,
    maxWidth: 2.5,
    backgroundColor: '#ffffff'
  });

  function resizeCanvas() {
    const ratio = Math.max(window.devicePixelRatio || 1, 1);
    canvas.width = canvas.offsetWidth * ratio;
    canvas.height = canvas.offsetHeight * ratio;
    canvas.getContext("2d").scale(ratio, ratio);
    signaturePad.clear();
  }

  window.addEventListener("resize", resizeCanvas);
  resizeCanvas();

  function clearSignature() {
    signaturePad.clear();
  }

  function validateSignature() {
    if (signaturePad.isEmpty()) {
      alert("Veuillez signer le contrat avant validation");
      return;
    }
    alert("Signature capturée ! Vous pouvez maintenant enregistrer le contrat.");
    const dataUrl = signaturePad.toDataURL();
    document.getElementById('signatureData').value = dataUrl;
  }

  function prepareSignature() {
    if (signaturePad.isEmpty()) {
      alert("Veuillez signer le contrat avant de soumettre !");
      return false;
    }
    const dataUrl = signaturePad.toDataURL();
    document.getElementById('signatureData').value = dataUrl;
    return true;
  }
</script>
  </body>
</html>