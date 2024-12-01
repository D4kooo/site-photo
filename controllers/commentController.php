<?php
class commentController
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Méthode pour lister les commentaires d'un post
    public function list($id_post)
    {
        $req = $this->pdo->prepare('
            SELECT C.*, M.Pseudo 
            FROM Commenter_post C
            JOIN Membre M ON C.ID_membre = M.ID_membre
            WHERE C.ID_post = :id_post
            ORDER BY C.Date_comment ASC
        ');
        
        $req->bindParam(':id_post', $id_post, PDO::PARAM_INT);
        $req->execute();
        return $req->fetchAll();
    }

    // Méthode pour ajouter un commentaire
    public function add()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
            try {
                $this->addComment($_POST, $_SESSION['user_id']);

                $_SESSION['message_modal'] = 'Commentaire ajouté avec succès.';
                $_SESSION['message_modal_type'] = 'success';
            } catch (Exception $e) {
                $_SESSION['message_modal'] = 'Erreur lors de l\'ajout du commentaire : ' . $e->getMessage();
                $_SESSION['message_modal_type'] = 'error';
            }

            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit;
        }
        
    }

    // Méthode pour modifier un commentaire
    public function edit()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
            try {
                $this->editComment($_POST, $_SESSION['user_id']);

                $_SESSION['message_modal'] = 'Commentaire modifié avec succès.';
                $_SESSION['message_modal_type'] = 'success';
            } catch (Exception $e) {
                $_SESSION['message_modal'] = 'Erreur lors de la modification du commentaire : ' . $e->getMessage();
                $_SESSION['message_modal_type'] = 'error';
            }

            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit;
        }
    }

    // Méthode pour supprimer un commentaire
    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
            try {
                $this->deleteComment($_POST, $_SESSION['user_id']);

                $_SESSION['message_modal'] = 'Commentaire supprimé avec succès.';
                $_SESSION['message_modal_type'] = 'success';
            } catch (Exception $e) {
                $_SESSION['message_modal'] = 'Erreur lors de la suppression du commentaire : ' . $e->getMessage();
                $_SESSION['message_modal_type'] = 'error';
            }

            $redirect_url = $_SERVER['HTTP_REFERER'] ?? '../index.php';
            header('Location: ' . $redirect_url);
            exit;
        }
    }

    // Méthode pour traiter l'ajout d'un commentaire
    public function addComment($data, $user_id)
    {
        $id_post = $data['id_post'];
        $comment_text = $data['comment_text'];
        $id_membre = $user_id;

        $req = $this->pdo->prepare('
            INSERT INTO Commenter_post (ID_post, ID_membre, Texte, Date_comment)
            VALUES (:id_post, :id_membre, :texte, NOW())
        ');

        $req->bindParam(':id_post', $id_post, PDO::PARAM_INT);
        $req->bindParam(':id_membre', $id_membre, PDO::PARAM_INT);
        $req->bindParam(':texte', $comment_text, PDO::PARAM_STR, 250);

        $req->execute();
    }

    // Méthode pour traiter la modification d'un commentaire
    public function editComment($data, $user_id)
    {
        $id_comment = $data['id_comment'];
        $comment_text = $data['comment_text'];
        $id_membre = $user_id;

        // Vérifier si le commentaire appartient à l'utilisateur
        $req = $this->pdo->prepare('
            SELECT * FROM Commenter_post 
            WHERE ID_comment = :id_comment AND ID_membre = :id_membre
        ');

        $req->bindParam(':id_comment', $id_comment, PDO::PARAM_INT);
        $req->bindParam(':id_membre', $id_membre, PDO::PARAM_INT);
        $req->execute();
        $comment = $req->fetch();

        if ($comment) {
            $req = $this->pdo->prepare('
                UPDATE Commenter_post 
                SET Texte = :texte 
                WHERE ID_comment = :id_comment
            ');
            $req->bindParam(':texte', $comment_text, PDO::PARAM_STR, 250);
            $req->bindParam(':id_comment', $id_comment, PDO::PARAM_INT);
            $req->execute();
        } else {
            throw new Exception('Commentaire introuvable ou non autorisé.');
        }
    }

    // Méthode pour traiter la suppression d'un commentaire
    public function deleteComment($data, $user_id)
    {
        $id_comment = $data['id_comment'];
        $id_membre = $user_id;

        // Vérifier si le commentaire appartient à l'utilisateur
        $req = $this->pdo->prepare('
            SELECT * FROM Commenter_post 
            WHERE ID_comment = :id_comment AND ID_membre = :id_membre
        ');

        $req->bindParam(':id_comment', $id_comment, PDO::PARAM_INT);
        $req->bindParam(':id_membre', $id_membre, PDO::PARAM_INT);
        $req->execute();
        $comment = $req->fetch();

        if ($comment) {
            $req = $this->pdo->prepare('
                DELETE FROM Commenter_post 
                WHERE ID_comment = :id_comment
            ');
            $req->bindParam(':id_comment', $id_comment, PDO::PARAM_INT);
            $req->execute();
        } else {
            throw new Exception('Commentaire introuvable ou non autorisé.');
        }
    }
}
?>
