<?php
session_start();
require_once '../includes/db_connexion.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['interlocuteur_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$interlocuteur_id = $_GET['interlocuteur_id'];

$query = "SELECT m.*, 
          IF(m.ID_membre_1 = :user_id, 1, 0) as is_sender,
          DATE_FORMAT(m.Date_message, '%H:%i') as date
          FROM Message m
          WHERE (m.ID_membre_1 = :user_id AND m.ID_membre_2 = :interlocuteur_id)
          OR (m.ID_membre_1 = :interlocuteur_id AND m.ID_membre_2 = :user_id)
          ORDER BY m.Date_message ASC";

$stmt = $pdo->prepare($query);
$stmt->execute([
    'user_id' => $user_id,
    'interlocuteur_id' => $interlocuteur_id
]);

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>