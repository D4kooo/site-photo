<?php 
    // Vérifier si l'utilisateur est connecté
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../auth/login.php");
        exit();
    }
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil de <?php echo htmlspecialchars($membre['Pseudo']); ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/profil.css">
    <link rel="stylesheet" href="../assets/css/header.css">
    <script src="../script/script.js"></script>
</head>

<body>
    <!-- Inclusion du header -->
    <?php include '../includes/header.php'; ?>

    <main class="profil-page main">
        <?php if (isset($userNotFound)): ?>
            <section class="error-message">
                <h1>Utilisateur non trouvé</h1>
                <p>Désolé, l'utilisateur que vous recherchez n'existe pas ou n'est pas accessible.</p>
            </section>
        <?php else: ?>

            <!-- Affichage du profil si l'utilisateur est trouvé -->
            <section class="profil-header">
                <div class="profil-picture">
                    <img src="../uploads/pp/<?php echo htmlspecialchars($membre['Photo_profil'] ?? 'defaultpp.svg'); ?>" alt="Photo de profil">
                </div>
                <div class="profil-info">
                <h1>
                    <?php echo htmlspecialchars($membre['Pseudo']); ?>
                    <span class="status-icon">
                        <img 
                            src="../assets/img/<?php echo htmlspecialchars($membre['Statut']) ?? 'Invalidé'; ?>.svg" 
                            alt="<?php echo htmlspecialchars($membre['Statut']); ?>" 
                            height="18px">
                        <span class="tooltip">
                            <?php echo $membre['Statut']?>
                        </span>
                    </span>
                </h1>
                    <p>Email : <?php echo htmlspecialchars($membre['Email']); ?></p>
                    <p>Nom : <?php echo htmlspecialchars($membre['Nom'] . ' ' . $membre['Prenom']); ?></p>
                </div>
            </section>

            <section class="profil-gallery">
                <h2>Galerie</h2>
                <div class="gallery-grid">
                    <?php foreach ($photos as $photo): ?>
                        <div class="gallery-item" data-post-id="<?php echo htmlspecialchars($photo['ID_post']); ?>">
                            <img src="../uploads/posts/opti/<?php echo htmlspecialchars($photo['ID_photo']); ?>" alt="Photo">
                            <p>Prise le : <?php echo (new DateTime($photo['Date_photo']))->format('d M Y'); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <!-- Modale pour afficher le post -->
        <div id="post-modal" class="modal">
            <div class="modal-content-post" id="post-modal-content">
                <!-- Croise statique -->
                <span class="close-button-post" onclick="closeModal('post-modal')">&times;</span>
                <!-- Le contenu chargé par AJAX sera ajouté ici -->
                <div id="post-content"></div>
            </div>
        </div>


    </main>
</body>
</html>
