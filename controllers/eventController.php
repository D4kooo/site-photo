<?php

require_once 'ajoutPhoto.php';

class eventController
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Méthode pour lister les events
    public function list()
    {
        // Récupération des events
        $req = $this->pdo->prepare('
            SELECT E.*
            FROM Evenement E
            WHERE E.Date_event > CURDATE()
            GROUP BY E.ID_event
            ORDER BY E.Date_event
        ');

        $req->execute();
        $events = $req->fetchAll();
    
        include 'views/events.php';
    }

    // Méthode pour ajouter un event
    public function add()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $this->addEvent($_POST, $_FILES, $_SESSION['user_id']);
                
                // Succès
                $_SESSION['message_modal'] = "Événement créé avec succès.";
                $_SESSION['message_modal_type'] = "success";
            } catch (Exception $e) {
                // En cas d'erreur
                $_SESSION['message_modal'] = $e->getMessage();
                $_SESSION['message_modal_type'] = "error";
            }
    
            header('Location: ../index.php');
            exit;
        }
    }
    
    // Méthode pour éditer un event
    public function edit()
    {
        //TODO
    }

    // Méthode pour supprimer un event
    public function delete()
    {
        //TODO
    }

    // Méthode pour traiter l'ajout d'un event
    public function addEvent($data, $files, $user_id)
    {
        // Récupération des données du formulaire
        $titre_event = $data['titre_event'];
        $type_event = $data['type_event'];
        $theme = isset($data['theme']) ? $data['theme'] : null;
        $visibilite = $data['visibilite'];
        $date_event = $data['date_event'];
        $nb_photo_par_membre = isset($data['nb_photo']) ? (int)$data['nb_photo'] : null;
        $description_event = $data['description_event'];
        $id_membre = $user_id;

        // Vérifier que nb_photo_par_membre est bien défini et valide
        if ($nb_photo_par_membre === null || $nb_photo_par_membre <= 0) {
            throw new Exception("Le nombre de photos par membre est requis et doit être un nombre positif.");
        }

        try {
            // Démarrer une transaction
            $this->pdo->beginTransaction();

            // Configuration de l'encodage pour la connexion
            $this->pdo->exec("SET NAMES utf8mb4");

            // Nouvelle gestion du thème
            if (!empty($theme)) {
                // Vérifie si le thème existe déjà en utilisant BINARY pour une comparaison sensible à la casse
                $reqTheme = $this->pdo->prepare("SELECT ID_theme FROM Thèmes WHERE BINARY ID_theme = ?");
                $reqTheme->execute([$theme]);
                
                if (!$reqTheme->fetch()) {
                    // Si le thème n'existe pas, on l'ajoute
                    $reqAddTheme = $this->pdo->prepare("INSERT INTO Thèmes (ID_theme) VALUES (?)");
                    $reqAddTheme->execute([$theme]);
                }
                $id_theme = $theme;
            } else {
                $id_theme = null;
            }

            // Traitement du fichier image
            $ajoutPhoto = new ajoutPhoto($this->pdo, $id_membre);
            $id_photo = $ajoutPhoto->uploadPhoto($files['photo'], '../uploads/events/');
            
            // Insertion dans la table Evenement
            $reqEvent = $this->pdo->prepare('
                INSERT INTO Evenement (
                    Nom_event, `Type`, Visibilite, Date_event,
                    Nb_photo_par_membre, Description, ID_photo, ID_theme, ID_membre
                ) VALUES (
                    :titre_event, :type_event, :visibilite, :date_event,
                    :nb_photo_par_membre, :description_event, :id_photo, :id_theme, :id_membre
                )
            ');

            // Liaison des variables aux paramètres
            $reqEvent->bindParam(':titre_event', $titre_event, PDO::PARAM_STR, 30);
            $reqEvent->bindParam(':type_event', $type_event, PDO::PARAM_STR, 20);
            $reqEvent->bindParam(':visibilite', $visibilite, PDO::PARAM_STR, 20);
            $reqEvent->bindParam(':date_event', $date_event, PDO::PARAM_STR);
            $reqEvent->bindParam(':nb_photo_par_membre', $nb_photo_par_membre, PDO::PARAM_INT);
            $reqEvent->bindParam(':description_event', $description_event, PDO::PARAM_STR, 500);
            $reqEvent->bindParam(':id_photo', $id_photo, PDO::PARAM_STR);
            $reqEvent->bindParam(':id_membre', $id_membre, PDO::PARAM_INT);

            // Gestion de l'ID du thème
            if ($id_theme !== null) {
                $reqEvent->bindParam(':id_theme', $id_theme, PDO::PARAM_STR);
            } else {
                $reqEvent->bindValue(':id_theme', null, PDO::PARAM_NULL);
            }

            // Exécution de la requête
            $reqEvent->execute();

            $this->pdo->commit();
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw new Exception("Erreur lors de l'ajout de l'événement : " . $e->getMessage());
        }
    }

    // Amélioration de la méthode getThemes pour gérer les erreurs
    public function getThemes() {
        try {
            // Configuration de l'encodage
            $this->pdo->exec("SET NAMES utf8mb4");
            
            $req = $this->pdo->query("SELECT ID_theme FROM Thèmes ORDER BY ID_theme");
            return $req->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) {
            error_log("Erreur lors de la récupération des thèmes : " . $e->getMessage());
            return [];
        }
    }

    public function getEvents() {
        $req = $this->pdo->prepare('
            SELECT e.*, m.Pseudo as organisateur 
            FROM Evenement e 
            JOIN Membre m ON e.ID_membre = m.ID_membre 
            ORDER BY e.Date_event DESC
        ');
        $req->execute();
        return $req->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
