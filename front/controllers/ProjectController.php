<?php
require_once 'models/Project.php';

class ProjectController {
    private $projectModel;
    
    public function __construct() {
        $this->projectModel = new Project();
    }
    
    // Display all projects
    public function index() {
        try {
            // Get projects from model
            $projects = $this->projectModel->getProjects();
            
            // Include view file
            require_once 'views/projects/index.php';
            
        } catch (Exception $e) {
            $errorTitle = 'Erreur lors du chargement des projets';
            $errorMessage = $e->getMessage();
            require_once 'views/templates/error.php';
        }
    }
    
    // Display form to create a new project
    public function create() {
        try {
            // Set error display for debugging
            ini_set('display_errors', 1);
            error_reporting(E_ALL);
            
            echo "<!-- Starting ProjectController::create method -->";
            
            // Get categories from the model
        $categories = $this->projectModel->getCategories();
            
            echo "<!-- Categories retrieved successfully: " . count($categories) . " found -->";
            
            // Include the view
            echo "<!-- Loading create.php view file -->";
            
            // Include the view with a shorter timeout
            $timeout = ini_get('max_execution_time');
            ini_set('max_execution_time', 30); // Set 30 seconds timeout
            
        require_once 'views/projects/create.php';
            
            ini_set('max_execution_time', $timeout); // Restore original timeout
            
            echo "<!-- Create view loaded successfully -->";
            
        } catch (Exception $e) {
            echo "<div style='background-color: #f8d7da; color: #721c24; padding: 15px; margin: 15px; border-radius: 5px;'>";
            echo "<h3>Erreur dans ProjectController::create</h3>";
            echo "<p>" . $e->getMessage() . "</p>";
            echo "<p>Trace: " . $e->getTraceAsString() . "</p>";
            echo "</div>";
        }
    }
    
    // Display simplified form to create a new project
    public function create_simple() {
        try {
            // Set error display for debugging
            ini_set('display_errors', 1);
            error_reporting(E_ALL);
            
            echo "<!-- Starting ProjectController::create_simple method -->";
            
            // Get categories from the model
            $categories = $this->projectModel->getCategories();
            
            echo "<!-- Categories retrieved successfully: " . count($categories) . " found -->";
            
            // Include the simplified view
            echo "<!-- Loading create_simple.php view file -->";
            
            require_once 'views/projects/create_simple.php';
            
            echo "<!-- Simple create view loaded successfully -->";
            
        } catch (Exception $e) {
            echo "<div style='background-color: #f8d7da; color: #721c24; padding: 15px; margin: 15px; border-radius: 5px;'>";
            echo "<h3>Erreur dans ProjectController::create_simple</h3>";
            echo "<p>" . $e->getMessage() . "</p>";
            echo "<p>Trace: " . $e->getTraceAsString() . "</p>";
            echo "</div>";
        }
    }
    
    // Handle project creation form submission
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $titre = $_POST['titre'] ?? '';
            $description = $_POST['description'] ?? '';
            $id_categorie = $_POST['id_categorie'] ?? 0;
            // In a real app, you'd get the user ID from the session
            $id_utilisateur = 1; // Default for now
            $montant_demande = $_POST['montant_demande'] ?? 0;
            
            if (empty($titre) || empty($description) || empty($id_categorie) || empty($montant_demande)) {
                $_SESSION['error'] = "Tous les champs sont obligatoires";
                header('Location: index.php?controller=project&action=create');
                return;
            }
            
            $success = $this->projectModel->createProject($titre, $description, $id_categorie, $id_utilisateur, $montant_demande);
            
            if ($success) {
                $_SESSION['success'] = "Projet créé avec succès";
                header('Location: index.php?controller=project&action=index');
            } else {
                $_SESSION['error'] = "Erreur lors de la création du projet";
                header('Location: index.php?controller=project&action=create');
            }
        }
    }
    
    // Display form to edit an existing project
    public function edit($id) {
        $project = $this->projectModel->getProjectById($id);
        $categories = $this->projectModel->getCategories();
        
        if (!$project) {
            $_SESSION['error'] = "Projet non trouvé";
            header('Location: index.php?controller=project&action=index');
            return;
        }
        
        require_once 'views/projects/edit.php';
    }
    
    // Handle project update form submission
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id_projet'] ?? 0;
            $titre = $_POST['titre'] ?? '';
            $description = $_POST['description'] ?? '';
            $id_categorie = $_POST['id_categorie'] ?? 0;
            $statut = $_POST['statut'] ?? 'Soumis';
            $montant_demande = $_POST['montant_demande'] ?? 0;
            
            if (empty($id) || empty($titre) || empty($description) || empty($id_categorie) || empty($montant_demande)) {
                $_SESSION['error'] = "Tous les champs sont obligatoires";
                header('Location: index.php?controller=project&action=edit&id=' . $id);
                return;
            }
            
            $success = $this->projectModel->updateProject($id, $titre, $description, $id_categorie, $statut, $montant_demande);
            
            if ($success) {
                $_SESSION['success'] = "Projet mis à jour avec succès";
                header('Location: index.php?controller=project&action=index');
            } else {
                $_SESSION['error'] = "Erreur lors de la mise à jour du projet";
                header('Location: index.php?controller=project&action=edit&id=' . $id);
            }
        }
    }
    
    // Display confirmation before deleting a project
    public function delete($id) {
        $project = $this->projectModel->getProjectById($id);
        
        if (!$project) {
            $_SESSION['error'] = "Projet non trouvé";
            header('Location: index.php?controller=project&action=index');
            return;
        }
        
        require_once 'views/projects/delete.php';
    }
    
    // Handle project deletion
    public function destroy() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id_projet'] ?? 0;
            
            if (empty($id)) {
                $_SESSION['error'] = "ID de projet invalide";
                header('Location: index.php?controller=project&action=index');
                return;
            }
            
            $success = $this->projectModel->deleteProject($id);
            
            if ($success) {
                $_SESSION['success'] = "Projet supprimé avec succès";
            } else {
                $_SESSION['error'] = "Erreur lors de la suppression du projet";
            }
            
            header('Location: index.php?controller=project&action=index');
        }
    }
    
    // Display details of a project
    public function show($id) {
        $project = $this->projectModel->getProjectById($id);
        
        if (!$project) {
            $_SESSION['error'] = "Projet non trouvé";
            header('Location: index.php?controller=project&action=index');
            return;
        }
        
        require_once 'views/projects/show.php';
    }
    
    // Display simplified form to edit a project
    public function edit_simple($id = null) {
        try {
            // Set error display for debugging
            ini_set('display_errors', 1);
            error_reporting(E_ALL);
            
            echo "<!-- Starting ProjectController::edit_simple method with ID: " . ($id !== null ? $id : "null") . " -->";
            
            // Check if ID is provided
            if ($id === null) {
                $_SESSION['error'] = "ID du projet non spécifié";
                header('Location: index.php?controller=project&action=index');
                return;
            }
            
            // Get project data
            $project = $this->projectModel->getProjectById($id);
            
            if (!$project) {
                $_SESSION['error'] = "Projet non trouvé";
                header('Location: index.php?controller=project&action=index');
                return;
            }
            
            // Get categories
            $categories = $this->projectModel->getCategories();
            
            // Include the simplified view
            require_once 'views/projects/edit_simple.php';
            
            echo "<!-- Edit simple view loaded successfully -->";
            
        } catch (Exception $e) {
            echo "<div style='background-color: #f8d7da; color: #721c24; padding: 15px; margin: 15px; border-radius: 5px;'>";
            echo "<h3>Erreur dans ProjectController::edit_simple</h3>";
            echo "<p>" . $e->getMessage() . "</p>";
            echo "</div>";
        }
    }
    
    // Display simplified delete confirmation
    public function delete_simple($id = null) {
        try {
            // Set error display for debugging
            ini_set('display_errors', 1);
            error_reporting(E_ALL);
            
            echo "<!-- Starting ProjectController::delete_simple method with ID: " . ($id !== null ? $id : "null") . " -->";
            
            // Check if ID is provided
            if ($id === null) {
                $_SESSION['error'] = "ID du projet non spécifié";
                header('Location: index.php?controller=project&action=index');
                return;
            }
            
            // Get project data
            $project = $this->projectModel->getProjectById($id);
            
            if (!$project) {
                $_SESSION['error'] = "Projet non trouvé";
                header('Location: index.php?controller=project&action=index');
                return;
            }
            
            // Include the simplified view
            require_once 'views/projects/delete_simple.php';
            
            echo "<!-- Delete simple view loaded successfully -->";
            
        } catch (Exception $e) {
            echo "<div style='background-color: #f8d7da; color: #721c24; padding: 15px; margin: 15px; border-radius: 5px;'>";
            echo "<h3>Erreur dans ProjectController::delete_simple</h3>";
            echo "<p>" . $e->getMessage() . "</p>";
            echo "</div>";
        }
    }
}
?> 