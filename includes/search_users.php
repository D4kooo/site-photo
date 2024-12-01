
<?php
session_start();
require_once 'db_connexion.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['term'])) {
    exit(json_encode([]));
}

$term = '%' . $_GET['term'] . '%';
$query = "SELECT ID_membre, Pseudo, Photo_profil 
          FROM Membre 
          WHERE Pseudo LIKE :term 
          AND ID_membre != :user_id 
          LIMIT 10";

$stmt = $pdo->prepare($query);
$stmt->execute([
    'term' => $term,
    'user_id' => $_SESSION['user_id']
]);

$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
header('Content-Type: application/json');
echo json_encode($users);
?>