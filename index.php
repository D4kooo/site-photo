<?php
session_start();

require_once 'includes/db_connexion.php';
require_once 'controllers/postController.php';
require_once 'controllers/eventController.php';

// Créer des instances des contrôleurs
$postController = new postController($pdo);
$eventController = new eventController($pdo);

// Capturer le contenu des posts
ob_start();
$postController->list();
$content_post = ob_get_clean();

// Capturer le contenu des événements
ob_start();
$eventController->list();
$content_event = ob_get_clean();

// Vérifie s'il y a un message à afficher
$message = isset($_SESSION['message_modal']) ? $_SESSION['message_modal'] : '';
$message_type = isset($_SESSION['message_modal_type']) ? $_SESSION['message_modal_type'] : 'error';

// Supprimer les messages de la session après les avoir récupérés
unset($_SESSION['message_modal']);
unset($_SESSION['message_modal_type']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Club Nailloux</title>
    <link rel="stylesheet" href="assets/css/header.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/auth.css">
    <script src="script/script.js"></script>

</head>
<body>
    <!-- popup si un message de succès est présent -->
    <?php if (!empty($message) && $message_type === 'success'): ?>
        <?php
            include 'views/message_popup.php';
        ?>
    <?php endif; ?>

    <!-- Header -->
    <?php include 'includes/header.php'; ?>

    <!-- Contenu principal -->
    <main>

        <!-- Section des events -->
        <section id="events-section">
            <?php
            // Afficher le contenu généré par le contrôleur
            echo $content_event;
            ?>
        </section>

        <?php if (isset($_SESSION['user_id'])): ?>
            <!-- Section des posts -->
            <section id="posts-container">
                <?php
                // Afficher le contenu généré par le contrôleur
                echo $content_post;
                ?>
            </section>

        <?php else: ?>
            <?php include 'auth/register.php'; ?>
        <?php endif; ?>

    </main>

    <!-- Bouton "+" -->
    <?php if (isset($_SESSION['user_id'])): ?>
        <button id="add-post-button" class="add-post-button" aria-label="Ajouter un post" onclick="openModal('add-modal')">
            <div class="plus-sign">
                <div class="horizontal"></div>
                <div class="vertical"></div>
            </div>
        </button>
    <?php endif; ?>

    <!-- Modale pour ajouter un post ou un événement -->
    <div id="add-modal" class="modal">
        <div class="modal-content">
            <span class="close-button" onclick="closeModal('add-modal')">&times;</span>

            <!-- Onglets pour basculer entre Post et Événement -->
            <div class="modal-tabs">
                <button class="tab-button active" id="post-tab" onclick="openTab('post-form', 'event-form', 'post-tab', 'event-tab')">Post</button>
                <button class="tab-button" id="event-tab" onclick="openTab('event-form', 'post-form', 'event-tab', 'post-tab')">Événement</button>
            </div>

            <!-- Formulaire d'ajout de post -->
            <div id="post-form" class="tab-content active">
                <!-- Affichage des messages d'erreur ou de succès -->
                <?php if (!empty($message) && $message_type === 'error'): ?>
                    <div class="message <?php echo $message_type; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <h2>Ajouter un Post</h2>
                <form id="add-post-form" method="post" action="controllers/controller.php?controller=post&action=add" enctype="multipart/form-data">
                    <input type="text" name="titre" placeholder="Titre du post" required>

                    <label for="visibilite">Visibilité :</label>
                    <select name="visibilite" id="visibilite" required>
                        <option value="Publique">Publique</option>
                        <option value="Privée">Privée</option>
                    </select>

                    <input type="text" name="mots_cles" placeholder="Mots-clés (séparés par des virgules)">

                    <textarea name="description" placeholder="Description" required></textarea>
                    
                    <label>
                        <input type="checkbox" name="watermark" value="1"> Ajouter un filigrane
                    </label>
                    
                    <input type="file" name="photo" accept="image/*" required>
                    
                    <button type="submit">Publier</button>
                </form>
            </div>

            <!-- Formulaire d'ajout d'événement -->
            <div id="event-form" class="tab-content">
                <!-- Affichage des messages d'erreur ou de succès -->
                <?php if (!empty($message) && $message_type === 'error'): ?>
                    <div class="message <?php echo $message_type; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <h2>Créer un Événement</h2>
                <form id="add-event-form" method="post" action="controllers/controller.php?controller=event&action=add" enctype="multipart/form-data">
                    <input type="text" name="titre_event" placeholder="Titre de l'événement" required>

                    <label for="type_event">Type :</label>
                    <select name="type_event" id="type" required onchange="showThemeOptions()">
                        <option value="Cours">Cours</option>
                        <option value="Sortie">Sortie</option>
                        <option value="Exposition">Exposition</option>
                        <option value="Réunion">Réunion</option>
                        <option value="Information">Information</option>
                        <option value="Collaboration">Collaboration</option>
                        <option value="Visionnage">Visionnage</option>
                    </select>

                    <input type="text" name="theme" id="theme-section" placeholder="Thème de l'évènement">

                    <label for="visibilite">Visibilité :</label>
                    <select name="visibilite" id="type" required>
                        <option value="Publique">Publique</option>
                        <option value="Privée">Privée</option>
                        <option value="Animateur">Animateur</option>
                    </select>

                    <label for="date_event">Date de l'événement :</label>
                    <input type="date" name="date_event" id="date_event" required>

                    <input type="file" name="photo" accept="image/*" required>

                    <input type="number" name="nb_photo" id="nb_photo" placeholder="Nombre de photo déposable par membre" required>

                    <textarea name="description_event" placeholder="Description de l'événement" required></textarea>

                    <button type="submit">Créer l'événement</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        var message = "<?php echo $message; ?>";
        var messageType = "<?php echo $message_type; ?>";

        // Si un message d'erreur est présent, ouvrir la modale
        if (message !== "" && messageType === "error") {
            document.getElementById('add-modal').style.display = 'block';
        }
    </script>

    <!-- Pied de page -->
    <?php include 'includes/footer.php'; ?>
