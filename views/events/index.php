<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Événements - Club Photo Nailloux</title>
    <link rel="stylesheet" href="/site-photo/assets/css/header.css">
    <link rel="stylesheet" href="/site-photo/assets/css/style.css">
    <link rel="stylesheet" href="/site-photo/assets/css/events.css">
</head>
<body>
<?php 
$root = $_SERVER['DOCUMENT_ROOT'] . '/site-photo/';
require_once $root . 'includes/header.php'; 
?>

<main class="events-page">
    <h1>Événements à venir</h1>
    
    <div class="events-grid">
        <?php foreach ($events as $event): ?>
            <?php include __DIR__ . '/_event_card.php'; ?>
        <?php endforeach; ?>
    </div>
</main>

<?php require_once $root . 'includes/footer.php'; ?>
<script src="/site-photo/script/script.js"></script>
</body>
</html>
