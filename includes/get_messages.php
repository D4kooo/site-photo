
<?php
session_start();
require_once 'db_connexion.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['user_id'])) {
    exit(json_encode([]));
}

$query = "SELECT *, 
          ID_membre_1 = :current_user as is_sender 
          FROM Message 
          WHERE (ID_membre_1 = :current_user AND ID_membre_2 = :other_user)
          OR (ID_membre_1 = :other_user AND ID_membre_2 = :current_user)
          ORDER BY Date_message ASC";

$stmt = $pdo->prepare($query);
$stmt->execute([
    'current_user' => $_SESSION['user_id'],
    'other_user' => $_GET['user_id']
]);

$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
header('Content-Type: application/json');
echo json_encode($messages);
?>