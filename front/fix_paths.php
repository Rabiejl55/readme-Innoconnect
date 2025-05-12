<?php
/**
 * Utilitaire pour corriger les chemins dans les fichiers
 * 
 * Ce script est conçu pour être exécuté une seule fois afin de corriger les chemins dans les fichiers
 * Si vous avez des problèmes avec les chemins de fichiers, exécutez ce script
 */

// Activer l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Fonction pour remplacer les chemins dans un fichier
function replacePaths($file, $patterns, $replacements) {
    if (!file_exists($file)) {
        return false;
    }
    
    $content = file_get_contents($file);
    $newContent = str_replace($patterns, $replacements, $content);
    
    if ($content !== $newContent) {
        return file_put_contents($file, $newContent) !== false;
    }
    
    return true;
}

// Liste des fichiers à corriger
$filesToFix = [
    'index.php',
    'projects.php',
    'simple_create.php',
    'controllers/ProjectController.php',
    'controllers/CategoryController.php',
    'views/projects/index.php',
    'views/projects/create.php',
    'views/projects/edit.php',
    'views/projects/show.php',
    'views/projects/delete.php'
];

// Motifs à remplacer et leurs remplacements
$patterns = [
    '../assets/' => 'assets/',
    '../config/' => 'config/',
    '../models/' => 'models/',
    '../views/' => 'views/',
    '../controllers/' => 'controllers/',
    './assets/' => 'assets/',
    './config/' => 'config/',
    './models/' => 'models/',
    './views/' => 'views/',
    './controllers/' => 'controllers/'
];

$replacements = [
    'assets/',
    'config/',
    'models/',
    'views/',
    'controllers/',
    'assets/',
    'config/',
    'models/',
    'views/',
    'controllers/'
];

// Démarrer la sortie HTML
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Correction des Chemins</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .success {
            color: green;
        }
        .error {
            color: red;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h1>Correction des Chemins de Fichiers</h1>
    
    <p>Cet utilitaire corrige les chemins dans les fichiers pour assurer le bon fonctionnement de l'application.</p>
    
    <table>
        <tr>
            <th>Fichier</th>
            <th>Statut</th>
        </tr>
        <?php foreach ($filesToFix as $file): ?>
            <tr>
                <td><?php echo $file; ?></td>
                <td>
                    <?php 
                    if (file_exists($file)) {
                        $result = replacePaths($file, array_keys($patterns), array_values($patterns));
                        if ($result) {
                            echo '<span class="success">Corrigé</span>';
                        } else {
                            echo '<span class="error">Erreur lors de la correction</span>';
                        }
                    } else {
                        echo '<span class="error">Fichier introuvable</span>';
                    }
                    ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
    
    <h2>Récapitulatif des remplacements</h2>
    
    <table>
        <tr>
            <th>Ancien chemin</th>
            <th>Nouveau chemin</th>
        </tr>
        <?php foreach ($patterns as $old => $new): ?>
            <tr>
                <td><?php echo htmlspecialchars($old); ?></td>
                <td><?php echo htmlspecialchars($replacements[$old]); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
    
    <div style="margin-top: 30px; text-align: center;">
        <a href="index.php" style="display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px;">Retour à l'accueil</a>
    </div>
    
    <p style="margin-top: 30px; font-style: italic;">Note: Ce script est conçu pour être exécuté une seule fois. Si vous l'exécutez plusieurs fois, il n'y aura pas d'effet secondaire, mais cela n'est généralement pas nécessaire.</p>
</body>
</html> 