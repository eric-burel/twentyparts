<?php

/**
 * @Class      Image
 * @Fonction   Permet la gestion des images (rezise, create...)
 * 
 * 
 * @version    1.0
 * @author     Savageman
 * 
 * @version    2.0 
 * @author     Dread <dreadlokeur@gmail.com>
 *                 Ajout de la phpdoc
 *
 * @license    Creative Commons http://creativecommons.org/licenses/by/2.0/fr/
 * @copyright  Copyright 2010 - Dread and contributors
 * @license    Creative Commons http://creativecommons.org/licenses/by/2.0/fr/
 * @copyright  Copyright 2010 - Dread and contributors
 */
class Image {

    /**
     * L'attribut qui permet de stocker les ressources necessaire à la creation d'une image
     *
     * @access protected
     * @var <string>
     */
    protected $resource;

    /**
     * L'attribut qui permet de stocker les informations hauteur, largeur... via getimagesize()
     *
     * @access protected
     * @var <string>
     */
    protected $info;

    /**
     * L'attribut qui permet de stocker le nom du fichier
     *
     * @access protected
     * @var <string>
     */
    protected $filename;

    /**
     * Le constructeur de la classe, permet de stocker le nom de fichier de l'objet, ainnsi que ses mesurations
     *
     * @access public
     * @param <string> $filename
     * @return <void>
     */
    public function __construct($filename) {
        if (!is_file($filename))
            throw new \Exception('Le fichier ' . $filename . ' n\'existe pas !');
        else {
            $this->filename = $filename;
            $this->info = getimagesize($this->filename);
        }
    }

    /**
     * Permet de redimensionner une image selon des valeurs maximum(hauteur et largeur)
     *
     * @access public
     * @param <string> $max_width
     * @param <string> $max_height
     * @return <string> $this
     */
    public function resizeTo($max_width, $max_height) {
        // Si l'image est trop petite, pas besoin de la redimensioner
        if ($this->info[0] <= $max_width && $this->info[1] <= $max_height) {
            $new_height = $this->info[1];
            $new_width = $this->info[0];
        } else {
            if ($max_width / $this->info[0] > $max_height / $this->info[1]) {
                $new_width = (int) round($this->info[0] * ($max_height / $this->info[1]));
                $new_height = $max_height;
            } else {
                $new_width = $max_width;
                $new_height = (int) round($this->info[1] * ($max_width / $this->info[0]));
            }
        }
        $new_img = imagecreatetruecolor($new_width, $new_height);

        // Si l'image est un PNG ou un GIF on la met en transparent
        if (($this->info[2] == 1) OR ($this->info[2] == 3)) {
            imagealphablending($new_img, false);
            imagesavealpha($new_img, true);
            $transparent = imagecolorallocatealpha($new_img, 255, 255, 255, 127);
            imagefilledrectangle($new_img, 0, 0, $new_width, $new_height, $transparent);
        }
        imagecopyresampled($new_img, $this->getResource(), 0, 0, 0, 0, $new_width, $new_height, $this->info[0], $this->info[1]);
        imagedestroy($this->resource);
        $this->resource = $new_img;

        $this->info[0] = $new_width;
        $this->info[1] = $new_height;

        return $this;
    }

    /**
     * Permet de sauvegarder une image via imagpng/gif/jpeg()
     *
     * @access public
     * @param <string> $filename
     * @return <string> $this
     */
    public function saveAs($filename) {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        switch ($this->info[2]) {
            case IMAGETYPE_PNG:
                imagepng($this->getResource(), $filename);
                break;
            case IMAGETYPE_JPEG:
                imagejpeg($this->getResource(), $filename);
                break;
            case IMAGETYPE_GIF:
                imagegif($this->getResource(), $filename);
                break;
            default :
                throw new \Exception('Type de fichier incompatible. Veuillez sauvegarder l\'image en .gif, .png ou .jpg');
                break;
        }
        $this->filename = $filename;
        return $this;
    }

    /**
     * Permet de recuperer l'attribut filename
     *
     * @access public
     * @param <void>
     * @return <string> $this->filename
     */
    public function getFilename() {
        return $this->filename;
    }

    /**
     * Permet de creer une image (png, jpeg, gif)
     *
     * @access protected
     * @param <void>
     * @return <string> $this->resource
     */
    protected function getResource() {
        if (empty($this->resource)) {
            switch ($this->info[2]) {
                case IMAGETYPE_PNG:
                    $this->resource = imagecreatefrompng($this->filename);
                    break;
                case IMAGETYPE_JPEG:
                    $this->resource = imagecreatefromjpeg($this->filename);
                    break;
                case IMAGETYPE_GIF:
                    $this->resource = imagecreatefromgif($this->filename);
                    break;
                default :
                    throw new \Exception('Type de fichier incompatible. Veuillez sauvegarder l\'image en .gif, .png ou .jpg');
                    break;
            }
        }
        return $this->resource;
    }

}

?>