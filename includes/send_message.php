
<?php
session_start();
require_once 'db_connexion.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['recipient_id']) || !isset($data['message'])) {
    http_response_code(400);
    exit;
}

$query = "INSERT INTO Message (ID_membre_1, ID_membre_2, Text, Date_message) 
          VALUES (:sender_id, :recipient_id, :message, NOW())";

$stmt = $pdo->prepare($query);
$success = $stmt->execute([
    'sender_id' => $_SESSION['user_id'],
    'recipient_id' => $data['recipient_id'],
    'message' => $data['message']
]);

http_response_code($success ? 200 : 500);
?>