<?php
require_once 'models/Category.php';

class CategoryController {
    private $categoryModel;
    
    public function __construct() {
        $this->categoryModel = new Category();
    }
    
    // Display all categories
    public function index() {
        $categories = $this->categoryModel->getAllCategories();
        require_once 'views/admin/categories/index.php';
    }
    
    // Show a single category
    public function show($id) {
        $category = $this->categoryModel->getCategoryById($id);
        
        if (!$category) {
            $_SESSION['error'] = "Catégorie non trouvée";
            header('Location: index.php?controller=category&action=index');
            return;
        }
        
        require_once 'views/admin/categories/show.php';
    }
    
    // Display form to create a new category
    public function create() {
        require_once 'views/admin/categories/create.php';
    }
    
    // Store a new category
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=category&action=create');
            return;
        }
        
        $nom = $_POST['nom'] ?? '';
        $description = $_POST['description'] ?? '';
        
        if (empty($nom)) {
            $_SESSION['error'] = "Le nom de la catégorie est obligatoire";
            header('Location: index.php?controller=category&action=create');
            return;
        }
        
        $result = $this->categoryModel->createCategory($nom, $description);
        
        if ($result) {
            $_SESSION['success'] = "Catégorie créée avec succès";
            header('Location: index.php?controller=category&action=index');
        } else {
            $_SESSION['error'] = "Erreur lors de la création de la catégorie";
            header('Location: index.php?controller=category&action=create');
        }
    }
    
    // Display form to edit a category
    public function edit($id) {
        $category = $this->categoryModel->getCategoryById($id);
        
        if (!$category) {
            $_SESSION['error'] = "Catégorie non trouvée";
            header('Location: index.php?controller=category&action=index');
            return;
        }
        
        require_once 'views/admin/categories/edit.php';
    }
    
    // Update a category
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=category&action=index');
            return;
        }
        
        $id = $_POST['id_categorie'] ?? 0;
        $nom = $_POST['nom'] ?? '';
        $description = $_POST['description'] ?? '';
        
        if (empty($id) || empty($nom)) {
            $_SESSION['error'] = "Le nom de la catégorie est obligatoire";
            header("Location: index.php?controller=category&action=edit&id=$id");
            return;
        }
        
        $result = $this->categoryModel->updateCategory($id, $nom, $description);
        
        if ($result) {
            $_SESSION['success'] = "Catégorie mise à jour avec succès";
            header('Location: index.php?controller=category&action=index');
        } else {
            $_SESSION['error'] = "Erreur lors de la mise à jour de la catégorie";
            header("Location: index.php?controller=category&action=edit&id=$id");
        }
    }
    
    // Delete confirmation
    public function delete($id) {
        $category = $this->categoryModel->getCategoryById($id);
        
        if (!$category) {
            $_SESSION['error'] = "Catégorie non trouvée";
            header('Location: index.php?controller=category&action=index');
            return;
        }
        
        $isUsed = $this->categoryModel->isCategoryUsed($id);
        
        if ($isUsed) {
            $_SESSION['error'] = "Cette catégorie est utilisée par des projets et ne peut pas être supprimée";
            header('Location: index.php?controller=category&action=index');
            return;
        }
        
        require_once 'views/admin/categories/delete.php';
    }
    
    // Perform the deletion
    public function destroy() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=category&action=index');
            return;
        }
        
        $id = $_POST['id_categorie'] ?? 0;
        
        if (empty($id)) {
            $_SESSION['error'] = "ID de catégorie invalide";
            header('Location: index.php?controller=category&action=index');
            return;
        }
        
        // Check if category is used in any projects
        if ($this->categoryModel->isCategoryUsed($id)) {
            $_SESSION['error'] = "Cette catégorie est utilisée par des projets et ne peut pas être supprimée";
            header('Location: index.php?controller=category&action=index');
            return;
        }
        
        $result = $this->categoryModel->deleteCategory($id);
        
        if ($result) {
            $_SESSION['success'] = "Catégorie supprimée avec succès";
        } else {
            $_SESSION['error'] = "Erreur lors de la suppression de la catégorie";
        }
        
        header('Location: index.php?controller=category&action=index');
    }
    
    // Dashboard stats
    public function getStats() {
        $count = $this->categoryModel->countCategories();
        return $count;
    }
} 