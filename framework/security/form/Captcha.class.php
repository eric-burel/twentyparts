<?php

// auteur : NAYRAND Jérémie
// TODO captcha algorithm type: string or matematic or pub ?
// TODO implement api externe support ? : http://www.solvemedia.com/ && recaptcha
// TODO Ajout d'une securité contre le pompage en masse des contenu captchas, ou utilisation externe see: http://fr.wikipedia.org/wiki/CAPTCHA#Contournement and http://www.xmco.fr/article-captcha.html

namespace framework\security\form;

use framework\security\IForm;
use framework\utility\Tools;
use framework\utility\Validate;
use framework\Session;
use framework\mvc\Router;
use framework\network\http\Header;
use framework\utility\Color;
use framework\Logger;
use framework\Language;

class Captcha implements IForm {

    protected $_formName = '';
    protected $_image = false; //true/false
    protected $_audio = false; //true/false
    // generale infos
    protected $_key = null;
    protected $_length = 6;
    protected $_refreshUrl = null;
    protected $_charsList = '';
    // image infos
    protected $_imageUrl = '';
    protected $_imageFormatList = array('png', 'jpg', 'jpeg', 'gif');
    protected $_imageContents = null;
    protected $_imageFormat = null;
    protected $_imageHeight = 0;
    protected $_imageWidth = 0;
    //background
    protected $_imageBackground = 0; // 0 = color, 1 = by file
    protected $_imageBackgroundColor = array('R' => 255, 'G' => 255, 'B' => 255);
    protected $_imageBackgroundFile = null;
    // font
    protected $_imageFontFile = null;
    protected $_imageFontSize = 0;
    protected $_imageFontAngle = 0;
    protected $_imageFontColor = null;
    //Noising
    protected $_imageNoise = false; //true/false
    protected $_imageNoiseCount = 0;
    protected $_imageNoiseMinSize = 0;
    protected $_imageNoiseMaxSize = 0;
    protected $_imageNoiseColorType = 2; // 0 = toutes les taches de la meme couleur, 1 = chaque tache à une coleur, 2 = couleur aléatoire par taches
    protected $_imageNoiseColorValue = null;
    // Lines
    protected $_imageVerticalLine = false;
    protected $_imageVerticalLineColorType = 2; // 0 = toutes les lignes de la meme couleur, 1 = chaque ligne à une coleur, 2 = couleur aléatoire par lignes
    protected $_imageVerticalLineColorValue = null;
    protected $_imageVerticalLineThickness = null; // null =  no set thickness, 0 =  random, else thickness value
    protected $_imageHorizontalLine = false;
    protected $_imageHorizontalLineColorType = 2; // 0 = toutes les lignes de la meme couleur, 1 = chaque ligne à une coleur, 2 = couleur aléatoire par lignes
    protected $_imageHorizontalLineColorValue = null;
    protected $_imageHorizontalLineThickness = null; // null =  no set thickness, 0 =  random, else thickness value
    //TODO : NOT YET
    protected $_imageDistortionType = 0; // 0 = none, 1 = only letters, 2 = full (letters and global image generated)
    protected $_imageDistortionLevel = null; //0.0 normal, 1.0 = very high distortion
    //
    // audio infos
    protected $_audioUrl = '';
    protected $_audioContents = null;
    protected $_audioLettersGapMin = 0;
    protected $_audioLettersGapMax = 600;
    protected $_audioLangDirectory = null;
    protected $_audioNoise = false;
    protected $_audioNoiseFile = null;

    /**
     * The method and threshold (or gain factor) used to normalize the mixing with background noise.
     * See http://www.voegler.eu/pub/audio/ for more information.
     *
     * Valid: <ul>
     *     <li> >= 1 - Normalize by multiplying by the threshold (boost - positive gain). <br />
     *            A value of 1 in effect means no normalization (and results in clipping). </li>
     *     <li> <= -1 - Normalize by dividing by the the absolute value of threshold (attenuate - negative gain). <br />
     *            A factor of 2 (-2) is about 6dB reduction in volume.</li>
     *     <li> [0, 1) - (open inverval - not including 1) - The threshold
     *            above which amplitudes are comressed logarithmically. <br />
     *            e.g. 0.6 to leave amplitudes up to 60% "as is" and compress above. </li>
     *     <li> (-1, 0) - (open inverval - not including -1 and 0) - The threshold
     *            above which amplitudes are comressed linearly. <br />
     *            e.g. -0.6 to leave amplitudes up to 60% "as is" and compress above. </li></ul>
     *
     * Default: 0.6
     *
     * @var float
     */
    protected $_audioNoiseMixNormalization = 0.6;
    protected $_audioDegrade = false;

    public function __construct($options = array()) {
        if (isset($options['dataFile'])) {
            if (!file_exists($options['dataFile']) || !Validate::isFileMimeType('xml', $options['dataFile']))
                throw new \Exception('Security captcha invalid data file');

            $xml = simplexml_load_file($options['dataFile']);
            if (is_null($xml) || !$xml)
                throw new \Exception('Security captcha invalid data xml file : "' . $options['dataFile'] . '"');

            // Casting options into xml data file
            $optionsData = $xml->option;
            $optionsLoaded = array();
            foreach ($optionsData as $option)
                $optionsLoaded[(string) $option->name] = Tools::castValue((string) $option->value);

            // merge
            $options = array_merge_recursive($options, $optionsLoaded);
        }
        if (!isset($options['refreshUrl']))
            throw new \Exception('Miss refresh url name');
        $this->_refreshUrl = $options['refreshUrl'];

        if (isset($options['charsList']))
            $this->setCharsList($options['charsList']);
        if (isset($options['length']))
            $this->setLength($options['length']);

        // Image options
        if (isset($options['image']))
            $this->setImage($options['image'], $options);

        // Audio options
        if (isset($options['audio']))
            $this->setAudio($options['audio'], $options);
    }

    public function setFormName($name) {
        if (!Validate::isVariableName($name))
            throw new \Exception('Form name must be a valid variable name');

        $this->_formName = $name;
    }

    public function getFormName() {
        return $this->_formName;
    }

    public function create($key = null, $captchaType = 'image') {
        $session = Session::getInstance();

        $this->_key = is_null($key) ? Tools::generateString($this->_length, $this->_charsList) : $key;

        if ($this->_image && $captchaType == 'image')
            $this->_createImage();


        if ($this->_audio && $captchaType == 'audio')
            $this->_createAudio();

        // Put into session (locked)
        $session->add($this->getFormName() . 'Captcha', $this->_key, true, true);
        Logger::getInstance()->debug('Captcha : "' . $this->getFormName() . '" create key value : "' . $this->_key . '"', 'security');
    }

    public function set() {
        
    }

    public function get($type = 'image', $url = false) {
        if ($type == 'image') {
            if (!$this->_image)
                throw new \Exception('Security captcha image parameter not allowed');

            if ($url)
                return Router::getUrl($this->_imageUrl, array($this->getFormName(), 'image'));

            $this->_display($type);
        } elseif ($type == 'audio') {
            if (!$this->_audio)
                throw new \Exception('Security captcha audio parameter not allowed');
            if ($url)
                return Router::getUrl($this->_imageUrl, array($this->getFormName(), 'audio'));

            $this->_display($type);
        }
    }

    public function flush() {
        // destruct key, session and contents
        Session::getInstance()->delete($this->getFormName() . 'Captcha', true);
        $this->_key = null;
        $this->_imageContents = null;
        $this->_audioContents = null;
    }

    public function check($checkingValue, $flush = false) {
        $realValue = Session::getInstance()->get($this->getFormName() . 'Captcha');
        if ($flush)
            $this->flush();

        if (is_null($realValue) || $realValue != $checkingValue) {
            Logger::getInstance()->debug('Captcha : "' . $this->getFormName() . '" invalid captcha value : "' . $checkingValue . '" need value : "' . $realValue . '"', 'security');
            return false;
        }

        return true;
    }

    public function setLength($lengh) {
        if (!is_int($lengh) || $lengh <= 0)
            throw new \Exception('Lengh parameter must be an valid integer');
        $this->_length = $lengh;
        // TODO check: font size, and image widht, for determine max lengt possible
    }

    public function setCharsList($chars) {
        if (!is_string($chars))
            throw new \Exception('CharsList parameter must be a string');
        $this->_charsList = $chars;
    }

    public function getRefreshUrl() {
        return Router::getUrl($this->_refreshUrl, array($this->getFormName(), 'refresh'));
    }

    public function setImage($activate, $options = array()) {
        if (!is_bool($activate))
            throw new \Exception('Activate must be an boolean');
        if ($activate) {
            if (!extension_loaded('gd'))
                throw new \Exception('Security captcha need GD extension');

            if (!isset($options['imageFormat']) || !in_array(strtolower($options['imageFormat']), $this->_imageFormatList))
                throw new \Exception('Security captcha need a valid image format');
            $this->_imageFormat = strtolower($options['imageFormat']);

            if (!isset($options['imageUrl']))
                throw new \Exception('Miss image url name');

            $this->_imageUrl = $options['imageUrl'];

            // Image Size
            if (!isset($options['imageWidth']))
                throw new \Exception('Security captcha need a imageWidth value');
            $this->setImageWidth($options['imageWidth']);
            if (!isset($options['imageHeight']))
                throw new \Exception('Security captcha need a imageHeight value');
            $this->setImageHeight($options['imageHeight']);

            // Font
            if (!isset($options['imageFontFile']))
                throw new \Exception('Security captcha need a font file');
            $size = isset($options['imageFontSize']) ? $options['imageFontSize'] : 50;
            $angle = isset($options['imageFontAngle']) ? $options['imageFontAngle'] : 0;
            $color = isset($options['imageFontColor']) ? $options['imageFontColor'] : array('R' => 0, 'V' => 0, 'B' => 0);
            $this->setImageFont($options['imageFontFile'], $size, $angle, $color);

            // Background
            if (isset($options['imageBackground'])) {
                $option = isset($options['imageBackgroundOption']) ? $options['imageBackgroundOption'] : null;
                $this->setImageBackground($options['imageBackground'], $option);
            }
            // Noise
            if (isset($options['imageNoise'])) {
                $noiseCount = isset($options['imageNoiseCount']) ? $options['imageNoiseCount'] : 50;
                $noiseMinSize = isset($options['imageNoiseMinSize']) ? $options['imageNoiseMinSize'] : 1;
                $noiseMaxSize = isset($options['imageNoiseMaxSize']) ? $options['imageNoiseMaxSize'] : 10;
                $noiseColorType = isset($options['imageNoiseColorType']) ? $options['imageNoiseColorType'] : 2;
                $noiseColorValue = isset($options['imageNoiseColorValue']) ? $options['imageNoiseColorValue'] : null;
                $this->setImageNoise($options['imageNoise'], $noiseCount, $noiseMinSize, $noiseMaxSize, $noiseColorType, $noiseColorValue);
            }

            //Lines
            if (isset($options['imageVerticalLine'])) {
                $verticalLineColorType = isset($options['imageVerticalLineColorType']) ? $options['imageVerticalLineColorType'] : 2;
                $verticalLineColorValue = isset($options['imageVerticalLineColorValue']) ? $options['imageVerticalLineColorValue'] : null;
                $verticalLineThickness = isset($options['imageVerticalLineThickness']) ? $options['imageVerticalLineThickness'] : null;
                $this->setImageVerticalLine($options['imageVerticalLine'], $verticalLineColorType, $verticalLineColorValue, $verticalLineThickness);
            }
            if (isset($options['imageHorizontalLine'])) {
                $horizontalLineColorType = isset($options['imageHorizontalLineColorType']) ? $options['imageHorizontalLineColorType'] : 2;
                $horizontalLineColorValue = isset($options['imageHorizontalLineColorValue']) ? $options['imageHorizontalLineColorValue'] : null;
                $horizontalLineThickness = isset($options['imageHorizontalLineThickness']) ? $options['imageHorizontalLineThickness'] : null;
                $this->setImageHorizontalLine($options['imageHorizontalLine'], $horizontalLineColorType, $horizontalLineColorValue, $horizontalLineThickness);
            }

            //TODO
            /* if (isset($options['imageDistortionType'])) {
              if (!isset($options['imageDistortionLevel']))
              throw new \Exception('Image distortion need a level');
              $this->setImageDistortion($options['imageDistortionType'], $options['imageDistortionLevel']);
              } */


            // TODO : blur, border image
            // TODO : vertical and horizontal lines (number/color(mode) and distortion?)
            // TODO : font position align: center, random, vertical, horinzontal (check imageBackgroundColor != imageFontColor != LignsColor && NoiseColor)
            // TODO : font transparently : on/off and percentage
            $this->_image = $activate;
        }
    }

    public function getImage() {
        return $this->_image;
    }

    public function setImageWidth($width) {
        if (!is_int($width) || $width <= 0)
            throw new \Exception('Width must be an integer');
        $this->_imageWidth = $width;
    }

    public function setImageHeight($height) {
        if (!is_int($height) || $height <= 0)
            throw new \Exception('Height must be an integer');

        $this->_imageHeight = $height;
    }

    public function setImageFont($file, $size, $angle, $color) {
        if (!file_exists($file) || !is_readable($file) || !Validate::isFileMimeType('ttf', $file))
            throw new \Exception('Invalid image font file, need ttf file');
        $this->_imageFontFile = $file;


        if (!is_int($size) || $size <= 0)// TODO check: image width and captcha length for determine max font size possible
            throw new \Exception('Font size invalid');
        $this->_imageFontSize = $size;

        if (!is_int($angle) && !is_float($angle))// TODO better check ...
            throw new \Exception('Font angle invalid');
        $this->_imageFontAngle = $angle;

        if (is_string($color))
            $rgb = explode('/', $color);
        $color = (isset($rgb) && is_array($rgb) && count($rgb) >= 3) ? new Color($rgb[0], $rgb[1], $rgb[2]) : new Color($color);
        $this->_imageFontColor = $color->getColor();
        if ($this->_imageBackgroundColor == $this->_imageFontColor)
            throw new \Exception('Invalid image font color, must be different background color');
    }

    public function setImageBackground($type, $option = null) {
        if (!is_int($type) || $type < 0 || $type > 1)
            throw new \Exception('Background image type invalid');
        $this->_imageBackground = $type;

        if (!is_null($option) && !is_string($option))
            throw new \Exception('Background option must be null or string');


        if ($this->_imageBackground == 0) {// by color
            if (is_string($option))
                $rgb = explode('/', $option);
            $color = (isset($rgb) && is_array($rgb) && count($rgb) >= 3) ? new Color($rgb[0], $rgb[1], $rgb[2]) : new Color($option);
            $this->_imageBackgroundColor = $color->getColor();
            if ($this->_imageBackgroundColor == $this->_imageFontColor)// todo check si different de noise color, et de v/h lines ?
                throw new \Exception('Invalid image background color, must be different font color');
        } elseif ($this->_imageBackground == 1) {// by file
            if (!file_exists($option) || !is_readable($option))
                throw new \Exception('Invalid image background file');
            $this->_imageBackgroundFile = $option;
            // Check format
            foreach ($this->_imageFormatList as $format) {
                if (Validate::isFileMimeType($format, $this->_imageBackgroundFile)) {
                    $match = true;
                    break;
                }
            }
            if (!isset($match))
                throw new \Exception('Invalid image background file format');
        }
    }

    public function setImageNoise($activate, $noiseCount = 50, $noiseMinSize = 1, $noiseMaxSize = 10, $noiseColorType = 2, $noiseColorValue = null) {
        if (!is_bool($activate))
            throw new \Exception('Activate noise must be an boolean');
        $this->_imageNoise = $activate;
        if (!is_int($noiseCount) || $noiseCount <= 0)
            throw new \Exception('Noise count must be a valid integer');
        $this->_imageNoiseCount = $noiseCount;
        if (!is_int($noiseMinSize) || $noiseMinSize <= 0)
            throw new \Exception('Noise Min Size must be a valid integer');
        $this->_imageNoiseMinSize = $noiseMinSize;
        if (!is_int($noiseMaxSize) || $noiseMaxSize <= 0 || $noiseMaxSize <= $this->_imageNoiseMinSize)
            throw new \Exception('Noise Max Size must be a valid integer');
        $this->_imageNoiseMaxSize = $noiseMaxSize; //TODO : verifier que la taille max n'est pas trop grand par rapport à la taille de l'image
        if (!is_int($noiseColorType) || $noiseColorType < 0 || $noiseColorType > 2)
            throw new \Exception('Noise color type must be a valid integer');
        $this->_imageNoiseColorType = $noiseColorType;

        // apply colors
        if (!is_null($noiseColorValue) && !is_string($noiseColorValue))
            throw new \Exception('Noise color value must be null or string');
        if ($this->_imageNoiseColorType == 0) {// 0 = toutes les taches de la meme couleur
            if (is_null($noiseColorValue))
                $this->_imageNoiseColorValue = array('R' => 0, 'G' => 0, 'B' => 0);
            else {
                if (is_string($noiseColorValue))
                    $rgb = explode('/', $noiseColorValue);
                $color = (isset($rgb) && is_array($rgb) && count($rgb) >= 3) ? new Color($rgb[0], $rgb[1], $rgb[2]) : new Color($noiseColorValue);
                $this->_imageNoiseColorValue = $color->getColor();
            }
            if ($this->_imageNoiseColorValue == $this->_imageBackgroundColor)
                throw new \Exception('Noise color value must be different to background color');
        } else {
            // check validty of color value for color type 1 (color by noise)
            if ($this->_imageNoiseColorType == 1) {
                $colors = explode('-', $noiseColorValue);
                if (!is_array($colors) || $this->_imageNoiseCount > count($colors))
                    throw new \Exception('Invalid color value, miss a color');
            }
            for ($i = 0; $i < $this->_imageNoiseCount; $i++) {
                if ($this->_imageNoiseColorType == 1) {//1 = chaque tache à une couleur
                    if (is_string($colors[$i]))
                        $rgb = explode('/', $colors[$i]);
                    $color = (isset($rgb) && is_array($rgb) && count($rgb) >= 3) ? new Color($rgb[0], $rgb[1], $rgb[2]) : new Color($colors[$i]);
                    $this->_imageNoiseColorValue[$i] = $color->getColor();
                    if ($this->_imageNoiseColorValue[$i] == $this->_imageBackgroundColor)
                        throw new \Exception('Noise color value must be different to background color');
                } elseif ($this->_imageNoiseColorType == 2) {//2 = couleur aléatoire par taches
                    $r = Tools::generateInt(0, 255, $this->_imageBackgroundColor['R']);
                    $g = Tools::generateInt(0, 255, $this->_imageBackgroundColor['G']);
                    $b = Tools::generateInt(0, 255, $this->_imageBackgroundColor['B']);
                    $color = new Color($r, $g, $b);
                    $this->_imageNoiseColorValue[$i] = $color->getColor();
                }
            }
        }
    }

    public function setImageVerticalLine($verticalLineCount, $verticalLineColorType = 0, $verticalLineColorValue = null, $verticalLineThickness = null) {
        if (!is_int($verticalLineCount) || $verticalLineCount < 1)
            throw new \Exception('vertical line count invalid');
        $this->_imageVerticalLine = $verticalLineCount;
        if (!is_int($verticalLineColorType) || $verticalLineColorType < 0 || $verticalLineColorType > 3)
            throw new \Exception('Vertical line color type invalid');
        $this->_imageVerticalLineColorType = $verticalLineColorType;

        if ($this->_imageVerticalLineColorType == 0) { // all vertical line have same color
            if (is_null($verticalLineColorValue))
                $this->_imageVerticalLineColorValue = array('R' => 0, 'G' => 0, 'B' => 0);
            else {
                if (is_string($verticalLineColorValue))
                    $rgb = explode('/', $verticalLineColorValue);
                $color = (isset($rgb) && is_array($rgb) && count($rgb) >= 3) ? new Color($rgb[0], $rgb[1], $rgb[2]) : new Color($verticalLineColorValue);
                $this->_imageVerticalLineColorValue = $color->getColor();
            }
            if ($this->_imageVerticalLineColorValue == $this->_imageBackgroundColor)
                throw new \Exception('Vertical line color value must be different to background color');
        } else {

            // check validty of color value for color type 1 
            if ($this->_imageVerticalLineColorType == 1) {
                $colors = explode('-', $verticalLineColorValue);
                if (!is_array($colors) || $this->_imageVerticalLine > count($colors))
                    throw new \Exception('Invalid vertical lines colors value, miss a color');
            }
            for ($i = 0; $i < $this->_imageVerticalLine; $i++) {
                if ($this->_imageVerticalLineColorType == 1) {
                    if (is_string($colors[$i]))
                        $rgb = explode('/', $colors[$i]);
                    $color = (isset($rgb) && is_array($rgb) && count($rgb) >= 3) ? new Color($rgb[0], $rgb[1], $rgb[2]) : new Color($colors[$i]);
                    $this->_imageVerticalLineColorValue[$i] = $color->getColor();
                    if ($this->_imageVerticalLineColorValue[$i] == $this->_imageBackgroundColor)
                        throw new \Exception('Vertical lines color value must be different to background color');
                } elseif ($this->_imageVerticalLineColorType == 2) { // random colors
                    $r = Tools::generateInt(0, 255, $this->_imageBackgroundColor['R']);
                    $g = Tools::generateInt(0, 255, $this->_imageBackgroundColor['G']);
                    $b = Tools::generateInt(0, 255, $this->_imageBackgroundColor['B']);
                    $color = new Color($r, $g, $b);
                    $this->_imageVerticalLineColorValue[$i] = $color->getColor();
                }
            }
        }
        if ((!is_null($verticalLineThickness) && !is_int($verticalLineThickness)) || $verticalLineThickness > $this->_imageHeight)
            throw new \Exception('Vertical line Thickness value must be an integer or null, and inferior to image height');
        $this->_imageVerticalLineThickness = $verticalLineThickness;

        // TODO imagedashedline ???
    }

    public function setImageHorizontalLine($horizontalLineCount, $horizontalLineColorType = 0, $horizontalLineColorValue = null, $horizontalLineThickness = null) {
        if (!is_int($horizontalLineCount) || $horizontalLineCount < 1)
            throw new \Exception('horizontal line count invalid');
        $this->_imageHorizontalLine = $horizontalLineCount;
        if (!is_int($horizontalLineColorType) || $horizontalLineColorType < 0 || $horizontalLineColorType > 3)
            throw new \Exception('Horizontal line color type invalid');
        $this->_imageHorizontalLineColorType = $horizontalLineColorType;

        if ($this->_imageHorizontalLineColorType == 0) { // all horizontal line have same color
            if (is_null($horizontalLineColorValue))
                $this->_imageHorizontalLineColorValue = array('R' => 0, 'G' => 0, 'B' => 0);
            else {
                if (is_string($horizontalLineColorValue))
                    $rgb = explode('/', $horizontalLineColorValue);
                $color = (isset($rgb) && is_array($rgb) && count($rgb) >= 3) ? new Color($rgb[0], $rgb[1], $rgb[2]) : new Color($horizontalLineColorValue);
                $this->_imageHorizontalLineColorValue = $color->getColor();
            }
            if ($this->_imageHorizontalLineColorValue == $this->_imageBackgroundColor)
                throw new \Exception('Horizontal line color value must be different to background color');
        } else {
            // check validty of color value for color type 1 
            if ($this->_imageHorizontalLineColorType == 1) {
                $colors = explode('-', $horizontalLineColorValue);
                if (!is_array($colors) || $this->_imageHorizontalLine > count($colors))
                    throw new \Exception('Invalid horizontal lines colors value, miss a color');
            }
            for ($i = 0; $i < $this->_imageHorizontalLine; $i++) {
                if ($this->_imageHorizontalLineColorType == 1) {
                    if (is_string($colors[$i]))
                        $rgb = explode('/', $colors[$i]);
                    $color = (isset($rgb) && is_array($rgb) && count($rgb) >= 3) ? new Color($rgb[0], $rgb[1], $rgb[2]) : new Color($colors[$i]);
                    $this->_imageHorizontalLineColorValue[$i] = $color->getColor();
                    if ($this->_imageHorizontalLineColorValue[$i] == $this->_imageBackgroundColor)
                        throw new \Exception('Horizontal lines color value must be different to background color');
                } elseif ($this->_imageHorizontalLineColorType == 2) { // random colors
                    $r = Tools::generateInt(0, 255, $this->_imageBackgroundColor['R']);
                    $g = Tools::generateInt(0, 255, $this->_imageBackgroundColor['G']);
                    $b = Tools::generateInt(0, 255, $this->_imageBackgroundColor['B']);
                    $color = new Color($r, $g, $b);
                    $this->_imageHorizontalLineColorValue[$i] = $color->getColor();
                }
            }
        }

        if ((!is_null($horizontalLineThickness) && !is_int($horizontalLineThickness)) || $horizontalLineThickness > $this->_imageWidth)
            throw new \Exception('Horizontal line Thickness value must be an integer or null, and inferior to image width');
        $this->_imageHorizontalLineThickness = $horizontalLineThickness;

        // TODO imagedashedline ???
    }

    public function setImageDistortion($type, $level) {
        if (!is_int($type) || $type < 0 || $type > 2)
            throw new \Exception('Distortion type must be an int');
        if (!is_int($level) || !is_float($level))
            throw new \Exception('Distortion level must be an int or float');
        $this->_imageDistortionType = $type;
        $this->_imageDistortionLevel = $level;
    }

    public function getImageFormat() {
        return $this->_imageFormat;
    }

    public function setAudio($activate, $options = array()) {
        if (!is_bool($activate))
            throw new \Exception('Activate must be an boolean');
        $this->_audio = $activate;
        if ($activate) {
            if (!isset($options['audioUrl']))
                throw new \Exception('Miss audio url name');
            $this->_audioUrl = $options['audioUrl'];

            if (!isset($options['audioLangDirectory']))
                throw new \Exception('Security captcha need a audio lang directory parameter');
            $this->setAudioLangDirectory($options['audioLangDirectory']);

            if (isset($options['audioLettersGapMin']))
                $this->setAudioLettersGap($options['audioLettersGapMin'], 0);
            if (isset($options['audioLettersGapMax']))
                $this->setAudioLettersGap($options['audioLettersGapMax'], 1);

            if (isset($options['audioNoise'])) {
                $this->setAudioNoise($options['audioNoise']);
                if (!isset($options['audioNoiseFile']))
                    throw new \Exception('Security captcha need a audio noise need audio noise file parameter');
                $this->setAudioNoiseFile($options['audioNoiseFile']);
                if (isset($options['audioNoiseMixNormalization']))
                    $this->setAudioNoiseMixNormalization($options['audioNoiseMixNormalization']);
            }
            if (isset($options['audioDegrade']))
                $this->setAudioDegrade($options['audioDegrade']);

            $this->_audio = $activate;
        }
    }

    public function getAudio() {
        return $this->_audio;
    }

    public function setAudioLangDirectory($path) {
        $path = realpath($path . Language::getInstance()->getLanguage());
        if (!is_dir($path))
            throw new \Exception('Invalid audio language path : "' . $path . '"');
        // TODO check if have all wav files (charslist)
        $this->_audioLangDirectory = $path . DS;
    }

    public function setAudioLettersGap($gapValue, $gabType) {
        if (!$gabType > 1 || $gabType < 0)
            throw new \Exception('Gap type invalid');

        $gapValue = (int) $gapValue;
        if (!is_int((int) $gapValue))
            throw new \Exception('Gap value invalid');

        if ($gabType == 0) {
            if ($gapValue >= $this->_audioLettersGapMax)
                throw new \Exception('min gap value too hight');
            $this->_audioLettersGapMin = $gapValue;
        }
        if ($gabType == 1) {
            if ($gapValue <= $this->_audioLettersGapMin)
                throw new \Exception('max gap value too low');
            $this->_audioLettersGapMax = $gapValue;
        }
    }

    public function setAudioNoise($bool) {
        if (!is_bool($bool))
            throw new \Exception('Audio noise parameter must be an boolean');

        $this->_audioNoise = $bool;
    }

    public function setAudioNoiseFile($noiseFile) {
        if (!file_exists($noiseFile) || !is_readable($noiseFile) || !Validate::isFileMimeType('wav', $noiseFile))
            throw new \Exception('Invalid audio noise file, must be an readable wav file');
        $this->_audioNoiseFile = $noiseFile;
    }

    public function setAudioNoiseMixNormalization($value) {
        if (!is_float($value) && !is_int($value))
            throw new \Exception('Audio noise mix normalization parameter must an integer or float');

        $this->_audioNoiseMixNormalization = $value;
    }

    public function setAudioDegrade($bool) {
        if (!is_bool($bool))
            throw new \Exception('Audio degrade parameter must be an boolean');

        $this->_audioDegrade = $bool;
    }

    protected function _createImage() {
        if (is_null($this->_key))
            throw new \Exception('Key must be generated');
        // create image ressource
        $this->_imageContents = imagecreate($this->_imageWidth, $this->_imageHeight);
        // set background
        if ($this->_imageBackground == 0) {// background color
            $background = imagecolorallocate($this->_imageContents, $this->_imageBackgroundColor['R'], $this->_imageBackgroundColor['G'], $this->_imageBackgroundColor['B']);
            // fill the background
            imagefill($this->_imageContents, 0, 0, $background);
        } elseif ($this->_imageBackground == 1) {// background file
            $datas = getimagesize($this->_imageBackgroundFile);
            if (!$datas)
                throw new \Exception('Invalid background file');
            switch ($datas[2]) {
                case 1: $background = imagecreatefromgif($this->_imageBackgroundFile);
                    break;
                case 2: $background = imagecreatefromjpeg($this->_imageBackgroundFile);
                    break;
                case 3: $background = imagecreatefrompng($this->_imageBackgroundFile);
                    break;
                default:
                    throw new \Exception('Invalid background file type');
            }
            imagecopyresized($this->_imageContents, $background, 0, 0, 0, 0, $this->_imageWidth, $this->_imageHeight, imagesx($background), imagesy($background));
        }



        // TODO distorsion of letters 
        if ($this->_imageDistortionType > 1) {
            
        }
        // Center key
        // TODO angle random by letter ?(besoin creer chaque lettre comme une image(imagecreatefromstring) voir http://jpgraph.net/download/manuals/chunkhtml/ch17s02.html)
        $tab = imagettfbbox($this->_imageFontSize, $this->_imageFontAngle, $this->_imageFontFile, $this->_key);
        $xKey = ($this->_imageWidth - ($tab[2] - $tab[6])) / 2;
        $yKey = ($this->_imageHeight - ($tab[1] - $tab[5])) / 2 + $this->_imageFontSize;
        //  Color key
        $color = imagecolorallocate($this->_imageContents, $this->_imageFontColor['R'], $this->_imageFontColor['G'], $this->_imageFontColor['B']);
        // Put on image
        imagettftext($this->_imageContents, $this->_imageFontSize, $this->_imageFontAngle, $xKey, $yKey, $color, $this->_imageFontFile, $this->_key);

        // Noise image with arc see http://en.wikipedia.org/wiki/Image_noise
        if ($this->_imageNoise) {
            for ($i = 0; $i < $this->_imageNoiseCount; ++$i) {
                if ($this->_imageNoiseColorType == 0)
                    $noiseColor = imagecolorallocate($this->_imageContents, $this->_imageNoiseColorValue['R'], $this->_imageNoiseColorValue['G'], $this->_imageNoiseColorValue['B']);
                elseif ($this->_imageNoiseColorType == 1 || $this->_imageNoiseColorType == 2)
                    $noiseColor = imagecolorallocate($this->_imageContents, $this->_imageNoiseColorValue[$i]['R'], $this->_imageNoiseColorValue[$i]['G'], $this->_imageNoiseColorValue[$i]['B']);

                $x = rand(1, $this->_imageWidth);
                $y = rand(1, $this->_imageHeight);
                $size = rand($this->_imageNoiseMinSize, $this->_imageNoiseMaxSize);
                imagefilledarc($this->_imageContents, $x, $y, $size, $size, 0, 360, $noiseColor, IMG_ARC_PIE);
            }
        }
        // Vertical lines
        for ($i = 0; $i < $this->_imageVerticalLine; $i++) {
            if ($this->_imageVerticalLineColorType == 0)
                $color = imagecolorallocate($this->_imageContents, $this->_imageVerticalLineColorValue['R'], $this->_imageVerticalLineColorValue['G'], $this->_imageVerticalLineColorValue['B']);
            else
                $color = imagecolorallocate($this->_imageContents, $this->_imageVerticalLineColorValue[$i]['R'], $this->_imageVerticalLineColorValue[$i]['G'], $this->_imageVerticalLineColorValue[$i]['B']);

            //Thickness
            if (!is_null($this->_imageVerticalLineThickness)) {
                if ($this->_imageVerticalLineThickness == 0) // random
                    imagesetthickness($this->_imageContents, mt_rand(0, 4)); // better random ?
                else
                    imagesetthickness($this->_imageContents, $this->_imageVerticalLineThickness);
            }
            imageline($this->_imageContents, $x = mt_rand($this->_imageWidth / 4, $this->_imageWidth * 3 / 4), 0, $x, $this->_imageHeight, $color);
        }
        // Horizontal lines
        for ($i = 0; $i < $this->_imageHorizontalLine; $i++) {
            if ($this->_imageHorizontalLineColorType == 0)
                $color = imagecolorallocate($this->_imageContents, $this->_imageHorizontalLineColorValue['R'], $this->_imageHorizontalLineColorValue['G'], $this->_imageHorizontalLineColorValue['B']);
            else
                $color = imagecolorallocate($this->_imageContents, $this->_imageHorizontalLineColorValue[$i]['R'], $this->_imageHorizontalLineColorValue[$i]['G'], $this->_imageHorizontalLineColorValue[$i]['B']);

            //Thickness
            if (!is_null($this->_imageHorizontalLineThickness)) {
                if ($this->_imageHorizontalLineThickness == 0) // random
                    imagesetthickness($this->_imageContents, mt_rand(0, 4)); // better random ?
                else
                    imagesetthickness($this->_imageContents, $this->_imageHorizontalLineThickness);
            }
            imageline($this->_imageContents, 0, $y = mt_rand($this->_imageHeight / 4, $this->_imageHeight * 3 / 4), $this->_imageWidth, $y, $color);
        }
    }

    protected function _createAudio() {
        if (is_null($this->_key))
            throw new \Exception('Key must be generated');

        // TODO need get IT, DE and ES audio files
        //http://www.gilles-joyeux.fr/MP3/NEn.mp3 letters
        //http://www.gilles-joyeux.fr/MP3/ten.mp3 numbers
        // revoir les sons S et F en anglais ...
        try {
            $globalWavFile = new \WavFile();
            // Set sample rate, bits/sample, and Num of channels // TODO setter and getter this params ?
            $globalWavFile->setSampleRate(8000);
            $globalWavFile->setBitsPerSample(8);
            $globalWavFile->setNumChannels(1);
            $letters = str_split(strtoupper($this->_key));
            foreach ($letters as $letter) {
                if (!file_exists($this->_audioLangDirectory . $letter . '.wav'))
                    throw new \Exception('Audio file : "' . $this->_audioLangDirectory . $letter . '.wav' . '" miss');

                $l = new \WavFile($this->_audioLangDirectory . $letter . '.wav');
                // append letter to the captcha audio
                $globalWavFile->appendWav($l);
                // random silence  between letters
                $globalWavFile->insertSilence(mt_rand($this->_audioLettersGapMin, $this->_audioLettersGapMax) / 1000.0); //rand min and max
            }
            // Add filters
            $filters = array();
            // noise by sound file
            if ($this->_audioNoise) {
                // use background audio
                $wavNoise = new \WavFile($this->_audioNoiseFile, false);
                $wavNoise->setSampleRate($globalWavFile->getSampleRate())
                        ->setBitsPerSample($globalWavFile->getBitsPerSample())
                        ->setNumChannels($globalWavFile->getNumChannels());

                // start at a random offset from the beginning of the wav file in order to add more randomness
                $randOffset = 0;
                if ($wavNoise->getNumBlocks() > 2 * $globalWavFile->getNumBlocks()) {
                    $randBlock = rand(0, $wavNoise->getNumBlocks() - $globalWavFile->getNumBlocks());
                    $wavNoise->readWavData($randBlock * $wavNoise->getBlockAlign(), $globalWavFile->getNumBlocks() * $wavNoise->getBlockAlign());
                } else {
                    $wavNoise->readWavData();
                    $randOffset = rand(0, $wavNoise->getNumBlocks() - 1);
                }


                $mixOpts = array('wav' => $wavNoise,
                    'loop' => true,
                    'blockOffset' => $randOffset);

                $filters[\WavFile::FILTER_MIX] = $mixOpts;
                $filters[\WavFile::FILTER_NORMALIZE] = $this->_audioNoiseMixNormalization;
            }
            // add random noise. Any noise level below 95% is intensely distorted and not pleasing to the ear
            if ($this->_audioDegrade)
                $filters[\WavFile::FILTER_DEGRADE] = rand(95, 98) / 100.0;

            // apply filters to audio file
            if (count($filters) > 0)
                $globalWavFile->filter($filters);


            // save
            $this->_audioContents = $globalWavFile->makeHeader();
            $this->_audioContents .= $globalWavFile->getDataSubchunk();

            unset($globalWavFile);
        } catch (\Exception $e) {
            Logger::getInstance()->debug('Security captcha generate audio file for form : "' . $this->getFormName() . '" error : "' . $e . '"', 'security');

            if (file_exists($this->_audioLangDirectory . 'error.wav'))
                $this->_audioContents = file_get_contents($this->_audioLangDirectory . 'error.wav');
        }
    }

    protected function _display($captchaType) {
        $this->create(Session::getInstance()->get($this->getFormName() . 'Captcha'), $captchaType);
        Header::sentHeader('Cache-Control', 'no-store, no-cache, must-revalidate');
        Header::sentHeader('Cache-Control', 'post-check=0, pre-check=0', false);
        Header::sentHeader('Pragma', 'no-cache');
        Header::sentHeader('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT');
        if ($captchaType == 'image') {
            Header::sentHeader('Content-Type', 'image/' . $this->_imageFormat);
            // display captcha
            switch ($this->_imageFormat) {
                case 'png':
                    imagepng($this->_imageContents);
                    break;
                case 'jpg':
                    imagejpeg($this->_imageContents);
                    break;
                case 'gif':
                    imagegif($this->_imageContents);
                    break;
            }
        } elseif ($captchaType == 'audio') {
            Header::sentHeader('Content-Type', 'audio/x-wav');
            Header::sentHeader('Content-Length', (string) strlen($this->_audioContents));
            // display captcha
            echo $this->_audioContents;
        }
    }

}

?>