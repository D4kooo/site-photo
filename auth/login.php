<?php
session_start();
require_once '../includes/db_connexion.php';

// Variables pour les messages
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = $_POST['login'];
    $password = $_POST['password'];

    if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
        // L'utilisateur a entré un email
        $req = $pdo->prepare('
            SELECT ID_membre, Pseudo, Password, Role
            FROM Membre 
            WHERE Email = :email
        ');
        
        // Liaison des variables aux paramètres
        $req->bindParam(':email', $login, PDO::PARAM_STR);
    
        // Exécution de la requête
        $req->execute();
    } else {
        // L'utilisateur a entré un pseudo
        $req = $pdo->prepare('
            SELECT ID_membre, Pseudo, Password, Role
            FROM Membre 
            WHERE Pseudo = :pseudo
        ');
        
        // Liaison des variables aux paramètres
        $req->bindParam(':pseudo', $login, PDO::PARAM_STR);
    
        // Exécution de la requête
        $req->execute();
    }
    

    $user = $req->fetch();

    if ($user && password_verify($password, $user['Password'])) {
        // Authentification réussie
        $_SESSION['user_id'] = $user['ID_membre'];
        $_SESSION['user_pseudo'] = $user['Pseudo'];
        $_SESSION['user_role'] = $user['Role'];

        // Redirection vers la page d'accueil
        header('Location: ../index.php');
        exit;
    } else {
        // Échec de l'authentification
        $message = "Identifiant ou mot de passe incorrect.";
        $message_type = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Club Nailloux</title>
    <link rel="stylesheet" href="../assets/css/auth.css">
</head>
<body>
    <!-- Contenu de la page -->
    <div class="form-container">
        <h2>Connexion</h2>

        <!-- Affichage des messages d'erreur -->
        <?php if (!empty($message)): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="post">
            <input type="text" name="login" placeholder="Pseudo ou Email" required>
            <input type="password" name="password" placeholder="Mot de passe" required>
            <button type="submit">Se connecter</button>
        </form>
        <div class="form-footer">
            <p>Pas encore membre ? <a href="../index.php">Inscrivez-vous</a></p>
        </div>
    </div>
</body>
</html>
