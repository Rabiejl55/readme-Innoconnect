<?php
require_once 'models/Category.php';

class CategoryController {
    private $categoryModel;
    
    public function __construct() {
        $this->categoryModel = new Category();
    }
    
    // Display all categories - SIMPLIFIED VERSION
    public function index() {
        try {
            // Get categories from model
            $categories = $this->categoryModel->getAllCategories();
            
            // Include view file
            require_once 'views/categories/index.php';
            
        } catch (Exception $e) {
            $errorTitle = 'Erreur lors du chargement des catégories';
            $errorMessage = $e->getMessage();
            require_once 'views/templates/error.php';
        }
    }
    
    // Display form to create a new category
    public function create() {
        require_once 'views/categories/create.php';
    }
    
    // Handle category creation form submission
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = $_POST['nom'] ?? '';
            $description = $_POST['description'] ?? '';
            
            if (empty($nom)) {
                $_SESSION['error'] = "Le nom de la catégorie est obligatoire";
                header('Location: index.php?controller=category&action=create');
                return;
            }
            
            $success = $this->categoryModel->createCategory($nom, $description);
            
            if ($success) {
                $_SESSION['success'] = "Catégorie créée avec succès";
                header('Location: index.php?controller=category&action=index');
            } else {
                $_SESSION['error'] = "Erreur lors de la création de la catégorie";
                header('Location: index.php?controller=category&action=create');
            }
        }
    }
    
    // Display form to edit an existing category
    public function edit($id) {
            $category = $this->categoryModel->getCategoryById($id);
            
            if (!$category) {
            $_SESSION['error'] = "Catégorie non trouvée";
            header('Location: index.php?controller=category&action=index');
            return;
        }
        
        require_once 'views/categories/edit.php';
    }
    
    // Handle category update form submission
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id_categorie'] ?? 0;
            $nom = $_POST['nom'] ?? '';
            $description = $_POST['description'] ?? '';
            
            if (empty($id) || empty($nom)) {
                $_SESSION['error'] = "L'ID et le nom de la catégorie sont obligatoires";
                header('Location: index.php?controller=category&action=edit&id=' . $id);
                return;
            }
            
            $success = $this->categoryModel->updateCategory($id, $nom, $description);
            
            if ($success) {
                $_SESSION['success'] = "Catégorie mise à jour avec succès";
                header('Location: index.php?controller=category&action=index');
            } else {
                $_SESSION['error'] = "Erreur lors de la mise à jour de la catégorie";
                header('Location: index.php?controller=category&action=edit&id=' . $id);
            }
        }
    }
    
    // Display confirmation before deleting a category
    public function delete($id) {
            $category = $this->categoryModel->getCategoryById($id);
            
            if (!$category) {
                $_SESSION['error'] = "Catégorie non trouvée";
            header('Location: index.php?controller=category&action=index');
            return;
        }
        
        require_once 'views/categories/delete.php';
    }
    
    // Handle category deletion
    public function destroy() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id_categorie'] ?? 0;
            
            if (empty($id)) {
                $_SESSION['error'] = "ID de catégorie invalide";
                header('Location: index.php?controller=category&action=index');
                return;
            }
            
            // Vérifier si la catégorie est utilisée par des projets
            if ($this->categoryModel->isCategoryUsed($id)) {
                $_SESSION['error'] = "Impossible de supprimer cette catégorie car elle est utilisée par un ou plusieurs projets.";
                header('Location: index.php?controller=category&action=index');
                return;
            }
            
            $success = $this->categoryModel->deleteCategory($id);
            
            if ($success) {
                $_SESSION['success'] = "Catégorie supprimée avec succès";
            } else {
                $_SESSION['error'] = "Erreur lors de la suppression de la catégorie";
            }
            
            header('Location: index.php?controller=category&action=index');
        }
    }
    
    // Display details of a category
    public function show($id) {
        try {
            // Check if ID is provided
            if ($id === null) {
                $_SESSION['error'] = "ID de catégorie non spécifié";
                header('Location: index.php?controller=category&action=index');
                return;
            }
            
            // Get category data
            $category = $this->categoryModel->getCategoryById($id);
            
            if (!$category) {
                $_SESSION['error'] = "Catégorie non trouvée";
                header('Location: index.php?controller=category&action=index');
                return;
            }
            
            // Include the view file
            require_once 'views/categories/show.php';
            
        } catch (Exception $e) {
            echo "<div style='background-color: #f8d7da; color: #721c24; padding: 15px; margin: 15px; border-radius: 5px;'>";
            echo "<h3>Erreur dans CategoryController::show</h3>";
            echo "<p>" . $e->getMessage() . "</p>";
            echo "</div>";
        }
    }
    
    // Display simplified form to edit a category
    public function edit_simple($id = null) {
        try {
            // Set error display for debugging
            ini_set('display_errors', 1);
            error_reporting(E_ALL);
            
            echo "<!-- Starting CategoryController::edit_simple method with ID: " . ($id !== null ? $id : "null") . " -->";
            
            // Check if ID is provided
            if ($id === null) {
                $_SESSION['error'] = "ID de catégorie non spécifié";
                header('Location: index.php?controller=category&action=index');
                return;
            }
            
            // Get category data
            $category = $this->categoryModel->getCategoryById($id);
            
            if (!$category) {
                $_SESSION['error'] = "Catégorie non trouvée";
                header('Location: index.php?controller=category&action=index');
                return;
            }
            
            // Include the simplified view
            require_once 'views/categories/edit_simple.php';
            
            echo "<!-- Edit simple view loaded successfully -->";
            
        } catch (Exception $e) {
            echo "<div style='background-color: #f8d7da; color: #721c24; padding: 15px; margin: 15px; border-radius: 5px;'>";
            echo "<h3>Erreur dans CategoryController::edit_simple</h3>";
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
            
            echo "<!-- Starting CategoryController::delete_simple method with ID: " . ($id !== null ? $id : "null") . " -->";
            
            // Check if ID is provided
            if ($id === null) {
                $_SESSION['error'] = "ID de catégorie non spécifié";
                header('Location: index.php?controller=category&action=index');
                return;
            }
            
            // Get category data
            $category = $this->categoryModel->getCategoryById($id);
            
            if (!$category) {
                $_SESSION['error'] = "Catégorie non trouvée";
                header('Location: index.php?controller=category&action=index');
                return;
            }
            
            // Include the simplified view
            require_once 'views/categories/delete_simple.php';
            
            echo "<!-- Delete simple view loaded successfully -->";
            
        } catch (Exception $e) {
            echo "<div style='background-color: #f8d7da; color: #721c24; padding: 15px; margin: 15px; border-radius: 5px;'>";
            echo "<h3>Erreur dans CategoryController::delete_simple</h3>";
            echo "<p>" . $e->getMessage() . "</p>";
            echo "</div>";
        }
    }
}
?> 