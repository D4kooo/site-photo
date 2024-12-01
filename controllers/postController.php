<?php

require_once 'ajoutPhoto.php';
require_once 'commentController.php';

class postController
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }
    
    // Méthode pour lister les posts
    public function list()
    {
        // Récupération des posts
        $req = $this->pdo->prepare('
            SELECT P.*, M.Photo_profil AS PP, M.Pseudo AS Pseudo, GROUP_CONCAT(MC.texte) AS Mots_cles,
                (SELECT COUNT(*) FROM Commenter_post C WHERE C.ID_post = P.ID_post) AS nb_comments
            FROM Post P
            JOIN Membre M ON P.ID_membre = M.ID_membre
            LEFT JOIN Decrire D ON P.ID_post = D.ID_post
            LEFT JOIN Mots_cle MC ON D.ID_mot = MC.ID_mot
            WHERE P.Visibilite = "Publique"
            GROUP BY P.ID_post, M.Pseudo
            ORDER BY P.Date_post DESC
        ');

        $req->execute();
        $posts = $req->fetchAll();
    
        // Créer une instance de commentController
        $commentController = new commentController($this->pdo);
    
        // Inclure la vue pour afficher les posts
        include 'views/posts.php';
    }
    

    // Méthode pour ajouter un post
    public function add()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Appel de la méthode addPost pour traiter les données du formulaire
                $this->addPost($_POST, $_FILES, $_SESSION['user_id']);

                // Succès
                $_SESSION['message_modal'] = 'Post ajouté avec succès.';
                $_SESSION['message_modal_type'] = 'success';
            } catch (Exception $e) {
                // En cas d'erreur
                $_SESSION['message_modal'] = $e->getMessage();
                $_SESSION['message_modal_type'] = "error";
            }

            header('Location: ../index.php');
            exit;
        }
    }

    // Méthode pour éditer un post
    public function edit()
    {
        // TODO
    }
    
    // Méthode pour supprimer un post
    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $this->deletePost($_POST['id_post'], $_SESSION['user_id']);

                // Succès
                $_SESSION['message_modal'] = "Post supprimé avec succès.";
                $_SESSION['message_modal_type'] = "success";
            } catch (Exception $e) {

                // En cas d'erreur
                $_SESSION['message_modal'] = $e->getMessage();
                $_SESSION['message_modal_type'] = "error";
            }
    
            $redirect_url = $_SERVER['HTTP_REFERER'] ?? '../index.php';
            header('Location: ' . $redirect_url);
            exit;
        }
    }
    
    // Méthode pour traiter l'ajout d'un post
    public function addPost($data, $files, $user_id)
    {
        // Récupération des données du formulaire
        $titre = $data['titre'];
        $visibilite = $data['visibilite'];
        $mots_cles = $data['mots_cles'];
        $description = $data['description'];
        $watermark = isset($data['watermark']) ? 1 : 0;
        $date_post = date('Y-m-d H:i:s');
        $id_membre = $user_id;

        try {
            // Démarrer une transaction
            $this->pdo->beginTransaction();
    
            // Traitement du fichier image
            $ajoutPhoto = new ajoutPhoto($this->pdo, $id_membre);
            $id_photo = $ajoutPhoto->uploadPhoto($files['photo'], '../uploads/posts/');


            // Préparation de la requête
            $req = $this->pdo->prepare('
            INSERT INTO Post (
                       Titre,  Visibilite,  Date_post,  Description,  Watermark,  ID_membre,  ID_Photo
            ) VALUES (:titre, :visibilite, :date_post, :description, :watermark, :id_membre, :id_photo)
            ');

            // Liaison des variables aux paramètres
            $req->bindParam(':titre', $titre, PDO::PARAM_STR, 40);
            $req->bindParam(':visibilite', $visibilite, PDO::PARAM_STR, 20);
            $req->bindParam(':date_post', $date_post, PDO::PARAM_STR);
            $req->bindParam(':description', $description, PDO::PARAM_STR, 500);
            $req->bindParam(':watermark', $watermark, PDO::PARAM_INT);
            $req->bindParam(':id_membre', $id_membre, PDO::PARAM_STR, 50);
            $req->bindParam(':id_photo', $id_photo, PDO::PARAM_STR, 100);

            // Exécution de la requête
            $req->execute();

            // Récupération de l'ID du post inséré
            $post_id = $this->pdo->lastInsertId();

            // Gestion des mots-clés
            $keywords = array_map('trim', explode(',', $mots_cles));
            foreach ($keywords as $keyword) {
                if (!empty($keyword)) {
                    // Vérification si le mot-clé existe déjà dans la table Mots_cle
                    $req = $this->pdo->prepare('SELECT ID_mot FROM Mots_cle WHERE texte = :keyword');
                    $req->bindParam(':keyword', $keyword, PDO::PARAM_STR);
                    $req->execute();
                    $keyword_id = $req->fetchColumn();

                    if (!$keyword_id) {
                        // Si le mot-clé n'existe pas, l'ajouter à la table Mots_cle
                        $req = $this->pdo->prepare('INSERT INTO Mots_cle (texte) VALUES (:keyword)');
                        $req->bindParam(':keyword', $keyword, PDO::PARAM_STR);
                        $req->execute();
                        $keyword_id = $this->pdo->lastInsertId();
                    }

                    // Lier le mot-clé au post dans la table Decrire
                    $req = $this->pdo->prepare('INSERT INTO Decrire (ID_post, ID_mot) VALUES (:post_id, :keyword_id)');
                    $req->bindParam(':post_id', $post_id, PDO::PARAM_INT);
                    $req->bindParam(':keyword_id', $keyword_id, PDO::PARAM_INT);
                    $req->execute();
                }
            }

            $this->pdo->commit();
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw new Exception("Erreur lors de l'ajout du post : " . $e->getMessage());
        }
    }

    // Méthode pour modifier un post
    public function editPost($post_id, $user_id)
    {
        // TODO
    }


    // Méthode pour traiter la suppression d'un post
    public function deletePost($post_id, $user_id)
    {
        try {
            // Vérifier que le post appartient à l'utilisateur
            $req = $this->pdo->prepare('SELECT ID_photo FROM Post WHERE ID_post = :post_id AND ID_membre = :user_id');
            $req->bindParam(':post_id', $post_id, PDO::PARAM_INT);
            $req->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $req->execute();
            $post = $req->fetch();
    
            if (!$post) {
                throw new Exception("Vous n'avez pas l'autorisation de supprimer ce post.");
            }
    
            // Démarrer une transaction
            $this->pdo->beginTransaction();
    
            // Supprimer les mots-clés associés au post
            $req = $this->pdo->prepare('DELETE FROM Decrire WHERE ID_post = :post_id');
            $req->bindParam(':post_id', $post_id, PDO::PARAM_INT);
            $req->execute();
    
            // Supprimer les commentaires associés au post
            $req = $this->pdo->prepare('DELETE FROM Commenter_post WHERE ID_post = :post_id');
            $req->bindParam(':post_id', $post_id, PDO::PARAM_INT);
            $req->execute();
    
            // Supprimer le post
            $req = $this->pdo->prepare('DELETE FROM Post WHERE ID_post = :post_id');
            $req->bindParam(':post_id', $post_id, PDO::PARAM_INT);
            $req->execute();
    
            // Supprimer l'image associée au post
            if (!empty($post['ID_photo'])) {
                $photo_path = '../uploads/posts/' . $post['ID_photo'];
                $photo_opti_path = '../uploads/posts/opti/' . $post['ID_photo'];
    
                if (file_exists($photo_path)) {
                    unlink($photo_path); // Supprimer l'image originale
                }
    
                if (file_exists($photo_opti_path)) {
                    unlink($photo_opti_path); // Supprimer l'image optimisée
                }
    
                // Supprimer l'entrée de la photo dans la table `Photo`
                $req = $this->pdo->prepare('DELETE FROM Photo WHERE ID_photo = :id_photo');
                $req->bindParam(':id_photo', $post['ID_photo'], PDO::PARAM_STR);
                $req->execute();
            }
    
            $this->pdo->commit();
        } catch (Exception $e) {
            $this->pdo->rollBack();
        }
    }  
    
    public function loadPost() {
        if (isset($_GET['id'])) {
            $postId = intval($_GET['id']);
            $post = $this->getPostById($postId);
            if ($post) {
                $posts = [$post];
    
                $commentController = new commentController($this->pdo);
    
                include '../views/posts.php';
            } else {
                echo '<p>Post non trouvé.</p>';
            }
        } else {
            echo '<p>Aucun ID de post spécifié.</p>';
        }
    }
    
        

    public function getPostById($postId)
    {
        $stmt = $this->pdo->prepare('
            SELECT P.*, M.Photo_profil AS PP, M.Pseudo AS Pseudo, GROUP_CONCAT(MC.texte) AS Mots_cles,
                (SELECT COUNT(*) FROM Commenter_post C WHERE C.ID_post = P.ID_post) AS nb_comments
            FROM Post P
            JOIN Membre M ON P.ID_membre = M.ID_membre
            LEFT JOIN Decrire D ON P.ID_post = D.ID_post
            LEFT JOIN Mots_cle MC ON D.ID_mot = MC.ID_mot
            WHERE P.ID_post = :postId
            GROUP BY P.ID_post, M.Pseudo
        ');

        $stmt->execute(['postId' => $postId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
