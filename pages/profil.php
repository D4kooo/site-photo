<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/db_connexion.php';
require_once '../controllers/profilController.php';

// Récupérer le nom d'utilisateur depuis l'URL
if (isset($_GET['username']) && !empty($_GET['username'])) {
    $username = $_GET['username'];
} else {
    $userNotFound = true;
}

// Instancier le contrôleur et afficher le profil
$controller = new profilController($pdo);
$controller->profil($username);
?>
