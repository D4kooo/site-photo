<?php

// Variables pour les messages
$auth_message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pseudo = $_POST['pseudo'];
    $photo_profil = 'defaultpp.svg';
    $email = $_POST['email'];
    $tel = $_POST['tel'];
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $date_naissance = $_POST['date_naissance'];
    $adresse = $_POST['adresse'];
    $ville = $_POST['ville'];
    $cp = $_POST['cp'];
    $statut = 'Invalidé';
    $role = 'invité';
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    try {
        // Vérification si le pseudo existe déjà
        $req = $pdo->prepare('SELECT COUNT(*) FROM Membre WHERE Pseudo = :pseudo');
        $req->execute(['pseudo' => $pseudo]);
        $countPseudo = $req->fetchColumn();

        // Vérification si l'email ou le téléphone existe déjà
        $req = $pdo->prepare('SELECT COUNT(*) FROM Membre WHERE Email = :email OR Tel = :tel');

        // Liaison des variables aux paramètres
        $req->bindParam(':email', $email, PDO::PARAM_STR);
        $req->bindParam(':tel', $tel, PDO::PARAM_STR);

        // Exécution de la requête
        $req->execute();

        $countMembre = $req->fetchColumn();

        if ($countPseudo > 0) {
            $auth_message = "Le pseudo est déjà utilisé.";
            $message_type = "error";
        } elseif ($countMembre > 0) {
            $auth_message = "Email ou téléphone déjà utilisé.";
            $message_type = "error";
        } else {
            $pdo->beginTransaction();

            // Insertion dans la table Membre
            $req = $pdo->prepare('
                INSERT INTO Membre 
                        (Pseudo,  Photo_profil,  Nom,  Prenom,  Date_naissance,  Email,  Tel,  Adresse,  Ville,  CP,  Statut,  Role,  Password) 
                VALUES (:pseudo, :photo_profil, :nom, :prenom, :date_naissance, :email, :tel, :adresse, :ville, :cp, :statut, :role, :password)
            ');
        
            // Liaison des variables aux paramètres
            $req->bindParam(':pseudo', $pseudo, PDO::PARAM_STR, 50);
            $req->bindParam(':photo_profil', $photo_profil, PDO::PARAM_STR, 50);
            $req->bindParam(':nom', $nom, PDO::PARAM_STR, 30);
            $req->bindParam(':prenom', $prenom, PDO::PARAM_STR, 30);
            $req->bindParam(':date_naissance', $date_naissance, PDO::PARAM_STR);
            $req->bindParam(':email', $email, PDO::PARAM_STR, 250);
            $req->bindParam(':tel', $tel, PDO::PARAM_STR, 10);
            $req->bindParam(':adresse', $adresse, PDO::PARAM_STR, 60);
            $req->bindParam(':ville', $ville, PDO::PARAM_STR, 30);
            $req->bindParam(':cp', $cp, PDO::PARAM_STR, 5);
            $req->bindParam(':statut', $statut, PDO::PARAM_STR, 20);
            $req->bindParam(':role', $role, PDO::PARAM_STR, 20);
            $req->bindParam(':password', $password, PDO::PARAM_STR, 100);
        
            // Exécution de la requête
            $req->execute();
        
            $pdo->commit();

            $auth_message = "Inscription réussie ! Vous pouvez maintenant vous connecter.";
            $message_type = "success";
        }
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $auth_message = "Erreur : " . $e->getMessage();
        $message_type = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Club Nailloux</title>
    <link rel="stylesheet" href="../assets/css/auth.css">
</head>
<body>
    <!-- Contenu de la page -->
    <div class="form-container">
        <h2>Inscription</h2>
        
        <!-- Affichage des messages d'erreur ou de succès -->
        <?php if (!empty($auth_message)): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo htmlspecialchars($auth_message); ?>
            </div>
        <?php endif; ?>

        <form action="index.php" method="post">
            <input type="text" name="pseudo" placeholder="Pseudo" required>
            <input type="text" name="nom" placeholder="Nom" required>
            <input type="text" name="prenom" placeholder="Prénom" required>
            <input type="date" name="date_naissance" placeholder="Date de naissance" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="tel" name="tel" placeholder="Téléphone" required>
            <input type="text" name="adresse" placeholder="Adresse" required>
            <input type="text" name="ville" placeholder="Ville" required>
            <input type="text" name="cp" placeholder="Code Postal" required>
            <input type="password" name="password" placeholder="Mot de passe" required>
            <button type="submit">S'inscrire</button>
        </form>

        <div class="form-footer">
            <p>Déjà membre ? <a href="auth/login.php">Connectez-vous</a></p>
        </div>
    </div>
</body>
</html>
