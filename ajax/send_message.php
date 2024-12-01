<?php
session_start();
require_once '../includes/db_connexion.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['interlocuteur_id']) || !isset($_POST['text'])) {
    echo json_encode(['success' => false, 'error' => 'Données manquantes']);
    exit;
}

$sender_id = $_SESSION['user_id'];
$receiver_id = $_POST['interlocuteur_id'];
$text = trim($_POST['text']);

if (empty($text)) {
    echo json_encode(['success' => false, 'error' => 'Message vide']);
    exit;
}

try {
    $query = "INSERT INTO Message (Text, Date_message, ID_membre_1, ID_membre_2) 
              VALUES (:text, NOW(), :sender_id, :receiver_id)";
    
    $stmt = $pdo->prepare($query);
    $success = $stmt->execute([
        'text' => $text,
        'sender_id' => $sender_id,
        'receiver_id' => $receiver_id
    ]);

    echo json_encode(['success' => $success]);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>