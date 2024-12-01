
<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /site-photo/index.php');
    exit();
}

require_once '../../includes/db_connexion.php';

$event_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Récupérer les informations de l'événement
$req_event = $pdo->prepare('
    SELECT e.*, m.Pseudo as organisateur 
    FROM Evenement e
    JOIN Membre m ON e.ID_membre = m.ID_membre
    WHERE e.ID_evenement = ?
');
$req_event->execute([$event_id]);
$event = $req_event->fetch();

// Récupérer la liste des inscrits
$req_inscrits = $pdo->prepare('
    SELECT m.ID_membre, m.Pseudo, m.Email, i.Date_inscription
    FROM Inscription i
    JOIN Membre m ON i.ID_membre = m.ID_membre
    WHERE i.ID_evenement = ?
    ORDER BY i.Date_inscription DESC
');
$req_inscrits->execute([$event_id]);
$inscrits = $req_inscrits->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscriptions - <?= htmlspecialchars($event['Titre']) ?></title>
    <link rel="stylesheet" href="../../assets/css/header.css">
    <link rel="stylesheet" href="../../assets/css/admin.css">
</head>
<body>
    <?php require_once '../../includes/header.php'; ?>
    
    <div class="container mt-4">
        <h2>Inscriptions pour : <?= htmlspecialchars($event['Titre']) ?></h2>
        <p>
            Date : <?= htmlspecialchars($event['Date_event']) ?><br>
            Organisateur : <?= htmlspecialchars($event['organisateur']) ?>
        </p>

        <table class="table">
            <thead>
                <tr>
                    <th>Membre</th>
                    <th>Email</th>
                    <th>Date d'inscription</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($inscrits as $inscrit): ?>
                <tr>
                    <td><?= htmlspecialchars($inscrit['Pseudo']) ?></td>
                    <td><?= htmlspecialchars($inscrit['Email']) ?></td>
                    <td><?= htmlspecialchars($inscrit['Date_inscription']) ?></td>
                    <td>
                        <a href="remove_inscription.php?event_id=<?= $event_id ?>&member_id=<?= $inscrit['ID_membre'] ?>" 
                           class="btn btn-danger btn-sm"
                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette inscription ?')">
                            Désinscrire
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <a href="dashboard.php" class="btn btn-primary">Retour au tableau de bord</a>
    </div>

    <?php require_once '../../includes/footer.php'; ?>
</body>
</html>