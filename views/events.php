<section class="events-section">
    <h2>Événements à venir</h2>
    <div class="events-container">
        <?php foreach ($events as $event): ?>
            <article class="event">
                <div class="event-image-container">
                     <img src="uploads/events/opti/<?= htmlspecialchars($event['ID_photo']) ?>" alt="<?= htmlspecialchars($event['Nom_event']) ?>" class="event-image">
                    <span class="event-status available"><?= htmlspecialchars($event['Type']) ?></span>
                </div>
                <h3 class="event-title"><?= htmlspecialchars($event['Nom_event']) ?></h3>
                <p class="event-date"><?= date('d M Y', strtotime($event['Date_event'])) ?></p>
            </article>
        <?php endforeach; ?>
    </div>
</section>
