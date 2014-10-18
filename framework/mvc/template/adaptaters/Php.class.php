<?php

namespace framework\mvc\template\adaptaters;

use framework\mvc\template\IAdaptater;
use framework\utility\Validate;
use framework\mvc\Template;
use framework\Logger;
use framework\mvc\Router;
use framework\network\Http;

class Php extends Template implements IAdaptater {

    protected $_name = null;
    protected $_path = null;
    protected $_file = null;
    protected $_assets = array();
    protected $_vars = null;
    protected $_charset = 'UTF-8';
    protected $_content = null;

    public function __construct($params) {
        if (!is_array($params))
            throw new \Exception('Params must be an array');

        if (!isset($params['name']))
            throw new \Exception('Miss param name');
        $this->setName($params['name']);
        if (!isset($params['path']))
            throw new \Exception('Miss param  path');
        $this->setPath($params['path']);
        if (isset($params['charset']))
            $this->setCharset($params['charset']);
        if (isset($params['autoSanitize']))
            $this->setAutoSanitize($params['autoSanitize']);
        if (isset($params['assets']))
            $this->setAssets($params['assets']);


        $this->_vars = new \stdClass();
        Logger::getInstance()->addGroup('template' . $this->_name, 'Template ' . $this->_name, true, true);
    }

    public function __get($name) {
        return $this->getVar($name);
    }

    public function __isset($name) {
        return \property_exists($this->_vars, $name);
    }

    public function setName($name) {
        $this->_name = $name;
    }

    public function getName() {
        return $this->_name;
    }

    public function setPath($path) {
        if (!is_dir($path))
            throw new \Exception('Template path ' . $path . ' don\'t exist');
        if (!is_readable($path))
            throw new \Exception('Path ' . $path . ' is not readable');
        $this->_path = realpath($path) . DS;
    }

    public function getPath() {
        return $this->_path;
    }

    public function setCharset($charset) {
        if (!Validate::isCharset($charset))
            throw new \Exception('Charset isn\'t a valid charset type for template : "' . $this->_name . '"');
        $this->charset = $charset;
    }

    public function getCharset() {
        return $this->charset;
    }

    public function setAssets($assets) {
        if (!is_array($assets))
            throw new \Exception('Assets must be an array');
        $this->_assets = $assets;
    }

    public function getAssets() {
        return $this->_assets;
    }

    public function getVar($name, $default = null) {
        if (property_exists($this->_vars, $name))
            return $this->_vars->$name;
        return $default;
    }

    public function setVar($name, $value, $safeValue = false, $forceReplace = false) {
        if (!Validate::isVariableName($name))
            throw new \Exception('Name of variable must be a valid variable name');
        if (!$forceReplace && property_exists($this->_vars, $name))
            throw new \Exception('Variable "' . $name . '" already defined in template');
        $this->_vars->{$name} = $safeValue || !$this->getAutoSanitize() ? $value : $this->sanitize($value, $name);

        Logger::getInstance()->debug('Add var : "' . $name . '"', 'template' . $this->_name);
        return $this;
    }

    public function mergeVar($vars, $safeValue = false, $forceReplace = false) {
        $table = $vars;
        if (is_object($table))
            $table = (array) $table;
        if (!is_array($vars))
            throw new \Exception('Parameter for merge must be an array or an object');
        foreach ($table as $key => &$value)
            $this->set($key, $value, $safeValue, $forceReplace);
        return $this;
    }

    public function deleteVar($name) {
        if (!Validate::isVariableName($name))
            throw new \Exception('Name of variable must be a valid variable name');
        if (!property_exists($this->_vars, $name))
            throw new \Exception('Variable : "' . $name . '" don\'t exists');
        unset($this->_vars->{$name});
    }

    public function purgeVars() {
        $this->_vars = new \stdClass();
    }

    public function setFile($file) {
        if (!file_exists($this->_path . $file))
            throw new \Exception('Template : "' . $this->_path . $file . '" no exists');
        if (!is_readable($this->_path . $file))
            throw new \Exception('Template : "' . $this->_path . $file . '" isn\'t a readable');

        Logger::getInstance()->debug('Set file : "' . $file . '"', 'template' . $this->_name);
        $this->_file = $file;

        return $this;
    }

    public function getFile() {
        return $this->_file;
    }

    public function getFileContents($file = false, $parse = false) {
        $file = (!$file) ? $this->_file : $file;
        if (!is_file($this->_path . $file) && !is_readable($this->_path . $file))
            throw new \Exception('Template contents invalid, not readable file setted');
        ob_start();
        require $this->_path . $file;
        $contents = $parse ? $this->parse() : ob_get_contents();
        ob_end_clean();
        return $contents;
    }

    public function parse() {
        ob_start();
        try {
            if (!is_null($this->_file)) {
                include($this->_path . $this->_file);
                $this->_content = ob_get_clean();
                Logger::getInstance()->debug('Parse template file', 'template' . $this->_name);
            }
        } catch (\Exception $e) {
            ob_end_clean();
            throw $e;
        }
    }

    public function display() {
        echo $this->getContent();
    }

    public function getContent($autoParse = true) {
        if ($this->_content === null && $autoParse)
            $this->parse();

        return $this->_content;
    }

    public function reset() {
        $this->_content = null;
        $this->purgeVars();
    }

    public function getUrl($routeName, $vars = array(), $lang = null, $ssl = false) {
        if (Http::isHttps())
            $ssl = true;
        return Router::getUrl($routeName, $vars, $lang, $ssl);
    }

    public function getUrls($lang = null, $ssl = false) {
        return Router::getUrls($lang, $ssl);
    }

    public function getCurrentUrl() {
        return Http::getCurrentUrl();
    }

    public function getRoute($routeName) {
        return Router::getRoute($routeName);
    }

    public function getCurrentRoute() {
        return Router::getInstance()->getCurrentRoute();
    }

    public function isCurrentRoute($routeName) {
        return Router::getInstance()->getCurrentRoute() == $routeName;
    }

    public function getRoutes() {
        return Router::getRoutes();
    }

    public function getCurrentRule() {
        return Router::getCurrentRule();
    }

    public function isCurrentRule($rule) {
        if (!is_string($rule))
            throw new \Exception('Rule must be a string');

        return stripos(self::getCurrentRule(), $rule) !== false;
    }

    public function getUrlAsset($type, $ssl = false) {
        if (!is_string($type))
            throw new \Exception('Asset type must be a string');
        if (Http::isHttps())
            $ssl = true;

        if (!is_array($this->_assets))
            return false;
        if (!array_key_exists($type, $this->_assets))
            return false;

        $asset = $this->_assets[$type];
        return Router::getHost(true, $ssl) . str_replace(DS, '/', str_replace(PATH_ROOT, '', $asset['directory']));
    }

    public function getCss() {
        return $this->_css;
    }

    public function getJs() {
        return $this->_js;
    }

}

?>
