<?php

namespace framework\mvc;

use framework\mvc\template\IAdaptater;
use framework\utility\Minify;
use framework\Logger;
use framework\network\Http;

abstract class Template {

    use \framework\pattern\Factory;

    const ASSET_JS = 'js';
    const ASSET_CSS = 'css';
    const ASSET_IMG = 'img';
    const ASSET_SOUND = 'sound';
    const ASSET_MODULE = 'module';
    const ASSET_FONT = 'font';

    protected static $_template = null;
    protected static $_templates = array();
    protected static $_assetsType = array(
        self::ASSET_JS,
        self::ASSET_CSS,
        self::ASSET_IMG,
        self::ASSET_SOUND,
        self::ASSET_MODULE,
        self::ASSET_FONT
    );
    protected $_css = '';
    protected $_js = '';
    protected $_autoSanitize = false;

    public static function addTemplate($name, IAdaptater $template, $forceReplace = false) {
        if (!is_string($name) && !is_int($name))
            throw new \Exception('Template name must be string or integer');

        if (array_key_exists($name, self::$_templates)) {
            if (!$forceReplace)
                throw new \Exception('Template : "' . $name . '" already defined');

            Logger::getInstance()->debug('Template : "' . $name . '" already defined, was overloaded');
        }

        self::$_templates[$name] = $template;
    }

    public static function getTemplate($templateName = null) {
        $templateName = is_null($templateName) ? self::$_template : $templateName;
        if (is_null($templateName))
            return false;
        if (array_key_exists($templateName, self::$_templates))
            return self::$_templates[$templateName];

        return false;
    }

    public static function getTemplates() {
        return self::$_templates;
    }

    public static function setTemplate($templateName) {
        if (!is_string($templateName))
            throw new \Exception('Template name must be a string');
        if (!array_key_exists($templateName, self::$_templates))
            throw new \Exception('Trying to set template : "' . $templateName . '", but isn\'t setted');

        self::$_template = $templateName;
    }

    public static function isValidAssetType($type) {
        if (!is_string($type))
            throw new \Exception('Asset type must be a string');

        return in_array($type, self::$_assetsType);
    }

    public function setAutoSanitize($sanitize) {
        if (!\is_bool($sanitize))
            throw new \Exception('Sanitize parameter must be a boolean');
        $this->_autoSanitize = $sanitize;
    }

    public function getAutoSanitize() {
        return $this->_autoSanitize;
    }

    public function sanitize($value, $name) {
        if (is_array($value)) {
            foreach ($value as &$v)
                $v = self::sanitize($v, $name);
        } elseif (is_object($value)) {
            $reflexion = new \ReflectionObject($value);
            $properties = $reflexion->getProperties(\ReflectionProperty::IS_PUBLIC);
            foreach ($properties as &$property)
                $value->{$property->name} = $this->sanitize($value->{$property->name}, $name);
        } elseif (is_string($value))
            $value = htmlspecialchars(htmlspecialchars_decode($value, ENT_QUOTES), ENT_QUOTES, $this->_charset);

        Logger::getInstance()->debug('Sanitize variable : "' . $name . '"', $this->_name);
        return $value;
    }

    public function initAssets() {
        Logger::getInstance()->debug('Initialize assets', 'template' . $this->_name);
        foreach ($this->_assets as $assetType => $assetDatas) {
            if (!isset($assetDatas['directory']))
                throw new \Exception('Miss asset : "' . $assetType . '" directory for template : "' . $this->_name . '"');

            //check directory
            if (!is_dir($assetDatas['directory']))
                throw new \Exception('Invalid asset : "' . $assetType . '" directory for template : "' . $this->_name . '"');

            //cache
            if ($assetType == self::ASSET_CSS || $assetType == self::ASSET_JS) {
                if (isset($assetDatas['cache'])) {
                    $compress = isset($assetDatas['cache']['compress']) ? $assetDatas['cache']['compress'] : false;
                    $rewriteUrls = isset($assetDatas['cache']['rewriteUrls']) ? $assetDatas['cache']['rewriteUrls'] : false;
                    $minify = new Minify($assetDatas['cache']['name'], $assetDatas['directory'], $assetType, $compress, $rewriteUrls, $this->_name);
                    if ($assetType == self::ASSET_CSS)
                        $this->_css = $minify->minify();
                    if ($assetType == self::ASSET_JS)
                        $this->_js = $minify->minify();
                }
            }


            //loadUrls and Langs into js
            if ($assetType == self::ASSET_JS) {
                if (isset($assetDatas['loadUrls'])) {
                    $this->_js .= 'var urls = {};';
                    foreach ($this->_vars->urls as $urlName => $urlValue)
                        $this->_js .= 'urls["' . $urlName . '"] = "' . $urlValue . '";';

                    //add img, css, js .. urls
                    if ($img = $this->getUrlAsset(self::ASSET_IMG, Http::isHttps()))
                        $this->_js .= 'urls["' . self::ASSET_IMG . '"] = "' . $img . '";';
                    if ($css = $this->getUrlAsset(self::ASSET_CSS, Http::isHttps()))
                        $this->_js .= 'urls["' . self::ASSET_CSS . '"] = "' . $css . '";';
                    if ($js = $this->getUrlAsset(self::ASSET_JS, Http::isHttps()))
                        $this->_js .= 'urls["' . self::ASSET_JS . '"] = "' . $js . '";';
                    if ($font = $this->getUrlAsset(self::ASSET_FONT, Http::isHttps()))
                        $this->_js .= 'urls["' . self::ASSET_FONT . '"] = "' . $font . '";';
                    if ($sound = $this->getUrlAsset(self::ASSET_SOUND, Http::isHttps()))
                        $this->_js .= 'urls["' . self::ASSET_SOUND . '"] = "' . $sound . '";';
                }

                if (isset($assetDatas['loadLangs'])) {
                    $this->_js .= 'var langs = {};';
                    foreach ($this->_vars->langs as $langName => $langValue)
                        $this->_js .= 'langs["' . $langName . '"] = "' . $langValue . '";';
                }
            }
            //add asset
            $this->_assets[$assetType] = $assetDatas;
        }
    }

}

?>