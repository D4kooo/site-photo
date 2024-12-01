<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /site-photo/index.php');
    exit();
}

require_once '../../includes/db_connexion.php';

if (isset($_GET['post_id']) && isset($_GET['member_id'])) {
    $post_id = intval($_GET['post_id']);
    $member_id = intval($_GET['member_id']);
    
    $req = $pdo->prepare('DELETE FROM Commenter_post WHERE ID_post = ? AND ID_membre = ?');
    
    try {
        $req->execute([$post_id, $member_id]);
        $_SESSION['message'] = "Commentaire supprimé avec succès.";
        $_SESSION['message_type'] = "success";
    } catch (Exception $e) {
        $_SESSION['message'] = "Erreur lors de la suppression du commentaire.";
        $_SESSION['message_type'] = "error";
    }
}

header('Location: dashboard.php');
exit;