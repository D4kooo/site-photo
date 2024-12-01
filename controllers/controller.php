<?php

session_start();

require_once '../includes/db_connexion.php';
require_once 'postController.php';
require_once 'eventController.php';
require_once 'commentController.php';

// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    // Redirige vers la page de connexion s'il n'est pas connecté
    header('Location: ../auth/login.php');
    exit;
}

// Récupération du contrôleur et de l'action depuis les paramètres GET
$controllerName = isset($_GET['controller']) ? $_GET['controller'] : 'post';
$action = isset($_GET['action']) ? $_GET['action'] : 'list';

// Instanciation du contrôleur approprié
switch ($controllerName) {
    case 'post':
        $controller = new postController($pdo);
        break;
    case 'event':
        $controller = new eventController($pdo);
        break;
    case 'comment':
        $controller = new commentController($pdo);
        break;
    case 'profile':
        $controller->profile();
        break;
    default:
        die("Contrôleur non reconnu.");
}

// Router l'action vers la méthode appropriée du contrôleur
switch ($action) {
    case 'list':
        $controller->list();
        break;
    case 'add':
        $controller->add();
        break;
    case 'edit':
        $controller->edit();
        break;
    case 'delete':
        $controller->delete();
        break;
    case 'loadPost':
        $controller->loadPost();
        break;
    default:
        die("Action non reconnue.");
}
?>
