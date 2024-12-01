<?php

class ajoutPhoto
{
    private $pdo;
    private $id_membre;

    public function __construct($pdo, $id_membre)
    {
        $this->pdo = $pdo;
        $this->id_membre = $id_membre;
    }

    public function uploadPhoto($file, $upload_dir)
    {
        // Vérification du fichier
        if (!isset($file) || $file['error'] != UPLOAD_ERR_OK) {
            throw new Exception("Veuillez sélectionner une image à télécharger.");
        }

        // Vérification du type de fichier
        $allowed_types = ['image/jpeg', 'image/png'];
        if (!in_array($file['type'], $allowed_types)) {
            throw new Exception("Type de fichier non autorisé. Seules les images JPEG et PNG sont autorisées.");
        }

        // Création du dossier de destination s'il n'existe pas
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        // Création du dossier pour les images optimisées
        $opt_dir = $upload_dir . 'opti/';
        if (!is_dir($opt_dir)) {
            mkdir($opt_dir, 0755, true);
        }

        // Génération d'un nom de fichier unique
        $file_ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $id_photo = uniqid('photo_') . '.' . $file_ext;
        $file_path = $upload_dir . $id_photo;

        // Déplacement du fichier téléchargé
        if (!move_uploaded_file($file['tmp_name'], $file_path)) {
            throw new Exception("Erreur lors du téléchargement de l'image.");
        }

        // Création de la version optimisée
        $opti_file_path = $opt_dir . $id_photo;
        $this->createOptimizedImage($file_path, $opti_file_path, $file['type']);

        // Extraction des métadonnées EXIF
        $exifData = $this->extractExifData($file_path);

        // Insertion dans la base de données
        $this->insertPhotoIntoDatabase($id_photo, $exifData);

        return $id_photo;
    }

    private function extractExifData($file_path)
    {
        $exif = @exif_read_data($file_path, 0, true);

        // Initialisation des données EXIF par défaut
        $data = [
            'resolution' => '',
            'balance_blancs' => '',
            'latitude' => '',
            'longitude' => '',
            'date_photo' => null,
            'appareil' => '',
            'exposition' => '',
            'ouverture' => '',
            'iso' => '',
            'focal' => '',
            'copyright' => ''
        ];

        if ($exif !== false) {
            // Traitement des données EXIF
            if (isset($exif['COMPUTED']['Width']) && isset($exif['COMPUTED']['Height'])) {
                $data['resolution'] = $exif['COMPUTED']['Width'] . 'x' . $exif['COMPUTED']['Height'];
            }
            if (isset($exif['EXIF']['WhiteBalance'])) {
                $data['balance_blancs'] = $exif['EXIF']['WhiteBalance'] == 1 ? 'Manuelle' : 'Automatique';
            }
            if (isset($exif['GPS']['GPSLatitude'])) {
                $data['latitude'] = $this->getGPS($exif['GPS']['GPSLatitude'], $exif['GPS']['GPSLatitudeRef']);
            }
            if (isset($exif['GPS']['GPSLongitude'])) {
                $data['longitude'] = $this->getGPS($exif['GPS']['GPSLongitude'], $exif['GPS']['GPSLongitudeRef']);
            }
            if (isset($exif['EXIF']['DateTimeOriginal'])) {
                $data['date_photo'] = date('Y-m-d H:i:s', strtotime($exif['EXIF']['DateTimeOriginal']));
            }
            if (isset($exif['IFD0']['Model'])) {
                $data['appareil'] = $exif['IFD0']['Model'];
            }
            if (isset($exif['EXIF']['ExposureTime'])) {
                $data['exposition'] = $exif['EXIF']['ExposureTime'] . ' sec';
            }
            if (isset($exif['EXIF']['FNumber'])) {
                $data['ouverture'] = 'f/' . $exif['EXIF']['FNumber'];
            }
            if (isset($exif['EXIF']['ISOSpeedRatings'])) {
                $data['iso'] = 'ISO ' . $exif['EXIF']['ISOSpeedRatings'];
            }
            if (isset($exif['EXIF']['FocalLength'])) {
                $data['focal'] = $exif['EXIF']['FocalLength'] . ' mm';
            }
            if (isset($exif['IFD0']['Copyright'])) {
                $data['copyright'] = $exif['IFD0']['Copyright'];
            }
        }

        return $data;
    }

    private function createOptimizedImage($source_path, $dest_path, $type)
    {
        // Définir les dimensions maximales pour l'image optimisée
        $max_width = 900; // Largeur maximale
        $max_height = 900; // Hauteur maximale

        if (!function_exists('imagecreatefromjpeg')) {
            die('GD library is not enabled or imagecreatefromjpeg function is not available.');
        }

        switch ($type) {
            case 'image/jpeg':
                $source_image = imagecreatefromjpeg($source_path);
                break;
            case 'image/png':
                $source_image = imagecreatefrompng($source_path);
                break;
            default:
                throw new Exception("Type de fichier non pris en charge pour l'optimisation.");
        }

        // Obtenir les dimensions de l'image source
        $source_width = imagesx($source_image);
        $source_height = imagesy($source_image);

        // Calculer les nouvelles dimensions en préservant le ratio
        $ratio = min($max_width / $source_width, $max_height / $source_height);
        $new_width = intval($source_width * $ratio);
        $new_height = intval($source_height * $ratio);

        // Créer une nouvelle image vide avec les nouvelles dimensions
        $optimized_image = imagecreatetruecolor($new_width, $new_height);

        // Redimensionner l'image source dans l'image optimisée
        imagecopyresampled(
            $optimized_image,
            $source_image,
            0, 0, 0, 0,
            $new_width, $new_height,
            $source_width, $source_height
        );

        // Sauvegarder l'image optimisée
        switch ($type) {
            case 'image/jpeg':
                imagejpeg($optimized_image, $dest_path, 100); // Qualité JPEG 100%
                break;
            case 'image/png':
                imagepng($optimized_image, $dest_path, 0); // Compression PNG : niveau 0
                break;
        }

        // Libérer la mémoire
        imagedestroy($source_image);
        imagedestroy($optimized_image);
    }

    private function insertPhotoIntoDatabase($id_photo, $exifData)
    {
        $opt_path = 'opti/' . $id_photo;

        $req = $this->pdo->prepare('
            INSERT INTO Photo (
                ID_Photo, Photo_opti, Resolution, Balance_blancs, Latitude, Longitude,
                Date_photo, Appareil, Exposition, Ouverture, ISO, Focal, Copyright, ID_membre
            ) VALUES (
                :id_photo, :photo_opti, :resolution, :balance_blancs, :latitude, :longitude,
                :date_photo, :appareil, :exposition, :ouverture, :iso, :focal, :copyright, :id_membre
            )
        ');

        $req->bindParam(':id_photo', $id_photo, PDO::PARAM_STR, 100);
        $req->bindParam(':photo_opti', $opt_path, PDO::PARAM_STR, 100);
        $req->bindParam(':resolution', $exifData['resolution'], PDO::PARAM_STR, 20);
        $req->bindParam(':balance_blancs', $exifData['balance_blancs'], PDO::PARAM_STR, 50);
        $req->bindParam(':latitude', $exifData['latitude'], PDO::PARAM_STR, 20);
        $req->bindParam(':longitude', $exifData['longitude'], PDO::PARAM_STR, 20);
        $req->bindParam(':date_photo', $exifData['date_photo'], PDO::PARAM_STR);
        $req->bindParam(':appareil', $exifData['appareil'], PDO::PARAM_STR, 50);
        $req->bindParam(':exposition', $exifData['exposition'], PDO::PARAM_STR, 20);
        $req->bindParam(':ouverture', $exifData['ouverture'], PDO::PARAM_STR, 10);
        $req->bindParam(':iso', $exifData['iso'], PDO::PARAM_STR, 15);
        $req->bindParam(':focal', $exifData['focal'], PDO::PARAM_STR, 15);
        $req->bindParam(':copyright', $exifData['copyright'], PDO::PARAM_STR, 50);
        $req->bindParam(':id_membre', $this->id_membre, PDO::PARAM_STR, 50);

        $req->execute();
    }

    private function getGPS($exifCoord, $hemi)
    {
        $degrees = count($exifCoord) > 0 ? $this->gps2Num($exifCoord[0]) : 0;
        $minutes = count($exifCoord) > 1 ? $this->gps2Num($exifCoord[1]) : 0;
        $seconds = count($exifCoord) > 2 ? $this->gps2Num($exifCoord[2]) : 0;

        $flip = ($hemi == 'W' or $hemi == 'S') ? -1 : 1;

        return $flip * ($degrees + ($minutes / 60) + ($seconds / 3600));
    }

    private function gps2Num($coordPart)
    {
        $parts = explode('/', $coordPart);

        if (count($parts) <= 0)
            return 0;

        if (count($parts) == 1)
            return $parts[0];

        return floatval($parts[0]) / floatval($parts[1]);
    }
}
?>
