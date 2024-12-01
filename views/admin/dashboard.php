<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /site-photo/index.php');
    exit();
}

require_once '../../includes/db_connexion.php';

// Récupérer tous les membres
$req = $pdo->prepare('SELECT ID_membre as id, Pseudo as username, Email as email, Role as role FROM Membre');
$req->execute();
$users = $req->fetchAll(PDO::FETCH_ASSOC);

// Récupérer tous les posts
$req_posts = $pdo->prepare('
    SELECT p.*, m.Pseudo as auteur 
    FROM Post p 
    JOIN Membre m ON p.ID_membre = m.ID_membre 
    ORDER BY p.Date_post DESC
');
$req_posts->execute();
$posts = $req_posts->fetchAll(PDO::FETCH_ASSOC);

// Récupérer tous les événements
$req_events = $pdo->prepare('
    SELECT e.*, m.Pseudo as organisateur 
    FROM Evenement e 
    JOIN Membre m ON e.ID_membre = m.ID_membre 
    ORDER BY e.Date_event DESC
');
$req_events->execute();
$events = $req_events->fetchAll(PDO::FETCH_ASSOC);

// Récupérer tous les commentaires
$req_comments = $pdo->prepare('
    SELECT c.ID_post, c.ID_membre, c.Texte, p.Titre as post_titre, m.Pseudo as auteur 
    FROM Commenter_post c
    JOIN Post p ON c.ID_post = p.ID_post 
    JOIN Membre m ON c.ID_membre = m.ID_membre 
    ORDER BY c.ID_post DESC
');
$req_comments->execute();
$comments = $req_comments->fetchAll(PDO::FETCH_ASSOC);

// Ajout des requêtes pour les statistiques après les autres requêtes
// Statistiques utilisateurs
$stats_users = $pdo->query('
    SELECT 
        COUNT(*) as total_users,
        COUNT(CASE WHEN Role = "admin" THEN 1 END) as total_admins,
        COUNT(CASE WHEN Role = "moderator" THEN 1 END) as total_mods
    FROM Membre
')->fetch(PDO::FETCH_ASSOC);

// Statistiques posts
$stats_posts = $pdo->query('
    SELECT 
        COUNT(*) as total_posts,
        COUNT(CASE WHEN Visibilite = "Publique" THEN 1 END) as public_posts,
        COUNT(CASE WHEN Visibilite = "Privée" THEN 1 END) as private_posts,
        COUNT(CASE WHEN Date_post >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as posts_last_30_days
    FROM Post
')->fetch(PDO::FETCH_ASSOC);

// Statistiques événements
$stats_events = $pdo->query('
    SELECT 
        COUNT(*) as total_events,
        COUNT(CASE WHEN Date_event >= CURDATE() THEN 1 END) as upcoming_events,
        COUNT(CASE WHEN Date_event < CURDATE() THEN 1 END) as past_events
    FROM Evenement
')->fetch(PDO::FETCH_ASSOC);

// Statistiques commentaires
$stats_comments = $pdo->query('
    SELECT 
        COUNT(*) as total_comments,
        COUNT(DISTINCT ID_membre) as unique_commenters
    FROM Commenter_post
')->fetch(PDO::FETCH_ASSOC);

// Récupérer les messages non lus
$req_messages = $pdo->prepare('
    SELECT m.ID_message, m.Text, m.Date_message, m.ID_membre_1, m.ID_membre_2, m.Type,
           exp.Pseudo as expediteur
    FROM Message m
    JOIN Membre exp ON m.ID_membre_1 = exp.ID_membre
    WHERE m.ID_membre_2 = ?
    ORDER BY m.Date_message DESC
');
$req_messages->execute([$_SESSION['user_id']]);
$unread_messages = $req_messages->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <link rel="stylesheet" href="../../assets/css/header.css">
    <link rel="stylesheet" href="../../assets/css/admin.css">
</head>
<body>
    <?php require_once '../../includes/header.php'; ?>
    
    <div class="container mt-4">
        <!-- Contenu des sections directement affiché sans onglets -->
        <section id="users">
            <h2>Gestion des utilisateurs</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom d'utilisateur</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['id']) ?></td>
                        <td><?= htmlspecialchars($user['username']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><?= htmlspecialchars($user['role']) ?></td>
                        <td>
                            <a href="edit_member.php?id=<?= $user['id'] ?>" 
                               class="btn btn-info btn-sm">
                                Modifier les infos
                            </a>
                            <a href="delete_member.php?id=<?= $user['id'] ?>" 
                               class="btn btn-danger btn-sm"
                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce membre ? Cette action est irréversible.')">
                                Supprimer le membre
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>

        <section id="posts">
            <h2>Gestion des posts</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Titre</th>
                        <th>Auteur</th>
                        <th>Date</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($posts as $post): ?>
                    <tr>
                        <td><?= htmlspecialchars($post['ID_post']) ?></td>
                        <td><?= htmlspecialchars($post['Titre']) ?></td>
                        <td><?= htmlspecialchars($post['auteur']) ?></td>
                        <td><?= htmlspecialchars($post['Date_post']) ?></td>
                        <td><?= htmlspecialchars($post['Visibilite']) ?></td>
                        <td>
                            <a href="edit_post.php?id=<?= $post['ID_post'] ?>" class="btn btn-primary btn-sm">Modifier</a>
                            <a href="delete_post.php?id=<?= $post['ID_post'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce post ?')">Supprimer</a>
                            <?php if ($post['Visibilite'] === 'En attente'): ?>
                                <a href="approve_post.php?id=<?= $post['ID_post'] ?>" class="btn btn-success btn-sm">Approuver</a>
                                <a href="reject_post.php?id=<?= $post['ID_post'] ?>" class="btn btn-warning btn-sm">Rejeter</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>

        <section id="events">
            <h2>Gestion des événements</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Titre</th>
                        <th>Organisateur</th>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Inscrits</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($events as $event): ?>
                    <tr>
                        <td><?= htmlspecialchars($event['ID_evenement']) ?></td>
                        <td><?= htmlspecialchars($event['Titre']) ?></td>
                        <td><?= htmlspecialchars($event['organisateur']) ?></td>
                        <td><?= htmlspecialchars($event['Date_event']) ?></td>
                        <td><?= htmlspecialchars($event['Type']) ?></td>
                        <td>
                            <a href="view_participants.php?id=<?= $event['ID_evenement'] ?>" 
                               class="btn btn-info btn-sm">
                                Voir les participants
                            </a>
                        </td>
                        <td>
                            <a href="edit_event.php?id=<?= $event['ID_evenement'] ?>" 
                               class="btn btn-primary btn-sm">Modifier</a>
                            <a href="delete_event.php?id=<?= $event['ID_evenement'] ?>" 
                               class="btn btn-danger btn-sm" 
                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet événement ?')">
                                Supprimer
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>

        <section id="comments">
            <h2>Gestion des commentaires</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Post</th>
                        <th>Auteur</th>
                        <th>Commentaire</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($comments as $comment): ?>
                    <tr>
                        <td><?= htmlspecialchars($comment['ID_post']) ?></td>
                        <td><?= htmlspecialchars($comment['post_titre']) ?></td>
                        <td><?= htmlspecialchars($comment['auteur']) ?></td>
                        <td><?= htmlspecialchars($comment['Texte']) ?></td>
                        <td>N/A</td>
                        <td>
                            <a href="delete_comment.php?post_id=<?= $comment['ID_post'] ?>&member_id=<?= $comment['ID_membre'] ?>" 
                               class="btn btn-danger btn-sm"
                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce commentaire ?')">
                                Supprimer
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>

        <section id="stats">
            <h2>Statistiques générales</h2>
            
            <!-- Statistiques utilisateurs -->
            <div class="stats-section">
                <h3>Utilisateurs</h3>
                <div class="stats-grid">
                    <div class="stat-card">
                        <span class="stat-value"><?= $stats_users['total_users'] ?></span>
                        <span class="stat-label">Utilisateurs totaux</span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-value"><?= $stats_users['total_admins'] ?></span>
                        <span class="stat-label">Administrateurs</span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-value"><?= $stats_users['total_mods'] ?></span>
                        <span class="stat-label">Modérateurs</span>
                    </div>
                </div>
            </div>

            <!-- Statistiques posts -->
            <div class="stats-section">
                <h3>Publications</h3>
                <div class="stats-grid">
                    <div class="stat-card">
                        <span class="stat-value"><?= $stats_posts['total_posts'] ?></span>
                        <span class="stat-label">Posts totaux</span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-value"><?= $stats_posts['public_posts'] ?></span>
                        <span class="stat-label">Posts publics</span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-value"><?= $stats_posts['posts_last_30_days'] ?></span>
                        <span class="stat-label">Posts ces 30 derniers jours</span>
                    </div>
                </div>
            </div>

            <!-- Statistiques événements -->
            <div class="stats-section">
                <h3>Événements</h3>
                <div class="stats-grid">
                    <div class="stat-card">
                        <span class="stat-value"><?= $stats_events['total_events'] ?></span>
                        <span class="stat-label">Événements totaux</span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-value"><?= $stats_events['upcoming_events'] ?></span>
                        <span class="stat-label">Événements à venir</span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-value"><?= $stats_events['past_events'] ?></span>
                        <span class="stat-label">Événements passés</span>
                    </div>
                </div>
            </div>

            <!-- Statistiques commentaires -->
            <div class="stats-section">
                <h3>Commentaires</h3>
                <div class="stats-grid">
                    <div class="stat-card">
                        <span class="stat-value"><?= $stats_comments['total_comments'] ?></span>
                        <span class="stat-label">Commentaires totaux</span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-value"><?= $stats_comments['unique_commenters'] ?></span>
                        <span class="stat-label">Commentateurs uniques</span>
                    </div>
                </div>
            </div>
        </section>

        <section id="messages">
            <h2>Messagerie interne</h2>
            
            <!-- Bouton pour nouveau message -->
            <button class="btn btn-primary mb-4" onclick="openNewMessageModal()">
                <i class="fas fa-envelope"></i> Nouveau message
            </button>

            <!-- Liste des messages -->
            <div class="messages-list">
                <?php if (empty($unread_messages)): ?>
                    <p>Aucun nouveau message</p>
                <?php else: ?>
                    <?php foreach ($unread_messages as $message): ?>
                        <div class="message-card <?= $message['Lu'] ? '' : 'unread' ?>">
                            <div class="message-header">
                                <strong><?= htmlspecialchars($message['expediteur']) ?></strong>
                                <span class="message-date"><?= htmlspecialchars($message['Date_message']) ?></span>
                            </div>
                            <div class="message-body">
                                <?= htmlspecialchars($message['Text']) ?>
                            </div>
                            <div class="message-actions">
                                <button class="btn btn-sm btn-info" onclick="replyMessage(<?= $message['ID_membre_1'] ?>)">
                                    Répondre
                                </button>
                                <button class="btn btn-sm btn-success" onclick="markAsRead(<?= $message['ID_message'] ?>)">
                                    Marquer comme lu
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <!-- Modal pour nouveau message -->
    <div class="modal" id="newMessageModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nouveau message</h5>
                    <button type="button" class="close" onclick="closeNewMessageModal()">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="messageForm" onsubmit="sendMessage(event)">
                        <div class="form-group">
                            <label>Destinataire :</label>
                            <select name="destinataire" class="form-control" required>
                                <?php foreach ($users as $user): ?>
                                    <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['username']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Message :</label>
                            <textarea name="message" class="form-control" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Envoyer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Supprimer la partie gestion des onglets
        // Garder uniquement les fonctions de messagerie
        function openNewMessageModal() {
            document.getElementById('newMessageModal').style.display = 'block';
        }

        function closeNewMessageModal() {
            document.getElementById('newMessageModal').style.display = 'none';
        }

        async function sendMessage(event) {
            event.preventDefault();
            const form = event.target;
            const formData = new FormData(form);

            try {
                const response = await fetch('send_message.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                
                if (data.success) {
                    alert('Message envoyé avec succès');
                    closeNewMessageModal();
                    location.reload();
                } else {
                    alert('Erreur lors de l\'envoi du message');
                }
            } catch (error) {
                alert('Erreur lors de l\'envoi du message');
            }
        }

        async function markAsRead(messageId) {
            try {
                const response = await fetch('mark_as_read.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ messageId })
                });
                const data = await response.json();
                
                if (data.success) {
                    location.reload();
                }
            } catch (error) {
                alert('Erreur lors du marquage du message');
            }
        }
    </script>

    <?php require_once '../../includes/footer.php'; ?>
</body>
</html>