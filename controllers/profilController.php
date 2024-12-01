<?php
class profilController
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function profil($username)
    {
        // Échapper le nom d'utilisateur pour éviter les injections
        $username = htmlspecialchars($username);

        // Récupérer les informations du membre en utilisant le pseudo
        $query = $this->pdo->prepare('SELECT * FROM Membre WHERE Pseudo = :pseudo');
        $query->execute([':pseudo' => $username]);
        $membre = $query->fetch();

        if (!$membre) {
            $userNotFound = true;
        }

        $id_membre = $membre['ID_membre'];

        // Récupérer toutes les photos du membre
        $query = $this->pdo->prepare('
            SELECT Post.*, Photo.Photo_opti, Photo.Date_photo
            FROM Post
            INNER JOIN Photo ON Post.ID_photo = Photo.ID_photo
            WHERE Post.ID_membre = :id_membre
        ');
        $query->execute([':id_membre' => $id_membre]);
        $photos = $query->fetchAll();

        // Inclure la vue pour afficher le profil
        include '../views/profil.php';
    }
}
?>
