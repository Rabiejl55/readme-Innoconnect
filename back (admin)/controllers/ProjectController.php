<?php
require_once 'models/Project.php';

class ProjectController {
    private $projectModel;
    
    public function __construct() {
        $this->projectModel = new Project();
    }
    
    // Dashboard - Projects overview
    public function dashboard() {
        $totalProjects = $this->projectModel->countProjects();
        $pendingProjects = $this->projectModel->getProjectsByStatus('Soumis');
        $approvedProjects = $this->projectModel->getProjectsByStatus('Approuvé');
        $rejectedProjects = $this->projectModel->getProjectsByStatus('Refusé');
        $projects = $this->projectModel->getProjects();
        
        // Include only 5 most recent projects for dashboard
        $recentProjects = array_slice($projects, 0, 5);
        
        // Donut chart data
        $projectStatusCounts = $this->projectModel->getProjectStatusCounts();
        $projectCategoryCounts = $this->projectModel->getProjectCategoryCounts();
        
        require_once 'views/admin/dashboard.php';
    }
    
    // Display all projects
    public function index() {
        $projects = $this->projectModel->getProjects();
        require_once 'views/admin/projects/index.php';
    }
    
    // Show a single project
    public function show($id) {
        $project = $this->projectModel->getProjectById($id);
        
        if (!$project) {
            $_SESSION['error'] = "Projet non trouvé";
            header('Location: index.php?controller=project&action=index');
            return;
        }
        
        require_once 'views/admin/projects/show.php';
    }
    
    // Display form to create a new project
    public function create() {
        $categories = $this->projectModel->getCategories();
        require_once 'views/admin/projects/create.php';
    }
    
    // Store a new project
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=project&action=create');
            return;
        }
        
        $titre = $_POST['titre'] ?? '';
        $description = $_POST['description'] ?? '';
        $id_categorie = $_POST['id_categorie'] ?? 0;
        // In a real app, you'd get the user ID from the session
        $id_utilisateur = $_POST['id_utilisateur'] ?? 1; 
        $montant_demande = $_POST['montant_demande'] ?? 0;
        
        if (empty($titre) || empty($description) || empty($id_categorie) || empty($montant_demande)) {
            $_SESSION['error'] = "Tous les champs sont obligatoires";
            header('Location: index.php?controller=project&action=create');
            return;
        }
        
        $result = $this->projectModel->createProject($titre, $description, $id_categorie, $id_utilisateur, $montant_demande);
        
        if ($result) {
            $_SESSION['success'] = "Projet créé avec succès";
            header('Location: index.php?controller=project&action=index');
        } else {
            $_SESSION['error'] = "Erreur lors de la création du projet";
            header('Location: index.php?controller=project&action=create');
        }
    }
    
    // Display form to edit a project
    public function edit($id) {
        $project = $this->projectModel->getProjectById($id);
        
        if (!$project) {
            $_SESSION['error'] = "Projet non trouvé";
            header('Location: index.php?controller=project&action=index');
            return;
        }
        
        $categories = $this->projectModel->getCategories();
        require_once 'views/admin/projects/edit.php';
    }
    
    // Update a project
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=project&action=index');
            return;
        }
        
        $id = $_POST['id_projet'] ?? 0;
        $titre = $_POST['titre'] ?? '';
        $description = $_POST['description'] ?? '';
        $id_categorie = $_POST['id_categorie'] ?? 0;
        $statut = $_POST['statut'] ?? 'Soumis';
        $montant_demande = $_POST['montant_demande'] ?? 0;
        
        if (empty($id) || empty($titre) || empty($description) || empty($id_categorie) || empty($montant_demande)) {
            $_SESSION['error'] = "Tous les champs sont obligatoires";
            header("Location: index.php?controller=project&action=edit&id=$id");
            return;
        }
        
        $result = $this->projectModel->updateProject($id, $titre, $description, $id_categorie, $statut, $montant_demande);
        
        if ($result) {
            $_SESSION['success'] = "Projet mis à jour avec succès";
            header('Location: index.php?controller=project&action=index');
        } else {
            $_SESSION['error'] = "Erreur lors de la mise à jour du projet";
            header("Location: index.php?controller=project&action=edit&id=$id");
        }
    }
    
    // Delete a project
    public function delete($id) {
        $project = $this->projectModel->getProjectById($id);
        
        if (!$project) {
            $_SESSION['error'] = "Projet non trouvé";
            header('Location: index.php?controller=project&action=index');
            return;
        }
        
        require_once 'views/admin/projects/delete.php';
    }
    
    // Perform the deletion
    public function destroy() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=project&action=index');
            return;
        }
        
        $id = $_POST['id_projet'] ?? 0;
        
        if (empty($id)) {
            $_SESSION['error'] = "ID de projet invalide";
            header('Location: index.php?controller=project&action=index');
            return;
        }
        
        $result = $this->projectModel->deleteProject($id);
        
        if ($result) {
            $_SESSION['success'] = "Projet supprimé avec succès";
        } else {
            $_SESSION['error'] = "Erreur lors de la suppression du projet";
        }
        
        header('Location: index.php?controller=project&action=index');
    }
    
    // Change project status
    public function changeStatus() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=project&action=index');
            return;
        }
        
        $id = $_POST['id_projet'] ?? 0;
        $statut = $_POST['statut'] ?? '';
        
        if (empty($id) || empty($statut)) {
            $_SESSION['error'] = "Paramètres manquants";
            header('Location: index.php?controller=project&action=index');
            return;
        }
        
        $project = $this->projectModel->getProjectById($id);
        
        if (!$project) {
            $_SESSION['error'] = "Projet non trouvé";
            header('Location: index.php?controller=project&action=index');
            return;
        }
        
        $result = $this->projectModel->updateProject(
            $id, 
            $project['titre'], 
            $project['description'], 
            $project['id_categorie'], 
            $statut, 
            $project['montant_demande']
        );
        
        if ($result) {
            $_SESSION['success'] = "Statut du projet mis à jour avec succès";
        } else {
            $_SESSION['error'] = "Erreur lors de la mise à jour du statut du projet";
        }
        
        header('Location: index.php?controller=project&action=index');
    }
    
    // Export projects to PDF
    public function export() {
        // Get all projects
        $projects = $this->projectModel->getProjects();
        
        // Require dompdf library
        require_once '../vendor/autoload.php';
        
        // Initialize dompdf
        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf = new \Dompdf\Dompdf($options);
        
        // Prepare HTML content for PDF
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>Rapport des Projets</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                h1 { color: #5e72e4; text-align: center; font-size: 24px; margin-bottom: 20px; }
                h2 { color: #11cdef; font-size: 18px; margin-top: 30px; }
                table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                th { background-color: #f4f5f7; color: #5e72e4; font-weight: bold; text-align: left; padding: 10px; }
                td { padding: 10px; border-bottom: 1px solid #e9ecef; }
                .project-status { padding: 5px 10px; border-radius: 15px; font-size: 12px; font-weight: bold; display: inline-block; }
                .status-approved { background-color: #2dce89; color: white; }
                .status-rejected { background-color: #f5365c; color: white; }
                .status-pending { background-color: #fb6340; color: white; }
                .info-section { margin-bottom: 15px; border-left: 3px solid #5e72e4; padding-left: 10px; }
                .header-section { border-bottom: 2px solid #5e72e4; padding-bottom: 10px; margin-bottom: 30px; }
                .footer { text-align: center; font-size: 12px; color: #8898aa; margin-top: 50px; }
            </style>
        </head>
        <body>
            <div class="header-section">
                <h1>Rapport Complet des Projets</h1>
                <p>Date de génération: ' . date('d/m/Y H:i:s') . '</p>
            </div>
            
            <div class="info-section">
                <p><strong>Nombre total de projets:</strong> ' . count($projects) . '</p>
            </div>
            
            <table>
                <tr>
                    <th>ID</th>
                    <th>Titre</th>
                    <th>Catégorie</th>
                    <th>Montant</th>
                    <th>Statut</th>
                    <th>Date Soumission</th>
                </tr>';
        
        foreach ($projects as $project) {
            $statusClass = '';
            if ($project['statut'] === 'Approuvé') {
                $statusClass = 'status-approved';
            } elseif ($project['statut'] === 'Refusé') {
                $statusClass = 'status-rejected';
            } else {
                $statusClass = 'status-pending';
            }
            
            $html .= '
                <tr>
                    <td>' . $project['id_projet'] . '</td>
                    <td>' . htmlspecialchars($project['titre']) . '</td>
                    <td>' . htmlspecialchars($project['categorie_nom']) . '</td>
                    <td>' . number_format($project['montant_demande'], 2, ',', ' ') . ' €</td>
                    <td><span class="project-status ' . $statusClass . '">' . htmlspecialchars($project['statut']) . '</span></td>
                    <td>' . date("d/m/Y", strtotime($project['date_soumission'])) . '</td>
                </tr>';
        }
        
        $html .= '
            </table>
            
            <h2>Détails des projets</h2>';
        
        foreach ($projects as $project) {
            $statusClass = '';
            if ($project['statut'] === 'Approuvé') {
                $statusClass = 'status-approved';
            } elseif ($project['statut'] === 'Refusé') {
                $statusClass = 'status-rejected';
            } else {
                $statusClass = 'status-pending';
            }
            
            $html .= '
            <div style="margin-bottom: 30px; padding: 15px; border: 1px solid #e9ecef; border-radius: 5px;">
                <h3 style="color: #5e72e4; margin-top: 0;">' . htmlspecialchars($project['titre']) . ' <span class="project-status ' . $statusClass . '">' . htmlspecialchars($project['statut']) . '</span></h3>
                <p><strong>Catégorie:</strong> ' . htmlspecialchars($project['categorie_nom']) . '</p>
                <p><strong>Montant demandé:</strong> ' . number_format($project['montant_demande'], 2, ',', ' ') . ' €</p>
                <p><strong>Date de soumission:</strong> ' . date("d/m/Y", strtotime($project['date_soumission'])) . '</p>
                <p><strong>Soumis par:</strong> ' . htmlspecialchars($project['utilisateur_prenom']) . ' ' . htmlspecialchars($project['utilisateur_nom']) . '</p>
                <p><strong>Description:</strong> ' . htmlspecialchars($project['description']) . '</p>
            </div>';
        }
        
        $html .= '
            <div class="footer">
                <p>Ce document a été généré automatiquement par l\'application Techie Admin.</p>
            </div>
        </body>
        </html>';
        
        // Load HTML content
        $dompdf->loadHtml($html);
        
        // Set paper size
        $dompdf->setPaper('A4', 'portrait');
        
        // Render PDF
        $dompdf->render();
        
        // Output PDF to browser with filename
        $dompdf->stream("Rapport_Projets_" . date("Y-m-d") . ".pdf", array("Attachment" => true));
        exit();
    }
} 