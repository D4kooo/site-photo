<div id="add-event-modal" class="modal">
    <div class="modal-content">
        <span class="close-button" onclick="closeModal('add-event-modal')">&times;</span>
        <h2>Créer un Événement</h2>
        
        <?php if (!empty($message) && $message_type === 'error'): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form id="add-event-form" method="post" action="/site-photo/controllers/controller.php?controller=event&action=add" enctype="multipart/form-data">
            <input type="text" name="titre_event" placeholder="Titre de l'événement" required>
            <select name="type_event" required>
                <option value="">Sélectionnez un type</option>
                <option value="Concours">Concours</option>
                <option value="Sortie">Sortie</option>
                <option value="Formation">Formation</option>
                <option value="Exposition">Exposition</option>
            </select>
            <select name="visibilite" required>
                <option value="">Sélectionnez la visibilité</option>
                <option value="Publique">Publique</option>
                <option value="Privée">Privée</option>
            </select>
            <input type="datetime-local" name="date_event" required>
            <input type="number" name="nb_photo" placeholder="Nombre de photos par membre" required>
            <textarea name="description_event" placeholder="Description de l'événement" required></textarea>
            <select name="theme">
                <option value="">Sélectionnez un thème (optionnel)</option>
                <?php 
                $themes = $eventController->getThemes();
                foreach ($themes as $theme): 
                ?>
                    <option value="<?= htmlspecialchars($theme) ?>"><?= htmlspecialchars($theme) ?></option>
                <?php endforeach; ?>
            </select>
            <input type="file" name="photo" accept="image/*" required>
            <button type="submit">Créer l'événement</button>
        </form>
    </div>
</div>