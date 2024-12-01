<article class="event">
    <div class="event-image-container">
        <img src="/site-photo/uploads/events/<?= htmlspecialchars($event['ID_photo']) ?>" 
             alt="<?= htmlspecialchars($event['Nom_event']) ?>" 
             class="event-image">
        <span class="event-status"><?= htmlspecialchars($event['Type']) ?></span>
    </div>
    <h3 class="event-title"><?= htmlspecialchars($event['Nom_event']) ?></h3>
    <p class="event-date"><?= date('d M Y à H:i', strtotime($event['Date_event'])) ?></p>
    <p class="event-organizer">Par <?= htmlspecialchars($event['organisateur']) ?></p>
    <p class="event-description"><?= htmlspecialchars($event['Description']) ?></p>
    <?php if ($event['Visibilite'] === 'Publique'): ?>
        <p class="event-photos">Photos par membre : <?= htmlspecialchars($event['Nb_photo_par_membre']) ?></p>
    <?php endif; ?>
</article>