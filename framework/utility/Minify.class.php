<?php

namespace framework\utility;

use framework\Cache;
use framework\mvc\Template;
use framework\mvc\Router;
use framework\utility\Tools;
use framework\utility\Validate;
use framework\network\Http;
use JavaScriptPacker;

class Minify {

    const TYPE_CSS = 'css';
    const TYPE_JS = 'js';

    protected $_cache = null;
    protected $_compress = false;
    protected $_rewriteUrls = false;
    protected $_path = null;
    protected $_type = null;
    protected $_files = array();
    protected $_key = '';
    protected $_name = '';
    protected $_content = '';

    public function __construct($cacheName, $path, $type, $compress = true, $rewriteUrls = true, $name = '') {
        $this->setCache($cacheName);
        $this->setPath($path);
        $this->setType($type);
        if ($compress)
            $this->setCompress($compress);
        if ($rewriteUrls)
            $this->setRewriteUrls($rewriteUrls);
        $this->setName($name);
    }

    public function setCache($cacheName) {
        $cache = Cache::getCache($cacheName);
        if (!$cache)
            throw new \Exception('Cache : "' . $cacheName . '" is not a valid cache');
        $this->_cache = $cache;
        return $this;
    }

    public function getCache() {
        return $this->_cache;
    }

    public function setType($type) {
        if ($type != Template::ASSET_JS && $type != Template::ASSET_CSS)
            throw new \Exception('Invalid minifier type');
        $this->_type = $type;
    }

    public function getType() {
        return $this->_type;
    }

    public function setCompress($compress) {
        if (!is_bool($compress))
            throw new \Exception('Compress parameter must be a boolean');
        $this->_compress = $compress;
    }

    public function getCompress() {
        return $this->_compress;
    }

    public function setRewriteUrls($rewriteUrls) {
        if (!is_bool($rewriteUrls))
            throw new \Exception('rewriteUrls parameter must be a boolean');
        $this->_rewriteUrls = $rewriteUrls;
    }

    public function getRewriteUrls() {
        return $this->_rewriteUrls;
    }

    public function setPath($path) {
        if (!is_dir($path))
            throw new \Exception('Path ' . $path . ' don\'t exist');
        if (!is_readable($path))
            throw new \Exception('Path ' . $path . ' is not readable');
        $this->_path = realpath($path) . DS;
        return $this;
    }

    public function getPath() {
        return $this->_path;
    }

    public function setName($name) {
        if (!is_string($name))
            throw new \Exception('Compress parameter must be a string');
        $this->_name = $name;
    }

    public function getName() {
        return $this->_name;
    }

    public function addFile($file, $alreadyCompressed = false) {
        if (!file_exists($file) || !is_file($file))
            throw new \Exception('File ' . $file . ' don\'t exist');

        $this->_files[] = array(
            'name' => $file,
            'filemtime' => filemtime($file),
            'alreadyCompressed' => (bool) $alreadyCompressed
        );
    }

    public function minify($returnContent = true, $forceCacheUpdate = false) {
        // autoloading files
        foreach (Tools::cleanScandir($this->getPath()) as $file) {
            if (Validate::isFileExtension($this->_type, $file))
                $this->addFile($this->getPath() . $file);
        }

        $this->_key = md5($this->_name . Router::getHost(true, Http::isHttps()) . $this->getPath());
        if ($this->_cacheExpired() || $forceCacheUpdate)
            $this->_generateCache();

        if ($returnContent)
            return $this->getContent();
    }

    public function getContent() {
        return $this->_content;
    }

    protected function _cacheExpired() {
        $content = $this->_cache->read($this->_key . 'content' . $this->_type);
        if (is_null($content))
            return true;
        $filesList = $this->_cache->read($this->_key . 'filesList' . $this->_type);
        if (is_null($filesList) || md5(serialize($this->_files)) != $filesList)
            return true;

        $filemtime = $this->_cache->read($this->_key . 'filemtime' . $this->_type);
        if (is_null($filemtime))
            return true;
        foreach ($this->_files as $file) {
            if ($filemtime < $file['filemtime'])
                return true;
        }

        $this->_content = $content;
        return false;
    }

    protected function _generateCache() {
        $content = $this->_getContent();
        $this->_cache->write($this->_key . 'content' . $this->_type, $content, true);
        $this->_cache->write($this->_key . 'filesList' . $this->_type, md5(serialize($this->_files)), true);
        $this->_cache->write($this->_key . 'filemtime' . $this->_type, time(), true);

        $this->_content = $content;
    }

    protected function _getContent() {
        if ($this->_type == Template::ASSET_CSS) {
            $content = '';
            foreach ($this->_files as $file) {
                $f = file_get_contents($file['name']);
                if ($this->_compress && !$file['alreadyCompressed'])
                    $f = $this->_compressCss($f);
                $content .= $f;
            }
            //rewrite url path
            if ($this->getRewriteUrls())
                return preg_replace("#\[HOSTNAME]#", Router::getHost(true, Http::isHttps()), $content);

            return $content;
        } elseif ($this->_type == Template::ASSET_JS) {
            $notCompressed = $content = '';
            foreach ($this->_files as $file) {
                $js = file_get_contents($file['name']);
                if ($this->_compress && !$file['alreadyCompressed']) {
                    // Compress file with Javascript Packer plugin
                    $packer = new JavaScriptPacker($js);
                    $notCompressed .= trim($packer->pack());
                } else
                    $content .= $js;

                if (substr($notCompressed, -1) != ';')
                    $notCompressed .= ';';
            }
            //rewrite url path
            if ($this->getRewriteUrls())
                return preg_replace("#\[HOSTNAME]#", Router::getHost(true, Http::isHttps()), $content . $notCompressed);

            return $content . $notCompressed;
        }
    }

    protected function _compressCss($buffer) {
        $buffer = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer); // remove comments
        $buffer = str_replace(array("\r\n", "\r", "\n", "\t", '  '), '', $buffer); // remove tabs, spaces, newlines, etc.
        $buffer = str_replace('{ ', '{', $buffer); // remove unnecessary spaces.
        $buffer = str_replace(' }', '}', $buffer);
        $buffer = str_replace('; ', ';', $buffer);
        $buffer = str_replace(', ', ',', $buffer);
        $buffer = str_replace(' {', '{', $buffer);
        $buffer = str_replace('} ', '}', $buffer);
        $buffer = str_replace(': ', ':', $buffer);
        $buffer = str_replace(' ,', ',', $buffer);
        $buffer = str_replace(' ;', ';', $buffer);

        return $buffer;
    }

}

?>