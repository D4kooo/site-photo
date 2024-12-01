<section class="feed">
    <?php foreach ($posts as $post): ?>
        <div class="post">
            <!-- En-tête du post -->
            <div class="post-header">
                <!-- Image de profil -->
                <a href="/site-photo/pages/<?php echo urlencode($post['Pseudo']); ?>">
                    <img class="post-user-img" src="/site-photo/uploads/pp/<?php echo htmlspecialchars($membre['Photo_profil'] ?? 'defaultpp.svg'); ?>" alt="Photo de profil"> <!-- TODO-->
                    <div class="post-user-info">
                    <div class="post-username"><?php echo htmlspecialchars($post['Pseudo']); ?></div>
                </a>
                    <div class="post-pos-date">
                        <!-- Titre du post -->
                        <div class="post-location">
                            <p><?php echo htmlspecialchars($post['Titre']); ?></p>
                        </div>
                        <div class="post-date">
                            <?php
                            setlocale(LC_TIME, 'fr_FR.utf8');
                            $date = new DateTime($post['Date_post']); 
                            
                            // Affichage de la date
                            echo htmlspecialchars($date->format('d M Y'));?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Image du post avec le menu en haut à droite -->
            <?php if (!empty($post['ID_photo'])): ?>
                <div class="post-image-container">
                    <img src="/site-photo/uploads/posts/opti/<?php echo htmlspecialchars($post['ID_photo']); ?>" alt="Image du post">
                    
                    <!-- Trois petits points pour le sous-menu -->
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="post-menu">
                            <button class="menu-btn" onclick="toggleMenu(this)">&#x22EE;</button>
                            <div class="menu-dropdown">

                                <!-- Options spécifiques au propriétaire -->
                                <?php if ($_SESSION['user_id'] === $post['ID_membre']): ?>
                                    <a href="controllers/controller.php?controller=post&action=edit">Modifier</a>
                                    <form method="post" action="controllers/controller.php?controller=post&action=delete" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce post ?');" style="display: inline;">
                                        <input type="hidden" name="id_post" value="<?php echo $post['ID_post']; ?>">
                                        <button type="submit" class="delete-button">Supprimer</button>
                                    </form>                                    
                                <?php endif; ?>

                                <!-- Options accessibles à tout le monde -->
                                <a href="#" onclick="showMetadata(<?php echo $post['ID_post']; ?>)">Informations</a>
                                <?php if (!empty($post['ID_photo'])): ?>
                                    <a href="/site-photo/uploads/<?php echo htmlspecialchars($post['ID_photo']); ?>" download>Télécharger l'image</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>
            <?php endif; ?>

            <!-- Mots-clés -->
            <?php if (!empty($post['Mots_cles'])): ?>
                <div class="post-keywords">
                    <?php
                    $keywords = explode(',', $post['Mots_cles']);
                    foreach ($keywords as $keyword):
                    ?>
                        <span class="keyword"><?php echo htmlspecialchars(trim($keyword)); ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Description du post -->
            <div class="post-description">
                <?php echo nl2br(htmlspecialchars($post['Description'])); ?>
            </div>

            <?php if (isset($_SESSION['user_id'])): ?>
                <!-- Actions du post -->
                <div class="post-actions">
                    <span class="comments-icon" onclick="toggleComments(<?php echo $post['ID_post']; ?>)">
                        <img src="/site-photo/assets/img/comment.svg" alt="Commentaire" id="comment-icon-img"><?php echo $post['nb_comments']; ?>
                    </span>
                </div>
    
                <!-- Section des commentaires -->
                <div class="post-comments" id="comments-<?php echo $post['ID_post']; ?>">

                    <!-- Formulaire pour ajouter un commentaire -->
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <form class="add-comment-form" method="post" action="/site-photo/controllers/controller.php?controller=comment&action=add">
                            <input type="hidden" name="id_post" value="<?php echo $post['ID_post']; ?>">
                            <textarea name="comment_text" placeholder="Votre commentaire..." required></textarea>
                            <button type="submit">Publier</button>
                        </form>
                    <?php endif; ?>

                    <?php
                    // Récupérer les commentaires pour ce post
                    $comments = $commentController->list($post['ID_post']);
                    foreach ($comments as $comment):
                    ?>
                        <div class="comment">
                            <div class="comment-user">
                            <strong><?php echo htmlspecialchars($comment['Pseudo']); ?></strong>
                                <!-- Trois petits points pour le sous-menu des commentaires -->
                                <?php if ($comment['ID_membre'] === $_SESSION['user_id']): ?>
                                    <div class="comment-menu">
                                        <button class="menu-btn" onclick="toggleMenu(this)">&#x22EE;</button>
                                        <div class="menu-dropdown">
                                            <form method="post" action="/site-photo/controllers/controller.php?controller=comment&action=edit">
                                                <!--<input type="hidden" name="id_comment" value="<?php echo $comment['ID_comment']; ?>">
                                                <textarea name="comment_text" required><?php echo htmlspecialchars($comment['Texte']); ?></textarea>-->
                                                <button type="submit">Modifier</button>
                                            </form>

                                            <form method="post" action="/site-photo/controllers/controller.php?controller=comment&action=delete" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce commentaire ?');">
                                                <input type="hidden" name="id_comment" value="<?php echo $comment['ID_comment']; ?>">
                                                <button type="submit">Supprimer</button>
                                            </form>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="comment-text">
                                <?php echo nl2br(htmlspecialchars($comment['Texte'])); ?>
                            </div>
                            <div class="comment-date">
                                <?php
                                $comment_date = new DateTime($comment['Date_comment']);
                                echo $comment_date->format('d M Y H:i');
                                ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
    
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</section>
