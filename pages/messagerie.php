<?php
session_start();
require_once '../includes/db_connexion.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login');
    exit;
}

// Récupérer la liste des conversations
$query = "SELECT DISTINCT 
          m2.ID_membre as id, 
          m2.Pseudo as username, 
          m2.Photo_profil as profile_image,
          (SELECT CASE 
              WHEN Type = 'photo' THEN 'Image'
              ELSE Text 
           END
           FROM Message msg 
           WHERE (msg.ID_membre_1 = :user_id AND msg.ID_membre_2 = m2.ID_membre)
           OR (msg.ID_membre_1 = m2.ID_membre AND msg.ID_membre_2 = :user_id)
           ORDER BY msg.Date_message DESC LIMIT 1) as last_message,
          (SELECT Date_message 
           FROM Message msg 
           WHERE (msg.ID_membre_1 = :user_id AND msg.ID_membre_2 = m2.ID_membre)
           OR (msg.ID_membre_1 = m2.ID_membre AND msg.ID_membre_2 = :user_id)
           ORDER BY msg.Date_message DESC LIMIT 1) as last_message_date
          FROM Message m
          INNER JOIN Membre m2 ON (m.ID_membre_1 = m2.ID_membre OR m.ID_membre_2 = m2.ID_membre)
          WHERE (m.ID_membre_1 = :user_id OR m.ID_membre_2 = :user_id)
          AND m2.ID_membre != :user_id
          GROUP BY m2.ID_membre
          ORDER BY last_message_date DESC";

$stmt = $pdo->prepare($query);
$stmt->execute(['user_id' => $_SESSION['user_id']]);
$conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer tous les membres (sauf l'utilisateur actuel)
$query_membres = "SELECT ID_membre, Pseudo, Photo_profil FROM Membre 
                 WHERE ID_membre != :user_id 
                 ORDER BY Pseudo ASC";
$stmt_membres = $pdo->prepare($query_membres);
$stmt_membres->execute(['user_id' => $_SESSION['user_id']]);
$membres = $stmt_membres->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Messagerie</title>
    <link rel="stylesheet" href="../assets/css/header.css">
    <link rel="stylesheet" href="../assets/css/messagerie.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <?php include('../includes/header.php'); ?>
    
    <div class="messagerie-container">
        <div class="conversations-list">
            <div class="new-message-container">
                <button id="new-message-btn">Nouveau message</button>
            </div>
            <div class="search-container">
                <input type="text" id="search-bar" placeholder="Rechercher une conversation...">
            </div>
            
            <!-- Modal nouveau message -->
            <div id="new-message-modal" class="modal">
                <div class="modal-content">
                    <span class="close">&times;</span>
                    <h2>Nouveau message</h2>
                    <input type="text" id="recipient-search" placeholder="Rechercher un utilisateur...">
                    <div id="recipients-list">
                        <?php foreach ($membres as $membre): ?>
                            <div class="user-item" 
                                 data-user-id="<?= htmlspecialchars($membre['ID_membre']) ?>"
                                 data-username="<?= htmlspecialchars($membre['Pseudo']) ?>">
                                <img src="<?= htmlspecialchars($membre['Photo_profil']) ?>" 
                                     alt="<?= htmlspecialchars($membre['Pseudo']) ?>" 
                                     class="user-avatar">
                                <span><?= htmlspecialchars($membre['Pseudo']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <?php foreach ($conversations as $conv): ?>
            <div class="conversation-item" 
                 data-interlocuteur="<?= htmlspecialchars($conv['username']) ?>"
                 data-interlocuteur-id="<?= $conv['id'] ?>">
                <img src="<?= htmlspecialchars($conv['profile_image']) ?>" 
                     alt="<?= htmlspecialchars($conv['username']) ?>" 
                     class="conversation-user-img">
                <div class="conversation-info">
                    <h3><?= htmlspecialchars($conv['username']) ?></h3>
                    <p><?= htmlspecialchars(strlen($conv['last_message']) > 30 ? 
                          substr($conv['last_message'], 0, 30) . '...' : 
                          $conv['last_message']) ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="conversation-content">
            <div class="conversation-header"></div>
            <div class="conversation-messages"></div>
            <div class="image-preview-container" style="display: none;">
                <div class="preview-content">
                    <img id="image-preview" src="" alt="Prévisualisation">
                    <button class="remove-preview"><i class="fas fa-times"></i></button>
                </div>
            </div>
            <div class="message-input-container">
                <label for="photo-upload" class="photo-upload-btn">
                    <i class="fas fa-image"></i>
                </label>
                <input type="file" id="photo-upload" accept="image/*" style="display: none;">
                <textarea id="message-input" placeholder="Écrivez votre message..."></textarea>
                <button id="send-button">Envoyer</button>
            </div>
        </div>
    </div>
    
    <script src="/site-photo/assets/script/messagerie.js"></script>
</body>
</html>