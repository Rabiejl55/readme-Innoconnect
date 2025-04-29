<?php
<<<<<<< HEAD
require_once '../../Model/Financement.php';
require_once '../../Controller/FinancementController.php';

$financementController = new FinancementController();

$errors = [];
$showAddModal = false;
$showEditModal = false;

// Appel de la fonction addFinancement
if (isset($_POST['submit'])) {
  $montant = $_POST['montant'];
  $typeOperation = $_POST['typeOperation'];
  $titre = $_POST['titre'];
  $date_operation = $_POST['date_operation'];
  $id_contrat = $_POST['id_contrat'];
  $id_Projet = $_POST['id_Projet'];

  $financement = new Financement($montant, $typeOperation, $titre, $date_operation, $id_contrat, $id_Projet);
  
  // Si validation réussie, ajouter et rediriger
  if ($financementController->addFinancement($financement)) {
    header('Location: FinancementView.php');
    exit();
  } else {
    // Sinon, récupérer les erreurs
    $errors = $financement->getErrors();
    // Les erreurs seront affichées dans le formulaire
    $showAddModal = true; // Indique qu'il faut ouvrir le modal d'ajout

  }
}

// Appel de la fonction delete
if (isset($_POST['delete_id'])) {
  $id_financement = $_POST['delete_id'];
  if ($financementController->showFinancement($id_financement)) {
    $financementController->deleteFinancement($id_financement);
  }
  header("Location: FinancementView.php");
}
// Appel de la fonction UpdateFinancement
if (isset($_POST['update'])) {
  $id_financement = $_POST['edit_id'];
  $financement = new Financement(
    $_POST['edit_montant'],
    $_POST['edit_typeOperation'],
    $_POST['edit_titre'],
    $_POST['edit_date_operation'],
    $_POST['edit_id_contrat'],
    $_POST['edit_id_Projet']
  );
  
  // Si validation réussie, mettre à jour et rediriger
  if ($financementController->updateFinancement($financement, $id_financement)) {
    header('Location: FinancementView.php');
    exit();
  } else {
    // Sinon, récupérer les erreurs
    $errors = $financement->getErrors();
    $showEditModal = true; // Indique qu'il faut ouvrir le modal d'édition
    $_POST['edit_id_saved'] = $id_financement; // Sauvegarde l'ID pour le modal  
    }
}
// Appel de la fonction afficher
$financements = $financementController->listFinancement();

?>


=======
require_once '../../Controller/FinancementController.php';
require_once '../../Model/Financement.php';
require_once '../../Controller/ContratController.php';

$errors = [];
$success = '';

$controller = new FinancementController();
$contractController = new ContratController();
$contracts = $contractController->getAllContrats();

// Handle Add New Financement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit']) && empty($_POST['id_financement'])) {
    try {
        $financement = new Financement();
        $financement->setTitre($_POST['titre']);
        $financement->setTypeOperation($_POST['typeOperation']);
        $financement->setMontant($_POST['montant']);
        $financement->setDateOperation($_POST['date_operation']);
        $financement->setIdContrat($_POST['id_contrat']);
        $financement->setIdProjet('1'); // Set a default project ID or adjust as necessary

        $result = $controller->addFinancement($financement);

        if ($result) {
            $success = "Financement ajouté avec succès !";
            $_POST = []; // Clear POST data after successful submission
        } else {
            $errors['global'] = "Erreur lors de l'ajout du financement.";
        }
    } catch (Exception $e) {
        $errors['global'] = "Erreur : " . $e->getMessage();
    }
}

// Handle Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    try {
        $deleteId = (int)$_POST['delete_id'];
        $controller->deleteFinancement($deleteId);
        $success = "Financement supprimé avec succès.";
    } catch (Exception $e) {
        $errors['global'] = "Erreur suppression : " . $e->getMessage();
    }
}

// Handle Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit']) && !empty($_POST['id_financement'])) {
    try {
        $financement = new Financement();
        $financement->setIdFinancement($_POST['id_financement']);
        $financement->setTitre($_POST['titre']);
        $financement->setTypeOperation($_POST['typeOperation']);
        $financement->setMontant($_POST['montant']);
        $financement->setDateOperation($_POST['date_operation']);
        $financement->setIdContrat($_POST['id_contrat']);
        $financement->setIdProjet($_POST['id_Projet']);

        $result = $controller->updateFinancement($financement, $_POST['id_financement']);

        if ($result) {
            $success = "Financement modifié avec succès !";
            $_POST = [];
        } else {
            $errors['global'] = "Erreur lors de la modification.";
        }
    } catch (Exception $e) {
        $errors['global'] = "Erreur : " . $e->getMessage();
    }
}

// Load all financements
$financements = $controller->listFinancement();
?>

>>>>>>> 06b9c94 (second)
<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
  <title>Gestion de Financements - Argon Dashboard</title>

  <!-- Fonts et icônes -->
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
  <link href="https://demos.creative-tim.com/argon-dashboard-pro/assets/css/nucleo-icons.css" rel="stylesheet" />
  <link href="https://demos.creative-tim.com/argon-dashboard-pro/assets/css/nucleo-svg.css" rel="stylesheet" />
  <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>

  <!-- CSS -->
  <link id="pagestyle" href="../../Assets/CSS/argon-dashboard.css?v=2.1.0" rel="stylesheet" />
  <style>
    #decaissementsTable th {
      background-color: #5e72e4;
      color: white;
    }

    #decaissementsTable td {
      vertical-align: middle;
    }

    .form-popup {
      display: none;
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      z-index: 9999;
    }

    .form-group {
      margin-bottom: 1rem;
    }

    .section-content {
      display: none;
    }

    .active {
      background-color: #f0f0f0;
    }

    .top-right-button {
      position: absolute;
      top: 10px;
      right: 10px;
    }

    .top-right-button a {
      display: inline-block;
      padding: 10px 20px;
      background-color: #28a745;
      /* Green color */
      color: white;
      text-decoration: none;
      border-radius: 5px;
      font-weight: bold;
    }

    .top-right-button a:hover {
      background-color: #218838;
      /* Darker green on hover */
    }

    .card-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .left-buttons {
      display: flex;
      align-items: center;
    }

    .green-button {
      padding: 5px 15px;
      background-color: #28a745;
      /* Green color */
      color: white;
      text-decoration: none;
      border-radius: 5px;
      font-size: 14px;
      font-weight: bold;
    }

    .green-button:hover {
      background-color: #218838;
      /* Darker green on hover */
    }
  </style>
</head>

<body class="g-sidenav-show bg-gray-100">
  <div class="min-height-300 bg-dark position-absolute w-100"></div>

  <!-- Sidebar -->
  <aside
    class="sidenav bg-white navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-4"
    id="sidenav-main">
    <div class="sidenav-header">
      <h4 class="text-dark text-center py-3">InnoConnect</h4>
    </div>
    <hr class="horizontal dark mt-0" />
    <div class="collapse navbar-collapse w-auto" id="sidenav-collapse-main">
      <ul class="navbar-nav">
<<<<<<< HEAD
        <li class="nav-item">
          <a class="nav-link" href="doch.html">
            <i class="fas fa-home text-primary"></i>
            <span class="nav-link-text ms-2">Dashboard</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="innovateur.html">
            <i class="fas fa-lightbulb text-warning"></i>
            <span class="nav-link-text ms-2">User Management</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="investisseur.html">
            <i class="fas fa-hand-holding-usd text-success"></i>
            <span class="nav-link-text ms-2">Project Management</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="contractIntelligent.html">
            <i class="fas fa-file-signature text-info"></i>
            <span class="nav-link-text ms-2">Feedback Management</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="contractIntelligent.html">
            <i class="fas fa-file-signature text-info"></i>
            <span class="nav-link-text ms-2">Collaborative Management</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link active" href="contractIntelligent.html">
=======
      <li class="nav-item">
          <a class="nav-link active" href="FinancementView.php">
>>>>>>> 06b9c94 (second)
            <i class="fas fa-file-signature text-info"></i>
            <span class="nav-link-text ms-2">Financement Management</span>
          </a>
        </li>
<<<<<<< HEAD
      </ul>
    </div>
  </aside>
=======
        <li class="nav-item">
          <a class="nav-link active" href="ContratView.php">
            <i class="fas fa-file-signature text-info"></i>
            <span class="nav-link-text ms-2">Contract Management</span>
          </a>
        </li>
      </ul>
    </div>
  </aside>
  
>>>>>>> 06b9c94 (second)

  <!-- Contenu principal -->
  <main class="main-content position-relative border-radius-lg">
    <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl" id="navbarBlur"
      data-scroll="false"></nav>

    <div class="container-fluid py-4">
      <div class="row">
        <div class="col-12">
          <div id="dashboard" class="section-content">
            <h6>Dashboard Content</h6>
          </div>

          <div id="encaissements" class="section-content">
            <h6>Encaissements Content</h6>
          </div>

          <div id="decaissements" class="section-content" style="display: block">
            <div class="card mb-4">
              <div class="card-header pb-0">
                <div class="left-buttons">
                  <button class="btn btn-danger btn-sm ms-2" onclick="openForm()">
                    + Ajouter Financement
                  </button>
                </div>
                <a href="../FrontOffice/acceuil.html" class="green-button">Voir front-office</a>
              </div>
              <div class="card-body px-0 pt-0 pb-2">
                <div class="form-group mb-4">
                  <label for="searchInput">Rechercher :</label>
                  <input type="text" class="form-control" id="searchInput" placeholder="Rechercher par ID, Type..."
                    oninput="filterTable()" />
                </div>
<<<<<<< HEAD
=======
                <div class="message-container">
    <?php if (!empty($success)): ?>
        <div class="alert alert-success" style="color:white !important;" role="alert">
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($errors['global'])): ?>
        <div class="alert alert-danger" role="alert" style="color:white !important;">
            <?php echo htmlspecialchars($errors['global']); ?>
        </div>
    <?php endif; ?>
</div>
>>>>>>> 06b9c94 (second)
                <div class="table-responsive p-0">
                  <table class="table align-items-center mb-0" id="decaissementsTable">
                    <thead>
                      <tr>
                        <th>titre</th>
                        <th>date_Operation</th>
                        <th>Montant (€)</th>
                        <th>typeOperation</th>
                        <th>id contrat</th>
                        <th>id projet</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($financements as $financement): ?>
                        <tr>
                          <td><?= $financement['titre'] ?></td>
                          <td><?= $financement['date_operation'] ?></td>
                          <td><?= $financement['montant'] ?> €</td>
                          <td><?= $financement['typeOperation'] ?></td>
                          <td><?= $financement['id_contrat'] ?></td>
<<<<<<< HEAD
                          <td><?= $financement['id_Projet'] ?></td>

                          <!-- action buttons -->
=======
                          <td><?= $financement['id_projet'] ?></td>

>>>>>>> 06b9c94 (second)
                          <td>
                            <button class="btn btn-link text-primary" onclick="openEditForm(
                '<?= $financement['id_financement'] ?>',
                '<?= $financement['titre'] ?>',
                '<?= $financement['typeOperation'] ?>',
                '<?= $financement['montant'] ?>',
                '<?= $financement['date_operation'] ?>',
                '<?= $financement['id_contrat'] ?>',
<<<<<<< HEAD
                '<?= $financement['id_Projet'] ?>'
=======
                '<?= $financement['id_projet'] ?>'
>>>>>>> 06b9c94 (second)
            )">
                              <i class="fas fa-pencil-alt me-2"></i>Modifier
                            </button>

                            <!-- Delete Form -->
                            <form method="POST" style="display:inline;">
                              <input type="hidden" name="delete_id" value="<?= $financement['id_financement'] ?>">
                              <button type="submit" class="btn btn-link text-danger">
                                <i class="fas fa-trash-alt me-2"></i>Supprimer
                              </button>
                            </form>

                          </td>
                          <!-- /action buttons -->

                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>

          <div id="tresorerie" class="section-content">
            <h6>Trésorerie Finale Content</h6>
          </div>
        </div>
      </div>
    </div>
  </main>

  <!-- Modal -->
  <div class="modal fade" id="FinancementModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
<<<<<<< HEAD
        <form method="POST" action="FinancementView.php">
          <div class="modal-header">
            <h5 class="modal-title">Ajouter Financement</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="form-group">
              <label>Titre</label>
              <input type="text" class="form-control <?php echo isset($errors['titre']) ? 'is-invalid' : ''; ?>" name="titre" value="<?php echo isset($_POST['titre']) ? htmlspecialchars($_POST['titre']) : ''; ?>" >
              <?php if (isset($errors['titre'])): ?>
                <div class="invalid-feedback"><?php echo $errors['titre']; ?></div>
              <?php endif; ?>
            </div>
            <div class="form-group">
              <label>Type d'opération</label>
              <select class="form-select <?php echo isset($errors['typeOperation']) ? 'is-invalid' : ''; ?>" name="typeOperation" >
                <option value="encaissement" <?php echo (isset($_POST['typeOperation']) && $_POST['typeOperation'] === 'encaissement') ? 'selected' : ''; ?>>Encaissement</option>
                <option value="decaissement" <?php echo (isset($_POST['typeOperation']) && $_POST['typeOperation'] === 'decaissement') ? 'selected' : ''; ?>>Decaissement</option>
                <option value="tresorerie finale" <?php echo (isset($_POST['typeOperation']) && $_POST['typeOperation'] === 'tresorerie finale') ? 'selected' : ''; ?>>Trésorerie Finale</option>
              </select>
              <?php if (isset($errors['typeOperation'])): ?>
                <div class="invalid-feedback"><?php echo $errors['typeOperation']; ?></div>
              <?php endif; ?>
            </div>
            <div class="form-group">
              <label>Montant (€)</label>
              <input type="number" class="form-control <?php echo isset($errors['montant']) ? 'is-invalid' : ''; ?>" name="montant" step="0.01" value="<?php echo isset($_POST['montant']) ? htmlspecialchars($_POST['montant']) : ''; ?>" >
              <?php if (isset($errors['montant'])): ?>
                <div class="invalid-feedback"><?php echo $errors['montant']; ?></div>
              <?php endif; ?>
            </div>
            <div class="form-group">
              <label>Date</label>
              <input type="date" class="form-control <?php echo isset($errors['date_operation']) ? 'is-invalid' : ''; ?>" name="date_operation" value="<?php echo isset($_POST['date_operation']) ? htmlspecialchars($_POST['date_operation']) : ''; ?>" >
              <?php if (isset($errors['date_operation'])): ?>
                <div class="invalid-feedback"><?php echo $errors['date_operation']; ?></div>
              <?php endif; ?>
            </div>
            <div class="form-group">
              <label>ID Contrat</label>
              <input type="text" class="form-control <?php echo isset($errors['id_contrat']) ? 'is-invalid' : ''; ?>" name="id_contrat" value="<?php echo isset($_POST['id_contrat']) ? htmlspecialchars($_POST['id_contrat']) : ''; ?>" >
              <?php if (isset($errors['id_contrat'])): ?>
                <div class="invalid-feedback"><?php echo $errors['id_contrat']; ?></div>
              <?php endif; ?>
            </div>
            <div class="form-group">
              <label>ID Projet</label>
              <input type="text" class="form-control <?php echo isset($errors['id_Projet']) ? 'is-invalid' : ''; ?>" name="id_Projet" value="<?php echo isset($_POST['id_Projet']) ? htmlspecialchars($_POST['id_Projet']) : ''; ?>" >
              <?php if (isset($errors['id_Projet'])): ?>
                <div class="invalid-feedback"><?php echo $errors['id_Projet']; ?></div>
              <?php endif; ?>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
            <button type="submit" class="btn btn-danger" name="submit">Enregistrer</button>
          </div>
        </form>
=======
     <!-- HTML Form for Adding Financement -->
<form method="POST" action="FinancementView.php">
    <div class="modal-header">
        <h5 class="modal-title">Ajouter Financement</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body">
        <div class="form-group">
            <label>Titre</label>
            <input type="text" class="form-control <?php echo isset($errors['titre']) ? 'is-invalid' : ''; ?>" name="titre" value="<?php echo isset($_POST['titre']) ? htmlspecialchars($_POST['titre']) : ''; ?>" >
            <?php if (isset($errors['titre'])): ?>
                <div class="invalid-feedback"><?php echo $errors['titre']; ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label>Type d'opération</label>
            <select class="form-select <?php echo isset($errors['typeOperation']) ? 'is-invalid' : ''; ?>" name="typeOperation">
                <option value="encaissement" <?php echo (isset($_POST['typeOperation']) && $_POST['typeOperation'] === 'encaissement') ? 'selected' : ''; ?>>Encaissement</option>
                <option value="decaissement" <?php echo (isset($_POST['typeOperation']) && $_POST['typeOperation'] === 'decaissement') ? 'selected' : ''; ?>>Décaissement</option>
                <option value="tresorerie finale" <?php echo (isset($_POST['typeOperation']) && $_POST['typeOperation'] === 'tresorerie finale') ? 'selected' : ''; ?>>Trésorerie Finale</option>
            </select>
            <?php if (isset($errors['typeOperation'])): ?>
                <div class="invalid-feedback"><?php echo $errors['typeOperation']; ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label>Montant (€)</label>
            <input type="number" class="form-control <?php echo isset($errors['montant']) ? 'is-invalid' : ''; ?>" name="montant" step="0.01" value="<?php echo isset($_POST['montant']) ? htmlspecialchars($_POST['montant']) : ''; ?>" >
            <?php if (isset($errors['montant'])): ?>
                <div class="invalid-feedback"><?php echo $errors['montant']; ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label>Date</label>
            <input type="date" class="form-control <?php echo isset($errors['date_operation']) ? 'is-invalid' : ''; ?>" name="date_operation" value="<?php echo isset($_POST['date_operation']) ? htmlspecialchars($_POST['date_operation']) : ''; ?>" >
            <?php if (isset($errors['date_operation'])): ?>
                <div class="invalid-feedback"><?php echo $errors['date_operation']; ?></div>
            <?php endif; ?>
        </div>
<!-- jointure avec contrat  -->
        <div class="form-group">
            <label>ID Contrat</label>
            <select class="form-select <?php echo isset($errors['id_contrat']) ? 'is-invalid' : ''; ?>" name="id_contrat">
                <option value="">Sélectionner un contrat</option>
                <?php foreach ($contracts as $contract): ?>
                    <option value="<?php echo $contract['id_contrat']; ?>" <?php echo (isset($_POST['id_contrat']) && $_POST['id_contrat'] === $contract['id_contrat']) ? 'selected' : ''; ?>>
                      <span >ID  : </span>  <?php echo $contract['id_contrat']; ?>  
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (isset($errors['id_contrat'])): ?>
                <div class="invalid-feedback"><?php echo $errors['id_contrat']; ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label>ID Projet</label>
            <input type="text" class="form-control" name="id_Projet" value="1" readonly>
        </div>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
        <button type="submit" class="btn btn-danger" name="submit">Enregistrer</button>
    </div>
</form>
>>>>>>> 06b9c94 (second)
      </div>
    </div>
  </div>

<<<<<<< HEAD
  <!-- Modal Edit -->
  <div class="modal fade" id="EditModal">
    <div class="modal-dialog">
      <div class="modal-content">
        <form method="POST">
          <div class="modal-header">
            <h5 class="modal-title">Modifier Financement</h5>
          </div>
          <div class="modal-body">
            <input type="hidden" name="edit_id" id="edit_id" value="<?php echo isset($_POST['edit_id_saved']) ? $_POST['edit_id_saved'] : ''; ?>">
            <div class="form-group">
              <label>Titre</label>
              <input type="text" class="form-control <?php echo isset($errors['titre']) ? 'is-invalid' : ''; ?>" name="edit_titre" id="edit_titre" value="<?php echo isset($_POST['edit_titre']) ? htmlspecialchars($_POST['edit_titre']) : ''; ?>" >
              <?php if (isset($errors['titre'])): ?>
                <div class="invalid-feedback"><?php echo $errors['titre']; ?></div>
              <?php endif; ?>
            </div>
            <div class="form-group">
              <label>Type d'opération</label>
              <select class="form-select <?php echo isset($errors['typeOperation']) ? 'is-invalid' : ''; ?>" name="edit_typeOperation" id="edit_typeOperation" >
                <option value="encaissement" <?php echo (isset($_POST['edit_typeOperation']) && $_POST['edit_typeOperation'] === 'encaissement') ? 'selected' : ''; ?>>Encaissement</option>
                <option value="decaissement" <?php echo (isset($_POST['edit_typeOperation']) && $_POST['edit_typeOperation'] === 'decaissement') ? 'selected' : ''; ?>>Decaissement</option>
                <option value="tresorerie finale" <?php echo (isset($_POST['edit_typeOperation']) && $_POST['edit_typeOperation'] === 'tresorerie finale') ? 'selected' : ''; ?>>Trésorerie Finale</option>
              </select>
              <?php if (isset($errors['typeOperation'])): ?>
                <div class="invalid-feedback"><?php echo $errors['typeOperation']; ?></div>
              <?php endif; ?>
            </div>
            <div class="form-group">
              <label>Montant (€)</label>
              <input type="number" class="form-control <?php echo isset($errors['montant']) ? 'is-invalid' : ''; ?>" name="edit_montant" id="edit_montant" step="0.01" value="<?php echo isset($_POST['edit_montant']) ? htmlspecialchars($_POST['edit_montant']) : ''; ?>" >
              <?php if (isset($errors['montant'])): ?>
                <div class="invalid-feedback"><?php echo $errors['montant']; ?></div>
              <?php endif; ?>
            </div>
            <div class="form-group">
              <label>Date</label>
              <input type="date" class="form-control <?php echo isset($errors['date_operation']) ? 'is-invalid' : ''; ?>" name="edit_date_operation" id="edit_date_operation" value="<?php echo isset($_POST['edit_date_operation']) ? htmlspecialchars($_POST['edit_date_operation']) : ''; ?>" >
              <?php if (isset($errors['date_operation'])): ?>
                <div class="invalid-feedback"><?php echo $errors['date_operation']; ?></div>
              <?php endif; ?>
            </div>
            <div class="form-group">
              <label>ID Contrat</label>
              <input type="text" class="form-control <?php echo isset($errors['id_contrat']) ? 'is-invalid' : ''; ?>" name="edit_id_contrat" id="edit_id_contrat" value="<?php echo isset($_POST['edit_id_contrat']) ? htmlspecialchars($_POST['edit_id_contrat']) : ''; ?>" >
              <?php if (isset($errors['id_contrat'])): ?>
                <div class="invalid-feedback"><?php echo $errors['id_contrat']; ?></div>
              <?php endif; ?>
            </div>
            <div class="form-group">
              <label>ID Projet</label>
              <input type="text" class="form-control <?php echo isset($errors['id_Projet']) ? 'is-invalid' : ''; ?>" name="edit_id_Projet" id="edit_id_Projet" value="<?php echo isset($_POST['edit_id_Projet']) ? htmlspecialchars($_POST['edit_id_Projet']) : ''; ?>" >
              <?php if (isset($errors['id_Projet'])): ?>
                <div class="invalid-feedback"><?php echo $errors['id_Projet']; ?></div>
              <?php endif; ?>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
            <button type="submit" class="btn btn-primary" name="update">Enregistrer</button>
          </div>
        </form>
      </div>
    </div>
  </div>
=======
<!-- Modal Edit -->
<div class="modal fade" id="EditModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Modifier Financement</h5>
                    <button type="button" class="close" data-bs-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_financement" id="edit_id" value="<?php echo isset($_POST['id_financement']) ? htmlspecialchars($_POST['id_financement']) : ''; ?>">
                    <div class="form-group">
                        <label>Titre</label>
                        <input type="text" class="form-control <?php echo isset($errors['titre']) ? 'is-invalid' : ''; ?>" name="titre" id="edit_titre" value="<?php echo isset($_POST['titre']) ? htmlspecialchars($_POST['titre']) : ''; ?>">
                        <?php if (isset($errors['titre'])): ?>
                            <div class="invalid-feedback"><?php echo $errors['titre']; ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label>Type d'opération</label>
                        <select class="form-select <?php echo isset($errors['typeOperation']) ? 'is-invalid' : ''; ?>" name="typeOperation" id="edit_typeOperation">
                            <option value="encaissement" <?php echo (isset($_POST['typeOperation']) && $_POST['typeOperation'] === 'encaissement') ? 'selected' : ''; ?>>Encaissement</option>
                            <option value="decaissement" <?php echo (isset($_POST['typeOperation']) && $_POST['typeOperation'] === 'decaissement') ? 'selected' : ''; ?>>Decaissement</option>
                            <option value="tresorerie finale" <?php echo (isset($_POST['typeOperation']) && $_POST['typeOperation'] === 'tresorerie finale') ? 'selected' : ''; ?>>Trésorerie Finale</option>
                        </select>
                        <?php if (isset($errors['typeOperation'])): ?>
                            <div class="invalid-feedback"><?php echo $errors['typeOperation']; ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label>Montant (€)</label>
                        <input type="number" class="form-control <?php echo isset($errors['montant']) ? 'is-invalid' : ''; ?>" name="montant" id="edit_montant" step="0.01" value="<?php echo isset($_POST['montant']) ? htmlspecialchars($_POST['montant']) : ''; ?>">
                        <?php if (isset($errors['montant'])): ?>
                            <div class="invalid-feedback"><?php echo $errors['montant']; ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label>Date</label>
                        <input type="date" class="form-control <?php echo isset($errors['date_operation']) ? 'is-invalid' : ''; ?>" name="date_operation" id="edit_date_operation" value="<?php echo isset($_POST['date_operation']) ? htmlspecialchars($_POST['date_operation']) : ''; ?>">
                        <?php if (isset($errors['date_operation'])): ?>
                            <div class="invalid-feedback"><?php echo $errors['date_operation']; ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label>ID Contrat</label>
                        <input type="text" class="form-control <?php echo isset($errors['id_contrat']) ? 'is-invalid' : ''; ?>" name="id_contrat" id="edit_id_contrat" value="<?php echo isset($_POST['id_contrat']) ? htmlspecialchars($_POST['id_contrat']) : ''; ?>">
                        <?php if (isset($errors['id_contrat'])): ?>
                            <div class="invalid-feedback"><?php echo $errors['id_contrat']; ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label>ID Projet</label>
                        <input type="text" class="form-control <?php echo isset($errors['id_Projet']) ? 'is-invalid' : ''; ?>" name="id_Projet" id="edit_id_Projet" value="<?php echo isset($_POST['id_Projet']) ? htmlspecialchars($_POST['id_Projet']) : ''; ?>">
                        <?php if (isset($errors['id_Projet'])): ?>
                            <div class="invalid-feedback"><?php echo $errors['id_Projet']; ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary" name="submit">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>
>>>>>>> 06b9c94 (second)
  <!-- JS -->
  <script src="../../assets/js/core/popper.min.js"></script>
  <script src="../../assets/js/core/bootstrap.min.js"></script>
  <script src="../assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="../assets/js/plugins/smooth-scrollbar.min.js"></script>


  <script>
    // Code pour ouvrir automatiquement le modal si nécessaire
    document.addEventListener('DOMContentLoaded', function() {
      <?php if($showAddModal): ?>
        var addModal = new bootstrap.Modal(document.getElementById('FinancementModal'));
        addModal.show();
      <?php endif; ?>
      
      <?php if($showEditModal): ?>
        var editModal = new bootstrap.Modal(document.getElementById('EditModal'));
        editModal.show();
      <?php endif; ?>
    });
  </script>
  <script>
    let decaissements = [];
    let currentEditIndex = -1;

    function openForm() {
      var modal = new bootstrap.Modal(
        document.getElementById("FinancementModal")
      );
      modal.show();
      
    }

    function closeForm() {
      var modal = bootstrap.Modal.getInstance(
        document.getElementById("FinancementModal")
      );
      modal.hide();
    }

    function resetForm() {
      document.getElementById("id_financement").value = "";
      document.getElementById("titre").value = "";
      document.getElementById("id_contrat").value = "";
      document.getElementById("id_Projet").value = "";
      document.getElementById("date_operation").value = "";
      document.getElementById("montant").value = "";
      document.getElementById("typeOperation").value = "encaissement";
    }


    function refreshTable() {
      const tbody = document.querySelector("#decaissementsTable tbody");
      tbody.innerHTML = "";
      decaissements.forEach((e, i) => {
        const row = document.createElement("tr");
        row.innerHTML = `
            <td>${e.id}</td>
            <td>${e.date}</td>
            <td>${e.montant}</td>
            <td>${e.type}</td>
            <td>
              <button class="btn btn-link text-primary" onclick="editDecaissement(${i})"><i class="fas fa-pencil-alt me-2"></i>Modifier</button>
              <button class="btn btn-link text-danger" onclick="deleteDecaissement(${i})"><i class="fas fa-trash-alt me-2"></i>Supprimer</button>
            </td>
          `;
        tbody.appendChild(row);
      });
    }

    function openEditForm(id, titre, type, montant, date, contrat, projet) {
      document.getElementById('edit_id').value = id;
      document.getElementById('edit_titre').value = titre;
      document.getElementById('edit_typeOperation').value = type;
      document.getElementById('edit_montant').value = montant;
      document.getElementById('edit_date_operation').value = date;
      document.getElementById('edit_id_contrat').value = contrat;
      document.getElementById('edit_id_Projet').value = projet;
      new bootstrap.Modal(document.getElementById('EditModal')).show();
    }



    function filterTable() {
      const input = document.getElementById("searchInput");
      const filter = input.value.toLowerCase();
      const rows = document.querySelectorAll("#decaissementsTable tbody tr");

      rows.forEach((row) => {
        const text = row.innerText.toLowerCase();
        row.style.display = text.includes(filter) ? "" : "none";
      });
    }
  </script>
</body>

</html>