<?php

namespace framework;

use framework\url\Rewriting;
use framework\url\Shortener;
use framework\utility\Tools;
use framework\utility\Validate;

class Url {

    protected $_file = null;
    protected $_args = array();
    protected $_rewrite = false;
    protected $_rewriteOptions = array('keepUrlFileExt' => true, 'notPutArgsKey' => true, 'checkRewriteRule' => true, 'rewriteRuleFile' => false);
    protected $_url = null;
    protected $_charset = 'UTF-8';
    protected $_shortUrl = null;

    public function __construct($file = null, $args = array()) {
        if ($file != null) {
            if (!file_exists($file))
                throw new \Exception('Url file : "' . $file . '" don\'t exists');

            $this->_file = $file;
        }
        $this->addArgs($args);
    }

    public function setCharset($charset) {
        if (!Validate::isCharset($charset))
            throw new \Exception('Charset in\'t a valid charset type');

        $this->_charset = $charset;
    }

    public function getCharset() {
        return $this->_charset;
    }

    public function setRewrite($rewrite, $keepUrlFile = true, $keepUrlFileExt = true, $notPutArgsKey = true, $checkRewriteRule = true, $rewriteRuleFile = false) {
        if (!is_bool($rewrite))
            throw new \Exception('Rewrite parameter must be a boolean');
        $this->_rewrite = $rewrite;
        if ($this->_rewrite) {
            $this->_rewriteOptions = array(
                'keepUrlFile' => $keepUrlFile,
                'keepUrlFileExt' => $keepUrlFileExt,
                'notPutArgsKey' => $notPutArgsKey,
                'checkRewriteRule' => $checkRewriteRule,
                'rewriteRuleFile' => $rewriteRuleFile);
        }
    }

    public function getRewrite() {
        return $this->_rewrite;
    }

    public function getRewriteOptions() {
        return $this->_rewriteOptions;
    }

    public function addArgs($args) {
        if (!is_array($args))
            throw new \Exception('Arguments parameter must be an array');
        foreach ($args as $key => &$value)
            $this->addArg($key, $value);
    }

    public function addArg($argKey, $argValue) {
        if (!is_string($argKey) && !is_int($argKey))
            throw new \Exception('Argument key must be a string or int');
        if (!is_string($argValue) && !is_int($argValue))
            throw new \Exception('Argument value must be a string or int');

        if (array_key_exists($argKey, $this->_args))
            throw new \Exception('Argument : "' . $argKey . '" is already defined');

        $this->_args[] = array($argKey => $argValue);
    }

    public function getArgs() {
        $this->_args;
    }

    public function getUrl($forceGenerate = false, $flushUrlAfter = false, $flusUrlParameters = false) {
        if ($this->_url === null || $forceGenerate)
            $this->_generateUrl($flushUrlAfter, $flusUrlParameters);

        $url = $this->_url;
        if ($flushUrlAfter)
            $this->flushUrl();
        if ($flusUrlParameters)
            $this->flusUrlParameters();

        return $url;
    }

    public static function getShortenedUrl($longUrl, $shortenerName, $shortenerOpstions) {
        if (!is_array($shortenerOpstions))
            throw new \Exception('Options parameter must be an array');

        $identifiers = array();
        if (isset($shortenerOpstions['apiKey']))
            $identifiers['key'] = $shortenerOpstions['apiKey'];
        if (isset($shortenerOpstions['apiLogin']))
            $identifiers['login'] = $shortenerOpstions['apiLogin'];

        $shortener = Shortener::factory($shortenerName, $identifiers);
        return $shortener->shorten($longUrl);
    }

    public static function getExpandedUrl($shortUrl, $shortenerName, $shortenerOpstions) {
        if (!is_array($shortenerOpstions))
            throw new \Exception('Options parameter must be an array');

       if (isset($shortenerOpstions['apiKey']))
            $identifiers['key'] = $shortenerOpstions['apiKey'];
        if (isset($shortenerOpstions['apiLogin']))
            $identifiers['login'] = $shortenerOpstions['apiLogin'];

        $shortener = Shortener::factory($shortenerName, $identifiers);
        return $shortener->expand($shortUrl);
    }

    public function flushUrl() {
        $this->_url = null;
    }

    public function flusUrlParameters($file = true, $args = true) {
        if ($file)
            $this->_file = null;
        if ($args)
            $this->_args = array();
    }

    protected function _generateUrl($flushUrlAfter = false, $flusUrlParameters = false) {
        if ($this->_rewrite) {
            $url = new Rewriting($this->_rewriteOptions['rewriteRuleFile']);
            $url->setCharset($this->_charset);
            if ($this->_file != null && $this->_rewriteOptions['keepUrlFile'])
                $url->setUrlFile($this->_file, $this->_rewriteOptions['keepUrlFileExt']);

            foreach ($this->_args as &$arg)
                $url->setArgs($arg, $this->_rewriteOptions['notPutArgsKey'], false, $flushUrlAfter, $flusUrlParameters);

            $this->_url = $url->getUrl($this->_rewriteOptions['checkRewriteRule']);
        } else {
            $argsLink = (count($this->_args) > 0) ? '?' : '';
            foreach ($this->_args as &$arg) {
                foreach ($arg as $key => &$value)
                    $argsLink .= Tools::stringToUrl($key, '_', $this->_charset) . '=' . Tools::stringToUrl($value, '_', $this->_charset) . '&amp;';
            }

            $this->_url = ($this->_file != null) ? $this->_file . trim($argsLink, '&amp;') : trim($argsLink, '&amp;');
        }
    }

}

?>