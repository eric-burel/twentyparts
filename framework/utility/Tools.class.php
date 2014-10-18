<?php

namespace framework\utility;

use framework\Language;
use framework\config\loaders\Constant;
use framework\network\Http;
use framework\utility\Validate;
use framework\utility\Superglobals;

class Tools {

    public static function isWindows() {
        if (Http::getServer('OS'))
            return (stripos(Http::getServer('OS'), 'win') !== false) ? true : false;
        elseif (Http::getServer('SERVER_SOFTWARE'))
            return (stripos(Http::getServer('SERVER_SOFTWARE'), 'win') !== false) ? true : false;
        else
            throw new Exception('Impossible to identify operating system');
    }

    public static function deleteTreeDirectory($directory, $deleteRootDirectory = true, $chmod = false) {
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory), \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($iterator as $path) {
            if ($chmod)
                chmod($path, $chmod);
            if ($path->isDir()) {
                $last = substr($path->__toString(), -1, 1);
                if ($last == '.' || $last == '..' || $last == '.svn')
                    continue;
                rmdir($path->__toString());
            } else
                unlink($path->__toString());
        }
        if ($deleteRootDirectory) {
            if ($chmod)
                chmod($directory, $chmod);
            rmdir($directory);
        }
    }

    /**
     * Compare the current PHP version to another
     *
     * @access public
     * @static
     * @param string $version php version
     * @return int  -1 if $version > current, 0 if equal, 1 if $version < current
     */
    public static function phpVersionCompareTo($version) {
        return version_compare(PHP_VERSION, $version);
    }

    /**
     * Convert a string to url format for url rewrite
     * 
     * @access public
     * @static
     * @param string $string String to encode
     * @param string $separator Separator for replacing space and ponctuation, by default "_"
     * @param string $encoding Set the encoding of source string
     * @return string
     */
    public static function stringToUrl($string, $separator = '_', $encoding = 'UTF-8', $lower = false) {
        if (!Validate::isCharset($encoding))
            throw new \Exception('Encoding in\'t a valid charset type');
        $string = htmlentities(html_entity_decode($string, null, $encoding), ENT_NOQUOTES, $encoding);
        if ($lower)
            $string = strtolower($string);
        $search = array('#\&(.)(?:uml|circ|tilde|acute|grave|cedil|ring)\;#', '#\&(.{2})(?:lig)\;#', '#\&[a-z]+\;#', '#[^a-zA-Z0-9_]#', '#' . $separator . '+#');
        $replace = array('\1', '\1', '', $separator, $separator);
        return trim(preg_replace($search, $replace, $string), $separator);
    }

    /**
     * Permet de selectionner un string dans une chaine en délimitant le début et la fin de la selection
     *
     * @access public
     * @static
     * @param string $haystack: la chaine dans laquelle la selection sera faite
     * @param string $delimiter_start: le début de la selection
     * @param string $delimiter_end: la fin de la selection
     * @return string la selection
     *
     * @example: $haystack = 'abcdefghij'; Utils::selectStringByDelimiter($haystack, 'b', 'g'); retourne 'bcdefg';
     */
    public static function selectStringByDelimiter($haystack, $delimiter_start, $delimiter_end) {
        $pos1 = strpos($haystack, $delimiter_start);
        if ($pos1 === false)
            throw new \Exception('Impossible de selectionner le string dans la chaine demandée car le délimiter de début "' . $delimiter_start . '" n\'exsite pas');
        $pos2 = strpos($haystack, $delimiter_end, $pos1);
        if ($pos2 === false)
            throw new \Exception('Impossible de selectionner le string dans la chaine demandée car le délimiter de fin "' . $delimiter_end . '" n\'exsite pas');

        return substr($haystack, $pos1, $pos2 - $pos1 + strlen($delimiter_end));
    }

    /**
     * Transforme un object en array
     *
     * @access public
     * @static
     * @param string $object : un objet
     * @return array $array
     */
    public static function parseObjectToArray($object) {
        $array = array();
        if (is_object($object))
            $array = get_object_vars($object);
        elseif (is_array($object)) {
            foreach ($object as $k => &$value)
                $array[$k] = self::parseObjectToArray($value);
        } else
            $array = $object;

        return $array;
    }

    /**
     * Permet de regarder si un fichier existe, depuis une url avec curl
     *
     * @access public
     * @static
     * @param string $url l'url
     * @return bool
     */
    public static function httpFileExistsWithCurl($url) {
        if (!extension_loaded('curl'))
            throw new \Exception('Curl extension not loaded try change your PHP configuration');
        return ($ch = curl_init($url)) ? @curl_close($ch) || true : false;
    }

    /**
     * Permet de regarder si un fichier existe, depuis une url avec file_fet_contents
     *
     * @access public
     * @static
     * @param string $url l'url
     * @return bool
     */
    public static function httpFileExists($url) {
        return (@file_get_contents($url)) ? true : false;
    }

    /**
     * Permet de sauvegarder une fichier dans un répertoire, engendre une exception si la copy echoue
     *
     * @access public
     * @static
     * @param string $file fichier à  sauvegarder
     * @param string $destination emplacement de sauvegarde
     * @param string $context une ressource de contexte (stream_context_create())
     * @return void
     *
     * TODO add ecrase file or not and check if file exists...
     */
    public static function saveFile($file, $destination, $context = null) {
        $context = is_null($context) ? stream_context_create(array()) : $context;
        if (!copy($file, $destination))
            throw new \Exception('La copie du fichier: ' . $file . ' a échouée...');
    }

    public static function saveDirectory($source, $target, $chmod = false) {
        if (!is_dir($source)) {
            self::saveFile($source, $target);
            return;
        }


        if (!is_dir($target)) {
            if (!mkdir($target, $chmod, true))
                throw new \Exception('Error on creating "' . $target . '" directory');
        }

        $d = dir($source);
        $navFolders = array('.', '..', '.svn');
        while (false !== ($fileEntry = $d->read() )) {//copy one by one
            //skip if it is navigation folder . or ..
            if (in_array($fileEntry, $navFolders))
                continue;

            //do copy
            $s = "$source/$fileEntry";
            $t = "$target/$fileEntry";
            self::saveDirectory($s, $t, $chmod);
        }
        $d->close();
    }

    public static function countFilesIntoDirectory($directory, $dirCleanList = array('.', '..', '.svn')) {
        if (!is_dir($directory))
            throw new \Exception('Directory doesn\'t exists');

        return count(self::cleanScandir($directory, $dirCleanList));
    }

    // TODO : Ajout d'un argument pour forcer la clé de la global qu'on veut
    public static function getUserIp($byGlobalType = null) {
        if (!is_null($byGlobalType) && $byGlobalType != Superglobals::SERVER && $byGlobalType != Superglobals::ENV)
            throw new \Exception('byGlobalType parameter invalid');

        // Get by global $_SERVER
        if (is_null($byGlobalType) || $byGlobalType == Superglobals::SERVER && Http::getServer()) {
            if (Http::getServer('HTTP_X_FORWARDED_FOR')) {
                $ips = explode(',', Http::getServer('HTTP_X_FORWARDED_FOR'));
                $ip = $ips[0];
            } elseif (Http::getServer('HTTP_CLIENT_IP'))
                $ip = Http::getServer('HTTP_CLIENT_IP');
            else
                $ip = Http::getServer('REMOTE_ADDR');
        } elseif (is_null($byGlobalType) || $byGlobalType == Superglobals::ENV && Http::getEnv()) {
            // Get by global $_ENV
            if (Http::getEnv('HTTP_X_FORWARDED_FOR')) {
                $ips = explode(',', Http::getEnv('HTTP_X_FORWARDED_FOR'));
                $ip = $ips[0];
            } elseif (Http::getEnv('HTTP_CLIENT_IP'))
                $ip = Http::getEnv('HTTP_CLIENT_IP');
            else
                $ip = Http::getEnv('REMOTE_ADDR');
        } else
            throw new \Exception('Unbearable user internet protocol, plz check your environnement');

        return $ip;
    }

    /**
     * Permet de lister les repertoires d'un dossier
     *
     * @access public
     * @static
     * @param string $dir le dossier Ã  parcourir
     * @return array $l: liste des dossiers dans le dossier
     *
     */
    public static function dirList($dir) {
        if (!is_dir($dir))
            throw new \Exception('"' . $dir . '" is not a valid directory');

        $l = array();
        foreach (self::cleanScandir($dir) as $f) {
            if (is_dir($dir . $f)) {
                $l[] = $dir . $f . '/';
                $l = array_merge($l, self::dirList($dir . $f . DS));
            }
        }
        return $l;
    }

    /**
     * Permet de retourner le contenu d'un fichier inclut
     *
     * @access public
     * @static
     * @param string $filename le fichier à  inclure
     * @param array $param_contents les paramètres pour l'inclusion du fichier: sous forme d'array: array(array('param_name' => 'var1', 'param_value' => 'test1'), array('param_name' => 'var2', 'param_value' => 'test2'))
     *              permet d'etablir les variables: $var1 et $var2 dans le fichier à  inclure
     *              (OPTIONNEL)
     * @return string le contenu du fichier
     */
    public static function getIncludeContents($filename, $param_contents = false) {
        //On verifie que le fichier existe, qu'il soit valid (lisible...) si ce n'est pas le cas, on genere une exception
        if (!is_file($filename) && !file_exists($filename) && !is_readable($filename))
            throw new \Exception('Le fichier "' . $filename . '" n\'existe pas ou est invalid');
        //Attribution du nom et valeur de variables contenu dans le fichier Ã  inclure
        if ($param_contents) {

            for ($i = 0; $i < count($param_contents); $i++) {
                if (array_key_exists('param_name', $param_contents) && array_key_exists('param_value', $param_contents))
                    ${$param_contents[$i]['param_name']} = $param_contents[$i]['param_value'];
            }
        }
        ob_start();
        include $filename;
        $contents = ob_get_contents();
        ob_end_clean();
        return $contents;
    }

    /**
     * Permet scanner un dossier en enlevent les dossier ., .. et .svn du scandir()
     *
     * @access public
     * @static
     * @param string $dir le dossier à  scanner
     * @return array le dossier scanner
     */
    public static function cleanScandir($dir, $dirCleanList = array('.', '..', '.svn')) {
        if (!is_dir($dir))
            throw new \Exception('Impossible de scanner le dossier "' . $dir . ', il n\'existe pas');
        return array_diff(scandir($dir), $dirCleanList);
    }

    public static function generateString($length, $charsList = '') {
        //TODO compatibility with mbstring and encoding
        if (!is_int($length))
            throw new \Exception('Length parameter must be an integer');
        if (!is_string($charsList) && !is_array($charsList))
            throw new \Exception('charsList parameter must be an array or string');

        if (Validate::isEmpty($charsList))
            $charsList = array_merge(range('a', 'z'), range('A', 'Z'), range('0', '9'));
        if (is_string($charsList))
            $charsList = str_split($charsList);


        $string = '';
        for ($to = 0; $to < $length; $to++) {
            $i = mt_rand(0, count($charsList) - 1);
            $string .= $charsList[$i];
        }
        return $string;
    }

    public static function generateInt($min = 0, $max = null, $excluded = array()) {
        if (!is_int($min))
            throw new \Exception('min must be an integer');
        if (!is_int($max) && !is_null($max))
            throw new \Exception('max must be an integer or null');
        if (!is_int($excluded) && !is_array($excluded))
            throw new \Exception('excluded must be an integer or null');

        if (is_null($max))
            $max = mt_getrandmax();
        if ($max < $min)
            throw new \Exception('Max must be superior to min');

        if (is_int($excluded))
            $excluded = array($excluded);

        $excluded = array_unique($excluded);
        // check excluded
        foreach ($excluded as $value) {
            if (!is_int($value))
                throw new \Exception('All excluded values must be an integer');
        }
        if (count($excluded) >= ($max - $min))
            throw new \Exception('Random impossible, all combinaisons are excluded');



        do {
            $rand = mt_rand($min, $max);
        } while (in_array($rand, $excluded));

        return $rand;
    }

    public static function castValue($value) {
        if (Validate::isInt($value))
            return (int) $value;
        elseif (Validate::isFloat($value) && (strpos($value, '.') != false || strpos($value, ',') != false))
            return (float) $value;
        elseif (Validate::isBool($value)) {
            if ($value == 'true' || $value == 'TRUE' || $value == '1')
                return true;
            if ($value == 'false' || $value == 'FALSE' || $value == '0')
                return false;
        } elseif (Validate::isString($value)) {
            //check cons pattern [CONS_NAME] or lang var {var_name}
            $pattern = array("#\[([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\]#", "#\{([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\}#");
            $callback = function ($item) {
                //Is defined, return value
                if (defined($item[1]))
                    return constant($item[1]);
                else {
                    //check if is loaded into config loader
                    $cons = Constant::getCons();
                    if (array_key_exists($item[1], $cons))
                        return $cons[$item[1]];

                    $varLang = Language::getInstance()->getVar($item[1]);

                    if (!is_null($varLang))
                        return $varLang;
                    // no find cons, or var lang
                    return $item[1];
                }
            };

            return preg_replace_callback($pattern, $callback, $value);
        } else
            return $value;
    }

    public static function getFileExtension($filename) {
        return str_replace('.', '', strrchr($filename, '.'));
    }

    public static function parseDsn($dsn) {
        if (!is_string($dsn))
            throw new \Exception('Dsn must ben a string');

        $datasDsn = array();
        //extract driver
        $dsnExplode = explode(':', $dsn);
        if (!$dsnExplode || !isset($dsnExplode[0]))
            throw new \Exception('Invalid dsn, please set driver');
        $datasDsn['driver'] = $dsnExplode[0];

        // Get others infos : host, dbname etc ...
        if (!isset($dsnExplode[1]))
            throw new \Exception('Invalid dsn format');
        $orthers = explode(';', $dsnExplode[1]);
        if (!$orthers)
            throw new \Exception('Invalid dsn format');
        foreach ($orthers as &$info) {
            $infoData = explode('=', $info);
            if (!is_array($infoData) || !isset($infoData[0]) || !isset($infoData[1]))
                throw new \Exception('Invalid dsn format');

            $datasDsn[$infoData[0]] = $infoData[1];
        }

        return $datasDsn;
    }

}

?>