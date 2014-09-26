<?php

namespace framework\mvc\template;

interface IAdaptater {

    public function __construct($params);

    public function setName($name);

    public function getName();

    public function setPath($path);

    public function getPath();

    public function setCharset($charset);

    public function getCharset();

    public function setAssets($assets);

    public function getAssets();

    public function initAssets();

    public function __get($name);

    public function getVar($name, $default = null); //alias

    public function setVar($name, $value, $safeValue = false, $forceReplace = false);

    public function mergeVar($vars, $safeValue = false, $forceReplace = false);

    public function deleteVar($name);

    public function purgeVars();

    public function setFile($file);

    public function getFile();

    public function getFileContents($file = false, $parse = false);

    public function parse();

    public function display();

    public function getContent($autoParse = true);

    public function reset();

    public function getUrl($routeName, $vars = array(), $lang = null, $ssl = false);

    public function getUrls($lang = null, $ssl = false);

    public function getCurrentUrl();

    public function getRoute($routeName);

    public function getCurrentRoute();

    public function isCurrentRoute($routeName);

    public function getRoutes();

    public function getCurrentRule();

    public function isCurrentRule($rule);

    public function getUrlAsset($type, $ssl = false);

    public function getCss();

    public function getJs();

    public function setAutoSanitize($sanitize);

    public function getAutoSanitize();

    public function sanitize($value, $name);
}

?>
