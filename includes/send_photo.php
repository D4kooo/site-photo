<?php
session_start();
require_once 'db_connexion.php';

// Empêcher toute sortie HTML et définir le header
ob_clean();
header('Content-Type: application/json');

// Configuration des logs d'erreur
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/upload_errors.log');

// Vérification des données
if (!isset($_SESSION['user_id']) || !isset($_FILES['photo']) || !isset($_POST['receiver_id'])) {
    die(json_encode(['success' => false, 'message' => 'Données manquantes']));
}

// Création et vérification du dossier d'upload
$upload_dir = dirname(__DIR__) . '/uploads/messages/';
if (!file_exists($upload_dir)) {
    if (!@mkdir($upload_dir, 0777, true)) {
        error_log("Erreur création dossier: " . error_get_last()['message']);
        die(json_encode(['success' => false, 'message' => "Erreur création dossier"]));
    }
}

// Vérification des permissions
if (!is_writable($upload_dir)) {
    @chmod($upload_dir, 0777);
    if (!is_writable($upload_dir)) {
        error_log("Dossier non accessible en écriture: " . $upload_dir);
        die(json_encode(['success' => false, 'message' => "Erreur permissions dossier"]));
    }
}

// Vérification et traitement du fichier
$file = $_FILES['photo'];
if ($file['error'] !== UPLOAD_ERR_OK) {
    $error_message = match($file['error']) {
        UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Fichier trop volumineux',
        UPLOAD_ERR_PARTIAL => 'Upload incomplet',
        UPLOAD_ERR_NO_FILE => 'Aucun fichier',
        UPLOAD_ERR_NO_TMP_DIR => 'Dossier temporaire manquant',
        UPLOAD_ERR_CANT_WRITE => 'Erreur écriture disque',
        UPLOAD_ERR_EXTENSION => 'Extension PHP bloquante',
        default => 'Erreur inconnue'
    };
    die(json_encode(['success' => false, 'message' => $error_message]));
}

// Génération du nom de fichier unique
$new_filename = uniqid('msg_', true) . '_' . bin2hex(random_bytes(8)) . '.jpg';
$upload_path = $upload_dir . $new_filename;

try {
    if (!@move_uploaded_file($file['tmp_name'], $upload_path)) {
        throw new Exception("Échec du déplacement du fichier");
    }

    $stmt = $pdo->prepare("INSERT INTO Message (ID_membre_1, ID_membre_2, Text, Date_message, Type) 
                          VALUES (:sender_id, :receiver_id, :photo_path, NOW(), 'photo')");
    
    $photo_url = '/site-photo/uploads/messages/' . $new_filename;
    
    if (!$stmt->execute([
        'sender_id' => $_SESSION['user_id'],
        'receiver_id' => $_POST['receiver_id'],
        'photo_path' => $photo_url
    ])) {
        throw new Exception("Échec de l'enregistrement en base de données");
    }

    die(json_encode([
        'success' => true,
        'photo_url' => $photo_url
    ]));

} catch (Exception $e) {
    error_log("Erreur upload: " . $e->getMessage());
    if (file_exists($upload_path)) {
        @unlink($upload_path);
    }
    die(json_encode([
        'success' => false,
        'message' => "Erreur lors du traitement: " . $e->getMessage()
    ]));
}