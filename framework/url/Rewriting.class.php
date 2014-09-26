<?php

// TODO : add filter for valid argument
// TODO : add Debug mod

namespace framework\url;

use framework\utility\Tools;
use framework\utility\Validate;

class Rewriting {

    protected $_rewriteRuleFile = '';
    protected $_rules = array();
    protected $_file = null;
    protected $_args = array();
    protected $_notPutArgsKey = true;
    protected $_url = null;
    protected $_charset = 'UTF-8';
    protected $_extension = '';

    public function __construct($rewriteRuleFile = false) {
        if (function_exists('apache_get_modules') && !in_array('mod_rewrite', apache_get_modules()))
            throw new \Exception('mod_rewrite module must be activated');


        $rewriteRuleFile = $rewriteRuleFile ? $rewriteRuleFile : PATH_ROOT . '.htaccess';
        $this->setRewriteRuleFile($rewriteRuleFile);



        $fileRule = new \SplFileObject($this->_rewriteRuleFile, 'r');
        if ($fileRule->flock(LOCK_EX)) {
            foreach ($fileRule as $line) {
                if (strpos($line, 'RewriteRule') !== false && strpos($line, '#') === false)
                    $this->setRewriteRule($line);
            }
            $fileRule->flock(LOCK_UN);
        }
    }

    public function setUrlExtension($ext) {
        if (!is_string($ext))
            throw new \Exception('extension parameter must be a string');

        $this->_extension = $ext;
    }

    public function getUrlExtension() {
        return $this->_extension;
    }

    public function setCharset($charset) {
        if (!Validate::isCharset($charset))
            throw new \Exception('Charset in\'t a valid charset type');

        $this->_charset = $charset;
    }

    public function getCharset() {
        return $this->_charset;
    }

    public function setRewriteRuleFile($rewriteRuleFile) {
        if (!is_readable($rewriteRuleFile))
            throw new \Exception('Rewrite Rule File : "' . $rewriteRuleFile . '" don\'t exists or not readable');

        $this->_rewriteRuleFile = $rewriteRuleFile;
    }

    public function getRewriteRuleFile() {
        return $this->_rewriteRuleFile;
    }

    public function setRewriteRule($rewriteRule) {
        if (in_array($rewriteRule, $this->_rules))
            throw new \Exception('Rule : "' . $rewriteRule . '" is already defined');
        $this->_rules[] = $rewriteRule;
    }

    public function getRewriteRules() {
        return $this->_rules;
    }

    public function setUrlFile($file, $keepFileExt = true) {
        if (!file_exists($file))
            throw new \Exception('Url file : "' . $file . '" don\'t exists');

        $this->_file = $file;
        // delete file extension
        if (!$keepFileExt) {
            $this->_file = explode('.', $this->_file);
            $this->_file = implode('.', array_slice($this->_file, 0, count($this->_file) - 1));
        }
    }

    public function setArgs($args, $notPutArgsKey = false) {
        if (!is_array($args))
            throw new \Exception('Arguments parameter must be an array');
        if (!is_bool($notPutArgsKey))
            throw new \Exception('notPutArgsKey parameter must be a boolean');
        $this->_notPutArgsKey = $notPutArgsKey;
        foreach ($args as $key => &$value) {
            if (!is_string($key) && !is_int($key))
                throw new \Exception('Argument key must be a string or int');
            if (!is_string($value) && !is_int($value))
                throw new \Exception('Argument value must be a string or int');
            $this->_args [] = ($notPutArgsKey) ? $value : array($key => $value);
        }
    }

    public function getArgs() {
        return $this->_args;
    }

    public function getUrl($checkRewriteRule = true, $forceGenerate = false, $flushUrlAfter = false, $flusUrlParameters = false) {
        if ($this->_url === null || $forceGenerate)
            $this->_generateUrl();

        if (!is_bool($checkRewriteRule))
            throw new \Exception('checkRewriteRule parameter must be a boolean');
        if ($checkRewriteRule) {
            if (!$this->_urlHaveRewriteRule($this->_url))
                throw new \Exception('Url : "' . $this->_url . '" must be have a rewrite rule');
        }
        $url = $this->_url;
        if ($flushUrlAfter)
            $this->flushUrl();
        if ($flusUrlParameters)
            $this->flusUrlParameters();

        return $url;
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

    protected function _generateUrl() {
        $argsLink = '';
        foreach ($this->_args as &$arg) {
            if ($this->_notPutArgsKey) {
                $argsLink .= Tools::stringToUrl($arg, '_', $this->_charset) . '/';
            } else {
                foreach ($arg as $key => &$value)
                    $argsLink .= Tools::stringToUrl($key, '_', $this->_charset) . '/' . Tools::stringToUrl($value, '_', $this->_charset) . '/';
            }
        }
        if ($this->_file != null)
            $this->_url = $this->_file . '/' . trim($argsLink, '/') . $this->_extension;
        else
            $this->_url = trim($argsLink, '/') . $this->_extension;
    }

    protected function _urlHaveRewriteRule($url) {
        foreach ($this->_rules as &$rule) {
            if (preg_match('#' . Tools::selectStringByDelimiter($rule, '^', '$') . '#', $url))
                return true;
        }
        return false;
    }

}

?>