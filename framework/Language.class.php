<?php

namespace framework;

use framework\utility\Validate;
use framework\utility\Tools;
use framework\Logger;

class Language {

    use pattern\Singleton;

    protected $_language = null;
    protected $_defaultLanguage = null;
    protected static $_datasPath = null;
    protected static $_languageVars = null;
    protected static $_defaultLanguageVars = null;

    public static function getVar($varName, $default = null) {
        if (!Validate::isVariableName($varName))
            throw new \Exception('language var name : "' . $varName . '"must be a valid variable');

        if (!property_exists(self::$_languageVars, $varName)) {
            Logger::getInstance()->debug('Language var ' . $varName . ' is not setted');
            return $default;
        } else
            return Tools::castValue((string) self::$_languageVars->$varName);
    }

    public static function getVars($cast = false) {
        if (!$cast)
            return self::$_languageVars;
        else {
            $vars = new \stdClass();
            foreach (self::$_languageVars as $name => $value) {
                $vars->{$name} = self::getVar($name);
            }

            return $vars;
        }
    }

    public static function countVars() {
        return count(self::$_languageVars);
    }

    public static function setVar($name, $value, $forceReplace = false) {
        if (!Validate::isVariableName($name))
            throw new \Exception('language var name must be a valid variable');

        if (method_exists(self::$_languageVars, $name) && !$forceReplace)
            throw new \Exception('language var already defined');

        //put on vars
        self::$_languageVars->$name = $value;
    }

    public function __set($name, $value) {
        return self::setVar($name, $value);
    }

    public function __get($name) {
        return self::getVar($name);
    }

    protected function __construct() {
        Logger::getInstance()->addGroup('language', 'Language informations', true, true);
    }

    public static function setDatasPath($datasPath) {
        if (!is_dir($datasPath))
            throw new \Exception('Directory "' . $datasPath . '" do not exists');
        if (!is_readable($datasPath))
            throw new \Exception('Directory "' . $datasPath . '" is not readable');

        self::$_datasPath = realpath($datasPath) . DS;
    }

    public static function getDatasPath() {
        return self::$_datasPath;
    }

    public function setLanguage($language, $setAsDefault = false) {
        if (!Validate::isLanguage($language))
            throw new \Exception('Invalid lang format');

        Logger::getInstance()->debug('Try load language : "' . $language . '"', 'language');
        //check datas files
        $file = self::getDatasPath() . $language . '.xml';
        if (!file_exists($file)) {
            if (!$setAsDefault) {
                Logger::getInstance()->debug('Invalid lang : "' . $language . '", have not xml datas file', 'language');
                return;
            }

            throw new \Exception('Invalid lang : "' . $language . '", have not xml datas file');
        }
        $xml = simplexml_load_file($file);
        if (is_null($xml) || !$xml)
            throw new \Exception('Invalid lang : "' . $language . '" invalid xml file');

        Logger::getInstance()->debug('Load datas file : "' . $file . '"', 'language');
        //delete comment
        unset($xml->comment);
        // set language
        self::$_languageVars = $xml;
        $this->_language = $language;
        if ($setAsDefault) {
            $this->_defaultLanguage = $this->_language;
            self::$_defaultLanguageVars = self::$_languageVars;
            Logger::getInstance()->debug('Language : "' . $this->_language . '" defined as default', 'language');
        }

        //Check if alls vars defined
        if ($this->_defaultLanguage != $this->_language) {
            foreach (self::$_defaultLanguageVars as $name => $value) {
                if (!property_exists(self::$_languageVars, $name)) {
                    Logger::getInstance()->debug('Miss language var : "' . $name . '" on new language : "' . $language . '"', 'language');
                    // restore var, by default language
                    self::$_languageVars->$name = self::$_defaultLanguageVars->$name;
                }
            }
        }

        Logger::getInstance()->debug('Current language is : "' . $this->_language . '"', 'language');
    }

    public function getLanguage() {
        return $this->_language;
    }

    public function getDefaultLanguage() {
        return $this->_defaultLanguage;
    }

}

?>