<?php

namespace framework\utility;

use framework\utility\Tools;
use framework\network\Http;

class Validate {

    /**
     * Checks if the a variable is empty with remove white space if source is string
     * !!Important!! '0' or 0 is not considered to be empty !
     * @param Mixed $var
     * @return boolean
     */
    public static function isEmpty($var) {
        if ($var === null || is_string($var) && trim($var) === '' || is_array($var) && count($var) == 0)
            return true;
        return false;
    }

    public static function isBool($var) {
        if (is_bool($var))
            return true;
        if (is_string($var)) {
            if ($var == 'true' || $var == 'TRUE' || $var == '1')
                return true;
            if ($var == 'false' || $var == 'FALSE' || $var == '0')
                return true;
        }
        if (is_int($var)) {
            if ($var == 1 || $var == 0)
                return true;
        }

        return false;
    }

    /**
     * Checks if the number is an int, and can be check the range
     * @param Mixed $number
     * @param Integer $gt Check if greater than $gt
     * @param Integer $lt Check if lower than $lt
     * @return Boolean
     */
    public static function isInt($number, $gt = false, $lt = false) {
        if (is_string($number)) {
            if (trim($number) == '')
                return false;
            // Extract sign if exist
            $sign = '';
            if ($number[0] == '+' || $number[0] == '-') {
                $sign = $number[0];
                $number = substr($number, 1);
            }
            $number = ($sign == '-' ? '-' . $number : $number);
            if ($number !== (string) (int) $number)
                return false;
            $number = (int) $number;
        }elseif (((int) $number) !== $number)
            return false;
        if (!$lt && !$gt || $lt && $number <= $lt && $gt && $number >= $gt || !$lt && $gt && $number >= $gt || !$gt && $lt && $number <= $lt)
            return true;
        return false;
    }

    /**
     * Checks if the number is a float and if is in the range of the given values.
     *
     * @param Mixed $number Number to check
     * @param int $from Beginning of the range
     * @param int $to Ending of the range
     *
     * @return boolean
     */
    public static function isFloat($number, $gt = false, $lt = false) {
        if (is_string($number)) {
            if (trim($number) == '')
                return false;
            // Extract sign if exist
            $sign = '';
            if ($number[0] == '+' || $number[0] == '-') {
                $sign = $number[0];
                $number = substr($number, 1);
            }
            $number = ($sign == '-' ? '-' . $number : $number);
            if (!\is_numeric($number))
                return false;
            $number = (float) $number;
        }elseif (is_int($number)) {
            $number = (float) $number;
        }
        if (!\is_float($number))
            return false;
        if (!$lt && !$gt || $lt && $number <= $lt && $gt && $number >= $gt || !$lt && $gt && $number >= $gt || !$gt && $lt && $number <= $lt)
            return true;
        return false;
    }

    /**
     * Check if a var is a string and can be check the size of it
     * @param Mixed $data
     * @param Integer $minLenght
     * @param Integer $maxLenght
     */
    public static function isString($var, $minLenght = false, $maxLenght = false) {
        if (!is_string($var))
            return false;
        if ($minLenght && strlen($var) < $minLenght)
            return false;
        if ($maxLenght && strlen($var) > $maxLenght)
            return false;
        return true;
    }

    /**
     * Check if a string is a correct variable name
     * @param String $name
     * @return Boolean
     */
    public static function isVariableName($name) {
        if (!is_string($name))
            return false;
        return (boolean) preg_match('`^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$`', $name);
    }

    /**
     * Checks if the string is a well formed email address..
     *
     * @param string $string
     * @return boolean
     */
    public static function isEmail($email) {
        if (!is_string($email) || strlen($email) > 255)
            return false;
        if (Tools::phpVersionCompareTo('5.3.3') >= 0)
            return (boolean) filter_var($email, FILTER_VALIDATE_EMAIL);
        else
            return (boolean) preg_match('`^(?!\.)[.\w!#$%&\'*+/=?^_\`{|}~-]+@(?!-)[\w-]+\.\w+$`i', $email);
    }

    /**
     * Check if the variable is a representation of ip
     * @param String $ip
     * @param Boolean $allowIpV4 allow ipv4 (true by default)
     * @param Boolean $allowIpV6 allow ipv6 (true by default)
     * @return Boolean
     */
    public static function isIp($ip, $allowIpV4 = true, $allowIpV6 = true) {
        if ($allowIpV6 && $allowIpV4)
            return \filter_var($ip, FILTER_VALIDATE_IP) !== false;
        if (!$allowIpV6 && $allowIpV4)
            return \filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false;
        if ($allowIpV6 && !$allowIpV4)
            return \filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;
    }

    /**
     * Permet de verifier si un fichier est de mimetype voulut
     *
     * @access public
     * @static
     * @param <string> $mimetype: le mimetype voulut
     * @param <string> $file: le fichier
     * @return <bool>
     */
    public static function isFileMimeType($mimetype, $file) {
        if (!extension_loaded('fileinfo'))
            throw new \Exception('fileinfo extension not loaded try change your PHP configuration');

        if (!is_file(realpath($file)) || !file_exists(realpath($file)) || !is_readable(realpath($file)))
            throw new \Exception('La vérification du mimetype du fichier "' . $file . '" à échoué, le fichier n\'existe pas ou est invalid');

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        return (strpos($finfo->file(realpath($file)), $mimetype) !== false) ? true : false;
    }

    /**
     * Permet de verifier si un fichier est d'une extension voulut, depuis son extension dans son nom (.type)
     *
     * @access public
     * @static
     * @param <string> $type: le type voulut
     * @param <string> $file: le nom du fichier
     * @return <bool>
     */
    public static function isFileExtension($type, $file) {
        return (str_replace('.', '', strrchr($file, '.')) != $type) ? false : true;
    }

    /**
     * Permet de verifier si un string fait entre 4 et 16 caractere et est valid : verification d'un pseudo
     *
     * @access public
     * @static
     * @param <string> $value
     * @return <bool>
     *
     * TODO add validation rule
     */
    public static function isPseudo($value) {
        return (bool) (preg_match("#^[A-Za-z][0-9A-Za-z_-]{2,14}[0-9A-Za-z_-]$#", $value));
    }

    /**
     * Permet de verifier si un string fait entre 4 et 16 caractere et est valid: verification d'un password
     *
     * @access public
     * @static
     * @param <void>
     * @return <bool>
     *
     * TODO add validation rule
     */
    public static function isPassword($value) {
        return (bool) (preg_match("#^[A-Za-z][0-9A-Za-z_-]{2,14}[0-9A-Za-z_-]$#", $value));
    }

    public static function isUrl($value, $checkIfIsSsl = false, $urlType = 'http') {
        if (!is_string($value))
            throw new \Exception('Url value parameter must be a string');
        if (!is_bool($checkIfIsSsl))
            throw new \Exception('checkIfIsSsl parameter must be a boolean');
        $scheme = parse_url($value, PHP_URL_SCHEME);
        if ($scheme === null)
            return false;
        if (($checkIfIsSsl) && (bool) (substr($scheme, -1) === 's') == false)
            return false;


        if (!is_string($urlType))
            throw new \Exception('Url type parameter must be a string');
        // TODO http://fr.wikipedia.org/wiki/Sch%C3%A9ma_d%27URI
        switch ($urlType) {
            case 'http':
            case 'javascript':
            case 'ftp':
            case 'file':
            case 'mailto':
            case 'telnet':
                break;
            default:
                throw new \Exception('UrlType parameter must be a string : http, javascript, ftp, file, mailto, telnet');
        }
        $info = parse_url($value);
        switch ($info['scheme']) {
            case 'http':
            case 'https':
                return ($urlType == 'http');
            case 'ftp':
            case 'ftps':
                return ($urlType == 'ftp');
            default:
                return ($urlType == $info['scheme']);
        }
    }

    public static function isCharset($charset, $charsetList = array()) {
        if (!is_string($charset))
            return false;
        if (!empty($charsetList))
            return (in_array($charset, $charsetList));
        else {
            if (file_exists(PATH_DATA . 'charset.xml')) {
                $charsetList = simplexml_load_file(PATH_DATA . 'charset.xml');
                if (is_null($charsetList) || !$charsetList)
                    throw new \Exception('Invalid charset datas file : "' . PATH_DATA . '' . 'charset.xml"');

                foreach ($charsetList->charset as $char) {
                    if ($char == $charset)
                        return true;
                }
                return false;
            } else
                throw new \Exception('Please set charset list data');
        }
    }

    public static function isLanguage($language, $languageList = array()) {
        if (!is_string($language))
            return false;
        if (!empty($languageList))
            return (in_array($language, $languageList));
        else {
            if (file_exists(PATH_DATA . 'language.xml')) {
                $languageList = simplexml_load_file(PATH_DATA . 'language.xml');
                if (is_null($languageList) || !$languageList)
                    throw new \Exception('Invalid language datas file : "' . PATH_DATA . '' . 'language.xml"');

                foreach ($languageList as $lang) {
                    if ($lang == $language)
                        return true;
                }
                return false;
            } else
                throw new \Exception('Please set language list data');
        }
    }

    public static function isTimeZone($timezone, $timezoneList = array()) {
        if (!is_string($timezone))
            return false;
        if (!empty($timezoneList))
            return (in_array($timezone, $timezoneList));
        else {
            if (file_exists(PATH_DATA . 'timezone.xml')) {
                $timezoneList = simplexml_load_file(PATH_DATA . 'timezone.xml');
                if (is_null($timezoneList) || !$timezoneList)
                    throw new \Exception('Invalid timezone datas file : "' . PATH_DATA . '' . 'timezone.xml"');

                foreach ($timezoneList as $zone) {
                    if ($zone == $timezone)
                        return true;
                }
                return false;
            } else
                throw new \Exception('Please set Timezone list data');
        }
    }

    public static function isPhoneNumber($phone, $format = 'fr') {
        // TODO implement format ...
        return preg_match('/^0[1-689][0-9]{8}$/', $phone);
    }

    public static function isCodePostal($codePostal, $format = 'fr') {
        // TODO format support ...
        return preg_match('`[0-9]{5}`', $codePostal);
    }

    public static function isWantedEmail($email, $byDomain = true, $wantedEmailList = array()) {
        if (!self::isEmail($email))
            throw new \Exception('Email must be a valid email');
        if (!is_bool($byDomain))
            throw new \Exception('By domain must be an boolen');

        if (!is_array($wantedEmailList))
            throw new \Exception('wantedEmailList must be an array');

        if (empty($wantedEmailList)) {
            if (file_exists(PATH_DATA . 'wantedEmail.xml')) {
                $wantedEmailList = simplexml_load_file(PATH_DATA . 'wantedEmail.xml');
                if (is_null($wantedEmailList) || !$wantedEmailList)
                    throw new \Exception('Invalid wantedEmail datas file : "' . PATH_DATA . '' . 'wantedEmail.xml"');

                $wantedEmailList = $wantedEmailList->email;
            } else
                throw new \Exception('Please set wantedEmail list datas');
        }

        if ($byDomain) {
            //On prend la valeur après le "@"
            preg_match('/[^@]+$/', $email, $matches);
            $wanted = $matches[0];
        } else
            $wanted = $email;

        //Check into wanted email list
        for ($i = 0; $i < count($wantedEmailList); $i++) {
            if ($wantedEmailList[$i] == $wanted)
                return true;
        }
        return false;
    }

    public static function isJson($str, $returnData = false) {
        if (!is_bool($returnData))
            throw new \Exception('returnData parameter must an boolean');

        $json = json_decode($str);
        if (!is_null($json))
            return $returnData ? $json : true;
        else
            return false;
    }

    public static function isGoogleBot() {
        if (stripos(Http::getServer('HTTP_USER_AGENT'), 'Googlebot') !== false)
            return true;
        return false;
    }

}

?>